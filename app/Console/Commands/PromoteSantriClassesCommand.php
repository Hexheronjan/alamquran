<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AcademicPromotionService;

class PromoteSantriClassesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'santri:promote-classes {--year= : Tahun kelulusan untuk kelas 3}';

    /**
     * The console command description.
     */
    protected $description = 'Proses otomatisasi kenaikan kelas santri (Kelas 1 -> 2 -> 3 -> Alumni)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year') ?: date('Y');

        $this->info("Memulai proses kenaikan kelas santri (Tahun kelulusan: {$year})...");

        $result = AcademicPromotionService::promoteAll([], $year);

        $rows = [
            ['Kelas 1 naik ke Kelas 2', $result['kelas_1_ke_2']],
            ['Kelas 2 naik ke Kelas 3', $result['kelas_2_ke_3']],
            ['Kelas 3 lulus menjadi Alumni', $result['kelas_3_alumni']],
        ];

        if (!empty($result['tetap_kelas_1'])) {
            $rows[] = ['Santri Baru (Tetap Kelas 1)', $result['tetap_kelas_1']];
        }

        $rows[] = ['Total Santri Diproses', $result['total_diproses']];

        $this->table(
            ['Kategori', 'Jumlah Santri'],
            $rows
        );

        $this->info('Proses kenaikan kelas berhasil diselesaikan!');
        return Command::SUCCESS;
    }
}
