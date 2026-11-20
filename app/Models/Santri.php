<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Santri extends Model
{
    use SoftDeletes;

    protected $table = 'santri';

    protected $fillable = [
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'nik',
        'sekolah_asal',
        'npsn',
        'nsm',
        'nisn',
        'anak_ke',
        'dari_bersaudara',
        'prestasi',
        'riwayat_penyakit',
        'hobi',
        'ekstrakurikuler',
        'tujuan_mendaftar',
        'status',
        'kelas',
        'status_santri',
        'tahun_masuk',
        'tahun_lulus',
        'promoted_tahun_ajaran',
        'nis',
        'foto_santri',
        'tahun_ajaran',
        'angkatan_id',
        'user_id',
        'orang_tua_id',
        'wa_saldo_warning_sent',
        'nominal_spp',
        'is_beasiswa',
        'keterangan_mutasi',
        'tanggal_mutasi',
        'nama_bank_spp',
        'nomor_rekening_spp',
        'atas_nama_rekening_spp',
    ];

    protected $casts = [
        'ekstrakurikuler' => 'array',
        'tanggal_lahir' => 'date',
        'tanggal_mutasi' => 'date',
        'wa_saldo_warning_sent' => 'boolean',
        'nominal_spp' => 'decimal:2',
        'is_beasiswa' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orangTua()
    {
        return $this->belongsTo(OrangTua::class);
    }

    public function dokumen()
    {
        return $this->hasOne(Dokumen::class);
    }

    public function uangSaku()
    {
        return $this->hasMany(UangSaku::class);
    }

    public function jadwalTelepons()
    {
        return $this->hasMany(JadwalTelepon::class)->orderBy('tanggal_telepon', 'desc')->orderBy('jam_mulai', 'desc');
    }

    public function nextJadwalTelepon()
    {
        return $this->hasOne(JadwalTelepon::class)
            ->where('tanggal_telepon', '>=', now()->toDateString())
            ->where('status', JadwalTelepon::STATUS_MENUNGGU)
            ->orderBy('tanggal_telepon', 'asc')
            ->orderBy('jam_mulai', 'asc');
    }

    public function angkatan()
    {
        return $this->belongsTo(Angkatan::class);
    }

    public function kelasHistories()
    {
        return $this->hasMany(KelasHistory::class)->orderBy('created_at', 'desc');
    }

    public function correctionNotes()
    {
        return $this->hasMany(SantriCorrectionNote::class)->latest('diberikan_pada');
    }

    public function correctionNoteOpen()
    {
        return $this->hasOne(SantriCorrectionNote::class)->whereNull('diselesaikan_pada')->latestOfMany('diberikan_pada');
    }

    public function getSaldoUangSakuAttribute()
    {
        $lastTrans = $this->uangSaku()->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();
        return $lastTrans ? (float) $lastTrans->saldo_akhir : 0;
    }

    /**
     * Dapatkan tahun mulai ajaran aktif berdasarkan kalender akademik.
     *
     * Tahun ajaran di Indonesia dimulai Juli. Jadi:
     *  - Januari–Juni  → tahun ajaran yang dimulai TAHUN LALU  (mis. Jan 2027 → tahun ajaran 2026)
     *  - Juli–Desember → tahun ajaran yang dimulai TAHUN INI   (mis. Okt 2026 → tahun ajaran 2026)
     *
     * Cara ini TIDAK bergantung pada angkatan mana yang sedang dibuka untuk PPDB,
     * sehingga membuka PPDB untuk tahun berikutnya tidak mengacaukan kelas santri aktif.
     */
    public static function getActiveStartYear(): int
    {
        $year  = (int) date('Y');
        $month = (int) date('n');

        // Tahun ajaran aktif = tahun sekarang jika sudah masuk Juli, sebaliknya tahun lalu
        return $month >= 7 ? $year : $year - 1;
    }

    /**
     * Hitung kelas santri otomatis berdasarkan Tahun Masuk dan Tahun Ajaran Aktif.
     * Kelas 1, Kelas 2, Kelas 3, atau Alumni.
     */
    public function calculateKelasForYear(?int $startYear = null): string
    {
        if ($this->kelas === 'alumni' || $this->status_santri === 'lulus') {
            return 'alumni';
        }

        if (blank($this->tahun_masuk)) {
            return in_array((string)$this->kelas, ['1', '2', '3'], true) ? (string)$this->kelas : '1';
        }

        $activeStart = $startYear ?? self::getActiveStartYear();
        $classNum = ($activeStart - (int) $this->tahun_masuk) + 1;

        if ($classNum <= 1) {
            return '1';
        } elseif ($classNum == 2) {
            return '2';
        } elseif ($classNum == 3) {
            return '3';
        } else {
            return 'alumni';
        }
    }

    /**
     * Accessor untuk kelas hasil kalkulasi otomatis.
     */
    public function getCalculatedKelasAttribute(): string
    {
        if (!blank($this->kelas)) {
            return (string) $this->kelas;
        }

        return $this->calculateKelasForYear();
    }

    /**
     * Sinkronisasi data kelas & status alumni di database berdasarkan tahun masuk & tahun ajaran aktif.
     */
    public static function syncClassesForActiveYear(?int $startYear = null): void
    {
        $activeStart = $startYear ?? self::getActiveStartYear();

        $santris = self::where('status', 'diterima')->get();
        foreach ($santris as $santri) {
            if ($santri->status_santri === 'lulus' || $santri->kelas === 'alumni' || $santri->status_santri === 'mutasi') {
                continue;
            }

            // Santri yang sudah memiliki kelas riil di database atau sudah dipromosikan tidak boleh ditimpa/diturunkan oleh rumus kalender
            if (!blank($santri->kelas) || !blank($santri->promoted_tahun_ajaran)) {
                continue;
            }

            if (blank($santri->tahun_masuk)) {
                continue;
            }

            $calculated = $santri->calculateKelasForYear($activeStart);
            if ($calculated === 'alumni') {
                $tahunLulus = ((int)$santri->tahun_masuk + 3);
                $santri->update([
                    'kelas' => 'alumni',
                    'status_santri' => 'lulus',
                    'tahun_lulus' => (string) $tahunLulus,
                ]);
            } elseif ($santri->kelas !== $calculated) {
                $santri->update([
                    'kelas' => $calculated,
                ]);
            }
        }
    }

    /**
     * Scope santri aktif:
     * - Status PPDB: 'diterima'
     * - Status Santri: 'aktif'
     * - Kelas: bukan 'alumni'
     * - Hasil perhitungan kelas <= 3 (tidak melampaui kelas 3)
     */
    public function scopeAktif($query)
    {
        $activeStartYear = self::getActiveStartYear();
        $minTahunMasuk = $activeStartYear - 2; // e.g., 2026 - 2 = 2024 (Class 3). 2023 becomes alumni.

        return $query->where('status', 'diterima')
            ->where(function ($q) {
                $q->whereNull('status_santri')->orWhere('status_santri', 'aktif');
            })
            ->where(function ($q) {
                $q->whereNull('kelas')->orWhere('kelas', '!=', 'alumni');
            })
            ->where(function ($q) use ($minTahunMasuk, $activeStartYear) {
                $q->whereNull('tahun_masuk')
                  ->orWhereBetween('tahun_masuk', [(string) $minTahunMasuk, (string) $activeStartYear]);
            });
    }

    public function scopeKelas($query, $kelas)
    {
        return $query->where('kelas', (string) $kelas);
    }

    public function scopeByAngkatan($query, $angkatanId)
    {
        return $query->where('angkatan_id', $angkatanId);
    }

    public function scopeAlumni($query)
    {
        return $query->where(function ($q) {
            $q->where('kelas', 'alumni')
              ->orWhere('status_santri', 'lulus');
        });
    }

    public function isAlumni(): bool
    {
        return $this->kelas === 'alumni' || $this->status_santri === 'lulus';
    }

    public function isMutasi(): bool
    {
        return $this->status_santri === 'mutasi';
    }

    public function scopeMutasi($query)
    {
        return $query->where('status_santri', 'mutasi');
    }

    public function isAktif(): bool
    {
        return $this->status === 'diterima' && $this->status_santri === 'aktif' && $this->kelas !== 'alumni';
    }

    public function getKelasLabelAttribute(): string
    {
        if ($this->isAlumni() || (string)$this->kelas === 'alumni') {
            return 'Alumni' . ($this->tahun_lulus ? " ({$this->tahun_lulus})" : '');
        }

        $kelasVal = !blank($this->kelas) ? (string) $this->kelas : $this->calculated_kelas;
        return match ((string) $kelasVal) {
            '1'     => 'Kelas 1',
            '2'     => 'Kelas 2',
            '3'     => 'Kelas 3',
            'alumni' => 'Alumni' . ($this->tahun_lulus ? " ({$this->tahun_lulus})" : ''),
            default => 'Kelas ' . $kelasVal,
        };
    }

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto_santri && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->foto_santri)) {
            return asset('storage/' . $this->foto_santri);
        }
        return asset('assets/images/profile/user-1.jpg');
    }

    public function getKelasSekarangAttribute(): string
    {
        return $this->kelas_label;
    }

    public function getIsBeasiswaAttribute(): bool
    {
        return !empty($this->attributes['is_beasiswa']) || ($this->attributes['nominal_spp'] !== null && (float)$this->attributes['nominal_spp'] === 0.0);
    }

    /**
     * Nominal SPP default milik santri (atau tarif kelas dari pengaturan jika belum diatur khusus).
     */
    public function getNominalSppDefaultAttribute(): float
    {
        if ($this->is_beasiswa) {
            return 0.0;
        }

        if ($this->attributes['nominal_spp'] !== null && (float)$this->attributes['nominal_spp'] > 0) {
            return (float) $this->attributes['nominal_spp'];
        }

        $setting = \App\Models\SppSetting::getActive();
        if ($setting) {
            $kelasKey = 'nominal_kelas_' . ($this->calculated_kelas ?: '1');
            if (!empty($setting->{$kelasKey}) && (float) $setting->{$kelasKey} > 0) {
                return (float) $setting->{$kelasKey};
            }
            if (!empty($setting->nominal_kelas_1) && (float) $setting->nominal_kelas_1 > 0) {
                return (float) $setting->nominal_kelas_1;
            }
        }

        return 1000000.0;
    }

    public function getTarifSppAttribute(): float
    {
        return $this->nominal_spp_default;
    }

    /**
     * Detail rekening/nomor pembayaran milik santri.
     */
    public function getInfoRekeningSppAttribute(): array
    {
        if (!empty($this->nomor_rekening_spp)) {
            return [
                'bank' => $this->nama_bank_spp ?: 'Bank Pembayaran',
                'nomor_rekening' => $this->nomor_rekening_spp,
                'atas_nama' => $this->atas_nama_rekening_spp ?: "a.n. {$this->nama_lengkap}",
            ];
        }

        return [
            'bank' => $this->nama_bank_spp ?: '-',
            'nomor_rekening' => 'Belum Diatur',
            'atas_nama' => 'Silakan hubungi admin pondok',
        ];
    }

    public function daftarUlangTagihan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DaftarUlangTagihan::class)->latestOfMany();
    }

    public function daftarUlangTagihans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DaftarUlangTagihan::class)->orderBy('tahun_angkatan', 'asc');
    }

    public function daftarUlangPembayarans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DaftarUlangPembayaran::class);
    }

    public function pelanggarans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PelanggaranSantri::class)->orderBy('tanggal', 'desc');
    }

    public function prestasis(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SantriPrestasi::class)->orderBy('tanggal_perolehan', 'desc');
    }

    public function addonTemplates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SantriAddonTemplate::class)->orderBy('created_at', 'asc');
    }
}
