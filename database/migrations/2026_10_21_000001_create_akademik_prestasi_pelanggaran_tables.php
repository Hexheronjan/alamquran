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
        // 1. Tabel Pelanggaran & Kedisiplinan Santri
        if (!Schema::hasTable('pelanggaran_santris')) {
            Schema::create('pelanggaran_santris', function (Blueprint $table) {
                $table->id();
                $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
                $table->date('tanggal');
                $table->string('kategori', 50)->default('sedang'); // ringan, sedang, berat
                $table->string('nama_pelanggaran', 255);
                $table->string('konsekuensi', 255); // Botak / Cukur, Diskors, Dikeluarkan, Ta'zir, dll.
                $table->string('status_pembinaan', 50)->default('sedang_dijalani'); // sedang_dijalani, selesai, dibatalkan
                $table->integer('poin')->default(0);
                $table->text('catatan')->nullable();
                $table->string('petugas_ustadz', 150)->nullable();
                $table->string('foto_bukti', 255)->nullable();
                $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 2. Tabel Saran & Masukan untuk Pengembangan Aplikasi
        if (!Schema::hasTable('saran_masukans')) {
            Schema::create('saran_masukans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->string('nama_pengirim', 150);
                $table->string('tipe_pengirim', 50)->default('wali_santri'); // wali_santri, admin, guru
                $table->string('kategori', 50)->default('saran'); // fitur_baru, tampilan_ui, kendala_bug, saran, lainnya
                $table->string('judul', 200);
                $table->text('pesan');
                $table->string('lampiran', 255)->nullable();
                $table->string('status', 50)->default('menunggu_review'); // menunggu_review, sedang_diproses, diterapkan, diarsipkan
                $table->text('tanggapan_admin')->nullable();
                $table->foreignId('responded_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tabel Kalender Akademik Alam Qur'an (Juli - Juni)
        if (!Schema::hasTable('kalender_akademiks')) {
            Schema::create('kalender_akademiks', function (Blueprint $table) {
                $table->id();
                $table->string('tahun_ajaran', 20)->default('2026/2027');
                $table->string('judul_kegiatan', 255);
                $table->string('kategori', 50)->default('kegiatan'); // libur, ujian, kedatangan, sambangan, phbi, asrama, kegiatan
                $table->string('warna', 30)->default('#0d9488'); // Hex color code
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->text('keterangan')->nullable();
                $table->boolean('is_public')->default(true);
                $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 4. Tabel Prestasi Santri (Dokumentasi Portofolio Tahunan)
        if (!Schema::hasTable('santri_prestasis')) {
            Schema::create('santri_prestasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
                $table->string('tahun_ajaran', 20)->default('2026/2027');
                $table->string('nama_prestasi', 255);
                $table->string('kategori', 50)->default('tahfidz'); // tahfidz, akademik, bahasa, seni_kaligrafi, olahraga, lainnya
                $table->string('tingkat', 50)->default('pondok'); // pondok, kecamatan, kabupaten, provinsi, nasional, internasional
                $table->string('peringkat', 50)->default('Juara 1'); // Juara 1, 2, 3, Harapan 1, Terbaik, dll
                $table->string('penyelenggara', 200)->nullable();
                $table->date('tanggal_perolehan')->nullable();
                $table->text('deskripsi')->nullable();
                $table->string('foto_piagam', 255)->nullable();
                $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 5. Tambah kolom Pelacakan Login & Aktivitas Online pada tabel users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
            if (!Schema::hasColumn('users', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('last_login_ip');
            }
            if (!Schema::hasColumn('users', 'login_count')) {
                $table->unsignedInteger('login_count')->default(0)->after('last_activity_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santri_prestasis');
        Schema::dropIfExists('kalender_akademiks');
        Schema::dropIfExists('saran_masukans');
        Schema::dropIfExists('pelanggaran_santris');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'last_login_ip', 'last_activity_at', 'login_count']);
        });
    }
};
