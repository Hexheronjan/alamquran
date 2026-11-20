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
        Schema::create('orang_tua', function (Blueprint $table) {
            $table->id();
            
            // Data Ayah
            $table->string('nama_ayah');
            $table->string('no_wa_ayah');
            $table->string('no_kk', 16)->unique();
            $table->string('nik_ayah', 16);
            $table->string('tempat_lahir_ayah');
            $table->date('tanggal_lahir_ayah');
            $table->string('pekerjaan_ayah');
            $table->enum('penghasilan_ayah', ['Rp500.000–Rp1.000.000', 'Rp1.000.000–Rp2.000.000', 'Rp2.000.000–Rp5.000.000', 'Lainnya']);
            $table->text('alamat_domisili_ayah');
            
            // Data Ibu
            $table->string('nama_ibu');
            $table->string('nik_ibu', 16);
            $table->string('tempat_lahir_ibu');
            $table->date('tanggal_lahir_ibu');
            $table->string('pekerjaan_ibu');
            $table->enum('penghasilan_ibu', ['Rp500.000–Rp1.000.000', 'Rp1.000.000–Rp2.000.000', 'Rp2.000.000–Rp5.000.000', 'Lainnya']);
            $table->text('alamat_domisili_ibu');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orang_tua');
    }
};
