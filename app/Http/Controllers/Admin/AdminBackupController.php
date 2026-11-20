<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminActivityLogger;
use App\Services\BackupService;
use Illuminate\Http\Request;

class AdminBackupController extends Controller
{
    /**
     * Tampilkan halaman utama Backup & Restore.
     */
    public function index()
    {
        $backups = BackupService::listBackups();
        $totalBytes = array_sum(array_column($backups, 'size_bytes'));
        $totalSizeHuman = BackupService::formatBytes($totalBytes);
        $totalFiles = count($backups);
        $lastBackup = !empty($backups) ? $backups[0]['date_human'] : '-';

        return view('admin.backup.index', compact('backups', 'totalBytes', 'totalSizeHuman', 'totalFiles', 'lastBackup'));
    }

    /**
     * Proses Backup Database (.sql).
     */
    public function backupDatabase()
    {
        try {
            $res = BackupService::backupDatabase();
            AdminActivityLogger::log('backup_database', "Membuat cadangan database SQL '{$res['filename']}' ({$res['size']} bytes).");

            return back()
                ->with('success', "Alhamdulillah, Backup Database '{$res['filename']}' berhasil dibuat! File otomatis terunduh dan tercatat di riwayat di bawah.")
                ->with('download_file', $res['filename']);
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal membuat backup database: " . $e->getMessage());
        }
    }

    /**
     * Proses Backup Data ke Excel (.xlsx).
     */
    public function backupExcel()
    {
        try {
            $res = BackupService::backupExcel();
            AdminActivityLogger::log('backup_excel', "Membuat cadangan data Excel '{$res['filename']}'.");

            return back()
                ->with('success', "Alhamdulillah, Backup Data Excel '{$res['filename']}' berhasil dibuat lengkap dengan seluruh sheet data santri, SPP, tahfidz, UKS, dan infaq! File otomatis terunduh.")
                ->with('download_file', $res['filename']);
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal membuat backup data Excel: " . $e->getMessage());
        }
    }

    /**
     * Proses Backup Script / Source Code (.zip).
     */
    public function backupScript()
    {
        try {
            $res = BackupService::backupScript();
            AdminActivityLogger::log('backup_script', "Membuat cadangan script aplikasi '{$res['filename']}'.");

            return back()
                ->with('success', "Alhamdulillah, Backup Script Sistem '{$res['filename']}' berhasil dibuat! Seluruh file kodingan telah dibungkus rapi dalam file ZIP.")
                ->with('download_file', $res['filename']);
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal membuat backup script sistem: " . $e->getMessage());
        }
    }

    /**
     * Proses Backup Lengkap (Database SQL + Excel + Script .zip).
     */
    public function backupFull()
    {
        try {
            $res = BackupService::backupFull();
            AdminActivityLogger::log('backup_full', "Membuat cadangan lengkap (Database + Excel + Script) '{$res['filename']}'.");

            return back()
                ->with('success', "Alhamdulillah, Backup Lengkap '{$res['filename']}' berhasil dibuat! Paket komplit berisi Database SQL, Data Excel, dan Script aplikasi telah siap diunduh.")
                ->with('download_file', $res['filename']);
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal membuat backup lengkap: " . $e->getMessage());
        }
    }

    /**
     * Download file backup secara aman.
     */
    public function download($filename)
    {
        $path = BackupService::getSafeFilePath($filename);

        if (!$path || !file_exists($path)) {
            return back()->with('error', "File backup '{$filename}' tidak ditemukan di server atau sudah dihapus.");
        }

        if (filesize($path) === 0) {
            return back()->with('error', "File backup '{$filename}' kosong (0 byte). Silakan klik tombol backup untuk membuat ulang.");
        }

        AdminActivityLogger::log('download_backup', "Mengunduh file backup '{$filename}'.");

        // Bersihkan seluruh output buffer agar file binary (xlsx / zip / sql) tidak corrupt di hosting cPanel
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'sql'  => 'application/sql',
            'zip'  => 'application/zip',
            default => 'application/octet-stream',
        };

        return response()->download($path, $filename, [
            'Content-Type'        => $mime,
            'Content-Length'      => (string) filesize($path),
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma'              => 'public',
        ]);
    }

    /**
     * Hapus file backup dari penyimpanan server.
     */
    public function destroy($filename)
    {
        if (BackupService::deleteBackup($filename)) {
            AdminActivityLogger::log('hapus_backup', "Menghapus file backup '{$filename}'.");
            return back()->with('success', "File backup '{$filename}' berhasil dihapus.");
        }

        return back()->with('error', "Gagal menghapus file backup '{$filename}'.");
    }

    /**
     * Restore database dari file .sql yang diunggah.
     */
    public function restoreDatabase(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|max:102400', // max 100MB
        ], [
            'sql_file.required' => 'Pilih file backup database (.sql) yang akan dipulihkan.',
            'sql_file.max'      => 'Ukuran file SQL maksimal 100 MB.',
        ]);

        $file = $request->file('sql_file');
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext !== 'sql') {
            return back()->with('error', "Format file tidak valid. Harap unggah file berekstensi .sql");
        }

        try {
            BackupService::restoreDatabase($file->getRealPath());
            AdminActivityLogger::log('restore_database', "Memulihkan database dari file '{$file->getClientOriginalName()}'.");

            return back()->with('success', "Alhamdulillah, Database berhasil dipulihkan dari file '{$file->getClientOriginalName()}'!");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal memulihkan database: " . $e->getMessage());
        }
    }
}
