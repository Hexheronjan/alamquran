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
        // 1. Database Surat Al-Qur'an (114 Surat)
        Schema::create('tahfidz_surahs', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor_surah')->unique();
            $table->string('nama_latin');
            $table->string('nama_arab');
            $table->string('arti');
            $table->integer('jumlah_ayat');
            $table->string('tempat_turun'); // Makkiyah / Madaniyah
            $table->timestamps();
        });

        // 2. Setoran Hafalan Harian Santri Kelas 1-3
        Schema::create('tahfidz_setorans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->date('tanggal');
            $table->foreignId('surah_id')->nullable()->constrained('tahfidz_surahs')->nullOnDelete();
            $table->string('nama_surah');
            $table->integer('ayat_awal');
            $table->integer('ayat_akhir');
            $table->integer('juz')->default(1);
            $table->enum('jenis_setoran', ['ziyadah', 'murajaah'])->default('ziyadah');
            $table->enum('kelancaran', ['sangat_lancar', 'lancar', 'kurang_lancar', 'mengulang'])->default('lancar');
            $table->text('catatan_ustadz')->nullable();
            $table->foreignId('ustadz_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['santri_id', 'tanggal']);
        });

        // 3. Kegiatan Harian Santri 24 Jam (Jadwal Baku & Kegiatan Insidental)
        Schema::create('kegiatan_harians', function (Blueprint $table) {
            $table->id();
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->string('nama_kegiatan');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_rutin_24jam')->default(true); // Kegiatan harian otomatis
            $table->date('tanggal_khusus')->nullable(); // Jika kegiatan insidental / manual
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        // 4. Catatan Kesehatan Santri (Rekam Medis UKS / Sakit)
        Schema::create('santri_kesehatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('keluhan_sakit');
            $table->text('tindakan_perawatan')->nullable(); // Istirahat UKS, dirujuk klinik, rawat jalan
            $table->string('obat_diberikan')->nullable();
            $table->enum('status_kesehatan', ['istirahat_uks', 'rawat_inap', 'sembuh', 'pantauan'])->default('istirahat_uks');
            $table->foreignId('petugas_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['santri_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santri_kesehatans');
        Schema::dropIfExists('kegiatan_harians');
        Schema::dropIfExists('tahfidz_setorans');
        Schema::dropIfExists('tahfidz_surahs');
    }
};
