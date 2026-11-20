<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Santri;
use App\Models\OrangTua;
use App\Models\UangSaku;
use App\Models\UangSakuManual;
use App\Models\UangSakuSetting;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;

class UangSakuController extends Controller
{
    public function index(Request $request)
    {
        Santri::syncClassesForActiveYear();

        $currentAdmin = auth('admin')->user();
        $selectedKelas = $request->get('kelas');
        if (!$request->has('kelas') && $selectedKelas === null && $currentAdmin && !empty($currentAdmin->assigned_kelas) && !$currentAdmin->isSuperAdmin()) {
            $selectedKelas = (string) $currentAdmin->assigned_kelas;
        }

        // HANYA SANTRI YANG DITERIMA yang muncul di Uang Saku
        $query = Santri::where('status', 'diterima')->with(['orangTua', 'uangSaku']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%')
                  ->orWhere('nik', 'like', '%' . $request->search . '%')
                  ->orWhere('nisn', 'like', '%' . $request->search . '%')
                  ->orWhereHas('orangTua', function($oq) use ($request) {
                      $oq->where('nama_ayah', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->tahun_ajaran) {
            $query->where('tahun_ajaran', $request->tahun_ajaran);
        }

        // Ambil populasi dasar (sesuai search & tahun_ajaran) untuk rincian per kelas agar konsisten 100%
        $baseSantriList = (clone $query)->get();
        $resolveKelasDigit = function ($s): string {
            $raw = trim((string) ($s->kelas ?? ''));
            $digits = preg_replace('/[^0-9]/', '', $raw);
            if (in_array($digits, ['1', '2', '3'], true)) {
                return $digits;
            }
            return (string) $s->calculated_kelas;
        };

        $totalPerKelas = [
            '1' => $baseSantriList->filter(fn($s) => $resolveKelasDigit($s) === '1')->sum(fn($s) => $s->saldo_uang_saku),
            '2' => $baseSantriList->filter(fn($s) => $resolveKelasDigit($s) === '2')->sum(fn($s) => $s->saldo_uang_saku),
            '3' => $baseSantriList->filter(fn($s) => $resolveKelasDigit($s) === '3')->sum(fn($s) => $s->saldo_uang_saku),
        ];
        $totalSemuaKelas = $baseSantriList->sum(fn($s) => $s->saldo_uang_saku);

        if ($selectedKelas) {
            $rawKelas = trim($selectedKelas);
            $digits = preg_replace('/[^0-9]/', '', $rawKelas);
            $targetKelas = $digits ?: $rawKelas;

            $query->where(function($q) use ($targetKelas, $rawKelas) {
                $q->where('kelas', $targetKelas)
                  ->orWhere('kelas', $rawKelas)
                  ->orWhere('kelas', 'Kelas ' . $targetKelas);
            });
        }

        // Hitung total titipan sesuai filter kelas yang sedang dipilih
        $filteredSantri = (clone $query)->get();
        $totalKas = $filteredSantri->sum(function($s) {
            return $s->saldo_uang_saku;
        });

        $santri = $query->paginate(15);

        // Ambil daftar tahun ajaran untuk dropdown filter (santri yang diterima saja)
        $tahunList = Santri::where('status', 'diterima')->distinct()->pluck('tahun_ajaran')->filter()->sort()->values();

        return view('admin.uang_saku.index', compact(
            'santri',
            'totalKas',
            'tahunList',
            'selectedKelas',
            'totalPerKelas',
            'totalSemuaKelas'
        ));
    }

    public function show(Request $request, $santri_id)
    {
        $santri = Santri::where('status', 'diterima')->with(['orangTua'])->findOrFail($santri_id);

        $query = $santri->uangSaku()->with('admin')->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        // Filter opsional berdasarkan kelas & tahun ajaran (biarkan tetap ada agar kompatibel)
        if ($request->kelas) {
            $rawKelas = trim($request->kelas);
            $digits = preg_replace('/[^0-9]/', '', $rawKelas);
            $targetKelas = $digits ?: $rawKelas;

            $query->where(function($q) use ($targetKelas, $rawKelas) {
                $q->where('kelas', $targetKelas)
                  ->orWhere('kelas', $rawKelas);
            });
        }
        if ($request->tahun_ajaran) {
            $query->where('tahun_ajaran', $request->tahun_ajaran);
        }

        // Filter Bulan & Tahun untuk Mutasi
        if ($request->filled('bulan')) {
            $query->whereMonth('created_at', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('created_at', $request->tahun);
        }

        // Hitung total ringkasan mutasi sebelum dipaginasi
        $allFiltered = $query->get();
        $totalUangTitip = $allFiltered->where('jenis', 'masuk')->sum('jumlah');
        $totalPengeluaran = $allFiltered->where('jenis', 'keluar')->sum('jumlah');
        $saldoSaatIni = $totalUangTitip - $totalPengeluaran;

        $transaksi = $query->paginate(15);

        // Ambil daftar tahun ajaran yang ada untuk filter
        $tahunList = $santri->uangSaku()->distinct()->pluck('tahun_ajaran')->filter()->sort()->values();

        // Ambil daftar tahun kalender (dari created_at) untuk filter tahun mutasi
        $availableYears = $santri->uangSaku()
            ->selectRaw('YEAR(created_at) as yr')
            ->distinct()
            ->orderBy('yr', 'desc')
            ->pluck('yr')
            ->filter()
            ->values();

        return view('admin.uang_saku.show', compact('santri', 'transaksi', 'tahunList', 'availableYears', 'totalUangTitip', 'totalPengeluaran', 'saldoSaatIni'));
    }

    public function storeDeposit(Request $request, $santri_id)
    {
        $request->validate([
            'jumlah'      => 'required|numeric|min:1000',
            'keterangan'  => 'required|string|max:500',
            'kelas'       => 'nullable|string|max:50',
            'tahun_ajaran'=> 'nullable|string|max:20',
        ], [
            'jumlah.required'     => 'Nominal titipan wajib diisi.',
            'jumlah.min'          => 'Nominal titipan minimal Rp 1.000.',
            'keterangan.required' => 'Catatan / Keterangan titipan wajib diisi.',
        ]);

        try {
            $santri = Santri::where('status', 'diterima')->findOrFail($santri_id);

            $adminId = auth('admin')->id();
            if (!$adminId || !\App\Models\Admin::where('id', $adminId)->exists()) {
                $adminId = null;
            }

            $transaksi = UangSaku::create([
                'santri_id'    => $santri->id,
                'jenis'        => 'masuk',
                'jumlah'       => $request->jumlah,
                'saldo_akhir'  => 0, // Will be updated by recalculate
                'keterangan'   => $request->keterangan,
                'kelas'        => $request->kelas,
                'tahun_ajaran' => $request->tahun_ajaran,
                'admin_id'     => $adminId,
            ]);

            // Recalculate balances
            $newBalance = UangSaku::recalculateBalance($santri->id);

            // Kirim notifikasi WhatsApp ke wali santri
            try {
                $saldoSebelum = $newBalance - (float) $request->jumlah;
                \App\Services\NotificationService::sendUangSaku(
                    $santri,
                    (float) $request->jumlah,
                    $request->keterangan,
                    $saldoSebelum,
                    (float) $newBalance,
                    (string) $transaksi->id
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notif WA deposit uang saku: ' . $e->getMessage());
            }

            // Reset WhatsApp warning if balance goes above the minimal limit
            $settings = UangSakuSetting::first();
            $batasMinimal = $settings ? $settings->batas_minimal_saldo : 50000;
            if ($newBalance > $batasMinimal && $santri->wa_saldo_warning_sent) {
                $santri->wa_saldo_warning_sent = false;
                $santri->save();
            }

            return redirect()->back()->with('success', 'Titipan uang saku berhasil ditambahkan.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error storeDeposit: ' . $e->getMessage());
            return redirect()->back()->withErrors([
                'error' => 'Gagal mencatat titipan: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function storeExpense(Request $request, $santri_id)
    {
        $request->validate([
            'jumlah'      => 'required|numeric|min:500',
            'keterangan'  => 'required|string|max:500',
            'kelas'       => 'nullable|string|max:50',
            'tahun_ajaran'=> 'nullable|string|max:20',
        ], [
            'jumlah.required'     => 'Nominal pengeluaran wajib diisi.',
            'jumlah.min'          => 'Nominal pengeluaran minimal Rp 500.',
            'keterangan.required' => 'Perincian belanja wajib diisi.',
        ]);

        try {
            return DB::transaction(function () use ($request, $santri_id) {
                $santri = Santri::where('status', 'diterima')->with('orangTua')->lockForUpdate()->findOrFail($santri_id);
                $currentSaldo = (float) $santri->saldo_uang_saku;
                $adminId = auth('admin')->id();
                if (!$adminId || !\App\Models\Admin::where('id', $adminId)->exists()) {
                    $adminId = null;
                }

                $newSaldoAkhir = $currentSaldo - (float) $request->jumlah;

                UangSaku::create([
                    'santri_id'    => $santri->id,
                    'jenis'        => 'keluar',
                    'jumlah'       => $request->jumlah,
                    'saldo_akhir'  => $newSaldoAkhir,
                    'keterangan'   => $request->keterangan,
                    'kelas'        => $request->kelas,
                    'tahun_ajaran' => $request->tahun_ajaran,
                    'admin_id'     => $adminId,
                ]);

                // Recalculate balances
                $newBalance = UangSaku::recalculateBalance($santri->id);

                // Log activity
                try {
                    \App\Services\AdminActivityLogger::log(
                        'uang_saku_keluar',
                        "Mencatat belanja santri '{$santri->nama_lengkap}' sebesar Rp " . number_format($request->jumlah, 0, ',', '.') . " ({$request->keterangan})",
                        'UangSaku',
                        $santri->id
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Gagal mencatat log aktivitas admin belanja: ' . $e->getMessage());
                }

                // Pemicu notifikasi batas saldo minimal (dilindungi try-catch agar tidak menyebabkan 500 server error)
                try {
                    $this->processSaldoWarningNotification($santri, $newBalance);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Gagal memproses notifikasi saldo minimal belanja: ' . $e->getMessage());
                }

                return redirect()->back()->with('success', 'Transaksi belanja santri berhasil dicatat.');
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error storeExpense: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors([
                'error' => 'Gagal mencatat belanja santri: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function destroy($id)
    {
        $trans = UangSaku::findOrFail($id);
        $santriId = $trans->santri_id;
        $trans->delete();

        // Recalculate remaining balances chronologically
        $newBalance = UangSaku::recalculateBalance($santriId);

        // Check if we need to reset/update warning sent status
        $santri = Santri::find($santriId);
        if ($santri) {
            $settings = UangSakuSetting::first();
            $batasMinimal = $settings ? $settings->batas_minimal_saldo : 50000;
            if ($newBalance > $batasMinimal && $santri->wa_saldo_warning_sent) {
                $santri->wa_saldo_warning_sent = false;
                $santri->save();
            }
        }

        return redirect()->back()->with('success', 'Transaksi berhasil dihapus & saldo dihitung ulang.');
    }

    public function exportPDF($santri_id)
    {
        $santri = Santri::where('status', 'diterima')->with(['orangTua'])->findOrFail($santri_id);
        $transaksi = $santri->uangSaku()->with('admin')->orderBy('created_at', 'asc')->orderBy('id', 'asc')->get();

        $pdf = Pdf::loadView('admin.uang_saku.pdf', compact('santri', 'transaksi'));
        $fileName = 'Uang_Saku_' . \Str::slug($santri->nama_lengkap) . '_' . date('Ymd') . '.pdf';

        return $pdf->download($fileName);
    }

    public function cetakKuitansi($id)
    {
        $transaksi = UangSaku::with(['santri.orangTua', 'admin'])->where('jenis', 'masuk')->findOrFail($id);
        $setting = \App\Models\InvoiceSetting::getSetting();
        $fileName = 'Kuitansi_Titipan_' . \Str::slug($transaksi->santri->nama_lengkap) . '_' . $transaksi->id . '.pdf';

        if (request('download') === 'pdf') {
            $pdf = Pdf::loadView('pdf.kuitansi_uang_saku', [
                'transaksi' => $transaksi,
                'setting' => $setting,
                'isPdf' => true,
            ])->setPaper('a4', 'portrait');
            return $pdf->download($fileName);
        }

        return view('pdf.kuitansi_uang_saku', [
            'transaksi' => $transaksi,
            'setting' => $setting,
            'fileName' => $fileName,
            'isPdf' => false,
        ]);
    }

    // --- FITUR BARU: MANUAL TRANSACTION (Santri Belum Terdaftar) ---
    public function createManual()
    {
        return view('admin.uang_saku.manual');
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'nama_santri'  => 'required|string|max:100',
            'no_wa_wali'   => 'required|string|max:20',
            'tanggal'      => 'required|date',
            'jumlah'       => 'required|numeric|min:500',
            'keterangan'   => 'required|string|max:500',
            'kelas'        => 'nullable|string|max:50',
            'tahun_ajaran' => 'nullable|string|max:20',
        ], [
            'nama_santri.required' => 'Nama santri wajib diisi.',
            'no_wa_wali.required'  => 'Nomor WA orang tua/wali wajib diisi.',
            'tanggal.required'     => 'Tanggal transaksi wajib diisi.',
            'jumlah.required'      => 'Nominal wajib diisi.',
            'jumlah.min'           => 'Nominal minimal Rp 500.',
            'keterangan.required'  => 'Keterangan wajib diisi.',
        ]);

        return DB::transaction(function () use ($request) {
            $trimmedName = trim($request->nama_santri);

            // Cari santri yang sudah ada berdasarkan ID atau nama_lengkap (case-insensitive & trimmed)
            $santri = null;
            if ($request->filled('santri_id')) {
                $santri = Santri::find($request->santri_id);
            }
            if (!$santri) {
                $santri = Santri::whereRaw('LOWER(TRIM(nama_lengkap)) = ?', [strtolower($trimmedName)])->first();
            }

            if ($santri) {
                // Perbarui nomor WA orang tua jika sudah ada santrinya
                if ($santri->orangTua) {
                    $santri->orangTua->update([
                        'no_wa_ayah' => trim($request->no_wa_wali)
                    ]);
                } else {
                    $uniqueKk = substr('KK-' . time() . rand(10, 99), 0, 16);
                    $uniqueNikAyah = substr('NIKA' . time() . rand(10, 99), 0, 16);
                    $uniqueNikIbu = substr('NIKI' . time() . rand(10, 99), 0, 16);

                    $orangTua = OrangTua::create([
                        'nama_ayah'            => 'Wali ' . $trimmedName,
                        'no_wa_ayah'           => trim($request->no_wa_wali),
                        'no_kk'                => $uniqueKk,
                        'nik_ayah'             => $uniqueNikAyah,
                        'tempat_lahir_ayah'    => 'Input Manual',
                        'tanggal_lahir_ayah'   => now()->toDateString(),
                        'pekerjaan_ayah'       => 'Lainnya',
                        'penghasilan_ayah'     => 'Lainnya',
                        'alamat_domisili_ayah' => 'Input Manual',
                        'nama_ibu'             => 'Ibu ' . $trimmedName,
                        'nik_ibu'              => $uniqueNikIbu,
                        'tempat_lahir_ibu'     => 'Input Manual',
                        'tanggal_lahir_ibu'    => now()->toDateString(),
                        'pekerjaan_ibu'        => 'Lainnya',
                        'penghasilan_ibu'      => 'Lainnya',
                        'alamat_domisili_ibu'  => 'Input Manual',
                    ]);

                    $santri->update([
                        'orang_tua_id' => $orangTua->id
                    ]);
                }
            } else {
                // Buat data orang tua baru
                $uniqueKk = substr('KK-' . time() . rand(10, 99), 0, 16);
                $uniqueNikAyah = substr('NIKA' . time() . rand(10, 99), 0, 16);
                $uniqueNikIbu = substr('NIKI' . time() . rand(10, 99), 0, 16);

                $orangTua = OrangTua::create([
                    'nama_ayah'            => 'Wali ' . $trimmedName,
                    'no_wa_ayah'           => trim($request->no_wa_wali),
                    'no_kk'                => $uniqueKk,
                    'nik_ayah'             => $uniqueNikAyah,
                    'tempat_lahir_ayah'    => 'Input Manual',
                    'tanggal_lahir_ayah'   => now()->toDateString(),
                    'pekerjaan_ayah'       => 'Lainnya',
                    'penghasilan_ayah'     => 'Lainnya',
                    'alamat_domisili_ayah' => 'Input Manual',
                    'nama_ibu'             => 'Ibu ' . $trimmedName,
                    'nik_ibu'              => $uniqueNikIbu,
                    'tempat_lahir_ibu'     => 'Input Manual',
                    'tanggal_lahir_ibu'    => now()->toDateString(),
                    'pekerjaan_ibu'        => 'Lainnya',
                    'penghasilan_ibu'      => 'Lainnya',
                    'alamat_domisili_ibu'  => 'Input Manual',
                ]);

                // Jika belum ada, buat record santri baru dengan status diterima agar muncul di Uang Saku
                $uniqueNik = substr('M' . time() . rand(100, 999), 0, 16);
                $uniqueNisn = substr('M' . rand(100000000, 999999999), 0, 10);

                $santri = Santri::create([
                    'nama_lengkap'     => $trimmedName,
                    'tempat_lahir'     => 'Input Manual',
                    'tanggal_lahir'    => now()->toDateString(),
                    'nik'              => $uniqueNik,
                    'sekolah_asal'     => 'Input Manual',
                    'npsn'             => '00000000',
                    'nisn'             => $uniqueNisn,
                    'anak_ke'          => 1,
                    'dari_bersaudara'  => 1,
                    'tujuan_mendaftar' => 'Input Manual',
                    'status'           => 'diterima',
                    'tahun_ajaran'     => $request->tahun_ajaran ?: date('Y') . '/' . (date('Y') + 1),
                    'orang_tua_id'     => $orangTua->id,
                ]);
            }

            // Simpan ke tabel uang_saku utama (selalu masuk/Pemasukan)
            $trans = new UangSaku([
                'santri_id'    => $santri->id,
                'jenis'        => 'masuk',
                'jumlah'       => $request->jumlah,
                'saldo_akhir'  => 0, // Temp
                'keterangan'   => $request->keterangan,
                'kelas'        => $request->kelas ?: $santri->kelas_sekarang,
                'tahun_ajaran' => $request->tahun_ajaran ?: $santri->tahun_ajaran,
                'admin_id'     => auth('admin')->id() ?? auth()->id(),
            ]);
            
            $trans->created_at = Carbon::parse($request->tanggal)->setTimeFrom(now());
            $trans->save();

            // Rekalculasi saldo secara kronologis
            UangSaku::recalculateBalance($santri->id);

            return redirect()->route('admin.uang_saku.index')->with('success', 'Transaksi manual (Pemasukan) untuk santri ' . $santri->nama_lengkap . ' berhasil disimpan.');
        });
    }

    // --- FITUR BARU: SETTINGS NOTIFIKASI WA ---
    public function editSettings()
    {
        $defaultTemplate = "*Notifikasi Batas Saldo Uang Saku*\n\n"
            . "Assalamu'alaikum Wr. Wb.\n\n"
            . "Bapak/Ibu Wali dari *[NAMA_SANTRI]*,\n\n"
            . "Kami menginformasikan bahwa saldo uang saku santri saat ini telah mencapai batas minimal.\n\n"
            . "*Detail Santri:*\n"
            . "- Nama: [NAMA_SANTRI]\n"
            . "- Kelas: [KELAS]\n"
            . "- Saldo Saat Ini: *[SALDO]*\n\n"
            . "Mohon untuk segera melakukan pengisian ulang (top up) saldo uang saku untuk kenyamanan belanja santri.\n\n"
            . "Terima kasih.\n\n"
            . "Wassalamu'alaikum Wr. Wb.";

        $settings = UangSakuSetting::firstOrCreate([], [
            'batas_minimal_saldo' => 50000.00,
            'is_active' => true,
            'template_pesan' => $defaultTemplate,
        ]);

        return view('admin.uang_saku.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'batas_minimal_saldo' => 'required|numeric|min:0',
            'is_active'           => 'required|boolean',
            'template_pesan'      => 'required|string|max:1000',
        ]);

        $settings = UangSakuSetting::first();
        if (!$settings) {
            $settings = new UangSakuSetting();
        }
        $settings->fill([
            'batas_minimal_saldo' => $request->batas_minimal_saldo,
            'is_active' => $request->is_active,
            'template_pesan' => $request->template_pesan,
        ])->save();

        return redirect()->back()->with('success', 'Pengaturan notifikasi WA berhasil diperbarui.');
    }

    /**
     * Kirim notifikasi WA batas saldo secara langsung (tanpa queue worker).
     * Hanya dipicu saat transaksi pengeluaran berhasil disimpan.
     */
    private function processSaldoWarningNotification(Santri $santri, float $newBalance): void
    {
        try {
            $settings = UangSakuSetting::first();

            if (!$settings || !$settings->is_active) {
                return;
            }

            if ($newBalance > $settings->batas_minimal_saldo || $santri->wa_saldo_warning_sent) {
                return;
            }

            $noWa = $santri->orangTua?->no_wa_ayah ?: ($santri->user?->no_wa ?? null);

            if (!$noWa) {
                Log::warning('[WA Saldo] Notifikasi terpicu tetapi nomor WA ayah/wali kosong', [
                    'santri_id' => $santri->id,
                    'nama_santri' => $santri->nama_lengkap,
                    'saldo_akhir' => $newBalance,
                    'batas_minimal' => $settings->batas_minimal_saldo,
                ]);
                return;
            }

            Log::info('[WA Saldo] Kondisi notifikasi terpicu', [
                'santri_id' => $santri->id,
                'nama_santri' => $santri->nama_lengkap,
                'saldo_akhir' => $newBalance,
                'batas_minimal' => $settings->batas_minimal_saldo,
                'nomor_asli' => $noWa,
            ]);

            $message = $settings->template_pesan;
            $message = str_replace('[NAMA_SANTRI]', $santri->nama_lengkap, $message);
            $message = str_replace('[KELAS]', $santri->kelas_sekarang ?? '-', $message);
            $message = str_replace('[SALDO]', 'Rp ' . number_format($newBalance, 0, ',', '.'), $message);
            $message = str_replace('[BATAS_MINIMAL]', 'Rp ' . number_format($settings->batas_minimal_saldo, 0, ',', '.'), $message);

            $notification = NotificationService::send([
                'recipient_number' => $noWa,
                'recipient_name' => $santri->orangTua?->nama_ayah,
                'user_id' => $santri->user_id,
                'santri_id' => $santri->id,
                'modul' => 'Uang Saku',
                'message' => $message,
                'reference_id' => 'SALDO-MIN-' . $santri->id . '-' . now()->format('YmdHis'),
                'sync' => true,
            ]);

            Log::info('[WA Saldo] Hasil pengiriman notifikasi', [
                'nama_santri' => $santri->nama_lengkap,
                'message_id' => $notification?->id,
                'status' => $notification?->status,
            ]);

            if ($notification) {
                $santri->wa_saldo_warning_sent = true;
                $santri->save();
            }
        } catch (\Throwable $e) {
            Log::warning('[WA Saldo] Gagal memproses notifikasi batas saldo: ' . $e->getMessage());
        }
    }

    public function destroySantri($id)
    {
        if (!auth('admin')->user()?->isSuperAdmin()) {
            return redirect()->route('admin.uang_saku.index')
                ->with('error', 'Akses ditolak: Hanya Super Admin yang berwenang menghapus data santri.');
        }

        $santri = Santri::findOrFail($id);
        $santriNama = $santri->nama_lengkap;

        $santri->delete();

        \App\Services\AdminActivityLogger::log(
            'soft_delete_santri',
            "Memindahkan santri '{$santriNama}' ke Tempat Sampah dari modul Uang Saku",
            'Santri',
            $id
        );
        
        return redirect()->route('admin.uang_saku.index')
            ->with('success', "Data santri '{$santriNama}' berhasil dipindahkan ke Tempat Sampah. Anda dapat memulihkannya kapan saja di menu Tempat Sampah.");
    }
}
