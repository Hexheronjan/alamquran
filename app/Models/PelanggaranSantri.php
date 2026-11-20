<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelanggaranSantri extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran_santris';

    protected $fillable = [
        'santri_id',
        'tanggal',
        'kategori',
        'nama_pelanggaran',
        'konsekuensi',
        'status_pembinaan',
        'poin',
        'catatan',
        'petugas_ustadz',
        'foto_bukti',
        'admin_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'poin' => 'integer',
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
            'ringan' => 'Pelanggaran Ringan',
            'sedang' => 'Pelanggaran Sedang',
            'berat'  => 'Pelanggaran Berat',
        ];
    }

    public static function getKonsekuensiPresets(): array
    {
        return [
            'Cukur Botak / Gundul',
            'Skorsing (Dipulangkan Sementara)',
            'Dikeluarkan dari Pesantren (Drop Out)',
            'Ta\'zir Hafalan Al-Qur\'an',
            'Kerja Bakti Kebersihan Lingkungan',
            'Peringatan Keras / Surat Peringatan (SP)',
            'Pembersihan Masjid & Asrama',
            'Teguran Lisan & Pembinaan Khusus',
        ];
    }
}
