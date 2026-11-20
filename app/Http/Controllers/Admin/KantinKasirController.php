<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use App\Models\KantinTransaksiItem;
use App\Models\Santri;
use App\Models\UangSaku;
use App\Services\AdminActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KantinKasirController extends Controller
{
    public function index(Request $request)
    {
        $admin        = auth('admin')->user();
        $allowedTokos = $admin->getAllowedTokos();

        // Kasir satu toko: paksa ke toko tersebut, abaikan parameter ?toko=
        if (count($allowedTokos) === 1) {
            $currentToko = $allowedTokos[0];
        } else {
            $currentToko = $request->get('toko', 'aqumart');
            if (!in_array($currentToko, $allowedTokos)) {
                $currentToko = $allowedTokos[0];
            }
        }

        $produks = KantinProduk::active()
            ->where('toko', $currentToko)
            ->where('stok', '>', 0)
            ->orderBy('nama_produk')
            ->get();

        $recentTransactions = KantinTransaksi::with(['santri', 'items'])
            ->where('toko', $currentToko)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.kantin.kasir.index', compact('currentToko', 'produks', 'recentTransactions', 'allowedTokos'));
    }


    public function searchSantri(Request $request)
    {
        $query = trim($request->get('q', ''));
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $santris = Santri::with('orangTua')
            ->where('status', 'diterima')
            ->where('status_santri', 'aktif')
            ->where(function ($q) use ($query) {
                $q->where('nama_lengkap', 'like', "%{$query}%")
                  ->orWhere('nis', 'like', "%{$query}%")
                  ->orWhere('nisn', 'like', "%{$query}%");
            })
            ->take(15)
            ->get()
            ->map(function ($s) {
                return [
                    'id'               => $s->id,
                    'nama_lengkap'     => $s->nama_lengkap,
                    'nis'              => $s->nis ?? '-',
                    'nisn'             => $s->nisn ?? '-',
                    'kelas'            => $s->kelas_label,
                    'kelas_raw'        => $s->kelas,
                    'saldo'            => (float) $s->saldo_uang_saku,
                    'saldo_formatted'  => 'Rp ' . number_format($s->saldo_uang_saku, 0, ',', '.'),
                    'foto_url'         => $s->foto_url,
                    'nama_wali'        => $s->orangTua?->nama_ayah ?? '-',
                    'no_wa_wali'       => $s->orangTua?->no_wa_ayah ?? '-',
                ];
            });

        return response()->json($santris);
    }

    public function checkout(Request $request)
    {
        $admin = auth('admin')->user();
        if ($admin && method_exists($admin, 'getAllowedTokos')) {
            $allowedTokos = $admin->getAllowedTokos();
            if ($request->filled('toko') && !in_array($request->toko, $allowedTokos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk melakukan transaksi di toko ini.',
                ], 403);
            }
        }

        $request->validate([
            'santri_id' => ['required', 'exists:santri,id'],
            'toko'      => ['required', 'in:kantin_1,kantin_2,aqumart'],
            'items'     => ['required', 'array', 'min:1'],
            'items.*.produk_id' => ['required', 'exists:kantin_produks,id'],
            'items.*.qty'       => ['required', 'integer', 'min:1'],
        ]);

        $toko = $request->toko;
        $itemsData = $request->items;

        DB::beginTransaction();
        try {
            $santri = Santri::with('orangTua')->lockForUpdate()->findOrFail($request->santri_id);

            $totalHarga = 0;
            $totalModal = 0;
            $validatedItems = [];

            // Validasi produk dan stok dengan row lock
            foreach ($itemsData as $item) {
                $produk = KantinProduk::lockForUpdate()->find($item['produk_id']);
                if (!$produk || !$produk->is_active) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Produk tidak aktif atau tidak ditemukan."], 422);
                }

                $qty = (int) $item['qty'];
                if ($produk->stok < $qty) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Stok produk '{$produk->nama_produk}' tidak mencukupi (Tersisa {$produk->stok})."], 422);
                }

                $subtotalHarga = $produk->harga_jual * $qty;
                $subtotalModal = $produk->harga_modal * $qty;

                $totalHarga += $subtotalHarga;
                $totalModal += $subtotalModal;

                $validatedItems[] = [
                    'produk'         => $produk,
                    'qty'            => $qty,
                    'subtotal_harga' => $subtotalHarga,
                    'subtotal_modal' => $subtotalModal,
                ];
            }

            // Cek saldo uang saku santri di bawah lock
            $currentSaldo = (float) $santri->saldo_uang_saku;
            if ($currentSaldo < $totalHarga) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Saldo uang saku santri tidak mencukupi! (Saldo: Rp " . number_format($currentSaldo, 0, ',', '.') . ", Tagihan: Rp " . number_format($totalHarga, 0, ',', '.') . ")",
                ], 422);
            }

            $noTransaksi = KantinTransaksi::generateNoTransaksi($toko);
            $labaBersih = $totalHarga - $totalModal;

            // 1. Simpan Transaksi
            $transaksi = KantinTransaksi::create([
                'no_transaksi'       => $noTransaksi,
                'toko'               => $toko,
                'santri_id'          => $santri->id,
                'kasir_id'           => auth('admin')->id(),
                'total_modal'        => $totalModal,
                'total_harga'        => $totalHarga,
                'laba_bersih'        => $labaBersih,
                'metode_pembayaran'  => 'uang_saku',
                'catatan'            => $request->catatan,
            ]);

            // 2. Simpan Item Transaksi & Potong Stok
            foreach ($validatedItems as $vItem) {
                KantinTransaksiItem::create([
                    'transaksi_id'   => $transaksi->id,
                    'produk_id'      => $vItem['produk']->id,
                    'nama_produk'    => $vItem['produk']->nama_produk,
                    'harga_modal'    => $vItem['produk']->harga_modal,
                    'harga_jual'     => $vItem['produk']->harga_jual,
                    'qty'            => $vItem['qty'],
                    'subtotal_modal' => $vItem['subtotal_modal'],
                    'subtotal_harga' => $vItem['subtotal_harga'],
                ]);

                // Kurangi stok
                $vItem['produk']->decrement('stok', $vItem['qty']);
            }

            // 3. Potong Saldo Uang Saku Santri
            $tokoLabel = $transaksi->toko_label;
            UangSaku::create([
                'santri_id'    => $santri->id,
                'jenis'        => 'keluar',
                'jumlah'       => $totalHarga,
                'saldo_akhir'  => max(0, $currentSaldo - $totalHarga),
                'keterangan'   => "Belanja {$tokoLabel} (#{$noTransaksi})",
                'kelas'        => $santri->kelas,
                'tahun_ajaran' => $santri->tahun_ajaran,
                'admin_id'     => auth('admin')->id(),
            ]);

            $sisaSaldo = UangSaku::recalculateBalance($santri->id);

            AdminActivityLogger::log(
                'kasir_transaksi',
                "Transaksi kasir {$tokoLabel} #{$noTransaksi} santri '{$santri->nama_lengkap}' sebesar Rp " . number_format($totalHarga, 0, ',', '.'),
                'KantinTransaksi',
                $transaksi->id
            );

            DB::commit();

            // 4. Kirim Notifikasi WhatsApp Otomatis ke Orang Tua
            try {
                $noWaOrtu = $santri->orangTua?->no_wa_ayah;
                if ($noWaOrtu) {
                    $itemSummary = collect($validatedItems)->map(function ($it) {
                        return "- {$it['produk']->nama_produk} ({$it['qty']}x)";
                    })->implode("\n");

                    $pesan = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
                           . "Pemberitahuan transaksi santri:\n"
                           . "👤 Santri: *{$santri->nama_lengkap}* (Kelas {$santri->kelas})\n"
                           . "🏪 Lokasi: *{$tokoLabel}*\n"
                           . "🧾 No. Nota: #{$noTransaksi}\n"
                           . "🛍️ Rincian Belanja:\n{$itemSummary}\n\n"
                           . "💵 Total Belanja: *Rp " . number_format($totalHarga, 0, ',', '.') . "*\n"
                           . "💰 Sisa Saldo Uang Saku: *Rp " . number_format($sisaSaldo, 0, ',', '.') . "*\n\n"
                           . "Waktu: " . date('d M Y H:i') . " WIB\n\n"
                           . "_Pesan otomatis dari Sistem Pesantren Tahfizh Alam Qur'an._";

                    NotificationService::sendKantin(
                        $santri,
                        $tokoLabel,
                        $itemSummary,
                        (float) $totalHarga,
                        (float) ($sisaSaldo + $totalHarga),
                        (float) $sisaSaldo,
                        (string) $transaksi->id
                    );
                }
            } catch (\Throwable $e) {
                \Log::warning('Gagal kirim notif WA kasir kantin: ' . $e->getMessage());
            }

            return response()->json([
                'success'      => true,
                'message'      => "Transaksi berhasil! Saldo uang saku terpotong Rp " . number_format($totalHarga, 0, ',', '.'),
                'no_transaksi' => $noTransaksi,
                'sisa_saldo'   => $sisaSaldo,
                'sisa_saldo_formatted' => 'Rp ' . number_format($sisaSaldo, 0, ',', '.'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}
