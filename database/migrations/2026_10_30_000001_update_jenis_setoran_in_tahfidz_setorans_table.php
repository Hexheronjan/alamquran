<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tahfidz_setorans') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `tahfidz_setorans` MODIFY COLUMN `jenis_setoran` VARCHAR(50) NOT NULL DEFAULT 'ziyadah'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tahfidz_setorans')) {
            DB::statement("ALTER TABLE `tahfidz_setorans` MODIFY COLUMN `jenis_setoran` ENUM('ziyadah', 'murajaah') NOT NULL DEFAULT 'ziyadah'");
        }
    }
};
