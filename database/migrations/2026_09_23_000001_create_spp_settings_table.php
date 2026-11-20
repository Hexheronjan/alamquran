<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bank')->nullable();           // Nama Bank (BSI, BRI, BCA, dll)
            $table->string('nomor_rekening')->nullable();      // Nomor rekening tujuan transfer
            $table->string('atas_nama_rekening')->nullable();  // Nama pemilik rekening
            $table->text('keterangan')->nullable();            // Info tambahan untuk wali
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default record
        \DB::table('spp_settings')->insert([
            'nama_bank'          => null,
            'nomor_rekening'     => null,
            'atas_nama_rekening' => null,
            'keterangan'         => null,
            'is_active'          => true,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('spp_settings');
    }
};
