<?php
/**
 * Script Pembersih Cache Laravel Otomatis (Standalone)
 * Buka di browser: https://app.alamquran.com/bersihkan.php
 */

header('Content-Type: text/html; charset=utf-8');

$candidateDirs = [
    __DIR__ . '/bootstrap/cache',
    __DIR__ . '/../bootstrap/cache',
    __DIR__ . '/ppdb-app/bootstrap/cache',
    __DIR__ . '/../ppdb-app/bootstrap/cache',
];

$deletedCount = 0;
$foundDirs = [];

foreach ($candidateDirs as $dir) {
    if (is_dir($dir)) {
        $foundDirs[] = realpath($dir);
        $files = glob($dir . '/*.php');
        foreach ($files as $file) {
            if (basename($file) !== '.gitignore') {
                @unlink($file);
                $deletedCount++;
                echo "<p style='color: green;'>✔ Terhapus: " . htmlspecialchars(basename($file)) . " (di " . htmlspecialchars($dir) . ")</p>";
            }
        }
    }
}

// Bersihkan cache view (Blade)
$viewDirs = [
    __DIR__ . '/storage/framework/views',
    __DIR__ . '/../storage/framework/views',
    __DIR__ . '/ppdb-app/storage/framework/views',
    __DIR__ . '/../ppdb-app/storage/framework/views',
];
foreach ($viewDirs as $vDir) {
    if (is_dir($vDir)) {
        $viewFiles = glob($vDir . '/*.php');
        foreach ($viewFiles as $vFile) {
            if (basename($vFile) !== '.gitignore') {
                @unlink($vFile);
                $deletedCount++;
            }
        }
    }
}

// Otomatis hubungkan folder storage/app/public ke web
$storageTarget = realpath(__DIR__ . '/storage/app/public') ?: realpath(__DIR__ . '/ppdb-app/storage/app/public');
if ($storageTarget) {
    $linkCandidates = [__DIR__ . '/public/storage'];
    foreach ($linkCandidates as $link) {
        if (!file_exists($link)) {
            @symlink($storageTarget, $link);
            if (file_exists($link)) {
                echo "<p style='color: green;'>✔ Storage link berhasil dibuat ke: " . htmlspecialchars($link) . "</p>";
            }
        }
    }
}

echo "<hr>";
if ($deletedCount > 0) {
    echo "<h2 style='color: #16a34a;'>✅ Sukses! Total {$deletedCount} file cache berhasil dihapus.</h2>";
    echo "<p>Sekarang rute baru sudah aktif. Silakan kembali ke menu <b>Master Data Surah</b> dan refresh halamannya.</p>";
} else {
    echo "<h2 style='color: #2563eb;'>ℹ Folder cache sudah bersih & storage link siap.</h2>";
}
echo "<p><a href='/admin/tahfidz/master-surah' style='display: inline-block; padding: 10px 20px; background: #16a34a; color: white; text-decoration: none; border-radius: 8px;'>Kembali ke Master Data Surah &rarr;</a></p>";
