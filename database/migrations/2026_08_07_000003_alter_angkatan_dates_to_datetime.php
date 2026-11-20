<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('angkatan', function (Blueprint $table) {
            $table->dateTime('tanggal_mulai_daftar')->change();
            $table->dateTime('tanggal_tutup_daftar')->change();
        });
    }

    public function down(): void
    {
        Schema::table('angkatan', function (Blueprint $table) {
            $table->date('tanggal_mulai_daftar')->change();
            $table->date('tanggal_tutup_daftar')->change();
        });
    }
};
