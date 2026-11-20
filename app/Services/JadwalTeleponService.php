<?php

namespace App\Services;

use App\Models\JadwalTelepon;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class JadwalTeleponService
{
    /**
     * Kirim notifikasi pengingat WhatsApp ke wali santri untuk satu jadwal telepon.
     */
    public static function sendReminder(JadwalTelepon $jadwal, bool $force = false): array
    {
        $jadwal->loadMissing(['santri.orangTua', 'santri.user']);
        $santri = $jadwal->santri;

        if (!$santri) {
            return [
                'success' => false,
                'message' => 'Data santri untuk jadwal ini tidak ditemukan.',
            ];
        }

        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        $formattedWa = WhatsappService::formatPhoneNumber($noWa);

        if (!$formattedWa) {
            $jadwal->update([
                'wa_reminder_status' => 'gagal',
                'wa_reminder_error'  => 'Nomor WhatsApp wali santri belum diisi atau tidak valid (' . ($noWa ?: '-') . ')',
            ]);

            return [
                'success' => false,
                'message' => "Nomor WhatsApp wali ananda {$santri->nama_lengkap} belum diisi atau tidak valid.",
            ];
        }

        $namaWali = $santri->orangTua?->nama_ayah ?: $santri->user?->name ?: 'Wali Santri';
        $namaSantri = $santri->nama_lengkap;
        $kelas = $santri->kelas ?: '-';
        $tanggalStr = $jadwal->formatted_tanggal;
        $jamStr = $jadwal->formatted_jam;
        $durasi = $jadwal->durasi_menit ?: 15;
        $ustadz = $jadwal->ustadz_pendamping ?: 'Ustadz Pendamping';
        $noPosko = $jadwal->no_hp_posko ?: '-';

        $keteranganTambahan = '';
        if (!empty($jadwal->topik)) {
            $keteranganTambahan .= "📌 *Topik Pembicaraan:* {$jadwal->topik}\n";
        }
        if (!empty($jadwal->keterangan)) {
            $keteranganTambahan .= "ℹ️ *Catatan:* {$jadwal->keterangan}\n";
        }

        $pesan = "Bismillahirrohmanirrohim.\n"
            . "Assalamu’alaikum Warahmatullahi Wabarakatuh.\n\n"
            . "Yth. Bapak/Ibu Wali dari ananda *{$namaSantri}* (Kelas {$kelas}),\n\n"
            . "Mengingatkan kembali bahwa sekitar *15 menit lagi* adalah jadwal giliran sambungan telepon ananda bersama keluarga di Pesantren Tahfizh Alam Qur'an:\n\n"
            . "🗓 *Hari/Tanggal:* {$tanggalStr}\n"
            . "⏰ *Waktu Giliran:* Pukul {$jamStr}\n"
            . "⏱ *Durasi Bicara:* Maksimal {$durasi} Menit\n"
            . "📱 *Nomor Penelepon:* {$noPosko}\n"
            . "👤 *Pendamping:* {$ustadz}\n\n"
            . ($keteranganTambahan ? "{$keteranganTambahan}\n" : "")
            . "Mohon Bapak/Ibu berkenan *stand by di dekat ponsel* sekarang agar ananda dapat langsung terhubung saat dihubungi.\n\n"
            . "Jazakumullah khairan katsiran atas perhatian dan kerja samanya.\n\n"
            . "_Pengasuhan Santri - Pesantren Tahfizh Alam Qur'an Ponorogo_";

        $jamMulaiClean = str_replace(':', '', substr($jadwal->jam_mulai, 0, 5));
        $referenceId = 'TEL-REMINDER-' . $jadwal->id . '-' . ($jadwal->tanggal_telepon ? $jadwal->tanggal_telepon->format('Ymd') : date('Ymd')) . '-' . $jamMulaiClean . '-' . time();

        try {
            $message = NotificationService::send([
                'recipient_number' => $formattedWa,
                'recipient_name'   => $namaWali,
                'user_id'          => $santri->user_id,
                'santri_id'        => $santri->id,
                'modul'            => 'Jadwal Telepon',
                'message'          => $pesan,
                'priority'         => 'high',
                'reference_id'     => $referenceId,
                'sync'             => true,
            ]);

            if ($message) {
                $jadwal->update([
                    'wa_reminder_sent'    => true,
                    'wa_reminder_sent_at' => now(),
                    'wa_reminder_status'  => 'berhasil',
                    'wa_reminder_error'   => null,
                ]);

                return [
                    'success' => true,
                    'message' => "Notifikasi pengingat jadwal telepon berhasil dikirim ke WhatsApp wali ({$formattedWa}).",
                ];
            } else {
                $jadwal->update([
                    'wa_reminder_status' => 'gagal',
                    'wa_reminder_error'  => 'Pesan tidak terkirim via WhatsApp Gateway.',
                ]);

                return [
                    'success' => false,
                    'message' => "Gagal mengirim pengingat ke {$formattedWa}. Silakan periksa status gateway.",
                ];
            }
        } catch (\Throwable $e) {
            Log::error("[JadwalTeleponService] Gagal kirim reminder WA: " . $e->getMessage());

            $jadwal->update([
                'wa_reminder_status' => 'gagal',
                'wa_reminder_error'  => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Broadcast pengingat jadwal telepon santri yang berlangsung hari ini.
     */
    public static function broadcastHariIni(): array
    {
        $today = now()->toDateString();
        $jadwals = JadwalTelepon::with(['santri.orangTua', 'santri.user'])
            ->whereDate('tanggal_telepon', $today)
            ->where('status', JadwalTelepon::STATUS_MENUNGGU)
            ->where('wa_reminder_sent', false)
            ->get();

        $successCount = 0;
        $failedCount = 0;

        foreach ($jadwals as $jadwal) {
            $result = self::sendReminder($jadwal);
            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        return [
            'total'   => $jadwals->count(),
            'success' => $successCount,
            'failed'  => $failedCount,
        ];
    }

    /**
     * Generate jadwal telepon secara massal (berurutan) untuk seluruh santri dalam 1 kelas.
     */
    public static function generateBulkPerKelas(array $data): array
    {
        $kelas = $data['kelas'];
        $tanggal = Carbon::parse($data['tanggal_telepon'])->toDateString();
        $jamMulai = Carbon::parse($data['jam_mulai']);
        $durasiMenit = (int) ($data['durasi_menit'] ?? 15);
        $ustadz = $data['ustadz_pendamping'] ?? 'Ustadz Pendamping';
        $noPosko = $data['no_hp_posko'];
        $topik = $data['topik'] ?? null;
        $keterangan = $data['keterangan'] ?? null;
        $adminId = $data['admin_id'] ?? null;

        $santris = Santri::where('status', 'diterima')
            ->where('kelas', $kelas)
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        if ($santris->isEmpty()) {
            return [
                'success' => false,
                'count'   => 0,
                'message' => "Tidak ada santri aktif berstatus 'Diterima' di Kelas {$kelas}.",
            ];
        }

        $created = 0;
        $currentStart = $jamMulai->copy();

        foreach ($santris as $santri) {
            $currentEnd = $currentStart->copy()->addMinutes($durasiMenit);

            JadwalTelepon::create([
                'santri_id'           => $santri->id,
                'tanggal_telepon'     => $tanggal,
                'jam_mulai'           => $currentStart->format('H:i'),
                'jam_selesai'         => $currentEnd->format('H:i'),
                'durasi_menit'        => $durasiMenit,
                'ustadz_pendamping'   => $ustadz,
                'no_hp_posko'         => $noPosko,
                'topik'               => $topik,
                'keterangan'          => $keterangan,
                'status'              => JadwalTelepon::STATUS_MENUNGGU,
                'created_by_admin_id' => $adminId,
            ]);

            $created++;
            $currentStart = $currentEnd->copy();
        }

        return [
            'success' => true,
            'count'   => $created,
            'message' => "Berhasil membuat {$created} jadwal giliran telepon berurutan untuk seluruh santri Kelas {$kelas}.",
        ];
    }
}
