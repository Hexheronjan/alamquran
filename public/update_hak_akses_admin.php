<?php
/**
 * Script Otomatis Menyesuaikan Hak Akses Seluruh Admin Pesantren Alam Qur'an via Browser
 * Buka di browser: https://app.alamquran.com/update_hak_akses_admin.php
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

use App\Models\Admin;

header('Content-Type: text/html; charset=utf-8');

$allKeys = array_keys(Admin::getAvailablePermissions());

function buildPermissions(array $activeKeys, array $allKeys): array {
    $res = [];
    foreach ($allKeys as $k) {
        $res[$k] = in_array($k, $activeKeys);
    }
    return $res;
}

$results = [];

try {
    $admins = Admin::all();

    foreach ($admins as $admin) {
        // Jangan ubah Super Admin id = 1
        if ($admin->isSuperAdmin() || $admin->id === 1) {
            $results[] = [
                'name' => $admin->name,
                'username' => $admin->username,
                'role' => 'Super Admin (Akses Penuh)',
                'permissions' => 'SEMUA MODUL (Super Admin)',
                'status' => 'Dipertahankan Penuh'
            ];
            continue;
        }

        $lowerName = strtolower(trim($admin->name));
        $lowerUser = strtolower(trim($admin->username));

        $granted = [];
        $role = $admin->role;

        // 1. Guru MTs: [Absen & prestasi]
        if (str_contains($lowerName, 'guru') || str_contains($lowerUser, 'guru') || str_contains($lowerName, 'mts') || str_contains($lowerUser, 'mts')) {
            $granted = ['absensi', 'prestasi'];
            $role = 'ustadz_kelas';
        }
        // 2. Aqila: [kesh, uang saku, tahfizh]
        elseif (str_contains($lowerName, 'aqila') || str_contains($lowerUser, 'aqila')) {
            $granted = ['kesehatan', 'uang_saku', 'tahfidz'];
            $role = 'operator';
        }
        // 3. Abdul aziz: [uang saku, tahfizh]
        elseif (str_contains($lowerName, 'aziz') || str_contains($lowerUser, 'aziz')) {
            $granted = ['uang_saku', 'tahfidz'];
            $role = 'ustadz_kelas';
        }
        // 4. Nabil: [uang saku, tahfizh]
        elseif (str_contains($lowerName, 'nabil') || str_contains($lowerUser, 'nabil')) {
            $granted = ['uang_saku', 'tahfidz'];
            $role = 'ustadz_kelas';
        }
        // 5. Hartono: semua akses kecuali keuangan
        elseif (str_contains($lowerName, 'hartono') || str_contains($lowerUser, 'hartono')) {
            $granted = array_diff($allKeys, ['spp', 'daftar_ulang', 'infaq']);
            $role = 'operator';
        }
        // 6. Dwi: keuangan & Absen
        elseif (str_contains($lowerName, 'dwi') || str_contains($lowerUser, 'dwi')) {
            $granted = ['daftar_ulang', 'spp', 'infaq', 'absensi'];
            $role = 'admin_keuangan';
        }
        // 7. Lutfie: keuangan
        elseif (str_contains($lowerName, 'lutfi') || str_contains($lowerUser, 'lutfi') || str_contains($lowerName, 'lutvi') || str_contains($lowerUser, 'lutvi')) {
            $granted = ['daftar_ulang', 'spp', 'infaq'];
            $role = 'admin_keuangan';
        }
        // 8. Yang lain: Tahfidz & kelola santri
        else {
            $granted = ['tahfidz', 'ppdb', 'ppdb_read'];
            $role = 'admin_tahfidz';
        }

        $permMap = buildPermissions($granted, $allKeys);

        $admin->role = $role;
        $admin->permissions = $permMap;
        $admin->save();

        $results[] = [
            'name' => $admin->name,
            'username' => $admin->username,
            'role' => Admin::getRoleLabels()[$role] ?? $role,
            'permissions' => implode(', ', $granted),
            'status' => 'Berhasil Disesuaikan'
        ];
    }
} catch (\Throwable $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penyesuaian Hak Akses Admin - Pesantren Alam Qur'an</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f0fdf4; padding: 2rem; color: #1e293b; }
        .card { max-width: 900px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: 1.25rem; box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1); border: 1px solid #bbf7d0; }
        h1 { color: #0f766e; margin-top: 0; font-size: 1.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; font-size: 0.85rem; }
        th, td { padding: 0.75rem 1rem; border: 1px solid #e2e8f0; text-align: left; }
        th { background: #f8fafc; font-weight: bold; color: #334155; }
        tr:hover { background: #f8fafc; }
        .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-weight: bold; font-size: 0.75rem; }
        .badge-success { background: #dcfce7; color: #166534; }
        .btn { display: inline-block; background: #0f766e; color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; font-weight: bold; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>✔ Penyesuaian Hak Akses Admin Berhasil!</h1>
        <p>Hak akses modul untuk seluruh staf pengurus telah otomatis disesuaikan sesuai pembagian tugas yang diminta:</p>

        <?php if (!empty($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                ❌ Terjadi Kesalahan: <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Nama Admin</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Modul yang Dibuka</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r): ?>
                <tr>
                    <td><b><?= htmlspecialchars($r['name']) ?></b></td>
                    <td><code><?= htmlspecialchars($r['username']) ?></code></td>
                    <td><?= htmlspecialchars($r['role']) ?></td>
                    <td><span style="color: #0f766e; font-weight: 600;"><?= htmlspecialchars($r['permissions']) ?></span></td>
                    <td><span class="badge badge-success"><?= htmlspecialchars($r['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p style="margin-top: 1.5rem; font-size: 0.85rem; color: #64748b;">
            💡 Akun <b>Guru MTs</b> sekarang sudah resmi memiliki akses ke <b>Absensi & Prestasi</b> tanpa ada blokir "Akses Ditolak".
        </p>

        <a href="/admin/admins" class="btn">👉 Buka Manajemen Admin</a>
    </div>
</body>
</html>
