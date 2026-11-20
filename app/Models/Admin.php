<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    public const ROLE_SUPER_ADMIN   = 'super_admin';
    public const ROLE_PPDB          = 'admin_ppdb';
    public const ROLE_KEUANGAN      = 'admin_keuangan';
    public const ROLE_TAHFIDZ       = 'admin_tahfidz';
    public const ROLE_OPERATOR      = 'operator';
    public const ROLE_USTADZ_KELAS  = 'ustadz_kelas';
    public const ROLE_KASIR         = 'admin_kasir';
    public const ROLE_SPP           = 'admin_spp';
    public const ROLE_UANG_SAKU     = 'admin_uang_saku';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'assigned_kelas',
        'assigned_toko',
        'is_active',
        'permissions',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'permissions'   => 'array',
        'last_login_at' => 'datetime',
    ];

    public static function getRoleLabels(): array
    {
        return [
            self::ROLE_SUPER_ADMIN  => 'Admin Utama (Super Admin)',
            self::ROLE_PPDB         => 'Admin PPDB',
            self::ROLE_KEUANGAN     => 'Admin Keuangan',
            self::ROLE_TAHFIDZ      => 'Admin Tahfidz',
            self::ROLE_OPERATOR     => 'Operator',
            self::ROLE_USTADZ_KELAS => 'Ustadz Pembina Kelas',
            self::ROLE_KASIR        => 'Kasir Kantin & Aqumart',
            self::ROLE_SPP          => 'Admin SPP & Administrasi Bulanan',
            self::ROLE_UANG_SAKU    => 'Petugas / Pengelola Uang Saku',
        ];
    }

    public static function getAvailablePermissions(): array
    {
        return [
            'admin_management' => 'Manajemen Admin (Daftar Admin, Log Aktivitas, Backup)',
            'master_data'      => 'Master Data (Data Madrasah & Data Angkatan)',
            'ppdb'             => 'Kelola Santri (PSB, Biodata, Kartu Santri, Alumni)',
            'ppdb_read'        => 'Data Santri (Hanya Lihat / Read-Only)',
            'kesehatan'        => 'Kesehatan Santri (UKS)',
            'uang_saku'        => 'Uang Saku Santri (Transaksi & Verifikasi Deposit)',
            'tahfidz'          => 'Tahfidz Al-Qur\'an (Setoran & Master Data Surah)',
            'daftar_ulang'     => 'Biaya Masuk & Daftar Ulang (Keuangan)',
            'spp'              => 'Administrasi & SPP Bulanan (Keuangan)',
            'infaq'            => 'Infaq & Sedekah Online (Keuangan)',
            'kantin'           => 'Kantin & Aqumart (Kasir POS & Kelola Produk)',
            'pengguna'         => 'Pengguna / Akun Wali Santri',
            'otp_monitoring'   => 'Monitoring WhatsApp OTP',
            'absensi'          => 'Absensi Santri (Harian & Rekap)',
            'pelanggaran'      => 'Kedisiplinan & Pelanggaran Santri',
            'prestasi'         => 'Dokumentasi Prestasi Santri',
            'telepon'          => 'Jadwal Telepon Santri & Reminder WA',
            'kalender'         => 'Kalender Akademik Pesantren',
            'saran'            => 'Kotak Saran & Masukan',
            'monitoring_wali'  => 'Monitoring Online & Login Wali Santri',
        ];
    }

    public static function getRoleDefaultPermissions(string $role): array
    {
        return match ($role) {
            self::ROLE_SUPER_ADMIN => [
                'admin_management',
                'master_data',
                'ppdb',
                'kesehatan',
                'uang_saku',
                'tahfidz',
                'daftar_ulang',
                'spp',
                'infaq',
                'kantin',
                'pengguna',
                'otp_monitoring',
                'absensi',
                'pelanggaran',
                'prestasi',
                'telepon',
                'kalender',
                'saran',
                'monitoring_wali',
            ],
            self::ROLE_PPDB => [
                'ppdb',
                'master_data',
                'kalender',
            ],
            self::ROLE_OPERATOR => [
                'tahfidz',
                'ppdb_read',
                'kesehatan',
                'uang_saku',
                'otp_monitoring',
                'pengguna',
                'pelanggaran',
                'prestasi',
                'telepon',
                'kalender',
                'saran',
                'monitoring_wali',
            ],
            self::ROLE_KEUANGAN => [
                'daftar_ulang',
                'spp',
                'infaq',
            ],
            self::ROLE_USTADZ_KELAS => [
                'absensi',
                'uang_saku',
                'pelanggaran',
                'prestasi',
                'telepon',
                'kalender',
            ],
            self::ROLE_KASIR => [
                'kantin',
            ],
            self::ROLE_SPP => [
                'spp',
            ],
            self::ROLE_TAHFIDZ => [
                'tahfidz',
                'ppdb_read',
                'prestasi',
                'telepon',
                'kalender',
            ],
            self::ROLE_UANG_SAKU => [
                'uang_saku',
                'ppdb_read',
            ],
            default => [],
        };
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::getRoleLabels()[$this->role] ?? ucfirst($this->role);
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    public function canAccess(string $permission): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Super Admin selalu memiliki akses penuh ke seluruh modul
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Cek jika ada custom permission override di JSON
        if (is_array($this->permissions)) {
            if (array_key_exists($permission, $this->permissions)) {
                return (bool) $this->permissions[$permission];
            }
            // Compatibility alias for old 'keuangan' permission
            if (in_array($permission, ['uang_saku', 'daftar_ulang', 'spp', 'infaq']) && isset($this->permissions['keuangan'])) {
                return (bool) $this->permissions['keuangan'];
            }
        }

        // Fallback ke permission bawaan role
        $defaults = self::getRoleDefaultPermissions($this->role);
        return in_array($permission, $defaults);
    }

    public function activityLogs()
    {
        return $this->hasMany(AdminActivityLog::class, 'admin_id')->latest();
    }

    /**
     * Apakah admin ini kasir yang dikunci ke satu toko tertentu?
     */
    public function hasAssignedToko(): bool
    {
        return !empty($this->assigned_toko);
    }

    /**
     * Return daftar toko yang boleh diakses admin ini.
     * Super admin / keuangan (null) = semua toko.
     */
    public function getAllowedTokos(): array
    {
        if ($this->isSuperAdmin() || empty($this->assigned_toko)) {
            return ['kantin_1', 'kantin_2', 'aqumart'];
        }
        return [$this->assigned_toko];
    }

    /**
     * Label toko yang ditugaskan.
     */
    public function getAssignedTokoLabelAttribute(): ?string
    {
        $labels = [
            'kantin_1' => 'Kantin 1',
            'kantin_2' => 'Kantin 2',
            'aqumart'  => 'Minimarket Aqumart',
        ];
        return $this->assigned_toko ? ($labels[$this->assigned_toko] ?? $this->assigned_toko) : null;
    }
}