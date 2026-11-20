<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    protected $table = 'dokumen';

    protected $fillable = [
        'santri_id',
        'bukti_transfer',
        'kartu_keluarga',
        'ktp_ayah',
        'ktp_ibu',
        'rapor'
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }
}
