<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrangTua extends Model
{
    protected $table = 'orang_tua';

    protected $fillable = [
        // Data Ayah
        'nama_ayah',
        'no_wa_ayah',
        'no_kk',
        'nik_ayah',
        'tempat_lahir_ayah',
        'tanggal_lahir_ayah',
        'pekerjaan_ayah',
        'penghasilan_ayah',
        'alamat_domisili_ayah',
        
        // Data Ibu
        'nama_ibu',
        'nik_ibu',
        'tempat_lahir_ibu',
        'tanggal_lahir_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'alamat_domisili_ibu'
    ];

    protected $casts = [
        'tanggal_lahir_ayah' => 'date',
        'tanggal_lahir_ibu' => 'date',
    ];

    public function santri()
    {
        return $this->hasOne(Santri::class);
    }
}
