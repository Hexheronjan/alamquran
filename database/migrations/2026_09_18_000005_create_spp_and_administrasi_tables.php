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
        // Tabel Tagihan SPP & Administrasi Bulanan
        Schema::create('spp_tagihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->tinyInteger('bulan'); // 1 - 12
            $table->smallInteger('tahun'); // e.g. 2026
            $table->decimal('nominal_tagihan', 12, 2);
            $table->decimal('nominal_terbayar', 12, 2)->default(0);
            $table->decimal('sisa_tagihan', 12, 2);
            $table->enum('status', ['belum_lunas', 'cicilan', 'lunas'])->default('belum_lunas');
            $table->string('nomor_va', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamp('broadcast_wa_at')->nullable();
            $table->timestamps();

            $table->unique(['santri_id', 'bulan', 'tahun']);
            $table->index(['bulan', 'tahun', 'status']);
        });

        // Tabel Pembayaran & Riwayat Cicilan SPP
        Schema::create('spp_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spp_tagihan_id')->constrained('spp_tagihans')->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->decimal('jumlah_bayar', 12, 2);
            $table->enum('metode_bayar', ['transfer', 'va', 'tunai'])->default('transfer');
            $table->string('bukti_bayar')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['menunggu_konfirmasi', 'diterima', 'ditolak'])->default('menunggu_konfirmasi');
            $table->foreignId('verified_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['santri_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spp_pembayarans');
        Schema::dropIfExists('spp_tagihans');
    }
};
