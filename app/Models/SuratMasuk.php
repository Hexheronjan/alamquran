<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    protected $table = 'surat_masuks';

    protected $fillable = [
        'nomor_surat',
        'tanggal_terima',
        'tanggal_surat',
        'asal_instansi',
        'perihal',
        'ringkasan_isi',
        'file_arsip',
        'admin_id',
    ];

    protected $casts = [
        'tanggal_terima' => 'date',
        'tanggal_surat'  => 'date',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
