<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekeningKelas extends Model
{
    protected $table = 'rekening_kelas';

    protected $fillable = [
        'kelas',
        'nama_ustadz',
        'bank_name',
        'nomor_rekening',
        'atas_nama',
        'no_wa_ustadz',
        'admin_id',
        'catatan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function depositTransfers()
    {
        return $this->hasMany(DepositTransferManual::class, 'rekening_kelas_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForKelas($query, string $kelas)
    {
        return $query->where('kelas', $kelas);
    }
}
