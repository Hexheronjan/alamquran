<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoice_settings')) {
            Schema::create('invoice_settings', function (Blueprint $table) {
                $table->id();
                $table->string('nama_lembaga', 200)->default("Pesantren Tahfizh Alam Quran");
                $table->string('tagline_kop', 255)->default("Lembaga Pendidikan dan Pembinaan Penghafal Al-Quran");
                $table->text('alamat')->nullable();
                $table->string('telepon', 100)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('website', 150)->nullable();
                $table->string('logo', 255)->nullable();
                $table->string('nama_bendahara', 150)->default('Bendahara Pesantren');
                $table->string('jabatan_penandatangan', 100)->default('Bendahara Pesantren');
                $table->text('catatan_bawah')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_settings');
    }
};
