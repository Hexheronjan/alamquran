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
        if (!Schema::hasTable('daftar_ulang_settings')) {
            Schema::create('daftar_ulang_settings', function (Blueprint $table) {
                $table->id();
                $table->string('status', 20)->default('buka'); // buka, tutup, otomatis
                $table->date('tanggal_buka')->nullable();
                $table->text('pesan_tutup')->nullable();
                $table->boolean('auto_notif_wa')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftar_ulang_settings');
    }
};
