<?php
/**
 * Script Otomatis Menjalankan Migration 5 Fitur Baru via Browser (Tanpa Terminal)
 * Buka di browser: https://domainanda.com/migrate_fitur_baru.php
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

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Migrasi Fitur Baru & Pengaturan Invoice - Pesantren Alam Qur'an</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f8fafc; padding: 2rem; color: #1e293b; }
        .card { max-width: 650px; margin: 0 auto; background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .badge-success { background: #dcfce7; color: #166534; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: bold; font-size: 0.85rem; }
        .log-box { background: #0f172a; color: #38bdf8; padding: 1rem; border-radius: 0.5rem; font-family: monospace; font-size: 0.85rem; margin-top: 1rem; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="card">
        <h2>🛠 Eksekusi Migration Otomatis</h2>
        <p>Menjalankan migrasi database untuk 5 fitur baru pesantren...</p>
        <?php
        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            echo "<p><span class='badge-success'>✔ BERHASIL DIJALANKAN (Exit Code: $exitCode)</span></p>";
            echo "<pre class='log-box'>" . htmlspecialchars($output) . "</pre>";
        } catch (\Throwable $e) {
            echo "<p style='color: red;'>❌ Terjadi Kesalahan: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
        <p style="margin-top: 1.5rem; font-size: 0.85rem; color: #64748b;">
            💡 Setelah selesai, Anda dapat menghapus file <code>migrate_fitur_baru.php</code> ini dari folder <code>public/</code> demi keamanan.
        </p>
    </div>
</body>
</html>
