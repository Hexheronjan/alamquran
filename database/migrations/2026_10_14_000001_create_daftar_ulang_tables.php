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
        // 1. Tabel Tagihan Biaya Masuk / Daftar Ulang Santri Baru
        if (!Schema::hasTable('daftar_ulang_tagihans')) {
            Schema::create('daftar_ulang_tagihans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
                $table->string('tahun_angkatan', 20)->default(date('Y'));
                $table->string('gelombang', 50)->nullable();
                $table->decimal('nominal_biaya', 12, 2)->default(0);
                $table->decimal('nominal_terbayar', 12, 2)->default(0);
                $table->decimal('sisa_tagihan', 12, 2)->default(0);
                $table->enum('status', ['belum_lunas', 'cicilan', 'lunas'])->default('belum_lunas');
                $table->string('nomor_va', 50)->nullable();
                $table->text('keterangan')->nullable();
                $table->boolean('is_ready')->default(false); // true jika rekening & biaya sudah diset admin
                $table->timestamps();

                $table->index(['santri_id', 'tahun_angkatan']);
            });
        }

        // 2. Tabel Riwayat Pembayaran / Cicilan Daftar Ulang
        if (!Schema::hasTable('daftar_ulang_pembayarans')) {
            Schema::create('daftar_ulang_pembayarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('daftar_ulang_tagihan_id')->constrained('daftar_ulang_tagihans')->cascadeOnDelete();
                $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
                $table->decimal('jumlah_bayar', 12, 2);
                $table->enum('metode_bayar', ['transfer', 'tunai'])->default('transfer');
                $table->string('bukti_bayar')->nullable();
                $table->string('catatan')->nullable();
                $table->enum('status', ['menunggu_konfirmasi', 'diterima', 'ditolak'])->default('menunggu_konfirmasi');
                $table->string('alasan_penolakan')->nullable();
                $table->foreignId('verified_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->dateTime('verified_at')->nullable();
                $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['daftar_ulang_tagihan_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftar_ulang_pembayarans');
        Schema::dropIfExists('daftar_ulang_tagihans');
    }
};
