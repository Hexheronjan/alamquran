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
        // Tambahkan qr_token unik pada tabel users (wali santri)
        Schema::table('users', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique()->after('remember_token');
        });

        // Tabel Presensi Kehadiran Kunjungan Ahad Pagi Wali Santri
        Schema::create('kehadiran_walis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('santri_id')->nullable()->constrained('santri')->nullOnDelete();
            $table->date('tanggal');
            $table->time('waktu_hadir');
            $table->string('kegiatan')->default('Kunjungan Ahad Pagi');
            $table->string('keterangan')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['tanggal', 'user_id']);
        });

        // Tabel Perizinan Santri Keluar / Bawa Pulang
        Schema::create('perizinan_santris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('jenis_izin', 50)->default('bawa_pulang');
            $table->string('tujuan')->nullable();
            $table->string('nama_penjemput');
            $table->string('no_wa_penjemput');
            $table->dateTime('waktu_keluar');
            $table->string('batas_jam_kembali', 50)->nullable()->default('17:00:00');
            $table->dateTime('waktu_kembali')->nullable();
            $table->string('status', 50)->default('keluar');
            $table->boolean('wa_reminder_sent')->default(false);
            $table->text('catatan')->nullable();
            $table->foreignId('petugas_keluar_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('petugas_kembali_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'waktu_keluar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perizinan_santris');
        Schema::dropIfExists('kehadiran_walis');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
