<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UangSakuManual extends Model
{
    protected $table = 'uang_saku_manual';

    protected $fillable = [
        'nama_santri',
        'kelas',
        'tahun_ajaran',
        'tanggal',
        'jenis',
        'jumlah',
        'keterangan',
        'admin_id',
    ];

    protected $casts = [
        'tanggal'    => 'date',
        'jumlah'     => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
