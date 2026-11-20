<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\SppPembayaran;
use App\Models\SppSetting;
use App\Models\SppTagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserSppController extends Controller
{
    public function index(Request $request)
    {
        $santris = Santri::where('user_id', auth()->id())
            ->where('status', 'diterima')
            ->get();

        $santriIds = $santris->pluck('id');

        $selectedSantriId = $request->get('santri_id', $santriIds->first());

        $tagihans = SppTagihan::with(['pembayarans', 'addons'])
            ->where('santri_id', $selectedSantriId)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        $activeSantri = $santris->firstWhere('id', $selectedSantriId);

        $sppSetting = SppSetting::getActive();
        $biayaAdmin = $sppSetting?->biaya_admin ? (float) $sppSetting->biaya_admin : 2500.0;

        // Semua tagihan yang belum lunas (diurutkan kronologis dari bulan terlama)
        $unpaidTagihans = $tagihans
            ->where('status', '!=', 'lunas')
            ->where('sisa_tagihan', '>', 0)
            ->sortBy(fn($t) => ($t->tahun * 100) + $t->bulan)
            ->values();

        // Total tagihan keseluruhan (pokok + add-on semua bulan yang belum lunas)
        $totalTagihanKeseluruhan = (float) $unpaidTagihans->sum('sisa_tagihan');
        // Biaya admin cukup 1 kali transaksi (tidak dikali per bulan)
        $totalHarusDibayar = $totalTagihanKeseluruhan > 0 ? ($totalTagihanKeseluruhan + $biayaAdmin) : 0.0;

        $hasPendingKonfirmasi = $unpaidTagihans->contains(function ($t) {
            return $t->pembayarans->where('status', 'menunggu_konfirmasi')->isNotEmpty();
        });

        $pendingPembayarans = SppPembayaran::where('santri_id', $activeSantri?->id)
            ->where('status', 'menunggu_konfirmasi')
            ->with('tagihan')
            ->get();

        $totalPendingNominal = (float) $pendingPembayarans->sum('jumlah_bayar');
        $pendingBulanLabels = $pendingPembayarans->map(fn($p) => $p->tagihan?->bulan_label)->filter()->unique()->implode(', ');

        return view('user.spp.index', compact(
            'santris',
            'tagihans',
            'activeSantri',
            'selectedSantriId',
            'sppSetting',
            'biayaAdmin',
            'unpaidTagihans',
            'totalTagihanKeseluruhan',
            'totalHarusDibayar',
            'hasPendingKonfirmasi',
            'pendingPembayarans',
            'totalPendingNominal',
            'pendingBulanLabels'
        ));
    }

    /**
     * Pembayaran Lunas Keseluruhan Tagihan (atau Pembayaran Multi-Bulan 3 Bulan / Lebih sekaligus)
     * Tidak bisa mengangsur, dan biaya admin hanya 1 kali per transaksi.
     */
    public function konfirmasiBayarSemua(Request $request)
    {
        $validated = $request->validate([
            'santri_id'    => ['required', 'exists:santri,id'],
            'mode_bayar'   => ['nullable', 'in:semua,multi_bulan'],
            'tahun_multi'  => ['nullable', 'integer', 'between:2020,2099'],
            'bulan_list'   => ['nullable', 'array'],
            'bulan_list.*' => ['integer', 'between:1,12'],
            'jumlah_bayar' => ['required', 'numeric', 'min:1000'],
            'metode_bayar' => ['required', 'string', 'in:transfer'],
            'bukti_bayar'  => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'catatan'      => ['nullable', 'string', 'max:500'],
        ], [
            'jumlah_bayar.required' => 'Nominal pembayaran wajib diisi.',
            'bukti_bayar.required'  => 'Bukti transfer/pembayaran wajib diunggah.',
            'bukti_bayar.mimes'     => 'Bukti transfer harus berupa JPG, JPEG, PNG, atau PDF.',
            'bukti_bayar.max'       => 'Ukuran bukti transfer maksimal 5 MB.',
        ]);

        $santri = Santri::where('user_id', auth()->id())
            ->where('status', 'diterima')
            ->findOrFail($validated['santri_id']);

        $sppSetting = SppSetting::getActive();
        $biayaAdmin = $sppSetting?->biaya_admin ? (float) $sppSetting->biaya_admin : 2500.0;

        // Jika wali memilih pembayaran multi-bulan (misal 3 bulan atau lebih ke depan lewat aplikasi),
        // pastikan record tagihan untuk bulan-bulan yang dipilih tersebut dibuat otomatis jika belum ada.
        if (($validated['mode_bayar'] ?? 'semua') === 'multi_bulan' && !empty($validated['bulan_list'])) {
            $tahunMulti = (int) ($validated['tahun_multi'] ?? date('Y'));
            $nominalTarif = (float) $santri->tarif_spp;
            $rekeningSantri = $santri->nomor_rekening_spp ?: null;

            foreach ($validated['bulan_list'] as $bln) {
                $blnInt = (int) $bln;
                $namaBulan = SppTagihan::getNamaBulan($blnInt);
                SppTagihan::firstOrCreate(
                    [
                        'santri_id' => $santri->id,
                        'bulan'     => $blnInt,
                        'tahun'     => $tahunMulti,
                    ],
                    [
                        'nominal_spp'      => $nominalTarif,
                        'nominal_addon'    => 0,
                        'nominal_tagihan'  => $nominalTarif,
                        'nominal_terbayar' => 0,
                        'sisa_tagihan'     => $nominalTarif,
                        'status'           => $nominalTarif == 0.0 ? 'lunas' : 'belum_lunas',
                        'nomor_va'         => $rekeningSantri,
                        'keterangan'       => "SPP {$namaBulan} {$tahunMulti}",
                    ]
                );
            }
        }

        // Ambil seluruh tagihan yang belum lunas milik santri ini
        $unpaidTagihans = SppTagihan::with(['pembayarans', 'addons'])
            ->where('santri_id', $santri->id)
            ->where('status', '!=', 'lunas')
            ->where('sisa_tagihan', '>', 0)
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get();

        if ($unpaidTagihans->isEmpty()) {
            return back()->with('error', 'Seluruh tagihan SPP untuk santri ini sudah lunas.');
        }

        foreach ($unpaidTagihans as $t) {
            if ($t->pembayarans()->where('status', 'menunggu_konfirmasi')->exists()) {
                return back()->with('error', "Bukti pembayaran untuk periode {$t->bulan_label} masih menunggu verifikasi bendahara.");
            }
        }

        $totalPokokKeseluruhan = (float) $unpaidTagihans->sum('sisa_tagihan');
        // Biaya admin cukup 1 kali transaksi (tidak sesuai jumlah bulan)
        $totalWajibBayar = $totalPokokKeseluruhan + $biayaAdmin;

        // Validasi: TIDAK BISA MENGANGSUR (harus bayar lunas sesuai total keseluruhan)
        $jumlahDitransfer = (float) $validated['jumlah_bayar'];
        if (abs($jumlahDitransfer - $totalWajibBayar) > 1 && abs($jumlahDitransfer - $totalPokokKeseluruhan) > 1) {
            return back()->with('error', 'Pembayaran wajib dilakukan secara LUNAS sesuai Total Tagihan Keseluruhan + 1x Biaya Admin (Rp ' . number_format($totalWajibBayar, 0, ',', '.') . ') dan tidak dapat diangsur.');
        }

        $path = $request->file('bukti_bayar')->store('bukti-spp', 'public');
        if (!$path || !Storage::disk('public')->exists($path)) {
            return back()->with('error', 'Gagal menyimpan file bukti transfer. Silakan coba kembali.');
        }

        $publicDest = public_path('storage/' . $path);
        $publicDir = dirname($publicDest);
        if (!file_exists($publicDir)) {
            @mkdir($publicDir, 0755, true);
        }
        if (!file_exists($publicDest)) {
            @copy(storage_path('app/public/' . $path), $publicDest);
        }

        $periodeLabels = $unpaidTagihans->pluck('bulan_label')->implode(', ');
        $jumlahBulan = $unpaidTagihans->count();
        $catatanUser = trim($validated['catatan'] ?? '');
        $catatanLengkap = ($catatanUser ? "{$catatanUser} | " : '')
            . "Pelunasan {$jumlahBulan} Bulan ({$periodeLabels}) - Total Transfer: Rp " . number_format($totalWajibBayar, 0, ',', '.')
            . " (Termasuk 1x Biaya Admin Rp " . number_format($biayaAdmin, 0, ',', '.') . ")";

        $firstPembayaran = null;

        DB::transaction(function () use ($unpaidTagihans, $santri, $path, $validated, $catatanLengkap, &$firstPembayaran) {
            foreach ($unpaidTagihans as $tagihan) {
                $pemb = $tagihan->pembayarans()->create([
                    'santri_id'            => $santri->id,
                    'submitted_by_user_id' => auth()->id(),
                    'jumlah_bayar'         => (float) $tagihan->sisa_tagihan,
                    'metode_bayar'         => $validated['metode_bayar'],
                    'bukti_bayar'          => $path,
                    'catatan'              => $catatanLengkap,
                    'status'               => 'menunggu_konfirmasi',
                ]);
                if (!$firstPembayaran) {
                    $firstPembayaran = $pemb;
                }
            }
        });

        $user = auth()->user();
        $noWa = $santri->orangTua?->no_wa_ayah ?: $user?->no_wa;

        // 1. Notifikasi konfirmasi ke Wali Santri
        if ($noWa && $firstPembayaran) {
            try {
                \App\Services\NotificationService::send([
                    'recipient_number' => $noWa,
                    'recipient_name'   => $santri->orangTua?->nama_ayah ?: $user?->name,
                    'user_id'          => $user?->id,
                    'santri_id'        => $santri->id,
                    'modul'            => 'SPP',
                    'priority'         => 'normal',
                    'reference_id'     => 'SPP-UPLOAD-ALL-' . $firstPembayaran->id,
                    'message'          => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n" .
                        "Bukti pembayaran LUNAS SPP ananda *{$santri->nama_lengkap}* untuk *{$jumlahBulan} bulan ({$periodeLabels})* dengan total transfer *Rp " . number_format($jumlahDitransfer, 0, ',', '.') . "* (sudah termasuk 1x biaya admin Rp " . number_format($biayaAdmin, 0, ',', '.') . ") telah kami terima dan sedang menunggu verifikasi bendahara.\n\n" .
                        "Status seluruh bulan tersebut akan otomatis menjadi LUNAS setelah diverifikasi bendahara. Terima kasih.\n\n" .
                        "_Pesantren Tahfizh Alam Qur'an_",
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notif upload bukti SPP ke wali: ' . $e->getMessage());
            }
        }

        // 2. Notifikasi otomatis ke nomor WA Bendahara
        if ($sppSetting && !empty($sppSetting->no_wa_bendahara) && $firstPembayaran) {
            try {
                \App\Services\NotificationService::sendSppKonfirmasiKeBendahara(
                    $santri,
                    $unpaidTagihans->first(),
                    $firstPembayaran,
                    $sppSetting->no_wa_bendahara
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notif konfirmasi SPP ke Bendahara: ' . $e->getMessage());
            }
        }

        return redirect()->route('user.spp.index', ['santri_id' => $santri->id])
            ->with('success', "Bukti pembayaran lunas untuk {$jumlahBulan} bulan ({$periodeLabels}) sebesar Rp " . number_format($totalWajibBayar, 0, ',', '.') . " berhasil dikirimkan dan sedang menunggu validasi bendahara.");
    }

    public function konfirmasiBayar(Request $request, $id)
    {
        $validated = $request->validate([
            'jumlah_bayar' => ['required', 'numeric', 'min:1000'],
            'metode_bayar' => ['required', 'string', 'in:transfer'],
            'bukti_bayar'  => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'catatan'      => ['nullable', 'string', 'max:500'],
        ], [
            'jumlah_bayar.required' => 'Nominal pembayaran wajib diisi.',
            'bukti_bayar.required'  => 'Bukti transfer/pembayaran wajib diunggah.',
            'bukti_bayar.mimes'     => 'Bukti transfer harus berupa JPG, JPEG, PNG, atau PDF.',
            'bukti_bayar.max'       => 'Ukuran bukti transfer maksimal 5 MB.',
        ]);

        $tagihan = SppTagihan::with(['santri.orangTua', 'addons', 'pembayarans'])->whereHas('santri', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        $santri = $tagihan->santri;

        if ($tagihan->status === 'lunas') {
            return back()->with('error', "Tagihan SPP periode {$tagihan->bulan_label} sudah lunas.");
        }

        if ($tagihan->pembayarans()->where('status', 'menunggu_konfirmasi')->exists()) {
            return back()->with('error', "Bukti pembayaran untuk periode {$tagihan->bulan_label} masih menunggu verifikasi bendahara.");
        }

        $sppSetting = SppSetting::getActive();
        $biayaAdmin = $sppSetting?->biaya_admin ? (float) $sppSetting->biaya_admin : 2500.0;
        $sisaPokok = (float) $tagihan->sisa_tagihan;
        $totalWajibBayar = $sisaPokok + $biayaAdmin;

        $jumlahDitransfer = (float) $validated['jumlah_bayar'];
        if ($jumlahDitransfer < 1000) {
            return back()->with('error', "Nominal pembayaran minimal Rp 1.000.");
        }
        if ($jumlahDitransfer > ($totalWajibBayar + 10)) {
            return back()->with('error', "Nominal transfer melebihi total tagihan bulan ini (Maksimal Rp " . number_format($totalWajibBayar, 0, ',', '.') . ").");
        }

        $path = $request->file('bukti_bayar')->store('bukti-spp', 'public');
        if (!$path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return back()->with('error', 'Gagal menyimpan file bukti transfer. Silakan coba kembali.');
        }

        $publicDest = public_path('storage/' . $path);
        $publicDir = dirname($publicDest);
        if (!file_exists($publicDir)) {
            @mkdir($publicDir, 0755, true);
        }
        if (!file_exists($publicDest)) {
            @copy(storage_path('app/public/' . $path), $publicDest);
        }

        $isLunasPenuh = (abs($jumlahDitransfer - $totalWajibBayar) <= 1 || $jumlahDitransfer >= $totalWajibBayar);
        $jenisBayar = $isLunasPenuh ? 'Pelunasan Penuh' : 'Cicilan / Angsuran';

        $catatanUser = trim($validated['catatan'] ?? '');
        $catatanLengkap = ($catatanUser ? "{$catatanUser} | " : '')
            . "{$jenisBayar} SPP {$tagihan->bulan_label} - Nominal Transfer: Rp " . number_format($jumlahDitransfer, 0, ',', '.')
            . " (Termasuk 1x Biaya Admin Rp " . number_format($biayaAdmin, 0, ',', '.') . ")";

        $pembayaran = $tagihan->pembayarans()->create([
            'santri_id'            => $santri->id,
            'submitted_by_user_id' => auth()->id(),
            'jumlah_bayar'         => $jumlahDitransfer,
            'metode_bayar'         => $validated['metode_bayar'],
            'bukti_bayar'          => $path,
            'catatan'              => $catatanLengkap,
            'status'               => 'menunggu_konfirmasi',
        ]);

        $user = auth()->user();
        $noWa = $santri->orangTua?->no_wa_ayah ?: $user?->no_wa;

        if ($noWa && $pembayaran) {
            try {
                if ($isLunasPenuh) {
                    $pesanKonfirmasi = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n" .
                        "Bukti pembayaran LUNAS SPP ananda *{$santri->nama_lengkap}* untuk periode *{$tagihan->bulan_label}* dengan total transfer *Rp " . number_format($jumlahDitransfer, 0, ',', '.') . "* (sudah termasuk biaya admin Rp " . number_format($biayaAdmin, 0, ',', '.') . ") telah kami terima dan sedang menunggu verifikasi bendahara.\n\n" .
                        "Status tagihan akan otomatis menjadi LUNAS setelah diverifikasi bendahara. Terima kasih.\n\n" .
                        "_Pesantren Tahfizh Alam Qur'an_";
                } else {
                    $pesanKonfirmasi = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n" .
                        "Bukti pembayaran cicilan/angsuran SPP ananda *{$santri->nama_lengkap}* untuk periode *{$tagihan->bulan_label}* dengan nominal transfer *Rp " . number_format($jumlahDitransfer, 0, ',', '.') . "* telah kami terima dan sedang menunggu verifikasi bendahara.\n\n" .
                        "Pembayaran akan dicatatkan pada tagihan setelah diverifikasi bendahara. Terima kasih.\n\n" .
                        "_Pesantren Tahfizh Alam Qur'an_";
                }

                \App\Services\NotificationService::send([
                    'recipient_number' => $noWa,
                    'recipient_name'   => $santri->orangTua?->nama_ayah ?: $user?->name,
                    'user_id'          => $user?->id,
                    'santri_id'        => $santri->id,
                    'modul'            => 'SPP',
                    'priority'         => 'normal',
                    'reference_id'     => 'SPP-UPLOAD-' . $pembayaran->id,
                    'message'          => $pesanKonfirmasi,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notif upload bukti SPP single ke wali: ' . $e->getMessage());
            }
        }

        if ($sppSetting && !empty($sppSetting->no_wa_bendahara) && $pembayaran) {
            try {
                \App\Services\NotificationService::sendSppKonfirmasiKeBendahara(
                    $santri,
                    $tagihan,
                    $pembayaran,
                    $sppSetting->no_wa_bendahara
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notif konfirmasi SPP single ke Bendahara: ' . $e->getMessage());
            }
        }

        return redirect()->route('user.spp.index', ['santri_id' => $santri->id])
            ->with('success', "Bukti pembayaran lunas untuk periode {$tagihan->bulan_label} sebesar Rp " . number_format($totalWajibBayar, 0, ',', '.') . " berhasil dikirimkan dan sedang menunggu validasi bendahara.");
    }

    public function cetakInvoice($id)
    {
        $tagihan = SppTagihan::whereHas('santri', function ($q) {
            $q->where('user_id', auth()->id());
        })->with(['santri.orangTua', 'addons', 'pembayarans.verifiedBy'])->findOrFail($id);

        $setting = \App\Models\InvoiceSetting::getSetting();
        $fileName = 'Invoice_SPP_' . \Illuminate\Support\Str::slug($tagihan->santri->nama_lengkap) . '_' . $tagihan->bulan . '_' . $tagihan->tahun . '.pdf';

        if (request('download') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice_spp', [
                'tagihan' => $tagihan,
                'setting' => $setting,
                'isPdf' => true,
            ])->setPaper('a4', 'portrait');
            return $pdf->download($fileName);
        }

        return view('pdf.invoice_spp', [
            'tagihan' => $tagihan,
            'setting' => $setting,
            'fileName' => $fileName,
            'isPdf' => false,
        ]);
    }
}
