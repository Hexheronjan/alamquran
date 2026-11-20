<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KehadiranWali extends Model
{
    protected $table = 'kehadiran_walis';

    protected $fillable = [
        'user_id',
        'santri_id',
        'tanggal',
        'waktu_hadir',
        'kegiatan',
        'keterangan',
        'admin_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
