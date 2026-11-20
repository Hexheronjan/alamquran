<?php
/**
 * Script Otomatis Menjalankan Migration Jadwal Telepon via Browser (Tanpa Terminal)
 * Buka di browser: https://app.alamquran.com/migrate_jadwal_telepon.php
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

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Migrasi Jadwal Telepon Santri & Reminder WA - Pesantren Alam Qur'an</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f8fafc; padding: 2rem; color: #1e293b; }
        .card { max-width: 650px; margin: 0 auto; background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .badge-success { background: #dcfce7; color: #166534; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: bold; font-size: 0.85rem; }
        .log-box { background: #0f172a; color: #38bdf8; padding: 1rem; border-radius: 0.5rem; font-family: monospace; font-size: 0.85rem; margin-top: 1rem; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="card">
        <h2>📞 Migrasi Fitur Jadwal Telepon Santri</h2>
        <p>Menjalankan migrasi database tabel <code>jadwal_telepons</code>...</p>
        <?php
        $success = false;
        $output = '';

        // Cara 1: Jalankan Artisan spesifik ke file migrasi ini saja
        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            $exitCode = Artisan::call('migrate', [
                '--path' => 'database/migrations/2026_11_19_104744_create_jadwal_telepons_table.php',
                '--force' => true,
            ]);
            $output = Artisan::output();
            echo "<p><span class='badge-success'>✔ BERHASIL DIJALANKAN (Exit Code: $exitCode)</span></p>";
            echo "<pre class='log-box'>" . htmlspecialchars($output) . "</pre>";
            if (Schema::hasTable('jadwal_telepons')) {
                $success = true;
            }
        } catch (\Throwable $e) {
            echo "<p style='color: red;'>❌ Terjadi Kesalahan: " . htmlspecialchars($e->getMessage()) . "</p>";
            $output .= "Artisan info: " . $e->getMessage() . "\n";
        }

        // Cara 2: Direct Schema Builder jika belum ada
        if (!Schema::hasTable('jadwal_telepons')) {
            try {
                Schema::create('jadwal_telepons', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
                    $table->date('tanggal_telepon');
                    $table->time('jam_mulai');
                    $table->time('jam_selesai');
                    $table->unsignedSmallInteger('durasi_menit')->default(15);
                    $table->string('ustadz_pendamping', 100)->default('Ustadz Pendamping');
                    $table->string('no_hp_posko', 30);
                    $table->string('topik', 150)->nullable();
                    $table->text('keterangan')->nullable();
                    $table->string('status', 30)->default('menunggu');
                    $table->text('catatan_panggilan')->nullable();
                    $table->dateTime('waktu_dihubungi')->nullable();
                    $table->boolean('wa_reminder_sent')->default(false);
                    $table->dateTime('wa_reminder_sent_at')->nullable();
                    $table->string('wa_reminder_status', 30)->nullable();
                    $table->text('wa_reminder_error')->nullable();
                    $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
                    $table->timestamps();

                    $table->index(['tanggal_telepon', 'status']);
                    $table->index(['santri_id', 'status']);
                });

                try {
                    $batch = (DB::table('migrations')->max('batch') ?? 0) + 1;
                    DB::table('migrations')->insertOrIgnore([
                        'migration' => '2026_11_19_104744_create_jadwal_telepons_table',
                        'batch' => $batch,
                    ]);
                } catch (\Throwable $migErr) {}

                $output .= "\nTabel jadwal_telepons berhasil dibuat melalui Direct Schema Builder!";
                $success = true;
            } catch (\Throwable $e) {
                $output .= "\nDirect Schema Error: " . $e->getMessage();
            }
        } else {
            $success = true;
            $output .= "\nTabel jadwal_telepons sudah ada dan siap digunakan.";
        }

        if ($success) {
            echo "<p><span class='badge-success'>✔ BERHASIL DIJALANKAN & TABEL SIAP</span></p>";
            echo "<pre class='log-box'>" . htmlspecialchars(trim($output)) . "</pre>";
        } else {
            echo "<p style='color: red;'>❌ Terjadi Kesalahan:</p>";
            echo "<pre class='log-box' style='color: #f87171;'>" . htmlspecialchars($output) . "</pre>";
        }
        ?>
        <p style="margin-top: 1.5rem; font-size: 0.85rem; color: #64748b;">
            💡 Setelah selesai, Anda dapat menghapus file <code>migrate_jadwal_telepon.php</code> ini dari folder <code>public/</code> demi keamanan.
        </p>
    </div>
</body>
</html>
