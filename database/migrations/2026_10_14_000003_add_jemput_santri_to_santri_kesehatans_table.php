<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modifikasi ENUM status_kesehatan untuk menyertakan 'istirahat_dirumah'
        if (Schema::hasTable('santri_kesehatans')) {
            try {
                DB::statement("ALTER TABLE santri_kesehatans MODIFY COLUMN status_kesehatan ENUM('istirahat_uks', 'rawat_inap', 'sembuh', 'pantauan', 'istirahat_dirumah') DEFAULT 'istirahat_uks'");
            } catch (\Throwable $e) {}

            Schema::table('santri_kesehatans', function (Blueprint $table) {
                if (!Schema::hasColumn('santri_kesehatans', 'tanggal_jemput')) {
                    $table->dateTime('tanggal_jemput')->nullable()->after('status_kesehatan');
                }
                if (!Schema::hasColumn('santri_kesehatans', 'nama_penjemput')) {
                    $table->string('nama_penjemput', 150)->nullable()->after('tanggal_jemput');
                }
                if (!Schema::hasColumn('santri_kesehatans', 'estimasi_kembali')) {
                    $table->date('estimasi_kembali')->nullable()->after('nama_penjemput');
                }
                if (!Schema::hasColumn('santri_kesehatans', 'catatan_penjemputan')) {
                    $table->text('catatan_penjemputan')->nullable()->after('estimasi_kembali');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('santri_kesehatans')) {
            Schema::table('santri_kesehatans', function (Blueprint $table) {
                $columns = ['tanggal_jemput', 'nama_penjemput', 'estimasi_kembali', 'catatan_penjemputan'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('santri_kesehatans', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
            try {
                DB::statement("ALTER TABLE santri_kesehatans MODIFY COLUMN status_kesehatan ENUM('istirahat_uks', 'rawat_inap', 'sembuh', 'pantauan') DEFAULT 'istirahat_uks'");
            } catch (\Throwable $e) {}
        }
    }
};
