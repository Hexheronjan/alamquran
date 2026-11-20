<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Angkatan extends Model
{
    protected $table = 'angkatan';

    protected $fillable = [
        'nama_angkatan',
        'tahun_ajaran',
        'tahun_angkatan',
        'tanggal_mulai_daftar',
        'tanggal_tutup_daftar',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai_daftar' => 'datetime',
        'tanggal_tutup_daftar' => 'datetime',
        'is_active'            => 'boolean',
        'tahun_angkatan'       => 'integer',
    ];

    public function santri()
    {
        return $this->hasMany(Santri::class);
    }

    /**
     * Cek apakah pendaftaran angkatan ini sedang buka hari ini.
     */
    public function isPendaftaranOpen(): bool
    {
        if (!$this->is_active) return false;
        $now = Carbon::now();
        return $now->between($this->tanggal_mulai_daftar, $this->tanggal_tutup_daftar);
    }

    /**
     * Scope: hanya angkatan yang sedang buka pendaftarannya hari ini.
     */
    public function scopeOpen($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
                     ->where('tanggal_mulai_daftar', '<=', $now)
                     ->where('tanggal_tutup_daftar', '>=', $now);
    }

    /**
     * Accessor untuk status pendaftaran otomatis.
     */
    public function getStatusPendaftaranAttribute(): string
    {
        if (!$this->is_active) {
            return 'Ditutup';
        }
        $now = Carbon::now();
        if ($now->lt($this->tanggal_mulai_daftar)) {
            return 'Akan Dibuka';
        } elseif ($now->gt($this->tanggal_tutup_daftar)) {
            return 'Ditutup';
        } else {
            return 'Dibuka';
        }
    }

    /**
     * Hitung tahun ajaran santri saat di kelas tertentu (7, 8, atau 9)
     * Contoh: angkatan 2026, kelas 7 → 2026/2027
     */
    public function getTahunAjaranKelas(int $kelas): string
    {
        $baseYear = $this->tahun_angkatan ?? (int) explode('/', $this->tahun_ajaran)[0];
        $startYear = $baseYear + ($kelas - 7);
        return $startYear . '/' . ($startYear + 1);
    }

    /**
     * Accessor: tahun angkatan dari nama_angkatan atau tahun_ajaran
     */
    public function getYearAttribute(): int
    {
        if ($this->tahun_angkatan) return $this->tahun_angkatan;
        return (int) explode('/', $this->tahun_ajaran)[0];
    }
}
