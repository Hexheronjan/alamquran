<?php

namespace App\Console\Commands;

use App\Models\Santri;
use App\Models\SppSetting;
use App\Models\SppTagihan;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BroadcastMonthlySppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spp:broadcast-monthly {--nominal=1000000 : Nominal default tagihan SPP bulanan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis buat tagihan dan broadcast rincian SPP transfer manual tanggal 1 ke wali santri';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bulan = (int) date('n');
        $tahun = (int) date('Y');
        $nominal = (float) $this->option('nominal');
        $namaBulan = SppTagihan::getNamaBulan($bulan);
        $rekening = SppSetting::getActive();
        $biayaAdmin = $rekening?->biaya_admin ? (float) $rekening->biaya_admin : 2500.0;
        $defaultRekeningTujuan = $rekening
            ? trim("{$rekening->nama_bank} {$rekening->nomor_rekening} a.n. {$rekening->atas_nama_rekening}")
            : 'Rekening pondok';

        $this->info("Memproses tagihan dan broadcast SPP untuk {$namaBulan} {$tahun}...");

        $santris = Santri::aktif()
            ->with(['orangTua', 'user', 'addonTemplates'])
            ->get();

        $broadcastCount = 0;

        foreach ($santris as $santri) {
            // Syarat mutlak: Lewati santri yang SPP & rekeningnya belum diatur oleh admin
            if (empty($santri->nomor_rekening_spp) && !$santri->is_beasiswa) {
                continue;
            }

            // Tentukan nominal: prioritas Beasiswa -> nominal SPP khusus santri -> nominal default
            if ($santri->is_beasiswa) {
                $nominalSantri = 0.0;
            } elseif ($santri->nominal_spp !== null && (float) $santri->nominal_spp > 0) {
                $nominalSantri = (float) $santri->nominal_spp;
            } else {
                $nominalSantri = $nominal;
            }

            $isBeasiswa = ($nominalSantri == 0.0);
            $rekeningSantri = $santri->nomor_rekening_spp ?: null;
            $rekSantri = $santri->info_rekening_spp;
            $rekeningTujuan = !empty($santri->nomor_rekening_spp)
                ? "{$rekSantri['bank']} {$rekSantri['nomor_rekening']} ({$rekSantri['atas_nama']})"
                : $defaultRekeningTujuan;

            $tagihan = SppTagihan::firstOrCreate(
                [
                    'santri_id' => $santri->id,
                    'bulan'     => $bulan,
                    'tahun'     => $tahun,
                ],
                [
                    'nominal_spp'      => $nominalSantri,
                    'nominal_addon'    => 0,
                    'nominal_tagihan'  => $nominalSantri,
                    'nominal_terbayar' => 0,
                    'sisa_tagihan'     => $nominalSantri,
                    'status'           => $isBeasiswa ? 'lunas' : 'belum_lunas',
                    'nomor_va'         => $rekeningSantri,
                    'keterangan'       => $isBeasiswa ? "SPP {$namaBulan} {$tahun} (Beasiswa Full)" : "SPP {$namaBulan} {$tahun}",
                ]
            );

            // Otomatis masukkan template add-on santri (e.g. Laundry, Penggaris, dll) jika tagihan baru dibuat
            if ($tagihan->wasRecentlyCreated && $santri->addonTemplates && $santri->addonTemplates->isNotEmpty()) {
                foreach ($santri->addonTemplates->where('is_active', true) as $tpl) {
                    $tagihan->addons()->create([
                        'nama_biaya' => $tpl->nama_biaya,
                        'nominal'    => (float) $tpl->nominal,
                        'keterangan' => $tpl->keterangan,
                    ]);
                }
                $tagihan->syncTotals();
                $tagihan->refresh();
            }

            // Jika belum lunas dan bukan beasiswa, kirim WA ke wali hanya jika nomor rekening/VA santri sudah diatur
            if ($tagihan->status !== 'lunas' && !$isBeasiswa) {
                $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
                if ($noWa && $noWa !== '-' && !empty($santri->nomor_rekening_spp)) {
                    $message = NotificationService::sendSppTagihan(
                        $santri,
                        $bulan,
                        $tahun,
                        (float) $tagihan->nominal_tagihan,
                        $rekeningTujuan,
                        (string) $tagihan->id,
                        $biayaAdmin
                    );
                    if ($message) {
                        $tagihan->update(['broadcast_wa_at' => now()]);
                        $broadcastCount++;
                    }
                }
            }
        }

        $this->info("Selesai! Berhasil memproses tagihan santri dan mengirimkan broadcast ke {$broadcastCount} wali santri.");
        return Command::SUCCESS;
    }
}
