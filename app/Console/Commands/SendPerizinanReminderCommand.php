<?php

namespace App\Console\Commands;

use App\Models\PerizinanSantri;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPerizinanReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perizinan:send-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi WA reminder jam 15:00 (2 jam sebelum batas jam 17:00) kepada wali santri yang anaknya izin keluar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memeriksa santri yang sedang izin keluar untuk pengiriman reminder...");

        $activeIzins = PerizinanSantri::with(['santri', 'user'])
            ->where('status', 'keluar')
            ->where('wa_reminder_sent', false)
            ->get();

        $sentCount = 0;

        foreach ($activeIzins as $izin) {
            $noWa = $izin->no_wa_penjemput ?: $izin->user?->no_wa;
            if (!$noWa || $noWa === '-') {
                continue;
            }

            $santri = $izin->santri;
            $namaSantri = $santri ? $santri->nama_lengkap : 'Santri';
            $kelasSantri = $santri ? $santri->kelas : '-';

            $pesan = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
                   . "Bapak/Ibu wali santri dari ananda *{$namaSantri}* (Kelas {$kelasSantri}),\n\n"
                   . "⏰ *PENGINGAT KEMBALI KE PONDOK*\n"
                   . "Diberitahukan bahwa batas maksimal pengantaran kembali santri ke Pesantren Tahfizh Alam Qur'an adalah sore ini pukul *17:00 WIB*.\n\n"
                   . "Mohon dapat mengantar ananda tepat waktu sebelum pintu gerbang asrama ditutup. Setibanya di pondok, harap melakukan *Scan Barcode Kembali* di pos piket/satpam.\n\n"
                   . "Jazakumullah khairan katsiran atas kerja samanya.\n"
                   . "_Pengurus Asrama Pesantren Tahfizh Alam Qur'an_";

            $message = NotificationService::send([
                'recipient_number' => $noWa,
                'recipient_name' => $izin->user?->name,
                'user_id' => $izin->user_id,
                'santri_id' => $izin->santri_id,
                'modul' => 'Perizinan',
                'message' => $pesan,
                'priority' => 'high',
                'reference_id' => 'IZIN-REMINDER-' . $izin->id,
            ]);
            if ($message) {
                $izin->update(['wa_reminder_sent' => true]);
                $sentCount++;
            }
        }

        $this->info("Selesai! {$sentCount} pesan reminder WA telah berhasil dikirimkan ke wali santri.");
        return Command::SUCCESS;
    }
}
