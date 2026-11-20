<?php
/**
 * Script Pembaruan & Diagnostik Database SPP Otomatis
 * URL: https://app.alamquran.com/migrate_spp.php
 */

define('LARAVEL_START', microtime(true));

$paths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
];

$autoload = null;
foreach ($paths as $p) {
    if (file_exists($p)) {
        $autoload = $p;
        break;
    }
}

if (!$autoload) {
    die("Autoload tidak ditemukan. Pastikan file ini berada di folder public_html atau public.");
}

require $autoload;

$appPaths = [
    __DIR__ . '/../bootstrap/app.php',
    __DIR__ . '/bootstrap/app.php',
];

$appFile = null;
foreach ($appPaths as $p) {
    if (file_exists($p)) {
        $appFile = $p;
        break;
    }
}

if (!$appFile) {
    die("Bootstrap app.php tidak ditemukan.");
}

$app = require_once $appFile;
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

// Handle update nomor bendahara langsung jika dikirim via form halaman ini
$postMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_wa_direct'])) {
    $waInput = trim($_POST['direct_no_wa'] ?? '');
    if (!empty($waInput)) {
        // Format nomor jika perlu
        $waClean = preg_replace('/[^0-9]/', '', $waInput);
        if (str_starts_with($waClean, '0')) {
            $waClean = '62' . substr($waClean, 1);
        }
        
        $exists = DB::table('spp_settings')->first();
        if ($exists) {
            DB::table('spp_settings')->where('id', $exists->id)->update([
                'no_wa_bendahara' => $waClean,
                'updated_at'      => now(),
            ]);
        } else {
            DB::table('spp_settings')->insert([
                'no_wa_bendahara' => $waClean,
                'biaya_admin'     => 2500,
                'is_active'       => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
        $postMessage = "✔ Berhasil menyimpan nomor WhatsApp Bendahara langsung ke database: <strong>{$waClean}</strong>";
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembaruan & Diagnostik Database SPP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 25px; background: #f8fafc; color: #1e293b; line-height: 1.6; }
        .card { background: #ffffff; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        h1 { color: #0f766e; font-size: 22px; margin-top: 0; }
        h2 { font-size: 16px; margin-top: 0; color: #334155; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
        .badge-success { background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 6px; font-weight: bold; display: inline-block; }
        .badge-info { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-weight: bold; display: inline-block; }
        .badge-danger { background: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 6px; font-weight: bold; display: inline-block; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 12px; text-align: left; }
        th { background: #f1f5f9; color: #475569; }
        input[type="text"] { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; width: 260px; }
        button { background: #0d9488; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; }
        button:hover { background: #0f766e; }
        .msg-box { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 15px; }
    </style>
</head>
<body>

    <h1>Pembaruan & Diagnostik Database SPP Alam Qur'an</h1>

    <?php if (!empty($postMessage)): ?>
        <div class="msg-box"><?= $postMessage ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>1. Eksekusi Skema Database SPP (Tabel & Kolom)</h2>
        <?php
        $logs = [];

        // 1. Periksa tabel spp_settings
        if (!Schema::hasTable('spp_settings')) {
            Schema::create('spp_settings', function (Blueprint $table) {
                $table->id();
                $table->string('nama_bank', 100)->nullable();
                $table->string('nomor_rekening', 50)->nullable();
                $table->string('atas_nama_rekening', 255)->nullable();
                $table->string('no_wa_bendahara', 50)->nullable();
                $table->decimal('biaya_admin', 12, 2)->default(2500);
                $table->decimal('nominal_kelas_1', 12, 2)->nullable();
                $table->decimal('nominal_kelas_2', 12, 2)->nullable();
                $table->decimal('nominal_kelas_3', 12, 2)->nullable();
                $table->text('keterangan')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            $logs[] = "<span class='badge-success'>✔ Tabel spp_settings berhasil dibuat lengkap!</span>";
        } else {
            $logs[] = "<span class='badge-info'>ℹ Tabel spp_settings sudah ada. Memeriksa kolom...</span>";
            
            // Kolom no_wa_bendahara
            if (!Schema::hasColumn('spp_settings', 'no_wa_bendahara')) {
                Schema::table('spp_settings', function (Blueprint $table) {
                    $table->string('no_wa_bendahara', 50)->nullable()->after('atas_nama_rekening');
                });
                $logs[] = "<span class='badge-success'>✔ Kolom spp_settings.no_wa_bendahara BERHASIL DITAMBAHKAN!</span>";
            } else {
                $logs[] = "<span class='badge-info'>✔ Kolom spp_settings.no_wa_bendahara SUDAH ADA.</span>";
            }

            // Kolom biaya_admin
            if (!Schema::hasColumn('spp_settings', 'biaya_admin')) {
                Schema::table('spp_settings', function (Blueprint $table) {
                    $table->decimal('biaya_admin', 12, 2)->default(2500)->after('no_wa_bendahara');
                });
                $logs[] = "<span class='badge-success'>✔ Kolom spp_settings.biaya_admin BERHASIL DITAMBAHKAN!</span>";
            }

            // Kolom nominal kelas 1, 2, 3
            if (!Schema::hasColumn('spp_settings', 'nominal_kelas_1')) {
                Schema::table('spp_settings', function (Blueprint $table) {
                    $table->decimal('nominal_kelas_1', 12, 2)->nullable()->after('biaya_admin');
                    $table->decimal('nominal_kelas_2', 12, 2)->nullable()->after('nominal_kelas_1');
                    $table->decimal('nominal_kelas_3', 12, 2)->nullable()->after('nominal_kelas_2');
                });
                $logs[] = "<span class='badge-success'>✔ Kolom nominal kelas 1, 2, 3 BERHASIL DITAMBAHKAN!</span>";
            }
        }

        // 2. Pastikan minimal ada 1 record di spp_settings
        $settingCount = DB::table('spp_settings')->count();
        if ($settingCount === 0) {
            DB::table('spp_settings')->insert([
                'is_active'   => 1,
                'biaya_admin' => 2500,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $logs[] = "<span class='badge-success'>✔ Baris default pengaturan SPP dibuat (ID 1).</span>";
        }

        // 3. Periksa santri (is_beasiswa, keterangan_mutasi, tanggal_mutasi)
        if (Schema::hasTable('santri')) {
            if (!Schema::hasColumn('santri', 'is_beasiswa')) {
                Schema::table('santri', function (Blueprint $table) {
                    $table->boolean('is_beasiswa')->default(false)->after('nominal_spp');
                });
                $logs[] = "<span class='badge-success'>✔ Kolom santri.is_beasiswa berhasil ditambahkan.</span>";
            }
            if (!Schema::hasColumn('santri', 'keterangan_mutasi')) {
                Schema::table('santri', function (Blueprint $table) {
                    $table->text('keterangan_mutasi')->nullable()->after('status_santri');
                    $table->date('tanggal_mutasi')->nullable()->after('keterangan_mutasi');
                });
                $logs[] = "<span class='badge-success'>✔ Kolom santri mutasi berhasil ditambahkan.</span>";
            }
        }

        // 4. Periksa spp_tagihans (nominal_spp, nominal_addon)
        if (Schema::hasTable('spp_tagihans')) {
            if (!Schema::hasColumn('spp_tagihans', 'nominal_spp')) {
                Schema::table('spp_tagihans', function (Blueprint $table) {
                    $table->decimal('nominal_spp', 12, 2)->default(0)->after('tahun');
                    $table->decimal('nominal_addon', 12, 2)->default(0)->after('nominal_spp');
                });
                DB::statement('UPDATE spp_tagihans SET nominal_spp = nominal_tagihan WHERE nominal_spp = 0 OR nominal_spp IS NULL');
                $logs[] = "<span class='badge-success'>✔ Kolom spp_tagihans.nominal_spp & nominal_addon berhasil ditambahkan.</span>";
            }
        }

        // 5. Periksa tabel spp_tagihan_addons
        if (!Schema::hasTable('spp_tagihan_addons')) {
            Schema::create('spp_tagihan_addons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('spp_tagihan_id')->constrained('spp_tagihans')->cascadeOnDelete();
                $table->string('nama_biaya', 100);
                $table->decimal('nominal', 12, 2);
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
            $logs[] = "<span class='badge-success'>✔ Tabel spp_tagihan_addons berhasil dibuat.</span>";
        }

        // 6. Periksa tabel daftar_ulang_tagihans (Biaya Masuk Santri Baru)
        if (!Schema::hasTable('daftar_ulang_tagihans')) {
            Schema::create('daftar_ulang_tagihans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
                $table->string('tahun_angkatan', 20)->default(date('Y'));
                $table->string('gelombang', 50)->nullable();
                $table->boolean('is_beasiswa')->default(false);
                $table->decimal('nominal_biaya', 12, 2)->default(0);
                $table->decimal('nominal_terbayar', 12, 2)->default(0);
                $table->decimal('sisa_tagihan', 12, 2)->default(0);
                $table->enum('status', ['belum_lunas', 'cicilan', 'lunas'])->default('belum_lunas');
                $table->string('nomor_va', 50)->nullable();
                $table->text('keterangan')->nullable();
                $table->boolean('is_ready')->default(false);
                $table->timestamps();
            });
            $logs[] = "<span class='badge-success'>✔ Tabel daftar_ulang_tagihans BERHASIL DIBUAT!</span>";
        } else {
            $logs[] = "<span class='badge-info'>✔ Tabel daftar_ulang_tagihans SUDAH ADA.</span>";
            if (!Schema::hasColumn('daftar_ulang_tagihans', 'is_beasiswa')) {
                Schema::table('daftar_ulang_tagihans', function (Blueprint $table) {
                    $table->boolean('is_beasiswa')->default(false)->after('gelombang');
                });
                $logs[] = "<span class='badge-success'>✔ Kolom daftar_ulang_tagihans.is_beasiswa BERHASIL DITAMBAHKAN!</span>";
            }
            if (!Schema::hasColumn('daftar_ulang_tagihans', 'nominal_addon')) {
                Schema::table('daftar_ulang_tagihans', function (Blueprint $table) {
                    $table->decimal('nominal_addon', 12, 2)->default(0)->after('nominal_biaya');
                });
                $logs[] = "<span class='badge-success'>✔ Kolom daftar_ulang_tagihans.nominal_addon BERHASIL DITAMBAHKAN!</span>";
            }
        }

        // 7. Periksa tabel daftar_ulang_pembayarans
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
            });
            $logs[] = "<span class='badge-success'>✔ Tabel daftar_ulang_pembayarans BERHASIL DIBUAT!</span>";
        } else {
            $logs[] = "<span class='badge-info'>✔ Tabel daftar_ulang_pembayarans SUDAH ADA.</span>";
        }

        // 7b. Periksa tabel daftar_ulang_tagihan_addons
        if (!Schema::hasTable('daftar_ulang_tagihan_addons')) {
            Schema::create('daftar_ulang_tagihan_addons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('daftar_ulang_tagihan_id')->constrained('daftar_ulang_tagihans')->cascadeOnDelete();
                $table->string('nama_biaya', 100);
                $table->decimal('nominal', 12, 2);
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
            $logs[] = "<span class='badge-success'>✔ Tabel daftar_ulang_tagihan_addons BERHASIL DIBUAT!</span>";
        } else {
            $logs[] = "<span class='badge-info'>✔ Tabel daftar_ulang_tagihan_addons SUDAH ADA.</span>";
        }

        // 7c. Periksa tabel santri_addon_templates (Master Template Addon SPP Bulanan per Santri)
        if (!Schema::hasTable('santri_addon_templates')) {
            Schema::create('santri_addon_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
                $table->string('nama_biaya', 100);
                $table->decimal('nominal', 12, 2)->default(0);
                $table->string('keterangan', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            $logs[] = "<span class='badge-success'>✔ Tabel santri_addon_templates BERHASIL DIBUAT!</span>";
        } else {
            $logs[] = "<span class='badge-info'>✔ Tabel santri_addon_templates SUDAH ADA.</span>";
        }

        // 8. Periksa template WhatsApp TAHFIDZ_SETORAN agar memuat {{juz}}
        if (Schema::hasTable('whatsapp_templates')) {
            $tahfidzTpl = DB::table('whatsapp_templates')->where('kode', 'TAHFIDZ_SETORAN')->first();
            if ($tahfidzTpl && !str_contains($tahfidzTpl->konten, '{{juz}}')) {
                $updatedKonten = str_replace(
                    '{{surat}} (Ayat {{ayat}})',
                    '{{surat}} (Ayat {{ayat}}) - Juz {{juz}}',
                    $tahfidzTpl->konten
                );
                DB::table('whatsapp_templates')->where('kode', 'TAHFIDZ_SETORAN')->update([
                    'konten'     => $updatedKonten,
                    'updated_at' => now(),
                ]);
                $logs[] = "<span class='badge-success'>✔ Template WhatsApp TAHFIDZ_SETORAN berhasil diperbarui (tambah Juz)!</span>";
            } else {
                $logs[] = "<span class='badge-info'>✔ Template WhatsApp TAHFIDZ_SETORAN sudah mutakhir.</span>";
            }

            // Template KONDISI_SANTRI_JEMPUT
            $jemputTpl = DB::table('whatsapp_templates')->where('kode', 'KONDISI_SANTRI_JEMPUT')->first();
            if (!$jemputTpl) {
                DB::table('whatsapp_templates')->insert([
                    'kode'       => 'KONDISI_SANTRI_JEMPUT',
                    'nama'       => 'Rekomendasi Jemput Santri Sakit (>3 Hari)',
                    'modul'      => 'Kesehatan',
                    'judul'      => 'Pemberitahuan Penjemputan Santri Sakit',
                    'konten'     => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu wali dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\nDiberitahukan dari Pos Kesehatan (UKS) Pondok bahwa ananda saat ini telah sakit/dirawat selama *{{durasi_hari}} hari* dengan keluhan: *{{keluhan}}*.\n\nUntuk pemulihan yang lebih intensif dan kenyamanan ananda, pihak pesantren menyarankan agar ananda *dijemput untuk beristirahat dan dirawat di rumah*:\n• Tanggal Penjemputan: {{tanggal_jemput}}\n• Penjemput: {{nama_penjemput}}\n• Estimasi Kembali: {{estimasi_kembali}}\n• Catatan/Pesan: {{catatan}}\n\nMohon koordinasi dengan pengurus UKS/asrama saat menjemput ananda. Syafakumullah, semoga ananda lekas sembuh.\n_Pos Kesehatan Santri (UKS) Pesantren Alam Qur'an_",
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $logs[] = "<span class='badge-success'>✔ Template WhatsApp KONDISI_SANTRI_JEMPUT berhasil ditambahkan!</span>";
            } else {
                $logs[] = "<span class='badge-info'>✔ Template WhatsApp KONDISI_SANTRI_JEMPUT sudah ada.</span>";
            }
        }

        // 9. Periksa kolom deleted_at (Tempat Sampah / Soft Deletes) pada tabel santri
        if (Schema::hasTable('santri')) {
            if (!Schema::hasColumn('santri', 'deleted_at')) {
                Schema::table('santri', function (Blueprint $table) {
                    $table->softDeletes();
                });
                $logs[] = "<span class='badge-success'>✔ Kolom deleted_at (Tempat Sampah/Soft Deletes) berhasil ditambahkan ke tabel santri!</span>";
            } else {
                $logs[] = "<span class='badge-info'>✔ Kolom deleted_at (Tempat Sampah/Soft Deletes) pada tabel santri sudah aktif.</span>";
            }
        }

        // 9. Periksa kolom fitur Jemput Santri pada santri_kesehatans
        if (Schema::hasTable('santri_kesehatans')) {
            $addedKesehatan = false;
            Schema::table('santri_kesehatans', function (Blueprint $table) use (&$addedKesehatan) {
                if (!Schema::hasColumn('santri_kesehatans', 'tanggal_jemput')) {
                    $table->dateTime('tanggal_jemput')->nullable()->after('status_kesehatan');
                    $table->string('nama_penjemput')->nullable()->after('tanggal_jemput');
                    $table->date('estimasi_kembali')->nullable()->after('nama_penjemput');
                    $table->text('catatan_penjemputan')->nullable()->after('estimasi_kembali');
                    $addedKesehatan = true;
                }
            });

            try {
                // Update enum status_kesehatan bila MySQL
                DB::statement("ALTER TABLE `santri_kesehatans` MODIFY COLUMN `status_kesehatan` ENUM('istirahat_uks', 'rawat_inap', 'sembuh', 'pantauan', 'istirahat_dirumah') NOT NULL DEFAULT 'istirahat_uks'");
            } catch (\Throwable $e) {}

            if ($addedKesehatan) {
                $logs[] = "<span class='badge-success'>✔ Kolom penjemputan santri_kesehatans berhasil ditambahkan!</span>";
            } else {
                $logs[] = "<span class='badge-info'>✔ Kolom penjemputan santri_kesehatans sudah lengkap.</span>";
            }
        }

        // Bersihkan cache model/view/config Laravel
        try {
            Artisan::call('optimize:clear');
            $logs[] = "<span class='badge-info'>✔ Cache aplikasi Laravel dibersihkan (optimize:clear).</span>";
        } catch (\Throwable $e) {}

        foreach ($logs as $log) {
            echo "<div style='margin-bottom:6px;'>{$log}</div>";
        }
        ?>
    </div>

    <div class="card">
        <h2>2. Data Tabel spp_settings Saat Ini di Database Online</h2>
        <?php
        $settings = DB::table('spp_settings')->get();
        if ($settings->count() > 0):
        ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nomor WhatsApp Bendahara</th>
                        <th>Biaya Admin</th>
                        <th>Bank Default</th>
                        <th>Nomor Rekening</th>
                        <th>Atas Nama</th>
                        <th>Status Aktif</th>
                        <th>Terakhir Diperbarui</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($settings as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s->id ?? '-') ?></td>
                            <td style="font-weight:bold; font-size:15px; color:<?= !empty($s->no_wa_bendahara) ? '#0f766e' : '#b91c1c' ?>;">
                                <?= htmlspecialchars($s->no_wa_bendahara ?? '(KOSONG / BELUM DIATUR)') ?>
                            </td>
                            <td>Rp <?= number_format($s->biaya_admin ?? 2500, 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($s->nama_bank ?? '-') ?></td>
                            <td><?= htmlspecialchars($s->nomor_rekening ?? '-') ?></td>
                            <td><?= htmlspecialchars($s->atas_nama_rekening ?? '-') ?></td>
                            <td><?= !empty($s->is_active) ? '<span class="badge-success">Aktif</span>' : '<span class="badge-danger">Nonaktif</span>' ?></td>
                            <td><?= htmlspecialchars($s->updated_at ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="badge-danger">Belum ada baris pengaturan di tabel spp_settings.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>3. Setel / Update Nomor WhatsApp Bendahara Langsung di Sini</h2>
        <p style="font-size:13px; color:#64748b; margin-top:0;">
            Anda juga dapat langsung menyimpan nomor WhatsApp Bendahara lewat tombol di bawah ini untuk memastikan nomor tersimpan permanen ke database:
        </p>
        <form method="POST" style="margin-top:10px;">
            <input type="hidden" name="save_wa_direct" value="1">
            <label style="font-size:12px; font-weight:bold; text-transform:uppercase; display:block; margin-bottom:5px;">
                Nomor WhatsApp Bendahara:
            </label>
            <input type="text" name="direct_no_wa" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($settings->first()->no_wa_bendahara ?? '') ?>" required>
            <button type="submit">Simpan ke Database Sekarang</button>
        </form>
    </div>

    <div style="text-align:center; margin-top:30px; font-size:13px; color:#64748b;">
        <p>Setelah selesai, silakan kembali ke <a href="/admin/spp?tab=rekening" style="color:#0f766e; font-weight:bold;">Menu Pengaturan SPP Admin</a>.</p>
    </div>

</body>
</html>
