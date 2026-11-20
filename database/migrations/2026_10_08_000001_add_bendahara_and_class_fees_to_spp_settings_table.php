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
        Schema::table('spp_settings', function (Blueprint $table) {
            $table->string('no_wa_bendahara', 50)->nullable()->after('atas_nama_rekening');
            $table->decimal('biaya_admin', 12, 2)->default(2500)->after('no_wa_bendahara');
            $table->decimal('nominal_kelas_1', 12, 2)->nullable()->after('biaya_admin');
            $table->decimal('nominal_kelas_2', 12, 2)->nullable()->after('nominal_kelas_1');
            $table->decimal('nominal_kelas_3', 12, 2)->nullable()->after('nominal_kelas_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spp_settings', function (Blueprint $table) {
            $table->dropColumn([
                'no_wa_bendahara',
                'biaya_admin',
                'nominal_kelas_1',
                'nominal_kelas_2',
                'nominal_kelas_3',
            ]);
        });
    }
};
