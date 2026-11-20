<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uang_saku_settings', function (Blueprint $table) {
            $table->string('bank_deposit')->nullable()->after('template_pesan');
            $table->string('nomor_rekening_deposit')->nullable()->after('bank_deposit');
            $table->string('atas_nama_rekening')->nullable()->after('nomor_rekening_deposit');
        });
    }

    public function down(): void
    {
        Schema::table('uang_saku_settings', function (Blueprint $table) {
            $table->dropColumn(['bank_deposit', 'nomor_rekening_deposit', 'atas_nama_rekening']);
        });
    }
};
