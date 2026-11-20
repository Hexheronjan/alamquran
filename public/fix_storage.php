<?php
/**
 * Script perbaikan storage symlink, cache, dan diagnosa dokumen santri untuk hosting cPanel
 * HAPUS file ini setelah berhasil digunakan!
 */

$secret = 'alamquran2026';

if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    die('Akses ditolak. Tambahkan ?key=alamquran2026 di URL.');
}

echo '<pre style="font-family: monospace; font-size: 13px; line-height: 1.6; padding: 20px; background: #0f172a; color: #f8fafc; border-radius: 12px; max-width: 1000px; margin: 20px auto;">';
echo "=== ALAM QUR'AN - DIAGNOSTIK & PERBAIKAN STORAGE SERVER ===\n\n";

$publicPath  = __DIR__;                                  // public/
$basePath    = dirname(__DIR__);                         // root laravel
$storagePath = $basePath . '/storage/app/public';        // storage/app/public
$linkPath    = $publicPath . '/storage';                 // public/storage (symlink target)
$htaccess    = $publicPath . '/.htaccess';

echo "📂 Base Path    : $basePath\n";
echo "📂 Public Path  : $publicPath\n";
echo "📂 Storage Path : $storagePath\n";
echo "🔗 Link Path    : $linkPath\n\n";

// ── 1. CEK & BERSIHKAN .htaccess (Hapus rewrite rule storage lama yang merusak) ──
echo "─── 1. PEMERIKSAAN PUBLIC/.HTACCESS ───\n";
if (file_exists($htaccess)) {
    $htContent = file_get_contents($htaccess);
    $needsUpdate = false;
    
    // Hapus rewrite rule ../storage/app/public/ yang memicu 404 pada LiteSpeed
    if (strpos($htContent, 'RewriteRule ^storage/(.*)$ ../storage/app/public/$1') !== false) {
        $htContent = preg_replace('/# Akses langsung file upload jika symlink storage belum ada\s*\n\s*RewriteCond %\{REQUEST_URI\} \^\/storage\/\(\.\*\)\$\s*\n\s*RewriteCond %\{DOCUMENT_ROOT\}\/\.\.\/storage\/app\/public\/%1 -f\s*\n\s*RewriteRule \^storage\/\(\.\*\)\$ \.\.\/storage\/app\/public\/\$1 \[L\]\s*\n/m', '', $htContent);
        // Fallback replacement jika comment berbeda
        $htContent = preg_replace('/RewriteCond %\{REQUEST_URI\} \^\/storage\/\(\.\*\)\$\s*\n\s*RewriteCond %\{DOCUMENT_ROOT\}\/\.\.\/storage\/app\/public\/%1 -f\s*\n\s*RewriteRule \^storage\/\(\.\*\)\$ \.\.\/storage\/app\/public\/\$1 \[L\]\s*\n/m', '', $htContent);
        $needsUpdate = true;
    }
    
    // Pastikan Options +FollowSymLinks +SymLinksIfOwnerMatch aktif
    if (strpos($htContent, '+FollowSymLinks') === false) {
        $htContent = str_replace(
            'Options -MultiViews -Indexes',
            'Options -MultiViews -Indexes +FollowSymLinks +SymLinksIfOwnerMatch',
            $htContent
        );
        $needsUpdate = true;
    }
    
    if ($needsUpdate) {
        if (@file_put_contents($htaccess, $htContent)) {
            echo "✅ .htaccess berhasil diperbarui: Rule rusak dibersihkan & FollowSymLinks diaktifkan!\n";
        } else {
            echo "⚠️ Gagal menulis otomatis ke .htaccess. Silakan edit .htaccess secara manual via cPanel File Manager.\n";
        }
    } else {
        echo "✅ .htaccess sudah bersih dan mendukung FollowSymLinks.\n";
    }
} else {
    echo "⚠️ File public/.htaccess tidak ditemukan.\n";
}
echo "\n";

