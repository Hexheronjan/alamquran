<?php

namespace App\Services;

use App\Exports\BackupDataExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BackupService
{
    /**
     * Dapatkan semua direktori penyimpanan backup (Laravel 11 private dan legacy).
     */
    public static function getBackupDirectories(): array
    {
        $dirs = [
            Storage::disk('local')->path('backups'),
            storage_path('app/private/backups'),
            storage_path('app/backups'),
        ];

        return array_values(array_unique($dirs));
    }

    /**
     * Dapatkan path direktori penyimpanan backup utama yang writable.
     */
    public static function getBackupDirectory(): string
    {
        $primary = Storage::disk('local')->path('backups');
        if (!File::exists($primary)) {
            @File::makeDirectory($primary, 0755, true);
        }

        if (File::exists($primary) && is_writable($primary)) {
            return $primary;
        }

        $fallback = storage_path('app/backups');
        if (!File::exists($fallback)) {
            @File::makeDirectory($fallback, 0755, true);
        }

        return $fallback;
    }

    /**
     * Backup Database MySQL ke format file .sql (Murni PHP PDO, aman di semua cPanel).
     */
    public static function backupDatabase(): array
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $dir = self::getBackupDirectory();
        $timestamp = date('Y-m-d_His');
        $filename = "backup_db_{$timestamp}.sql";
        $filePath = $dir . DIRECTORY_SEPARATOR . $filename;

        $pdo = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();

        $handle = fopen($filePath, 'w+');
        if (!$handle) {
            throw new \Exception("Gagal membuat file backup database di {$filePath}");
        }

        // Header SQL
        fwrite($handle, "-- ============================================================\n");
        fwrite($handle, "-- Pesantren Tahfizh Alam Qur'an - Database Backup\n");
        fwrite($handle, "-- Database: {$dbName}\n");
        fwrite($handle, "-- Dibuat Pada: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Server PHP: " . phpversion() . "\n");
        fwrite($handle, "-- ============================================================\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

        // Ambil semua tabel
        $tables = [];
        $stmt = $pdo->query('SHOW TABLES');
        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            // Drop Table
            fwrite($handle, "-- ------------------------------------------------------------\n");
            fwrite($handle, "-- Struktur Tabel untuk: `{$table}`\n");
            fwrite($handle, "-- ------------------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            // Create Table
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt->fetch(\PDO::FETCH_NUM);
            if ($createRow && isset($createRow[1])) {
                fwrite($handle, $createRow[1] . ";\n\n");
            }

            // Dump Data (Baris per Baris)
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $totalRows = (int) $countStmt->fetchColumn();

            if ($totalRows > 0) {
                fwrite($handle, "-- Dumping data untuk tabel: `{$table}` (Total: {$totalRows} baris)\n");

                $chunkSize = 200;
                $offset = 0;

                while ($offset < $totalRows) {
                    $dataStmt = $pdo->query("SELECT * FROM `{$table}` LIMIT {$chunkSize} OFFSET {$offset}");
                    $rows = $dataStmt->fetchAll(\PDO::FETCH_ASSOC);

                    if (empty($rows)) {
                        break;
                    }

                    $columns = array_keys($rows[0]);
                    $quotedColumns = array_map(fn($c) => "`{$c}`", $columns);
                    $colList = implode(', ', $quotedColumns);

                    $insertValues = [];
                    foreach ($rows as $row) {
                        $escapedVals = [];
                        foreach ($columns as $col) {
                            $val = $row[$col];
                            if ($val === null) {
                                $escapedVals[] = 'NULL';
                            } elseif (is_numeric($val) && !str_starts_with((string)$val, '0')) {
                                $escapedVals[] = $val;
                            } else {
                                $escapedVals[] = $pdo->quote($val);
                            }
                        }
                        $insertValues[] = '(' . implode(', ', $escapedVals) . ')';
                    }

                    fwrite($handle, "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $insertValues) . ";\n\n");
                    $offset += $chunkSize;
                }
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        return [
            'filename' => $filename,
            'path'     => $filePath,
            'size'     => filesize($filePath),
            'type'     => 'database',
        ];
    }

    /**
     * Backup Data ke format Excel (.xlsx) dengan Sheet Santri, SPP, Uang Saku, dll.
     */
    public static function backupExcel(): array
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $timestamp = date('Y-m-d_His');
        $filename = "backup_excel_{$timestamp}.xlsx";

        // Pastikan folder backups di storage disk local ada
        Storage::disk('local')->makeDirectory('backups');

        // Simpan menggunakan Maatwebsite Excel langsung ke disk local
        Excel::store(new BackupDataExport(), "backups/{$filename}", 'local');

        $diskPath = Storage::disk('local')->path("backups/{$filename}");

        // Salin juga ke legacy directory jika berbeda, agar pasti ada di kedua tempat
        $legacyPath = storage_path("app/backups/{$filename}");
        if (File::exists($diskPath) && $diskPath !== $legacyPath) {
            if (!File::exists(dirname($legacyPath))) {
                @File::makeDirectory(dirname($legacyPath), 0755, true);
            }
            @File::copy($diskPath, $legacyPath);
        }

        $realPath = (File::exists($diskPath) && filesize($diskPath) > 0) ? $diskPath : $legacyPath;
        $size = File::exists($realPath) ? filesize($realPath) : 0;

        return [
            'filename' => $filename,
            'path'     => $realPath,
            'size'     => $size,
            'type'     => 'excel',
        ];
    }

    /**
     * Backup Script / Kodingan Aplikasi ke format .zip (Aman, Cepat & Ringan).
     */
    public static function backupScript(): array
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $dir = self::getBackupDirectory();
        $timestamp = date('Y-m-d_His');
        $filename = "backup_script_{$timestamp}.zip";
        $filePath = $dir . DIRECTORY_SEPARATOR . $filename;

        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Gagal menginisialisasi file ZIP di {$filePath}");
        }

        $basePath = base_path();

        // 1. Folder inti source code
        $includeFolders = [
            'app',
            'config',
            'database/migrations',
            'database/seeders',
            'resources/views',
            'routes',
        ];

        // 2. File root inti
        $includeFiles = [
            '.env.example',
            'artisan',
            'composer.json',
            'composer.lock',
            'package.json',
            'vite.config.js',
            'tailwind.config.js',
            'public/index.php',
            'public/.htaccess',
            'public/migrate_spp.php',
        ];

        // Tambah file spesifik
        foreach ($includeFiles as $file) {
            $fullFilePath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
            if (File::exists($fullFilePath)) {
                $zip->addFile($fullFilePath, $file);
            }
        }

        // Tambah folder inti
        foreach ($includeFolders as $folder) {
            $folderPath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $folder);
            if (!File::exists($folderPath)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    $itemPath = $item->getRealPath();
                    $relativePath = substr($itemPath, strlen($basePath) + 1);
                    $normalizedRel = str_replace('\\', '/', $relativePath);

                    if (str_starts_with($normalizedRel, 'routes/') && !str_ends_with($normalizedRel, '.php')) {
                        continue;
                    }

                    if (!str_ends_with($normalizedRel, '.zip') && !str_ends_with($normalizedRel, '.sql')) {
                        $zip->addFile($itemPath, $normalizedRel);
                    }
                }
            }
        }

        $zip->close();

        return [
            'filename' => $filename,
            'path'     => $filePath,
            'size'     => filesize($filePath),
            'type'     => 'script',
        ];
    }

    /**
     * Backup Lengkap (Database SQL + Data Excel + Script Aplikasi dalam 1 file ZIP).
     */
    public static function backupFull(): array
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(600);

        $dir = self::getBackupDirectory();
        $timestamp = date('Y-m-d_His');
        $filename = "backup_full_{$timestamp}.zip";
        $filePath = $dir . DIRECTORY_SEPARATOR . $filename;

        // 1. Ekspor database
        $dbRes = self::backupDatabase();

        // 2. Ekspor excel
        $excelRes = self::backupExcel();

        // 3. Buat ZIP Master
        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Gagal membuat file ZIP Master di {$filePath}");
        }

        // Masukkan Database SQL & Excel ke root zip
        if (File::exists($dbRes['path'])) {
            $zip->addFile($dbRes['path'], 'DATABASE_' . $dbRes['filename']);
        }
        if (File::exists($excelRes['path'])) {
            $zip->addFile($excelRes['path'], 'EXCEL_' . $excelRes['filename']);
        }

        // Masukkan Source Code aplikasi
        $basePath = base_path();
        $includeFolders = [
            'app',
            'config',
            'database/migrations',
            'database/seeders',
            'resources/views',
            'routes',
        ];
        $includeFiles = [
            '.env.example',
            'artisan',
            'composer.json',
            'composer.lock',
            'package.json',
            'vite.config.js',
            'tailwind.config.js',
            'public/index.php',
            'public/.htaccess',
            'public/migrate_spp.php',
        ];

        foreach ($includeFiles as $file) {
            $f = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
            if (File::exists($f)) {
                $zip->addFile($f, "script/{$file}");
            }
        }

        foreach ($includeFolders as $folder) {
            $folderPath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $folder);
            if (!File::exists($folderPath)) continue;

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    $itemPath = $item->getRealPath();
                    $relativePath = substr($itemPath, strlen($basePath) + 1);
                    $normalizedRel = str_replace('\\', '/', $relativePath);

                    if (str_starts_with($normalizedRel, 'routes/') && !str_ends_with($normalizedRel, '.php')) {
                        continue;
                    }

                    if (!str_ends_with($normalizedRel, '.zip') && !str_ends_with($normalizedRel, '.sql')) {
                        $zip->addFile($itemPath, "script/{$normalizedRel}");
                    }
                }
            }
        }

        $zip->close();

        return [
            'filename' => $filename,
            'path'     => $filePath,
            'size'     => filesize($filePath),
            'type'     => 'full',
        ];
    }

    /**
     * Dapatkan daftar file backup yang tersedia di server.
     */
    public static function listBackups(): array
    {
        $directories = self::getBackupDirectories();
        $seen = [];
        $backups = [];

        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                continue;
            }

            try {
                $files = File::files($dir);
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($files as $file) {
                $name = $file->getFilename();
                if (isset($seen[$name])) {
                    continue; // hindari duplikasi jika ada di kedua direktori
                }
                $seen[$name] = true;
                $ext = strtolower($file->getExtension());

                // Tentukan tipe backup
                $type = 'other';
                $typeLabel = 'Lainnya';
                $badgeColor = 'bg-gray-100 text-gray-800 border-gray-200';
                $icon = 'ti-file';

                if (str_starts_with($name, 'backup_db_') || $ext === 'sql') {
                    $type = 'database';
                    $typeLabel = 'Database (.sql)';
                    $badgeColor = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                    $icon = 'ti-database';
                } elseif (str_starts_with($name, 'backup_excel_') || $ext === 'xlsx') {
                    $type = 'excel';
                    $typeLabel = 'Excel (.xlsx)';
                    $badgeColor = 'bg-blue-100 text-blue-800 border-blue-200';
                    $icon = 'ti-file-spreadsheet';
                } elseif (str_starts_with($name, 'backup_full_')) {
                    $type = 'full';
                    $typeLabel = 'Full Backup (.zip)';
                    $badgeColor = 'bg-purple-100 text-purple-800 border-purple-200';
                    $icon = 'ti-archive';
                } elseif (str_starts_with($name, 'backup_script_') || $ext === 'zip') {
                    $type = 'script';
                    $typeLabel = 'Script (.zip)';
                    $badgeColor = 'bg-amber-100 text-amber-800 border-amber-200';
                    $icon = 'ti-code';
                }

                $backups[] = [
                    'filename'    => $name,
                    'path'        => $file->getRealPath(),
                    'size_bytes'  => $file->getSize(),
                    'size_human'  => self::formatBytes($file->getSize()),
                    'created_at'  => $file->getMTime(),
                    'date_human'  => date('d/m/Y H:i:s', $file->getMTime()),
                    'type'        => $type,
                    'type_label'  => $typeLabel,
                    'badge_color' => $badgeColor,
                    'icon'        => $icon,
                ];
            }
        }

        // Urutkan dari yang paling baru
        usort($backups, fn($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $backups;
    }

    /**
     * Dapatkan path aman untuk download / operasi file (cegah directory traversal).
     */
    public static function getSafeFilePath(string $filename): ?string
    {
        $cleanName = basename($filename);

        $candidates = [
            Storage::disk('local')->path("backups/{$cleanName}"),
            Storage::disk('local')->path($cleanName),
            storage_path("app/private/backups/{$cleanName}"),
            storage_path("app/backups/{$cleanName}"),
            storage_path("app/private/{$cleanName}"),
            storage_path("app/{$cleanName}"),
            self::getBackupDirectory() . DIRECTORY_SEPARATOR . $cleanName,
        ];

        // Prioritaskan file yang ukurannya > 0
        foreach ($candidates as $fullPath) {
            if ($fullPath && File::exists($fullPath) && is_file($fullPath) && filesize($fullPath) > 0) {
                return $fullPath;
            }
        }

        // Fallback jika hanya ada file 0 byte
        foreach ($candidates as $fullPath) {
            if ($fullPath && File::exists($fullPath) && is_file($fullPath)) {
                return $fullPath;
            }
        }

        return null;
    }

    /**
     * Hapus file backup dari semua lokasi penyimpanan.
     */
    public static function deleteBackup(string $filename): bool
    {
        $cleanName = basename($filename);
        $deleted = false;
        foreach (self::getBackupDirectories() as $dir) {
            $fullPath = $dir . DIRECTORY_SEPARATOR . $cleanName;
            if (File::exists($fullPath) && is_file($fullPath)) {
                if (File::delete($fullPath)) {
                    $deleted = true;
                }
            }
        }
        return $deleted;
    }

    /**
     * Restore database dari file .sql yang diunggah.
     */
    public static function restoreDatabase(string $sqlFilePath): bool
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        if (!File::exists($sqlFilePath)) {
            throw new \Exception("File SQL untuk restore tidak ditemukan.");
        }

        $pdo = DB::connection()->getPdo();
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

        $sqlContent = File::get($sqlFilePath);

        // Pisahkan query berdasarkan titik koma di akhir baris
        $queries = preg_split('/;\s*[\r\n]+/', $sqlContent);

        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query) && !str_starts_with($query, '--') && !str_starts_with($query, '/*')) {
                try {
                    $pdo->exec($query);
                } catch (\Throwable $e) {
                    // Abaikan error minor jika ada
                }
            }
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        return true;
    }

    /**
     * Format bytes menjadi KB / MB / GB yang mudah dibaca.
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
