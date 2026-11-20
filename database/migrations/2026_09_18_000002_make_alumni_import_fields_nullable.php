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
            $table->string('tempat_lahir')->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->change();
            $table->string('nik', 16)->nullable()->change();
            $table->string('sekolah_asal')->nullable()->change();
            $table->string('npsn')->nullable()->change();
            $table->string('nisn', 10)->nullable()->change();
            $table->integer('anak_ke')->nullable()->change();
            $table->integer('dari_bersaudara')->nullable()->change();
            $table->text('tujuan_mendaftar')->nullable()->change();
        });

        Schema::table('orang_tua', function (Blueprint $table) {
            $table->string('no_kk', 16)->nullable()->change();
            $table->string('nik_ayah', 16)->nullable()->change();
            $table->string('tempat_lahir_ayah')->nullable()->change();
            $table->date('tanggal_lahir_ayah')->nullable()->change();
            $table->string('pekerjaan_ayah')->nullable()->change();
            $table->enum('penghasilan_ayah', ['Rp500.000–Rp1.000.000', 'Rp1.000.000–Rp2.000.000', 'Rp2.000.000–Rp5.000.000', 'Lainnya'])->nullable()->change();
            $table->text('alamat_domisili_ayah')->nullable()->change();

            $table->string('nama_ibu')->nullable()->change();
            $table->string('nik_ibu', 16)->nullable()->change();
            $table->string('tempat_lahir_ibu')->nullable()->change();
            $table->date('tanggal_lahir_ibu')->nullable()->change();
            $table->string('pekerjaan_ibu')->nullable()->change();
            $table->enum('penghasilan_ibu', ['Rp500.000–Rp1.000.000', 'Rp1.000.000–Rp2.000.000', 'Rp2.000.000–Rp5.000.000', 'Lainnya'])->nullable()->change();
            $table->text('alamat_domisili_ibu')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tetap nullable jika di-rollback untuk menjaga integritas data alumni yang sudah diinput
    }
};
