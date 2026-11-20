<?php

namespace App\Console\Commands;

use App\Models\SppTagihan;
use App\Models\SppSetting;
use App\Models\WhatsappSetting;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendSppReminderCommand extends Command
{
    protected $signature = 'spp:send-reminders';
    protected $description = 'Mengantrekan pengingat SPP pada tanggal yang diatur di WhatsApp Gateway';

    public function handle(): int
    {
        $days = collect(explode(',', WhatsappSetting::get('spp_reminder_days', '1,5,8,10')))
            ->map(fn ($day) => (int) trim($day))
            ->filter(fn ($day) => $day >= 1 && $day <= 31);

        if (!$days->contains(now()->day)) {
            return self::SUCCESS;
        }

        $queued = 0;
        $rekening = SppSetting::getActive();
        $biayaAdmin = $rekening?->biaya_admin ? (float) $rekening->biaya_admin : 2500.0;
        $rekeningTujuan = $rekening
            ? trim("{$rekening->nama_bank} {$rekening->nomor_rekening} a.n. {$rekening->atas_nama_rekening}")
            : 'Hubungi bagian administrasi untuk rekening tujuan';
        $tagihans = SppTagihan::with(['santri.orangTua', 'santri.user', 'addons'])
            ->where('bulan', now()->month)
            ->where('tahun', now()->year)
            ->where('status', '!=', 'lunas')
            ->where('sisa_tagihan', '>', 0)
            ->get();

        foreach ($tagihans as $tagihan) {
            $santri = $tagihan->santri;
            if (!$santri || $tagihan->status === 'lunas') {
                continue;
            }

            // Hanya kirim pengingat jika nomor rekening/VA santri sudah diatur
            if (empty($santri->nomor_rekening_spp)) {
                continue;
            }

            $rekSantri = $santri->info_rekening_spp;
            $rekTujuanSantri = "{$rekSantri['bank']} {$rekSantri['nomor_rekening']} ({$rekSantri['atas_nama']})";

            $message = NotificationService::sendSppTagihan(
                $santri,
                (int) $tagihan->bulan,
                (int) $tagihan->tahun,
                (float) $tagihan->nominal_tagihan,
                $rekTujuanSantri,
                'REMINDER-' . $tagihan->id . '-' . now()->format('Ymd'),
                $biayaAdmin
            );
            $queued += $message ? 1 : 0;
        }

        $this->info("{$queued} pengingat SPP dimasukkan ke antrean.");
        return self::SUCCESS;
    }
}
