<?php
/**
 * Script Otomatis Update Database WhatsApp Gateway via Browser
 * Buka di browser: https://app.alamquran.com/migrate_wa_gateway.php
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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Update Database WhatsApp Gateway</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f0fdf4; padding: 2rem; color: #1e293b; }
        .card { max-width: 650px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); border: 1px solid #bbf7d0; }
        .badge-success { background: #dcfce7; color: #166534; padding: 0.35rem 0.85rem; border-radius: 9999px; font-weight: bold; font-size: 0.9rem; display: inline-block; }
        .log-box { background: #0f172a; color: #38bdf8; padding: 1.25rem; border-radius: 0.5rem; font-family: monospace; font-size: 0.85rem; margin-top: 1rem; overflow-x: auto; line-height: 1.5; }
        .btn { display: inline-block; background: #0f766e; color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; font-weight: bold; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="color: #0f766e; margin-top: 0;">⚡ Update Database Kolom WhatsApp Gateway</h2>
        <p>Menambahkan kolom <code>gateway_message_id</code>, <code>delivered_at</code>, dan <code>read_at</code> pada tabel <code>whatsapp_messages</code>...</p>

        <?php
        try {
            $updatedColumns = [];

            // 1. Tambahkan kolom via Schema jika belum ada
            Schema::table('whatsapp_messages', function (Blueprint $table) use (&$updatedColumns) {
                if (!Schema::hasColumn('whatsapp_messages', 'gateway_message_id')) {
                    $table->string('gateway_message_id')->nullable()->index()->after('reference_id');
                    $updatedColumns[] = 'gateway_message_id';
                }
                if (!Schema::hasColumn('whatsapp_messages', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable()->after('sent_at');
                    $updatedColumns[] = 'delivered_at';
                }
                if (!Schema::hasColumn('whatsapp_messages', 'read_at')) {
                    $table->timestamp('read_at')->nullable()->after('delivered_at');
                    $updatedColumns[] = 'read_at';
                }
            });

            // 2. Jalankan artisan migrate juga untuk sinkronisasi batch migration
            Artisan::call('migrate', ['--force' => true]);
            $artisanOut = Artisan::output();

            echo "<p><span class='badge-success'>✔ BERHASIL! Database Berhasil Diperbarui</span></p>";
            echo "<div class='log-box'>";
            if (!empty($updatedColumns)) {
                echo "Kolom baru berhasil ditambahkan: " . implode(', ', $updatedColumns) . "\n";
            } else {
                echo "Semua kolom WhatsApp Gateway sudah tersedia di database.\n";
            }
            echo "\nOutput Artisan Migrate:\n" . htmlspecialchars($artisanOut);
            echo "</div>";

            echo "<p style='margin-top: 1.5rem;'>Database sudah siap. Anda sekarang bisa kembali ke halaman WhatsApp Logs dan klik <b>Sinkronkan Status dari Gateway</b>.</p>";
            echo "<a href='/admin/whatsapp/logs' class='btn'>👉 Kembali ke Log WhatsApp</a>";

        } catch (\Throwable $e) {
            echo "<p style='color: #dc2626; font-weight: bold;'>❌ Terjadi Kesalahan: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>
</body>
</html>