// ── 2. CEK & BERSIHKAN BOOTSTRAP CACHE (Route & Config Cache) ──
echo "─── 2. BERSIHKAN CACHE LARAVEL (BOOTSTRAP/CACHE) ───\n";
$cacheDir = $basePath . '/bootstrap/cache';
if (is_dir($cacheDir)) {
    $cacheFiles = ['routes-v7.php', 'config.php', 'events.php'];
    $clearedCount = 0;
    foreach ($cacheFiles as $cf) {
        $fullCf = $cacheDir . '/' . $cf;
        if (file_exists($fullCf)) {
            if (@unlink($fullCf)) {
                echo "🧹 Cache file dihapus: $cf\n";
                $clearedCount++;
            }
        }
    }
    if ($clearedCount === 0) {
        echo "✅ Cache route/config sudah bersih (tidak ada file cache lama yang memblokir).\n";
    } else {
        echo "✅ Berhasil membersihkan $clearedCount file cache lama!\n";
    }
}
echo "\n";

// ── 3. PEMERIKSAAN SYMLINK PUBLIC/STORAGE ──
echo "─── 3. STATUS SYMLINK PUBLIC/STORAGE ───\n";
if (!is_dir($storagePath)) {
    echo "❌ ERROR: Folder storage/app/public tidak ditemukan di: $storagePath\n";
} else {
    echo "✅ Folder storage/app/public ditemukan.\n";
    
    $isForce = isset($_GET['force']) && $_GET['force'] === '1';
    
    if (is_dir($linkPath) && !is_link($linkPath)) {
        echo "⚠️ public/storage saat ini adalah FOLDER BIASA (bukan symlink).\n";
        if ($isForce) {
            $backupPath = $publicPath . '/storage_backup_' . time();
            if (@rename($linkPath, $backupPath)) {
                echo "✅ Folder dummy lama dipindahkan ke: " . basename($backupPath) . "\n";
            }
        } else {
            echo "👉 <a href='?key=alamquran2026&force=1' style='color:#38bdf8; font-weight:bold;'>KLIK DISINI UNTUK PERBAIKI SYMLINK OTOMATIS (?key=alamquran2026&force=1)</a>\n";
        }
    }
    
    if (!file_exists($linkPath)) {
        if (@symlink($storagePath, $linkPath)) {
            echo "✅ Symlink public/storage → storage/app/public BERHASIL dibuat!\n";
        } else {
            echo "⚠️ symlink() dinonaktifkan hosting.\n";
        }
    } elseif (is_link($linkPath)) {
        echo "✅ Symlink public/storage sudah aktif.\n";
        echo "   Target: " . readlink($linkPath) . "\n";
    }
}
echo "\n";

// ── 4. CEK DATABASE & FILE DOKUMEN SANTRI TERTENTU (misal 2229) ──
$checkSantriId = isset($_GET['santri_id']) ? (int)$_GET['santri_id'] : 2229;
echo "─── 4. CEK DOKUMEN SANTRI (ID: $checkSantriId) ───\n";

$envFile = $basePath . '/.env';
$dbConfig = [];
if (file_exists($envFile)) {
    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $dbConfig[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
        }
    }
}

$pdo = null;
if (!empty($dbConfig['DB_DATABASE'])) {
    try {
        $host = $dbConfig['DB_HOST'] ?? '127.0.0.1';
        $port = $dbConfig['DB_PORT'] ?? '3306';
        $db   = $dbConfig['DB_DATABASE'];
        $user = $dbConfig['DB_USERNAME'] ?? 'root';
        $pass = $dbConfig['DB_PASSWORD'] ?? '';
        $dsn  = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
        $pdo  = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } catch (Throwable $e) {
        echo "⚠️ Koneksi DB gagal: " . $e->getMessage() . "\n";
    }
}

