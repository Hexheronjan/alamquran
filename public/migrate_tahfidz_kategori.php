<?php
/**
 * Script Otomatis Update Database Kategori Setoran Tahfidz via Browser
 * Buka di browser: https://app.alamquran.com/migrate_tahfidz_kategori.php
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
    die("Autoload tidak ditemukan. Pastikan file ini berada di folder public_html atau root public.");
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

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Update Database Kategori Tahfidz</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1e293b; padding: 40px 20px; }
        .card { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        h1 { font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 0; display: flex; align-items: center; gap: 10px; }
        .log { background: #0f172a; color: #38bdf8; padding: 15px; border-radius: 10px; font-family: monospace; font-size: 13px; line-height: 1.6; margin: 20px 0; overflow-x: auto; }
        .success { color: #4ade80; }
        .warn { color: #facc15; }
        .btn { display: inline-block; background: #2563eb; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; margin-top: 10px; }
    </style>
</head>
<body>
<div class="card">
    <h1>🚀 Migrasi Kategori Setoran Tahfidz</h1>
    <p style="color: #64748b; font-size: 14px; margin-top: 5px;">Memperbarui struktur database agar mendukung 3 kategori setoran: <strong>Ziyadah</strong>, <strong>Murojaah</strong>, dan <strong>Ujian Juz'iyah</strong>.</p>
    
    <div class="log">
<?php
try {
    echo "1. Memeriksa tabel `tahfidz_setorans`...<br>";
    if (Schema::hasTable('tahfidz_setorans')) {
        echo "<span class='success'>✓ Tabel ditemukan.</span><br>";
        
        echo "2. Memodifikasi kolom `jenis_setoran` menjadi VARCHAR(50)...<br>";
        DB::statement("ALTER TABLE `tahfidz_setorans` MODIFY COLUMN `jenis_setoran` VARCHAR(50) NOT NULL DEFAULT 'ziyadah'");
        echo "<span class='success'>✓ Kolom `jenis_setoran` berhasil diperbarui.</span><br>";
    } else {
        echo "<span class='warn'>! Tabel `tahfidz_setorans` tidak ditemukan.</span><br>";
    }

    echo "<br>3. Menjalankan artisan migrate (jika ada file migrasi baru)...<br>";
    Artisan::call('migrate', ['--force' => true]);
    $out = Artisan::output();
    echo "<span class='success'>" . nl2br(htmlspecialchars($out ?: 'Tidak ada migrasi tambahan.')) . "</span><br>";

    echo "<br>4. Membersihkan cache konfigurasi & view...<br>";
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    echo "<span class='success'>✓ Cache view & config berhasil dibersihkan!</span><br>";

    echo "<br><span class='success' style='font-size: 15px; font-weight: bold;'>🎉 SEMUA PROSES BERHASIL SELESAI!</span>";
} catch (\Exception $e) {
    echo "<br><span style='color: #f87171;'>✗ Terjadi error: " . htmlspecialchars($e->getMessage()) . "</span>";
}
?>
    </div>

    <p style="font-size: 13px; color: #64748b;">Sekarang kategori <strong>Ziyadah</strong>, <strong>Murojaah</strong>, dan <strong>Ujian Juz'iyah</strong> sudah siap digunakan sepenuhnya di portal ustadz dan wali santri.</p>
    <a href="/admin/tahfidz" class="btn">Buka Menu Tahfidz</a>
</div>
</body>
</html>
