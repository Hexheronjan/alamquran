<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KehadiranWali;
use App\Models\PerizinanSantri;
use App\Models\Santri;
use App\Models\User;
use App\Services\AdminActivityLogger;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KehadiranPerizinanController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        // Presensi Kunjungan Ahad Pagi Hari Ini
        $presensiHariIni = KehadiranWali::with(['user', 'santri'])
            ->whereDate('tanggal', $today)
            ->latest()
            ->paginate(15, ['*'], 'presensi_page');

        // Pengajuan izin yang menunggu persetujuan
        $pengajuanIzin = PerizinanSantri::with(['santri', 'user'])
            ->where('status', 'pengajuan')
            ->latest('created_at')
            ->get();

        // Santri yang sedang izin keluar (belum kembali)
        $sedangKeluar = PerizinanSantri::with(['santri', 'user'])
            ->where('status', 'keluar')
            ->latest('waktu_keluar')
            ->get();

        // Riwayat perizinan
        $riwayatIzin = PerizinanSantri::with(['santri', 'user'])
            ->latest('created_at')
            ->paginate(15, ['*'], 'izin_page');

        $counts = [
            'hadir_hari_ini'  => KehadiranWali::whereDate('tanggal', $today)->count(),
            'sedang_keluar'   => $sedangKeluar->count(),
            'total_pengajuan' => $pengajuanIzin->count(),
            'total_izin'      => PerizinanSantri::count(),
        ];

        return view('admin.perizinan.index', compact('presensiHariIni', 'sedangKeluar', 'riwayatIzin', 'pengajuanIzin', 'counts', 'today'));
    }

    public function scanner()
    {
        return view('admin.perizinan.scanner');
    }

    public function processScan(Request $request)
    {
        $request->validate([
            'qr_token' => ['required', 'string'],
            'aksi'     => ['required', 'in:presensi_ahad,izin_keluar,izin_kembali'],
            'jenis_izin' => ['nullable', 'string'],
            'catatan'    => ['nullable', 'string'],
        ]);

        $rawToken = trim($request->qr_token);
        $cleanToken = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rawToken));
        $normalizedToken = strtoupper(str_replace(' ', '', $rawToken));
        $aksi = $request->aksi;

        // 1. Cari User (Wali) berdasarkan qr_token (fleksibel termasuk variasi spasi / strip)
        $user = User::with(['santri' => function ($q) {
            $q->where('status', 'diterima');
        }])
        ->where('qr_token', $rawToken)
        ->orWhere('qr_token', $normalizedToken)
        ->orWhere('qr_token', 'WALI-' . $cleanToken)
        ->orWhereRaw("REPLACE(REPLACE(qr_token, ' ', ''), '-', '') = ?", [$cleanToken])
        ->first();

        // 2. Jika tidak ditemukan via qr_token wali, cari berdasarkan santri (NIS, NISN, NIK, ID)
        if (!$user) {
            $santriMatch = Santri::where('status', 'diterima')
                ->where(function ($q) use ($rawToken) {
                    $q->where('nis', $rawToken)
                      ->orWhere('nisn', $rawToken)
                      ->orWhere('nik', $rawToken)
                      ->orWhere('id', $rawToken);
                })
                ->first();

            if ($santriMatch && $santriMatch->user) {
                $user = $santriMatch->user()->with(['santri' => function ($q) {
                    $q->where('status', 'diterima');
                }])->first();
            }
        }

        if (!$user) {
            return $this->scanErrorResponse($request, 'QR Code Wali tidak valid atau tidak terdaftar dalam sistem.', 404);
        }

        $santri = $user->santri->first();
        if (!$santri) {
            return $this->scanErrorResponse($request, "Wali terdaftar ({$user->name}), namun tidak memiliki santri dengan status diterima.", 422);
        }

        $adminId = auth('admin')->id();

        if ($aksi === 'presensi_ahad') {
            // Catat presensi kunjungan Ahad
            $today = Carbon::today()->toDateString();
            $existing = KehadiranWali::where('user_id', $user->id)
                ->whereDate('tanggal', $today)
                ->first();

            if ($existing) {
                $pesan = "Wali {$user->name} sudah tercatat hadir hari ini pada pukul {$existing->waktu_hadir}.";
                return $this->scanSuccessResponse($request, $pesan, [
                    'wali'   => $user->name,
                    'santri' => $santri->nama_lengkap,
                    'kelas'  => $santri->kelas_label,
                    'waktu'  => $existing->waktu_hadir,
                ], 'Presensi Ahad Pagi');
            }

            $isAhad = Carbon::today()->isSunday();
            $labelKegiatan = $isAhad ? 'Kunjungan Ahad Pagi' : 'Kunjungan Wali (' . Carbon::now()->locale('id')->isoFormat('dddd') . ')';

            $kehadiran = KehadiranWali::create([
                'user_id'     => $user->id,
                'santri_id'   => $santri->id,
                'tanggal'     => $today,
                'waktu_hadir' => Carbon::now()->format('H:i:s'),
                'kegiatan'    => $labelKegiatan,
                'admin_id'    => $adminId,
            ]);

            AdminActivityLogger::log('scan_presensi_wali', "Presensi wali {$user->name} santri {$santri->nama_lengkap} ({$labelKegiatan})");

            $pesan = "Presensi Berhasil! Selamat datang {$user->name} (Wali {$santri->nama_lengkap} - {$santri->kelas_label})";
            return $this->scanSuccessResponse($request, $pesan, [
                'wali'   => $user->name,
                'santri' => $santri->nama_lengkap,
                'kelas'  => $santri->kelas_label,
                'waktu'  => $kehadiran->waktu_hadir,
            ], $labelKegiatan);

        } elseif ($aksi === 'izin_keluar') {
            // Cek apakah santri sedang keluar
            $activeIzin = PerizinanSantri::where('santri_id', $santri->id)
                ->where('status', 'keluar')
                ->first();

            if ($activeIzin) {
                $pesan = "Santri {$santri->nama_lengkap} sudah memiliki izin keluar aktif sejak " . Carbon::parse($activeIzin->waktu_keluar)->format('d/m H:i') . ".";
                return $this->scanErrorResponse($request, $pesan, 422);
            }

            $batasJam = $request->filled('batas_jam_kembali') ? $request->batas_jam_kembali : '17:00:00';
            $batasLabel = substr($batasJam, 0, 5) . ' WIB';

            $izin = PerizinanSantri::create([
                'santri_id'         => $santri->id,
                'user_id'           => $user->id,
                'jenis_izin'        => $request->jenis_izin ?? 'bawa_pulang',
                'tujuan'            => $request->catatan ?? 'Izin bersama orang tua',
                'nama_penjemput'    => $user->name,
                'no_wa_penjemput'   => $user->no_wa ?? '-',
                'waktu_keluar'      => Carbon::now(),
                'batas_jam_kembali' => $batasJam,
                'status'            => 'keluar',
                'petugas_keluar_id' => $adminId,
            ]);

            AdminActivityLogger::log('scan_izin_keluar', "Santri {$santri->nama_lengkap} izin keluar dibawa oleh wali {$user->name}");

            $noWaWali = $santri->orangTua?->no_wa_ayah ?: $user->no_wa;
            if ($noWaWali) {
                try {
                    NotificationService::send([
                        'recipient_number' => $noWaWali,
                        'recipient_name'   => $user->name,
                        'user_id'          => $user->id,
                        'santri_id'        => $santri->id,
                        'modul'            => 'Perizinan',
                        'priority'         => 'high',
                        'reference_id'     => 'IZIN-SCAN-KELUAR-' . $izin->id,
                        'message'          => "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
                            . "Santri *{$santri->nama_lengkap}* ({$santri->kelas_label}) telah tercatat keluar dari pondok pada pukul " . Carbon::now()->format('H:i') . " WIB bersama {$user->name}.\n\n"
                            . "⏰ Batas waktu kembali ke pondok: *{$batasLabel}*.\n"
                            . "Mohon untuk kembali tepat waktu. Jazakumullahu khairan.\n\n"
                            . "_Pesantren Tahfizh Alam Qur'an_",
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Gagal kirim notif scan keluar: ' . $e->getMessage());
                }
            }

            $pesan = "Izin Keluar Berhasil! Santri {$santri->nama_lengkap} tercatat keluar pada " . Carbon::now()->format('H:i') . " WIB. Batas kembali: {$batasLabel}.";
            return $this->scanSuccessResponse($request, $pesan, [
                'wali'   => $user->name,
                'santri' => $santri->nama_lengkap,
                'kelas'  => $santri->kelas_label,
                'waktu'  => Carbon::now()->format('d M Y H:i'),
            ], 'Izin Keluar');

        } elseif ($aksi === 'izin_kembali') {
            // Pengantaran kembali ke pondok
            $activeIzin = PerizinanSantri::where('santri_id', $santri->id)
                ->where('status', 'keluar')
                ->latest('waktu_keluar')
                ->first();

            if (!$activeIzin) {
                $pesan = "Santri {$santri->nama_lengkap} tidak memiliki catatan izin keluar yang aktif.";
                return $this->scanErrorResponse($request, $pesan, 422);
            }

            $now = Carbon::now();
            $isTerlambat = false;
            if ($activeIzin->batas_jam_kembali) {
                try {
                    $isTerlambat = $now->greaterThan(Carbon::parse($activeIzin->batas_jam_kembali));
                } catch (\Exception $e) {}
            }

            $activeIzin->update([
                'waktu_kembali'      => $now,
                'status'             => $isTerlambat ? 'terlambat' : 'kembali',
                'petugas_kembali_id' => $adminId,
            ]);

            AdminActivityLogger::log('scan_izin_kembali', "Santri {$santri->nama_lengkap} diantar kembali ke pondok oleh wali {$user->name}");

            $noWaWali = $santri->orangTua?->no_wa_ayah ?: $user->no_wa;
            if ($noWaWali) {
                try {
                    NotificationService::send([
                        'recipient_number' => $noWaWali,
                        'recipient_name'   => $user->name,
                        'user_id'          => $user->id,
                        'santri_id'        => $santri->id,
                        'modul'            => 'Perizinan',
                        'priority'         => 'normal',
                        'reference_id'     => 'IZIN-SCAN-KEMBALI-' . $activeIzin->id,
                        'message'          => "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
                            . "Alhamdulillah, santri *{$santri->nama_lengkap}* ({$santri->kelas_label}) telah kembali ke pondok pada " . $now->format('d/m/Y H:i') . " WIB (" . ($isTerlambat ? "Terlambat melewati batas jam" : "Kembali Tepat Waktu") . ").\n\n"
                            . "Terima kasih atas kerja samanya.\n\n"
                            . "_Pesantren Tahfizh Alam Qur'an_",
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Gagal kirim notif scan kembali: ' . $e->getMessage());
                }
            }

            $statusText = $isTerlambat ? "Kembali (Terlambat melewati batas jam)" : "Kembali Tepat Waktu";
            $pesan = "Santri {$santri->nama_lengkap} berhasil tercatat kembali ke pondok pada " . $now->format('H:i') . " WIB ({$statusText}).";

            return $this->scanSuccessResponse($request, $pesan, [
                'wali'   => $user->name,
                'santri' => $santri->nama_lengkap,
                'kelas'  => $santri->kelas_label,
                'status' => $statusText,
                'waktu'  => $now->format('d M Y H:i'),
            ], 'Izin Kembali');
        }

        return $this->scanErrorResponse($request, 'Aksi tidak valid.', 400);
    }

    protected function scanSuccessResponse(Request $request, string $message, array $data, string $aksiLabel)
    {
        $payload = [
            'success'     => true,
            'status'      => 'success',
            'message'     => $message,
            'pesan'       => $message,
            'nama_wali'   => $data['wali'] ?? '-',
            'nama_santri' => $data['santri'] ?? '-',
            'kelas'       => $data['kelas'] ?? '-',
            'aksi_label'  => $aksiLabel,
            'data'        => $data,
        ];

        if (!$request->expectsJson() && !$request->ajax()) {
            return back()->with('scan_success', $message)->with('scan_result', $payload);
        }

        return response()->json($payload);
    }

    protected function scanErrorResponse(Request $request, string $message, int $statusCode = 422)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return back()->with('scan_error', $message)->with('scan_result', [
                'status'  => 'error',
                'success' => false,
                'pesan'   => $message,
                'message' => $message,
            ]);
        }

        return response()->json([
            'success' => false,
            'status'  => 'error',
            'message' => $message,
            'pesan'   => $message,
        ], $statusCode);
    }

    public function setujui($id)
    {
        $izin = PerizinanSantri::with('santri.orangTua')->findOrFail($id);
        $adminId = auth('admin')->id();

        $izin->update([
            'status'            => 'keluar',
            'waktu_keluar'      => Carbon::now(),
            'petugas_keluar_id' => $adminId,
        ]);

        $namaSantri = $izin->santri->nama_lengkap ?? 'Santri';
        AdminActivityLogger::log('setujui_izin_keluar', "Admin menyetujui perizinan keluar santri {$namaSantri}");
        NotificationService::sendPerizinanDisetujui($izin->fresh('santri.orangTua'));

        return back()->with('success', "Pengajuan izin untuk santri {$namaSantri} berhasil disetujui. Status santri kini 'Sedang Keluar'.");
    }

    public function tolak(Request $request, $id)
    {
        $izin = PerizinanSantri::with('santri.orangTua')->findOrFail($id);

        $catatan = $request->input('catatan', 'Pengajuan izin tidak disetujui / ditolak oleh pihak pondok.');

        $izin->update([
            'status'  => 'ditolak',
            'catatan' => $catatan,
        ]);

        $namaSantri = $izin->santri->nama_lengkap ?? 'Santri';
        AdminActivityLogger::log('tolak_izin_keluar', "Admin menolak perizinan keluar santri {$namaSantri}");
        NotificationService::send([
            'recipient_number' => $izin->santri?->orangTua?->no_wa_ayah ?: $izin->user?->no_wa,
            'recipient_name' => $izin->santri?->orangTua?->nama_ayah ?: $izin->user?->name,
            'user_id' => $izin->user_id,
            'santri_id' => $izin->santri_id,
            'modul' => 'Perizinan',
            'reference_id' => 'IZIN-DITOLAK-' . $izin->id,
            'message' => "Assalamu'alaikum. Pengajuan izin {$namaSantri} belum dapat disetujui. Alasan: {$catatan}",
        ]);

        return back()->with('success', "Pengajuan izin santri {$namaSantri} telah ditolak.");
    }

    public function kembali($id)
    {
        $izin = PerizinanSantri::with('santri.orangTua')->findOrFail($id);
        $adminId = auth('admin')->id();

        $now = Carbon::now();
        $isTerlambat = false;
        if ($izin->batas_jam_kembali) {
            try {
                $isTerlambat = $now->greaterThan(Carbon::parse($izin->batas_jam_kembali));
            } catch (\Exception $e) {}
        }

        $izin->update([
            'waktu_kembali'      => $now,
            'status'             => $isTerlambat ? 'terlambat' : 'kembali',
            'petugas_kembali_id' => $adminId,
        ]);

        $namaSantri = $izin->santri->nama_lengkap ?? 'Santri';
        AdminActivityLogger::log('izin_kembali', "Santri {$namaSantri} tercatat kembali ke pondok");
        NotificationService::send([
            'recipient_number' => $izin->santri?->orangTua?->no_wa_ayah ?: $izin->user?->no_wa,
            'recipient_name' => $izin->santri?->orangTua?->nama_ayah ?: $izin->user?->name,
            'user_id' => $izin->user_id,
            'santri_id' => $izin->santri_id,
            'modul' => 'Perizinan',
            'reference_id' => 'IZIN-KEMBALI-' . $izin->id,
            'message' => "Assalamu'alaikum. {$namaSantri} tercatat kembali ke pondok pukul {$now->format('H:i')} WIB" . ($isTerlambat ? ' (melewati batas kembali).' : '.'),
        ]);

        return back()->with('success', "Santri {$namaSantri} telah tercatat kembali ke pondok.");
    }
}
