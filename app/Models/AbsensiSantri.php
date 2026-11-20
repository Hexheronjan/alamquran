<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiSantri extends Model
{
    protected $table = 'absensi_santris';

    protected $fillable = [
        'santri_id',
        'tanggal',
        'kelas',
        'status_kehadiran',
        'catatan',
        'guru_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function guru()
    {
        return $this->belongsTo(Admin::class, 'guru_id');
    }
}
