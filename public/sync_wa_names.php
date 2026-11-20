<?php
/**
 * Script Perbaikan Nama Penerima di Log WhatsApp Otomatis
 * URL: https://app.alamquran.com/sync_wa_names.php
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
    die("Autoload tidak ditemukan.");
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

use App\Models\WhatsappMessage;

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Sinkronisasi Nama Penerima WhatsApp...</h2><ul>";

$messages = WhatsappMessage::whereNull('recipient_name')
    ->orWhere('recipient_name', '')
    ->orWhere('recipient_name', '-')
    ->get();

$updated = 0;
foreach ($messages as $msg) {
    $resolved = WhatsappMessage::resolveRecipientNameByPhone($msg->recipient_number);
    if ($resolved) {
        $msg->update(['recipient_name' => $resolved]);
        echo "<li>ID #{$msg->id} ({$msg->recipient_number}) ➔ <strong>{$resolved}</strong></li>";
        $updated++;
    }
}

echo "</ul>";
echo "<p style='color:green;font-weight:bold;'>Selesai! {$updated} data nama penerima berhasil diperbarui.</p>";
echo "<p><a href='/admin/whatsapp/logs'>Kembali ke Log WhatsApp</a></p>";
