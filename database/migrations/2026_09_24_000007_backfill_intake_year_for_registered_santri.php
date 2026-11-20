<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Give prior registrations an intake year from their selected angkatan.
     * This also corrects legacy class values (7-9) on records that did not
     * yet have a tahun_masuk.
     */
    public function up(): void
    {
        DB::table('santri')
            ->whereNull('tahun_masuk')
            ->whereNotNull('angkatan_id')
            ->orderBy('id')
            ->each(function (object $santri): void {
                $angkatan = DB::table('angkatan')->where('id', $santri->angkatan_id)->first();

                if (! $angkatan) {
                    return;
                }

                $tahunMasuk = $angkatan->tahun_angkatan
                    ?? (int) explode('/', $angkatan->tahun_ajaran)[0];

                $updates = ['tahun_masuk' => (string) $tahunMasuk];

                if (in_array((string) $santri->kelas, ['', '7', '8', '9'], true)) {
                    $updates['kelas'] = '1';
                }

                if (empty($santri->status_santri)) {
                    $updates['status_santri'] = 'aktif';
                }

                DB::table('santri')->where('id', $santri->id)->update($updates);
            });
    }

    public function down(): void
    {
        // Backfilled values are valid academic data and must not be erased.
    }
};
