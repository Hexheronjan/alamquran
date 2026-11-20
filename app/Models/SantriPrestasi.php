<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SantriPrestasi extends Model
{
    use HasFactory;

    protected $table = 'santri_prestasis';

    protected $fillable = [
        'santri_id',
        'tahun_ajaran',
        'nama_prestasi',
        'kategori',
        'tingkat',
        'peringkat',
        'penyelenggara',
        'tanggal_perolehan',
        'deskripsi',
        'foto_piagam',
        'admin_id',
    ];

    protected $casts = [
        'tanggal_perolehan' => 'date',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public static function getKategoriList(): array
    {
        return [
            'tahfidz'        => 'Tahfidz Al-Qur\'an',
            'akademik'       => 'Akademik & Sains',
            'bahasa'         => 'Bahasa Arab & Inggris',
            'seni_kaligrafi' => 'Seni Kaligrafi & Qira\'ah',
            'pidato_dai'     => 'Khitobah / Pidato / Da\'i',
            'olahraga'       => 'Olahraga & Bela Diri',
            'lainnya'        => 'Lainnya',
        ];
    }

    public static function getTingkatList(): array
    {
        return [
            'pondok'        => 'Internal Pondok',
            'kecamatan'     => 'Tingkat Kecamatan',
            'kabupaten'     => 'Tingkat Kabupaten/Kota',
            'provinsi'      => 'Tingkat Provinsi',
            'nasional'      => 'Tingkat Nasional',
            'internasional' => 'Tingkat Internasional',
        ];
    }

    public function getKategoriLabel(): string
    {
        return self::getKategoriList()[$this->kategori] ?? ucfirst($this->kategori);
    }

    public function getTingkatLabel(): string
    {
        return self::getTingkatList()[$this->tingkat] ?? ucfirst($this->tingkat);
    }
}
