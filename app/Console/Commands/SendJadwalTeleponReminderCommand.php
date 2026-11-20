<?php

namespace App\Console\Commands;

use App\Models\JadwalTelepon;
use App\Services\JadwalTeleponService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendJadwalTeleponReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telepon:send-reminder {--force : Kirim reminder langsung tanpa cek jeda waktu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi pengingat WhatsApp ke wali santri untuk jadwal sambungan telepon hari ini';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memeriksa jadwal telepon santri hari ini untuk pengiriman reminder WhatsApp (15 menit sebelum giliran)...");

        $today = now()->toDateString();
        $isForce = (bool) $this->option('force');
        $now = now();

        // Ambil jadwal hari ini yang masih menunggu dan belum dikirimkan reminder
        $query = JadwalTelepon::with(['santri.orangTua', 'santri.user'])
            ->whereDate('tanggal_telepon', $today)
            ->where('status', JadwalTelepon::STATUS_MENUNGGU)
            ->where('wa_reminder_sent', false);

        $jadwals = $query->get();

        if ($jadwals->isEmpty()) {
            $this->info("Tidak ada jadwal telepon hari ini yang perlu dikirimi pengingat.");
            return Command::SUCCESS;
        }

        $sentCount = 0;
        $failedCount = 0;
        $skippedCount = 0;

        foreach ($jadwals as $jadwal) {
            // Jika bukan force, hanya kirim jika waktu sekarang sudah masuk 15 menit sebelum jam giliran dimulai
            if (!$isForce) {
                try {
                    $tanggalStr = $jadwal->tanggal_telepon instanceof Carbon
                        ? $jadwal->tanggal_telepon->format('Y-m-d')
                        : (string) $jadwal->tanggal_telepon;

                    $waktuMulai = Carbon::parse($tanggalStr . ' ' . $jadwal->jam_mulai, 'Asia/Jakarta');
                    $windowStart = $waktuMulai->copy()->subMinutes(15);
                    $waktuSelesai = Carbon::parse($tanggalStr . ' ' . $jadwal->jam_selesai, 'Asia/Jakarta');

                    // Jika waktu sekarang masih sebelum 15 menit dari jam giliran, simpan dulu untuk giliran berikutnya
                    if ($now->lt($windowStart)) {
                        $this->line("⏳ Belum waktunya: {$jadwal->santri?->nama_lengkap} (Jadwal: {$jadwal->formatted_jam}, pengingat dijadwalkan pukul {$windowStart->format('H:i')} WIB)");
                        $skippedCount++;
                        continue;
                    }

                    // Jika jadwal sudah lewat dari jam selesai, tandai dilewati agar tidak membingungkan orang tua
                    if ($now->gt($waktuSelesai)) {
                        $this->warn("⚠️ Lewat waktu: {$jadwal->santri?->nama_lengkap} (Jadwal: {$jadwal->formatted_jam} sudah berlalu)");
                        $skippedCount++;
                        continue;
                    }
                } catch (\Throwable $e) {
                    // Jika parsing waktu gagal, tetap lanjutkan kirim
                }
            }

            $result = JadwalTeleponService::sendReminder($jadwal, $isForce);
            if ($result['success']) {
                $sentCount++;
                $this->info("✓ Reminder terkirim: {$jadwal->santri?->nama_lengkap} ({$jadwal->formatted_jam})");
            } else {
                $failedCount++;
                $this->warn("✗ Gagal kirim: {$jadwal->santri?->nama_lengkap} - {$result['message']}");
            }
        }

        $this->info("Selesai. Terkirim: {$sentCount}, Belum/Lewat Waktu: {$skippedCount}, Gagal: {$failedCount}");
        return Command::SUCCESS;
    }
}
