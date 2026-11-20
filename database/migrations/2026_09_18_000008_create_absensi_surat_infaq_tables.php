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
        // 1. Absensi Masuk Kelas Santri
        Schema::create('absensi_santris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('kelas', 10); // '1', '2', '3'
            $table->enum('status_kehadiran', ['hadir', 'sakit', 'izin', 'alpa'])->default('hadir');
            $table->text('catatan')->nullable();
            $table->foreignId('guru_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->unique(['santri_id', 'tanggal']);
            $table->index(['tanggal', 'kelas']);
        });

        // 2. Absensi Kehadiran Guru / Ustadz
        Schema::create('absensi_gurus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->enum('status_kehadiran', ['hadir', 'sakit', 'izin', 'alpa'])->default('hadir');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['admin_id', 'tanggal']);
        });

        // 3. Surat Keluar Resmi
        Schema::create('surat_keluars', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique();
            $table->date('tanggal_surat');
            $table->string('tujuan_surat');
            $table->string('perihal');
            $table->text('isi_surat');
            $table->string('penandatangan')->default('Pimpinan Pesantren');
            $table->string('file_lampiran')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        // 4. Arsip Digital Surat Masuk
        Schema::create('surat_masuks', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->date('tanggal_terima');
            $table->date('tanggal_surat');
            $table->string('asal_instansi');
            $table->string('perihal');
            $table->text('ringkasan_isi')->nullable();
            $table->string('file_arsip'); // PDF / Gambar scan
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Program Kampanye Donasi / Sedekah / Infaq
        Schema::create('infaq_programs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_program');
            $table->text('deskripsi');
            $table->decimal('target_dana', 14, 2)->default(0);
            $table->decimal('dana_terkumpul', 14, 2)->default(0);
            $table->decimal('dana_tersalurkan', 14, 2)->default(0);
            $table->string('foto_banner')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Transaksi Infaq / Sedekah Online
        Schema::create('infaq_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infaq_program_id')->constrained('infaq_programs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('santri_id')->nullable()->constrained('santri')->nullOnDelete();
            $table->string('nama_donatur');
            $table->string('no_wa_donatur')->nullable();
            $table->decimal('nominal', 12, 2);
            $table->enum('metode_pembayaran', ['potong_saldo', 'transfer'])->default('transfer');
            $table->string('bukti_transfer')->nullable();
            $table->enum('status', ['menunggu_konfirmasi', 'berhasil', 'ditolak'])->default('berhasil');
            $table->text('doa_harapan')->nullable();
            $table->foreignId('verified_by_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infaq_transaksis');
        Schema::dropIfExists('infaq_programs');
        Schema::dropIfExists('surat_masuks');
        Schema::dropIfExists('surat_keluars');
        Schema::dropIfExists('absensi_gurus');
        Schema::dropIfExists('absensi_santris');
    }
};
