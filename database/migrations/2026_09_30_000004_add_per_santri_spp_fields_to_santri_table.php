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
        Schema::table('santri', function (Blueprint $table) {
            $table->decimal('nominal_spp', 12, 2)->nullable()->after('wa_saldo_warning_sent');
            $table->string('nama_bank_spp', 100)->nullable()->after('nominal_spp');
            $table->string('nomor_rekening_spp', 50)->nullable()->after('nama_bank_spp');
            $table->string('atas_nama_rekening_spp', 255)->nullable()->after('nomor_rekening_spp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->dropColumn([
                'nominal_spp',
                'nama_bank_spp',
                'nomor_rekening_spp',
                'atas_nama_rekening_spp',
            ]);
        });
    }
};
