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
        Schema::table('santri', function (Blueprint $table) {
            $table->string('nis', 30)->nullable()->unique()->after('nisn');
            $table->string('foto_santri')->nullable()->after('nis');
            $table->string('kelas', 20)->default('1')->after('status');
            $table->string('status_santri', 20)->default('aktif')->after('kelas'); // aktif, lulus, mutasi, keluar
            $table->string('tahun_masuk', 10)->nullable()->after('status_santri');
            $table->string('tahun_lulus', 10)->nullable()->after('tahun_masuk');
        });

        // Update santri yang sudah diterima menjadi kelas 7 aktif jika belum diset
        DB::table('santri')->where('status', 'diterima')->update([
            'kelas' => '1',
            'status_santri' => 'aktif',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->dropColumn([
                'nis',
                'foto_santri',
                'kelas',
                'status_santri',
                'tahun_masuk',
                'tahun_lulus',
            ]);
        });
    }
};
