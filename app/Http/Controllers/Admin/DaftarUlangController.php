<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\Angkatan;
use App\Models\DaftarUlangTagihan;
use App\Models\DaftarUlangTagihanAddon;
use App\Models\DaftarUlangPembayaran;
use App\Models\DaftarUlangSetting;
use App\Models\SppSetting;
use App\Models\WhatsappMessage;
use App\Services\AdminActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DaftarUlangController extends Controller
{
    public function index(Request $request)
    {
        // Otomatis picu pengiriman notifikasi jika jadwal buka telah tiba / status terbuka
        try {
            $settingDU = DaftarUlangSetting::getActive();
            if ($settingDU->isOpen()) {
                NotificationService::broadcastDaftarUlangDibuka(null, true);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal auto broadcast daftar ulang di index: ' . $e->getMessage());
        }

        // 1. Ambil daftar tahun angkatan:
        // - Angkatan master PPDB
        // - Santri AKTIF berstatus diterima (belum lulus / bukan alumni)
        // - Santri ALUMNI yang MASIH MEMILIKI TUNGGAKAN / CICILAN (belum lunas)
        $fromAngkatan = Angkatan::all()->flatMap(fn($a) => [
            (string) $a->tahun_angkatan,
            (string) $a->tahun_ajaran,
        ]);

        $fromSantriAktif = Santri::where('status', 'diterima')
            ->where(function ($qa) {
                $qa->where(function ($sub) {
                    $sub->whereNull('kelas')->orWhere('kelas', '!=', 'alumni');
                })->where(function ($sub) {
                    $sub->whereNull('status_santri')->orWhere('status_santri', '!=', 'lulus');
                });
            })
            ->whereNotNull('tahun_masuk')
            ->pluck('tahun_masuk')
            ->map(fn($y) => (string) $y);

        $fromAlumniBerhutang = Santri::where('status', 'diterima')
            ->where(function ($qa) {
                $qa->where('kelas', 'alumni')->orWhere('status_santri', 'lulus');
            })
            ->whereHas('daftarUlangTagihan', function ($qt) {
                $qt->where('is_ready', true)
                   ->whereIn('status', ['belum_lunas', 'cicilan'])
                   ->where('sisa_tagihan', '>', 0);
            })
            ->whereNotNull('tahun_masuk')
            ->pluck('tahun_masuk')
            ->map(fn($y) => (string) $y);

        $angkatanList = $fromAngkatan->concat($fromSantriAktif)->concat($fromAlumniBerhutang)
            ->filter(fn($y) => !empty($y) && is_numeric($y))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if (empty($angkatanList)) {
            $angkatanList = [date('Y'), (string)((int)date('Y') + 1)];
        }

        $defaultYear = $angkatanList[0] ?? date('Y');
        $selectedYear = (string) $request->get('tahun_angkatan', $defaultYear);
        $selectedStatus = $request->get('status', 'all');
        $search = trim($request->get('search', ''));

        // 2. Query Santri pada tahun angkatan tersebut (status: diterima)
        $query = Santri::where('status', 'diterima');

        if ($selectedYear !== 'all') {
            $query->where(function ($q) use ($selectedYear) {
                $q->where('tahun_masuk', $selectedYear)
                  ->orWhereHas('angkatan', function ($qa) use ($selectedYear) {
                      $qa->where('tahun_angkatan', $selectedYear)
                         ->orWhere('tahun_ajaran', $selectedYear);
                  });
            });
        }

        $query->with(['orangTua', 'daftarUlangTagihan.pembayarans.verifiedBy', 'daftarUlangTagihan.addons']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nomor_rekening_spp', 'like', "%{$search}%")
                  ->orWhereHas('orangTua', function ($qo) use ($search) {
                      $qo->where('nama_ayah', 'like', "%{$search}%")
                         ->orWhere('no_wa_ayah', 'like', "%{$search}%");
                  });
            });
        }

        // Aturan Alumni & Status Tagihan:
        // - Santri alumni yang sudah LUNAS otomatis disembunyikan agar tidak menumpuk di tampilan aktif angkatan.
        // - Santri alumni yang BELUM LUNAS (masih memiliki sisa tagihan/mengangsur) TETAP DITAMPILKAN agar pencatatan piutang terjaga.
        // - Santri aktif (bukan alumni) selalu tampil sesuai filter status yang dipilih.
        if ($selectedStatus === 'all') {
            $query->where(function ($q) {
                // Santri Aktif (bukan alumni)
                $q->where(function ($qa) {
                    $qa->where(function ($sub) {
                        $sub->whereNull('kelas')->orWhere('kelas', '!=', 'alumni');
                    })->where(function ($sub) {
                        $sub->whereNull('status_santri')->orWhere('status_santri', '!=', 'lulus');
                    });
                })
                // ATAU Alumni yang masih ada sisa tagihan belum lunas / mengangsur
                ->orWhere(function ($qal) {
                    $qal->where(function ($sub) {
                        $sub->where('kelas', 'alumni')->orWhere('status_santri', 'lulus');
                    })->whereHas('daftarUlangTagihan', function ($qt) {
                        $qt->where('is_ready', true)
                           ->whereIn('status', ['belum_lunas', 'cicilan'])
                           ->where('sisa_tagihan', '>', 0);
                    });
                });
            });
        } elseif ($selectedStatus === 'belum_diatur') {
            // Santri aktif yang belum diatur tagihannya
            $query->where(function ($qa) {
                $qa->where(function ($sub) {
                    $sub->whereNull('kelas')->orWhere('kelas', '!=', 'alumni');
                })->where(function ($sub) {
                    $sub->whereNull('status_santri')->orWhere('status_santri', '!=', 'lulus');
                });
            })->whereDoesntHave('daftarUlangTagihan', function ($q) {
                $q->where('is_ready', true);
            });
        } elseif ($selectedStatus === 'belum_lunas') {
            $query->whereHas('daftarUlangTagihan', function ($q) {
                $q->where('status', 'belum_lunas')->where('is_ready', true);
            });
        } elseif ($selectedStatus === 'cicilan') {
            $query->whereHas('daftarUlangTagihan', function ($q) {
                $q->where('status', 'cicilan')->where('is_ready', true);
            });
        } elseif ($selectedStatus === 'lunas') {
            $query->whereHas('daftarUlangTagihan', function ($q) {
                $q->where('status', 'lunas')->where('is_ready', true);
            });
        }

        $santris = $query->orderBy('nama_lengkap', 'asc')->paginate(25)->withQueryString();

        // 3. Rekapitulasi Statistik Keuangan Angkatan Terpilih
        $baseSantriQuery = Santri::where('status', 'diterima');
        if ($selectedYear !== 'all') {
            $baseSantriQuery->where(function ($q) use ($selectedYear) {
                $q->where('tahun_masuk', $selectedYear)
                  ->orWhereHas('angkatan', function ($qa) use ($selectedYear) {
                      $qa->where('tahun_angkatan', $selectedYear)
                         ->orWhere('tahun_ajaran', $selectedYear);
                  });
            });
        }

        $activeSantriQuery = (clone $baseSantriQuery)->where(function ($qa) {
            $qa->where(function ($sub) {
                $sub->whereNull('kelas')->orWhere('kelas', '!=', 'alumni');
            })->where(function ($sub) {
                $sub->whereNull('status_santri')->orWhere('status_santri', '!=', 'lulus');
            });
        });

        $unpaidAlumniQuery = (clone $baseSantriQuery)->where(function ($qa) {
            $qa->where('kelas', 'alumni')->orWhere('status_santri', 'lulus');
        })->whereHas('daftarUlangTagihan', function ($qt) {
            $qt->where('is_ready', true)->whereIn('status', ['belum_lunas', 'cicilan'])->where('sisa_tagihan', '>', 0);
        });

        $totalSantriAktif = (clone $activeSantriQuery)->count();
        $totalAlumniBerhutang = (clone $unpaidAlumniQuery)->count();
        $totalSantriAngkatan = $totalSantriAktif + $totalAlumniBerhutang;

        $santriIdsAngkatan = (clone $baseSantriQuery)->pluck('id');
        $allTagihanQuery = DaftarUlangTagihan::whereIn('santri_id', $santriIdsAngkatan);

        $totalKewajiban = (float) (clone $allTagihanQuery)->where('is_ready', true)->sum('nominal_biaya')
                        + (float) (clone $allTagihanQuery)->where('is_ready', true)->sum('nominal_addon');
        $totalTerbayar  = (float) (clone $allTagihanQuery)->where('is_ready', true)->sum('nominal_terbayar');
        $totalSisa      = (float) (clone $allTagihanQuery)->where('is_ready', true)->sum('sisa_tagihan');

        $countLunas      = (clone $allTagihanQuery)->where('status', 'lunas')->where('is_ready', true)->count();
        $countCicilan    = (clone $allTagihanQuery)->where('status', 'cicilan')->where('is_ready', true)->count();
        $countBelumLunas = (clone $allTagihanQuery)->where('status', 'belum_lunas')->where('is_ready', true)->count();
        $countBelumDiatur = max(0, $totalSantriAktif - (clone $allTagihanQuery)->where('is_ready', true)->whereIn('santri_id', (clone $activeSantriQuery)->pluck('id'))->count());

        // 4. Pembayaran Menunggu Konfirmasi (Pending Verification)
        $pendingPembayarans = DaftarUlangPembayaran::with(['santri.orangTua', 'tagihan', 'submittedBy'])
            ->where('status', 'menunggu_konfirmasi')
            ->latest()
            ->get();

        $sppSetting = SppSetting::getActive();
        $daftarUlangSetting = DaftarUlangSetting::getActive();

        // 5. Data Log WhatsApp untuk status per santri pada modul Daftar Ulang
        $displayedSantriIds = $santris->pluck('id');
        $waLogsBySantri = WhatsappMessage::where('modul', 'Daftar Ulang')
            ->whereIn('santri_id', $displayedSantriIds)
            ->latest('id')
            ->get()
            ->groupBy('santri_id');

        return view('admin.daftar_ulang.index', compact(
            'santris',
            'angkatanList',
            'selectedYear',
            'selectedStatus',
            'search',
            'totalSantriAktif',
            'totalAlumniBerhutang',
            'totalSantriAngkatan',
            'totalKewajiban',
            'totalTerbayar',
            'totalSisa',
            'countLunas',
            'countCicilan',
            'countBelumLunas',
            'countBelumDiatur',
            'pendingPembayarans',
            'sppSetting',
            'daftarUlangSetting',
            'waLogsBySantri'
        ));
    }

    /**
     * Perbarui Pengaturan Jadwal Pembayaran Daftar Ulang (Buka / Tutup / Otomatis).
     */
    public function updateSetting(Request $request)
    {
        $validated = $request->validate([
            'status'        => ['required', 'in:buka,tutup,otomatis'],
            'tanggal_buka'  => ['nullable', 'date'],
            'pesan_tutup'   => ['nullable', 'string', 'max:500'],
            'auto_notif_wa' => ['nullable'],
        ]);

        $setting = DaftarUlangSetting::getActive();
        $setting->update([
            'status'        => $validated['status'],
            'tanggal_buka'  => $validated['status'] === 'otomatis' ? ($validated['tanggal_buka'] ?? null) : null,
            'pesan_tutup'   => trim($validated['pesan_tutup'] ?? '') ?: 'Pembayaran daftar ulang saat ini belum dibuka. Silakan menunggu informasi resmi dari pesantren.',
            'auto_notif_wa' => $request->boolean('auto_notif_wa'),
        ]);
        $setting->touch();

        $statusText = match($setting->status) {
            'buka'     => 'DIBUKA SEKARANG',
            'tutup'    => 'DITUTUP SEMENTARA',
            'otomatis' => 'OTOMATIS (Mulai ' . ($setting->tanggal_buka ? $setting->tanggal_buka->format('d/m/Y') : 'belum ditentukan') . ')',
        };

        AdminActivityLogger::log(
            'update_daftar_ulang_setting',
            "Memperbarui pengaturan jadwal pembayaran daftar ulang menjadi: {$statusText}",
            'DaftarUlangSetting',
            $setting->id
        );

        $extraNotifMsg = '';
        if ($setting->isOpen()) {
            $sentCount = NotificationService::broadcastDaftarUlangDibuka();
            if ($sentCount > 0) {
                $extraNotifMsg = " Otomatis mengirim {$sentCount} notifikasi WhatsApp pembukaan ke wali santri yang sudah siap.";
            }
        }

        return back()->with('success', "Pengaturan jadwal pembayaran berhasil disimpan ({$statusText}).{$extraNotifMsg}");
    }

    /**
     * Atur Nomor Rekening/VA dan Nominal Biaya Daftar Ulang Santri.
     * Otomatis menyimpan rekening ke profil Santri (sehingga langsung terhubung ke SPP).
     */
    public function updateBiayaDanRekening(Request $request, Santri $santri)
    {
        $validated = $request->validate([
            'nominal_biaya'          => ['nullable', 'numeric', 'min:0'],
            'is_beasiswa'            => ['nullable'],
            'nama_bank_spp'          => ['nullable', 'string', 'max:100'],
            'nomor_rekening_spp'     => ['nullable', 'string', 'max:50'],
            'atas_nama_rekening_spp' => ['nullable', 'string', 'max:255'],
            'gelombang'              => ['nullable', 'string', 'max:50'],
            'keterangan'             => ['nullable', 'string', 'max:500'],
        ]);

        $isBeasiswa = $request->boolean('is_beasiswa');
        $nominalBiaya = $isBeasiswa ? 0.0 : (float) ($validated['nominal_biaya'] ?? 0);

        // 1. Simpan nomor rekening/VA langsung ke profil Santri
        $santriUpdates = [
            'nama_bank_spp'          => trim($validated['nama_bank_spp'] ?? '') ?: null,
            'nomor_rekening_spp'     => trim($validated['nomor_rekening_spp'] ?? '') ?: null,
            'atas_nama_rekening_spp' => trim($validated['atas_nama_rekening_spp'] ?? '') ?: null,
            'is_beasiswa'            => $isBeasiswa,
        ];
        $santri->update($santriUpdates);

        $tahunAngkatan = (string) ($santri->tahun_masuk ?: ($santri->angkatan?->tahun_angkatan ?: ($santri->angkatan?->tahun_ajaran ?: date('Y'))));

        // 2. Simpan atau perbarui tagihan Daftar Ulang untuk angkatan/tahun ini
        $tagihan = DaftarUlangTagihan::firstOrNew([
            'santri_id'      => $santri->id,
            'tahun_angkatan' => $tahunAngkatan,
        ]);
        $tagihan->gelombang      = $validated['gelombang'] ?? 'Reguler';
        $tagihan->is_beasiswa    = $isBeasiswa;
        $tagihan->nominal_biaya  = $nominalBiaya;
        $tagihan->nomor_va       = $santri->nomor_rekening_spp;

        $keteranganInput = trim($validated['keterangan'] ?? '');
        if ($isBeasiswa) {
            $tagihan->keterangan = $keteranganInput ?: 'Beasiswa Penuh (Bebas Biaya Masuk)';
        } else {
            if ($keteranganInput === 'Beasiswa Penuh (Bebas Biaya Masuk)') {
                $tagihan->keterangan = null;
            } else {
                $tagihan->keterangan = $keteranganInput ?: null;
            }
        }

        $tagihan->is_ready       = true;
        $tagihan->save();

        // 3. Sinkronkan saldo & status
        $tagihan->updateStatusAndBalance();

        // Cek apakah santri ini memiliki sisa angsuran dari tahun lalu
        $prevUnpaid = (float) DaftarUlangTagihan::where('santri_id', $santri->id)
            ->where('id', '!=', $tagihan->id)
            ->where('status', '!=', 'lunas')
            ->where('sisa_tagihan', '>', 0)
            ->sum('sisa_tagihan');

        $infoMsg = $isBeasiswa ? "Beasiswa Penuh (Rp 0)" : "Rp " . number_format($nominalBiaya, 0, ',', '.');
        $extraMsg = $prevUnpaid > 0 ? " (Terdapat sisa angsuran tahun lalu Rp " . number_format($prevUnpaid, 0, ',', '.') . " yang otomatis diakumulasikan ke akun wali)" : "";

        AdminActivityLogger::log(
            'update_biaya_daftar_ulang',
            "Mengatur Biaya Daftar Ulang santri '{$santri->nama_lengkap}': {$infoMsg}, Rekening: {$santri->nama_bank_spp} {$santri->nomor_rekening_spp}{$extraMsg}",
            'DaftarUlangTagihan',
            $tagihan->id
        );

        // Otomatis kirim WA pembukaan jika portal saat ini berstatus BUKA & santri belum menerima di sesi pembukaan ini
        $extraNotifWa = '';
        $settingDU = DaftarUlangSetting::getActive();
        if ($settingDU->isOpen() && !$isBeasiswa && !empty($santri->nomor_rekening_spp) && $santri->nomor_rekening_spp !== 'Belum Diatur') {
            $sessionStart = $settingDU->getOpeningSessionTimestamp() ?? now()->startOfDay();
            $alreadySent = \App\Models\WhatsappMessage::where('santri_id', $santri->id)
                ->where('modul', 'Daftar Ulang')
                ->where('reference_id', 'like', 'DU-OPENING-%')
                ->where('created_at', '>=', $sessionStart)
                ->whereIn('status', ['sent', 'pending', 'processing'])
                ->exists();

            if (!$alreadySent) {
                $waMsg = NotificationService::sendDaftarUlangDibuka($santri, $tagihan, true);
                if ($waMsg) {
                    $extraNotifWa = " Notifikasi WA pembukaan sistem pembayaran otomatis dikirimkan ke wali ananda.";
                }
            }
        }

        return back()->with('success', "Pengaturan biaya daftar ulang & rekening ananda {$santri->nama_lengkap} berhasil disimpan ({$infoMsg}){$extraMsg}.{$extraNotifWa}");
    }

    /**
     * Catat Pembayaran / Angsuran Manual (Tunai / Transfer Langsung di Kantor).
     */
    public function storePembayaranManual(Request $request, $tagihanId)
    {
        $request->validate([
            'jumlah_bayar' => ['required', 'numeric', 'min:1000'],
            'metode_bayar' => ['required', 'in:transfer,tunai'],
            'catatan'      => ['nullable', 'string', 'max:255'],
        ]);

        $tagihan = DaftarUlangTagihan::with('santri.orangTua')->findOrFail($tagihanId);
        $jumlahBayar = (float) $request->jumlah_bayar;

        if ($jumlahBayar > $tagihan->sisa_tagihan) {
            return back()->with('error', "Nominal pembayaran melebihi sisa tagihan (Maksimal: Rp " . number_format($tagihan->sisa_tagihan, 0, ',', '.') . ")");
        }

        DB::beginTransaction();
        try {
            $pembayaran = DaftarUlangPembayaran::create([
                'daftar_ulang_tagihan_id' => $tagihan->id,
                'santri_id'               => $tagihan->santri_id,
                'jumlah_bayar'            => $jumlahBayar,
                'metode_bayar'            => $request->metode_bayar,
                'catatan'                 => $request->catatan,
                'status'                  => 'diterima',
                'verified_by_admin_id'    => auth('admin')->id(),
                'verified_at'             => now(),
            ]);

            $tagihan->updateStatusAndBalance();

            AdminActivityLogger::log(
                'manual_payment_daftar_ulang',
                "Mencatat pembayaran daftar ulang santri '{$tagihan->santri->nama_lengkap}' sebesar Rp " . number_format($jumlahBayar, 0, ',', '.') . " (Status: {$tagihan->status})",
                'DaftarUlangPembayaran',
                $pembayaran->id
            );

            DB::commit();

            // Notifikasi WA ke Wali Santri
            try {
                NotificationService::sendDaftarUlangDiterima(
                    $tagihan->santri,
                    $tagihan,
                    $jumlahBayar,
                    (string) $pembayaran->id
                );
            } catch (\Throwable $e) {}

            return back()->with('success', "Pembayaran daftar ulang sebesar Rp " . number_format($jumlahBayar, 0, ',', '.') . " berhasil dicatat.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Verifikasi Bukti Transfer dari Wali Santri (Terima / Tolak).
     */
    public function verifikasiPembayaran(Request $request, $pembayaranId)
    {
        $request->validate([
            'action'           => ['required', 'in:terima,tolak'],
            'alasan_penolakan' => ['nullable', 'string', 'max:255'],
        ]);

        $pembayaran = DaftarUlangPembayaran::with(['tagihan.santri.orangTua', 'santri'])->findOrFail($pembayaranId);

        if ($pembayaran->status !== 'menunggu_konfirmasi') {
            return back()->with('error', 'Pembayaran ini sudah pernah diproses sebelumnya.');
        }

        if ($request->action === 'terima') {
            $pembayaran->update([
                'status'               => 'diterima',
                'verified_by_admin_id' => auth('admin')->id(),
                'verified_at'          => now(),
            ]);

            $tagihan = $pembayaran->tagihan;
            $tagihan->updateStatusAndBalance();

            AdminActivityLogger::log(
                'verify_daftar_ulang_accepted',
                "Menyetujui bukti transfer daftar ulang {$pembayaran->santri->nama_lengkap} Rp " . number_format($pembayaran->jumlah_bayar, 0, ',', '.'),
                'DaftarUlangPembayaran',
                $pembayaran->id
            );

            try {
                NotificationService::sendDaftarUlangDiterima(
                    $pembayaran->santri,
                    $tagihan,
                    (float) $pembayaran->jumlah_bayar,
                    (string) $pembayaran->id
                );
            } catch (\Throwable $e) {}

            return back()->with('success', "Pembayaran ananda {$pembayaran->santri->nama_lengkap} sebesar Rp " . number_format($pembayaran->jumlah_bayar, 0, ',', '.') . " berhasil diverifikasi.");
        } else {
            $pembayaran->update([
                'status'               => 'ditolak',
                'alasan_penolakan'     => $request->alasan_penolakan ?: 'Bukti transfer tidak valid atau dana belum masuk.',
                'verified_by_admin_id' => auth('admin')->id(),
                'verified_at'          => now(),
            ]);

            AdminActivityLogger::log(
                'verify_daftar_ulang_rejected',
                "Menolak bukti transfer daftar ulang {$pembayaran->santri->nama_lengkap}: {$pembayaran->alasan_penolakan}",
                'DaftarUlangPembayaran',
                $pembayaran->id
            );

            return back()->with('warning', "Pembayaran ananda {$pembayaran->santri->nama_lengkap} ditolak. Wali santri dapat mengunggah ulang bukti transfer.");
        }
    }

    /**
     * Batalkan status pelunasan / reset riwayat pembayaran.
     */
    public function batalkanLunas(Request $request, $tagihanId)
    {
        $tagihan = DaftarUlangTagihan::with('santri')->findOrFail($tagihanId);

        DB::beginTransaction();
        try {
            DaftarUlangPembayaran::where('daftar_ulang_tagihan_id', $tagihan->id)->delete();
            $tagihan->is_beasiswa = false;
            if ($tagihan->keterangan === 'Beasiswa Penuh (Bebas Biaya Masuk)') {
                $tagihan->keterangan = null;
            }
            if ((float)$tagihan->nominal_biaya === 0.0) {
                $tagihan->nominal_biaya = 6000000;
            }
            $tagihan->save();

            if ($tagihan->santri) {
                $tagihan->santri->update(['is_beasiswa' => false]);
            }

            $tagihan->updateStatusAndBalance();

            AdminActivityLogger::log(
                'reset_daftar_ulang_payment',
                "Mereset seluruh riwayat pembayaran dan status beasiswa daftar ulang santri '{$tagihan->santri->nama_lengkap}'",
                'DaftarUlangTagihan',
                $tagihan->id
            );

            DB::commit();
            return back()->with('success', "Status dan riwayat pembayaran daftar ulang ananda {$tagihan->santri->nama_lengkap} berhasil di-reset kembali.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan lunas: ' . $e->getMessage());
        }
    }

    /**
     * Kirim manual notifikasi WA resmi pembukaan sistem pembayaran ke wali santri tertentu.
     */
    public function kirimWaDibuka(Request $request, Santri $santri)
    {
        $tagihan = $santri->daftarUlangTagihan;
        if (!$tagihan || !$tagihan->is_ready) {
            return back()->with('error', "Biaya daftar ulang ananda {$santri->nama_lengkap} belum diatur.");
        }

        if (empty($santri->nomor_rekening_spp) || $santri->nomor_rekening_spp === 'Belum Diatur') {
            return back()->with('error', "Nomor rekening/VA ananda {$santri->nama_lengkap} belum diisi.");
        }

        if ($tagihan->is_beasiswa) {
            return back()->with('info', "Ananda {$santri->nama_lengkap} berstatus Beasiswa Penuh (Rp 0), tidak memerlukan transfer.");
        }

        $waMsg = NotificationService::sendDaftarUlangDibuka($santri, $tagihan, true);
        if ($waMsg) {
            return back()->with('success', "Notifikasi WA resmi pembukaan sistem pembayaran berhasil dikirimkan ke wali ananda {$santri->nama_lengkap}.");
        }

        return back()->with('error', "Gagal mengirim notifikasi WA. Pastikan nomor WA wali ananda valid.");
    }

    /**
     * Broadcast manual notifikasi WA resmi pembukaan ke semua santri yang sudah siap pada angkatan tertentu.
     */
    public function broadcastWaDibuka(Request $request)
    {
        $tahunAngkatan = $request->input('tahun_angkatan');
        $sentCount = NotificationService::broadcastDaftarUlangDibuka($tahunAngkatan, true);

        if ($sentCount > 0) {
            return back()->with('success', "Berhasil mengirimkan {$sentCount} notifikasi WA resmi pembukaan ke wali santri yang sudah siap.");
        }

        return back()->with('info', "Semua wali santri yang memenuhi syarat sudah menerima notifikasi, atau belum ada santri yang siap diatur.");
    }

    /**
     * Kirim ulang pesan WA yang gagal.
     */
    public function retryWaMessage($id)
    {
        $msg = WhatsappMessage::findOrFail($id);

        $msg->update([
            'status'        => 'pending',
            'error_message' => null,
            'scheduled_at'  => null,
        ]);

        // Eksekusi kirim sekarang langsung
        (new \App\Jobs\ProcessWhatsappQueueJob($msg->id))->handle();
        $msg->refresh();

        AdminActivityLogger::log('retry_whatsapp_message', "Melakukan retry pengiriman pesan WA [{$msg->modul}] ID {$msg->id} ke {$msg->recipient_number}", 'WhatsappMessage', $msg->id);

        if ($msg->status === 'sent') {
            return back()->with('success', "Pesan WA berhasil dikirim ulang ke {$msg->recipient_number}!");
        }

        return back()->with('warning', "Status pengiriman pesan WA: {$msg->status}. " . ($msg->error_message ? "Info: {$msg->error_message}" : ''));
    }

    /**
     * Tambah biaya add-on (misal Seragam Tambahan, Buku/Kitab, Kasur, Kegiatan) pada tagihan Daftar Ulang santri.
     */
    public function storeAddon(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_biaya' => ['required', 'string', 'max:100'],
            'nominal'    => ['required', 'numeric', 'min:500'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'nama_biaya.required' => 'Nama biaya add-on wajib diisi (misal: Seragam Tambahan).',
            'nama_biaya.max'      => 'Nama biaya add-on maksimal 100 karakter.',
            'nominal.required'    => 'Nominal biaya wajib diisi.',
            'nominal.numeric'     => 'Nominal biaya harus berupa angka.',
            'nominal.min'         => 'Nominal biaya minimal Rp 500.',
        ]);

        $tagihan = DaftarUlangTagihan::with(['santri', 'pembayarans', 'addons'])->findOrFail($id);

        $addon = $tagihan->addons()->create([
            'nama_biaya' => trim($validated['nama_biaya']),
            'nominal'    => (float) $validated['nominal'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        $tagihan->syncTotals();

        AdminActivityLogger::log(
            'add_daftar_ulang_addon',
            "Menambahkan add-on '{$addon->nama_biaya}' Rp " . number_format($addon->nominal, 0, ',', '.') . " pada biaya daftar ulang {$tagihan->santri?->nama_lengkap} (Angkatan {$tagihan->tahun_angkatan})",
            'DaftarUlangTagihan',
            $tagihan->id
        );

        return back()->with('success', "Biaya tambahan '{$addon->nama_biaya}' sebesar Rp " . number_format($addon->nominal, 0, ',', '.') . " berhasil ditambahkan.");
    }

    /**
     * Hapus biaya add-on dari tagihan Daftar Ulang.
     */
    public function destroyAddon(DaftarUlangTagihanAddon $addon)
    {
        $tagihan = $addon->tagihan;
        $namaBiaya = $addon->nama_biaya;
        $nominal = (float) $addon->nominal;

        if ($tagihan) {
            $totalDiterima = (float) $tagihan->pembayarans()->where('status', 'diterima')->sum('jumlah_bayar');
            $newTotal = ((float) $tagihan->nominal_biaya + (float) $tagihan->nominal_addon) - $nominal;
            if ($newTotal < $totalDiterima) {
                return back()->with('error', "Biaya tambahan tidak dapat dihapus karena total pembayaran yang telah diterima (Rp " . number_format($totalDiterima, 0, ',', '.') . ") akan melebihi total tagihan baru.");
            }
        }

        $addon->delete();

        if ($tagihan) {
            $tagihan->syncTotals();
            AdminActivityLogger::log(
                'delete_daftar_ulang_addon',
                "Menghapus add-on '{$namaBiaya}' Rp " . number_format($nominal, 0, ',', '.') . " dari biaya daftar ulang {$tagihan->santri?->nama_lengkap} (Angkatan {$tagihan->tahun_angkatan})",
                'DaftarUlangTagihan',
                $tagihan->id
            );
        }

        return back()->with('success', "Biaya tambahan '{$namaBiaya}' berhasil dihapus.");
    }

    public function cetakInvoice($id)
    {
        $tagihan = DaftarUlangTagihan::with(['santri.orangTua', 'addons', 'pembayarans.verifiedBy'])->findOrFail($id);
        $setting = \App\Models\InvoiceSetting::getSetting();
        $fileName = 'Invoice_Daftar_Ulang_' . \Illuminate\Support\Str::slug($tagihan->santri->nama_lengkap) . '_' . $tagihan->tahun_angkatan . '.pdf';

        if (request('download') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice_daftar_ulang', [
                'tagihan' => $tagihan,
                'setting' => $setting,
                'isPdf' => true,
            ])->setPaper('a4', 'portrait');
            return $pdf->download($fileName);
        }

        return view('pdf.invoice_daftar_ulang', [
            'tagihan' => $tagihan,
            'setting' => $setting,
            'fileName' => $fileName,
            'isPdf' => false,
        ]);
    }
}

