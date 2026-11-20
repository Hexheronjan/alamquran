<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE perizinan_santris MODIFY jenis_izin VARCHAR(50) NOT NULL DEFAULT 'bawa_pulang'");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE perizinan_santris MODIFY status VARCHAR(50) NOT NULL DEFAULT 'keluar'");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE perizinan_santris MODIFY batas_jam_kembali VARCHAR(50) NULL DEFAULT '17:00:00'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE perizinan_santris MODIFY jenis_izin ENUM('bawa_pulang','keluar_sebentar','keperluan_khusus') NOT NULL DEFAULT 'bawa_pulang'");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE perizinan_santris MODIFY status ENUM('keluar','kembali','terlambat') NOT NULL DEFAULT 'keluar'");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE perizinan_santris MODIFY batas_jam_kembali TIME NOT NULL DEFAULT '17:00:00'");
        }
    }
};
