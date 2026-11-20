<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratKeluar extends Model
{
    protected $table = 'surat_keluars';

    protected $fillable = [
        'nomor_surat',
        'tanggal_surat',
        'tujuan_surat',
        'perihal',
        'isi_surat',
        'penandatangan',
        'file_lampiran',
        'admin_id',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
