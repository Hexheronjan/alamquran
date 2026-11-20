<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositTransferManual extends Model
{
    protected $fillable = [
        'user_id',
        'santri_id',
        'rekening_kelas_id',
        'jumlah',
        'bukti_transfer',
        'status',
        'catatan_wali',
        'catatan_admin',
        'admin_id',
        'diverifikasi_pada',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'diverifikasi_pada' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function rekeningKelas()
    {
        return $this->belongsTo(RekeningKelas::class, 'rekening_kelas_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
