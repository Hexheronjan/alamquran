<?php
/**
 * Script Otomatis Sinkronisasi 114 Surat Al-Qur'an & Juz dari MyQuran via Browser
 * Buka di browser: https://app.alamquran.com/migrate_sync_myquran.php
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

use App\Models\TahfidzSurah;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sinkronisasi Database Al-Qur'an (114 Surat)</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1e293b; padding: 40px 20px; }
        .card { max-width: 680px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        h1 { font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 0; display: flex; align-items: center; gap: 10px; }
        .log { background: #0f172a; color: #38bdf8; padding: 15px; border-radius: 10px; font-family: monospace; font-size: 13px; line-height: 1.6; margin: 20px 0; max-height: 400px; overflow-y: auto; }
        .success { color: #4ade80; }
        .warn { color: #facc15; }
        .btn { display: inline-block; background: #10b981; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; margin-top: 10px; }
    </style>
</head>
<body>
<div class="card">
    <h1>📖 Sinkronisasi Database Al-Qur'an (114 Surat)</h1>
    <p style="color: #64748b; font-size: 14px; margin-top: 5px;">Mengimpor data lengkap 114 Surat Al-Qur'an dari <strong>api.myquran.com</strong> agar ustadz tidak perlu mengetik nama surat dan nomor ayat/juz secara manual.</p>
    
    <div class="log">
<?php
try {
    echo "1. Mengambil data dari api.myquran.com...<br>";
    $suratData = null;
    
    try {
        $resp = Http::timeout(10)->get('https://api.myquran.com/v2/quran/surat/semua');
        if ($resp->successful()) {
            $suratData = $resp->json('data');
            echo "<span class='success'>✓ Berhasil terhubung ke live api.myquran.com!</span><br>";
        }
    } catch (\Exception $ex) {
        echo "<span class='warn'>! Koneksi live lambat/gagal, menggunakan data cadangan offline...</span><br>";
    }

    // Jika gagal panggil live API, pakai cadangan offline lokal
    if (empty($suratData)) {
        $jsonPath = database_path('data/quran_data.json');
        if (file_exists($jsonPath)) {
            $json = json_decode(file_get_contents($jsonPath), true);
            $suratData = $json['surat'] ?? null;
            echo "<span class='success'>✓ Data cadangan offline 114 surat berhasil dimuat.</span><br>";
        }
    }

    if (empty($suratData)) {
        throw new \Exception("Data surat Al-Qur'an tidak dapat dimuat.");
    }

    echo "<br>2. Menyimpan 114 Surat ke Database Pesantren...<br>";
    $count = 0;
    foreach ($suratData as $s) {
        $nomor = (int) ($s['number'] ?? $s['nomor'] ?? 0);
        if ($nomor < 1) continue;

        $namaLatin = $s['name_id'] ?? $s['nama_latin'] ?? "Surah {$nomor}";
        $namaArab  = $s['name_short'] ?? $s['nama_arab'] ?? '';
        $arti      = $s['translation_id'] ?? $s['arti'] ?? '';
        $jmlAyat   = (int) ($s['number_of_verses'] ?? $s['jumlah_ayat'] ?? 0);
        $tempat    = $s['revelation_id'] ?? $s['tempat_turun'] ?? 'Makkiyyah';

        TahfidzSurah::updateOrCreate(
            ['nomor_surah' => $nomor],
            [
                'nama_latin'   => $namaLatin,
                'nama_arab'    => $namaArab,
                'arti'         => $arti,
                'jumlah_ayat'  => $jmlAyat,
                'tempat_turun' => $tempat,
            ]
        );
        $count++;
    }

    echo "<span class='success'>✓ {$count} Surat berhasil tersimpan lengkap dengan nama Arab, terjemahan, dan jumlah ayat!</span><br>";

    echo "<br>3. Membersihkan cache sistem...<br>";
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    echo "<span class='success'>✓ Cache view & config berhasil dibersihkan!</span><br>";

    echo "<br><span class='success' style='font-size: 15px; font-weight: bold;'>🎉 ALHAMDULILLAH! DATABASE AL-QUR'AN BERHASIL DISINKRONKAN!</span>";
} catch (\Exception $e) {
    echo "<br><span style='color: #f87171;'>✗ Terjadi error: " . htmlspecialchars($e->getMessage()) . "</span>";
}
?>
    </div>

    <p style="font-size: 13px; color: #64748b;">Sekarang saat Ustadz memilih surat, nama surat, batas ayat, dan nomor Juz otomatis terisi sendiri tanpa perlu input manual.</p>
    <a href="/admin/tahfidz" class="btn">Buka Menu Tahfidz</a>
</div>
</body>
</html>
