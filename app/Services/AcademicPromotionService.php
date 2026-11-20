<?php

namespace App\Services;

use App\Models\KelasHistory;
use App\Models\Santri;
use Illuminate\Support\Facades\DB;

class AcademicPromotionService
{
    /**
     * Jalankan proses kenaikan kelas dan kelulusan massal santri:
     * - Kelas 1 -> Kelas 2
     * - Kelas 2 -> Kelas 3
     * - Kelas 3 -> Alumni (status_santri = 'lulus', tahun_lulus = tahun berjalan)
     *
     * Dilindungi dari double-promotion dengan kolom `promoted_tahun_ajaran`.
     *
     * @param  array       $excludedSantriIds ID santri yang dikecualikan
     * @param  string|null $tahunLulus        Tahun kelulusan untuk kelas 3 (default: tahun ini)
     * @param  string|null $tahunAjaranBaru   Tahun ajaran baru (contoh: "2026/2027"). Default: auto-generate
     * @param  string      $diprosesOleh      'system' atau nama admin
     * @return array       Ringkasan proses
     */
    public static function promoteAll(
        array $excludedSantriIds = [],
        ?string $tahunLulus = null,
        ?string $tahunAjaranBaru = null,
        string $diprosesOleh = 'system'
    ): array {
        $year = $tahunLulus ?: date('Y');

        // Generate tahun ajaran baru secara otomatis jika tidak diberikan
        // Tahun ajaran baru: {$year}/{$year+1}
        $tahunAjaran = $tahunAjaranBaru ?: ($year . '/' . ($year + 1));

        return DB::transaction(function () use ($excludedSantriIds, $year, $tahunAjaran, $diprosesOleh) {

            // ====================================================================
            // 1. Santri Kelas 3 -> Alumni / Lulus
            //    Hanya yang belum pernah diproses di tahun ajaran ini
            // ====================================================================
            $graduating = Santri::where('status', 'diterima')
                ->where('status_santri', 'aktif')
                ->where('kelas', '3')
                ->where(function ($q) use ($year) {
                    $q->where(function ($sub) use ($year) {
                        $sub->whereNotNull('tahun_masuk')
                            ->where('tahun_masuk', '<=', (string) $year);
                    })->orWhere(function ($sub) use ($year) {
                        $sub->whereNull('tahun_masuk')
                            ->whereYear('created_at', '<=', $year);
                    });
                })
                ->where(function ($q) use ($tahunAjaran) {
                    $q->whereNull('promoted_tahun_ajaran')
                      ->orWhere('promoted_tahun_ajaran', '!=', $tahunAjaran);
                })
                ->whereNotIn('id', $excludedSantriIds)
                ->get();

            foreach ($graduating as $s) {
                // Simpan riwayat kelas
                KelasHistory::create([
                    'santri_id'     => $s->id,
                    'kelas_dari'    => '3',
                    'kelas_ke'      => 'alumni',
                    'tahun_ajaran'  => $tahunAjaran,
                    'tahun_kenaikan' => (int) $year,
                    'diproses_oleh' => $diprosesOleh,
                    'keterangan'    => "Kelulusan otomatis — santri kelas 9 lulus menjadi Alumni tahun {$year}.",
                ]);

                $s->update([
                    'kelas'                  => 'alumni',
                    'status_santri'          => 'lulus',
                    'tahun_lulus'            => $year,
                    'promoted_tahun_ajaran'  => $tahunAjaran,
                ]);
                NotificationService::send([
                    'recipient_number' => $s->orangTua?->no_wa_ayah ?: $s->user?->no_wa,
                    'recipient_name' => $s->orangTua?->nama_ayah,
                    'user_id' => $s->user_id, 'santri_id' => $s->id, 'modul' => 'Akademik',
                    'reference_id' => "KELULUSAN-{$s->id}-{$tahunAjaran}",
                    'message' => "Selamat, {$s->nama_lengkap} telah menyelesaikan pendidikan dan dinyatakan lulus pada tahun ajaran {$tahunAjaran}.",
                ]);
            }

            // ====================================================================
            // 2. Santri Kelas 2 -> Kelas 3
            //    Hanya yang belum pernah diproses di tahun ajaran ini
            // ====================================================================
            $to3 = Santri::where('status', 'diterima')
                ->where('status_santri', 'aktif')
                ->where('kelas', '2')
                ->where(function ($q) use ($year) {
                    $q->where(function ($sub) use ($year) {
                        $sub->whereNotNull('tahun_masuk')
                            ->where('tahun_masuk', '<=', (string) $year);
                    })->orWhere(function ($sub) use ($year) {
                        $sub->whereNull('tahun_masuk')
                            ->whereYear('created_at', '<=', $year);
                    });
                })
                ->where(function ($q) use ($tahunAjaran) {
                    $q->whereNull('promoted_tahun_ajaran')
                      ->orWhere('promoted_tahun_ajaran', '!=', $tahunAjaran);
                })
                ->whereNotIn('id', $excludedSantriIds)
                ->get();

            foreach ($to3 as $s) {
                KelasHistory::create([
                    'santri_id'      => $s->id,
                    'kelas_dari'     => '2',
                    'kelas_ke'       => '3',
                    'tahun_ajaran'   => $tahunAjaran,
                    'tahun_kenaikan' => (int) $year,
                    'diproses_oleh'  => $diprosesOleh,
                    'keterangan'     => "Naik kelas dari Kelas 2 ke Kelas 3, Tahun Ajaran {$tahunAjaran}.",
                ]);

                $s->update([
                    'kelas'                 => '3',
                    'promoted_tahun_ajaran' => $tahunAjaran,
                ]);
                NotificationService::send([
                    'recipient_number' => $s->orangTua?->no_wa_ayah ?: $s->user?->no_wa,
                    'recipient_name' => $s->orangTua?->nama_ayah,
                    'user_id' => $s->user_id, 'santri_id' => $s->id, 'modul' => 'Akademik',
                    'reference_id' => "NAIK-KELAS-{$s->id}-{$tahunAjaran}",
                    'message' => "{$s->nama_lengkap} naik ke Kelas 3 untuk tahun ajaran {$tahunAjaran}.",
                ]);
            }

            // ====================================================================
            // 3. Santri Kelas 1 -> Kelas 2
            //    Hanya yang belum pernah diproses di tahun ajaran ini DAN
            //    tahun masuk <= tahun kelulusan/kenaikan (angkatan baru mendatang tetap di Kelas 1)
            // ====================================================================
            $to2 = Santri::where('status', 'diterima')
                ->where('status_santri', 'aktif')
                ->where('kelas', '1')
                ->where(function ($q) use ($year) {
                    $q->where(function ($sub) use ($year) {
                        $sub->whereNotNull('tahun_masuk')
                            ->where('tahun_masuk', '<=', (string) $year);
                    })->orWhere(function ($sub) use ($year) {
                        $sub->whereNull('tahun_masuk')
                            ->whereYear('created_at', '<=', $year);
                    });
                })
                ->where(function ($q) use ($tahunAjaran) {
                    $q->whereNull('promoted_tahun_ajaran')
                      ->orWhere('promoted_tahun_ajaran', '!=', $tahunAjaran);
                })
                ->whereNotIn('id', $excludedSantriIds)
                ->get();

            foreach ($to2 as $s) {
                KelasHistory::create([
                    'santri_id'      => $s->id,
                    'kelas_dari'     => '1',
                    'kelas_ke'       => '2',
                    'tahun_ajaran'   => $tahunAjaran,
                    'tahun_kenaikan' => (int) $year,
                    'diproses_oleh'  => $diprosesOleh,
                    'keterangan'     => "Naik kelas dari Kelas 1 ke Kelas 2, Tahun Ajaran {$tahunAjaran}.",
                ]);

                $s->update([
                    'kelas'                 => '2',
                    'promoted_tahun_ajaran' => $tahunAjaran,
                ]);
                NotificationService::send([
                    'recipient_number' => $s->orangTua?->no_wa_ayah ?: $s->user?->no_wa,
                    'recipient_name' => $s->orangTua?->nama_ayah,
                    'user_id' => $s->user_id, 'santri_id' => $s->id, 'modul' => 'Akademik',
                    'reference_id' => "NAIK-KELAS-{$s->id}-{$tahunAjaran}",
                    'message' => "{$s->nama_lengkap} naik ke Kelas 2 untuk tahun ajaran {$tahunAjaran}.",
                ]);
            }

            $stayIn1Count = Santri::where('status', 'diterima')
                ->where('status_santri', 'aktif')
                ->where('kelas', '1')
                ->where('tahun_masuk', '>', (string) $year)
                ->count();

            $summary = [
                'kelas_1_ke_2'   => $to2->count(),
                'kelas_2_ke_3'   => $to3->count(),
                'kelas_3_alumni' => $graduating->count(),
                'tetap_kelas_1'  => $stayIn1Count,
                'total_diproses' => $to2->count() + $to3->count() + $graduating->count(),
                'tahun_ajaran'   => $tahunAjaran,
            ];

            AdminActivityLogger::log(
                'kenaikan_kelas_massal',
                "Kenaikan kelas & kelulusan massal [{$tahunAjaran}]: {$summary['kelas_1_ke_2']} santri naik ke Kelas 2, {$summary['kelas_2_ke_3']} santri naik ke Kelas 3, {$summary['kelas_3_alumni']} santri lulus menjadi Alumni" . ($stayIn1Count > 0 ? " ({$stayIn1Count} santri baru tetap di Kelas 1)." : "."),
                'AcademicPromotion'
            );

            return $summary;
        });
    }

