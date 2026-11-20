<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerizinanSantri extends Model
{
    protected $table = 'perizinan_santris';

    protected $fillable = [
        'santri_id',
        'user_id',
        'jenis_izin',
        'tujuan',
        'nama_penjemput',
        'no_wa_penjemput',
        'waktu_keluar',
        'batas_jam_kembali',
        'waktu_kembali',
        'status',
        'wa_reminder_sent',
        'catatan',
        'petugas_keluar_id',
        'petugas_kembali_id',
    ];

    protected $casts = [
        'waktu_keluar'     => 'datetime',
        'waktu_kembali'    => 'datetime',
        'wa_reminder_sent' => 'boolean',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function petugasKeluar()
    {
        return $this->belongsTo(Admin::class, 'petugas_keluar_id');
    }

    public function petugasKembali()
    {
        return $this->belongsTo(Admin::class, 'petugas_kembali_id');
    }
}