$dokumenDir = $storagePath . '/dokumen';

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM dokumen WHERE santri_id = ? LIMIT 1");
        $stmt->execute([$checkSantriId]);
        $dokRow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dokRow) {
            echo "📋 Data dokumen Santri ID $checkSantriId ditemukan di database:\n";
            $fields = [
                'bukti_transfer' => 'Bukti Transfer',
                'kartu_keluarga' => 'Kartu Keluarga (KK)',
                'ktp_ayah'       => 'KTP Ayah',
                'ktp_ibu'        => 'KTP Ibu',
                'rapor'          => 'Rapor'
            ];
            
            foreach ($fields as $col => $lbl) {
                $fileName = $dokRow[$col] ?? null;
                if ($fileName) {
                    $physicalPath = $dokumenDir . '/' . $fileName;
                    $exists = file_exists($physicalPath);
                    $readable = is_readable($physicalPath);
                    $fileSize = $exists ? round(filesize($physicalPath) / 1024, 1) . ' KB' : '0 KB';
                    
                    $url = "/storage/dokumen/" . $fileName;
                    
                    if ($exists) {
                        echo "   ✅ $lbl: $fileName ($fileSize)\n";
                        echo "      🔗 Link tes: <a href='$url' target='_blank' style='color:#38bdf8;'>$url</a>\n";
                    } else {
                        echo "   ❌ $lbl: $fileName\n";
                        echo "      ⚠️ STATUS: FILE FISIK TIDAK DITEMUKAN DI SERVER!\n";
                        echo "      Path dicari: $physicalPath\n";
                    }
                } else {
                    echo "   ⚪ $lbl: (Kosong / Belum diupload)\n";
                }
            }
        } else {
            echo "⚠️ Santri ID $checkSantriId tidak memiliki catatan di tabel 'dokumen'.\n";
            // Tampilkan beberapa ID santri terakhir
            $latest = $pdo->query("SELECT id, nama_lengkap FROM santri ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            echo "Santri terbaru di database:\n";
            foreach ($latest as $s) {
                echo "   - ID {$s['id']}: {$s['nama_lengkap']}\n";
            }
        }
    } catch (Throwable $e) {
        echo "⚠️ Query DB gagal: " . $e->getMessage() . "\n";
    }
}

// ── 5. DAFTAR FILE DI STORAGE/APP/PUBLIC/DOKUMEN ──
echo "\n─── 5. STATUS FOLDER FISIK (STORAGE/APP/PUBLIC/DOKUMEN) ───\n";
if (is_dir($dokumenDir)) {
    $files = scandir($dokumenDir);
    $files = array_values(array_filter($files, fn($f) => !in_array($f, ['.', '..'])));
    echo "📁 Total file di folder fisik: " . count($files) . " file\n";
    if (count($files) > 0) {
        echo "Daftar 10 file pertama:\n";
        for ($i = 0; $i < min(10, count($files)); $i++) {
            $f = $files[$i];
            echo "   - $f (" . round(filesize($dokumenDir . '/' . $f)/1024, 1) . " KB)\n";
        }
    } else {
        echo "⚠️ Folder dokumen di server ini MASIH KOSONG.\n";
    }
} else {
    echo "⚠️ Folder $dokumenDir belum ada di server.\n";
}

// ── 6. DIAGNOSA ERROR 500 BELANJA UANG SAKU ──
echo "\n─── 6. DIAGNOSA KODE CONTROLLER UANG SAKU & LOG ERROR ───\n";
$uangSakuCtrl = $basePath . '/app/Http/Controllers/Admin/UangSakuController.php';
if (file_exists($uangSakuCtrl)) {
    $ctrlContent = file_get_contents($uangSakuCtrl);
    $hasTryCatch = strpos($ctrlContent, 'processSaldoWarningNotification') !== false && strpos($ctrlContent, 'catch (\Throwable $e)') !== false;
    $hasAdminGuard = strpos($ctrlContent, "auth('admin')->id()") !== false;
    
    echo "📄 UangSakuController.php ditemukan di server.\n";
    echo "   Modifikasi terakhir : " . date('Y-m-d H:i:s', filemtime($uangSakuCtrl)) . "\n";
    if ($hasTryCatch && $hasAdminGuard) {
        echo "   ✅ Versi kode: SUDAH VERSI TERBARU (Try-catch & Auth Admin aktif).\n";
    } else {
        echo "   ❌ Versi kode: MASIH VERSI LAMA!\n";
        echo "      Penyebab 500: File UangSakuController.php yang baru di laptop belum diupload/ditimpa ke server!\n";
        echo "      Lokasi upload: app/Http/Controllers/Admin/UangSakuController.php\n";
    }
} else {
    echo "❌ File $uangSakuCtrl TIDAK DITEMUKAN di server!\n";
}

$logFile = $basePath . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "\n📋 10 Baris Terakhir Log Error Server (laravel.log):\n";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -15);
    foreach ($lastLines as $ll) {
        echo htmlspecialchars($ll);
    }
}

echo "\n=== SELESAI ===\n";
echo "💡 Jika dokumen sudah tampil normal, jangan lupa hapus file fix_storage.php demi keamanan.\n";
echo '</pre>';