    /**
     * Naikkan/ubah kelas santri perorangan.
     * Menyimpan riwayat ke kelas_histories.
     */
    public static function updateIndividualClass(
        int $santriId,
        string $targetKelas,
        ?string $tahunLulus = null,
        string $diprosesOleh = 'admin'
    ): Santri {
        $santri    = Santri::findOrFail($santriId);
        $oldKelas  = $santri->kelas_label;
        $year      = $tahunLulus ?: date('Y');
        $tahunAjaran = $year . '/' . ($year + 1);

        $updateData = ['kelas' => $targetKelas];

        if ($targetKelas === 'alumni') {
            $updateData['status_santri'] = 'lulus';
            $updateData['tahun_lulus']   = $year;
        } else {
            $updateData['status_santri'] = 'aktif';
        }

        $santri->update($updateData);

        // Simpan ke riwayat
        KelasHistory::create([
            'santri_id'      => $santri->id,
            'kelas_dari'     => $santri->getOriginal('kelas'),
            'kelas_ke'       => $targetKelas,
            'tahun_ajaran'   => $tahunAjaran,
            'tahun_kenaikan' => (int) $year,
            'diproses_oleh'  => $diprosesOleh,
            'keterangan'     => "Perubahan kelas manual oleh admin: {$oldKelas} -> {$santri->kelas_label}.",
        ]);

        AdminActivityLogger::log(
            'update_kelas_santri',
            "Mengubah jenjang santri '{$santri->nama_lengkap}' dari {$oldKelas} menjadi {$santri->kelas_label}",
            'Santri',
            $santri->id
        );

        return $santri;
    }

    /**
     * Cek apakah proses kenaikan kelas sudah pernah dilakukan untuk tahun ajaran tertentu.
     * Berguna untuk menampilkan peringatan di UI sebelum menjalankan proses.
     */
    public static function checkAlreadyPromoted(string $tahunAjaran): array
    {
        $count = Santri::where('promoted_tahun_ajaran', $tahunAjaran)->count();
        return [
            'already_promoted' => $count > 0,
            'count'            => $count,
            'tahun_ajaran'     => $tahunAjaran,
        ];
    }
}
