<?php
/**
 * Script Otomatis Sinkronisasi Tagihan SPP November 2026
 * Buka di browser: https://app.alamquran.com/sinkron_spp_otomatis.php
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

use App\Models\Santri;
use App\Models\SppTagihan;
use Illuminate\Support\Facades\Artisan;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sinkronisasi Otomatis SPP</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1e293b; padding: 40px 20px; }
        .card { max-width: 650px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        h1 { font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 0; }
        .log { background: #0f172a; color: #38bdf8; padding: 15px; border-radius: 10px; font-family: monospace; font-size: 13px; line-height: 1.7; margin: 20px 0; max-height: 400px; overflow-y: auto; }
        .success { color: #4ade80; }
        .purple { color: #c084fc; font-weight: bold; }
        .btn { display: inline-block; background: #2563eb; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; margin-top: 10px; }
    </style>
</head>
<body>
<div class="card">
    <h1>⚡ Sinkronisasi Otomatis SPP Periode Berjalan</h1>
    <p style="color: #64748b; font-size: 14px; margin-top: 5px;">Menyesuaikan otomatis semua tagihan: <strong>Beasiswa menjadi Rp 0 (Lunas)</strong> dan <strong>Tarif Khusus Santri</strong> sesuai nominal aslinya.</p>
    
    <div class="log">
<?php
try {
    $bulan = (int) date('n');
    $tahun = (int) date('Y');
    
    $tagihans = SppTagihan::with('santri')
        ->where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->get();

    echo "Memeriksa {$tagihans->count()} tagihan bulan {$bulan}/{$tahun}...<br><br>";

    $beasiswaUpdated = 0;
    $tarifSantriUpdated = 0;
    $regulerUpdated = 0;

    foreach ($tagihans as $t) {
        $santri = $t->santri;
        if (!$santri) continue;

        if ($santri->is_beasiswa) {
            // Set Beasiswa Full Rp 0 dan Lunas
            $t->update([
                'nominal_spp'      => 0,
                'nominal_tagihan'  => 0,
                'nominal_terbayar' => 0,
                'sisa_tagihan'     => 0,
                'status'           => 'lunas',
                'keterangan'       => "SPP " . SppTagihan::getNamaBulan($bulan) . " {$tahun} (Beasiswa Full)",
            ]);
            echo "<span class='purple'>✓ [BEASISWA]</span> {$santri->nama_lengkap} ➔ Diubah ke Rp 0 (LUNAS)<br>";
            $beasiswaUpdated++;
        } elseif ($santri->nominal_spp !== null && (float)$santri->nominal_spp > 0) {
            // Set Tarif Khusus Santri (misal Rp 750.000)
            $nomSpp = (float) $santri->nominal_spp;
            $nomTotal = $nomSpp + (float) $t->nominal_addon;
            $sisa = max(0, $nomTotal - (float) $t->nominal_terbayar);
            $status = ($sisa <= 0) ? 'lunas' : ((float)$t->nominal_terbayar > 0 ? 'cicilan' : 'belum_lunas');

            $t->update([
                'nominal_spp'     => $nomSpp,
                'nominal_tagihan' => $nomTotal,
                'sisa_tagihan'    => $sisa,
                'status'          => $status,
            ]);
            echo "<span class='success'>✓ [TARIF KHUSUS]</span> {$santri->nama_lengkap} ➔ Rp " . number_format($nomTotal, 0, ',', '.') . "<br>";
            $tarifSantriUpdated++;
        } else {
            // Reguler: pastikan nominal_spp sama dengan nominal_tagihan jika nominal_addon 0
            if ((float)$t->nominal_spp === 0.0 && (float)$t->nominal_tagihan > 0) {
                $t->update([
                    'nominal_spp' => (float) $t->nominal_tagihan - (float) $t->nominal_addon
                ]);
            }
            $regulerUpdated++;
        }
    }

    // Clear view cache agar tampilan langsung segar
    Artisan::call('view:clear');

    echo "<br><span class='success' style='font-size: 15px; font-weight: bold;'>🎉 SUKSES DISINKRONKAN!</span><br>";
    echo "• {$beasiswaUpdated} santri beasiswa diset Rp 0 (Lunas)<br>";
    echo "• {$tarifSantriUpdated} santri tarif khusus disesuaikan<br>";
    echo "• {$regulerUpdated} santri reguler dirapikan rinciannya<br>";
} catch (\Exception $e) {
    echo "<br><span style='color: #f87171;'>✗ Terjadi error: " . htmlspecialchars($e->getMessage()) . "</span>";
}
?>
    </div>

    <a href="/admin/spp" class="btn">Kembali ke Halaman SPP</a>
</div>
</body>
</html>
