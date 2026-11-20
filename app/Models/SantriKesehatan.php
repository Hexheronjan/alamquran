<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SantriKesehatan extends Model
{
    protected $table = 'santri_kesehatans';

    protected $fillable = [
        'santri_id',
        'tanggal',
        'keluhan_sakit',
        'tindakan_perawatan',
        'obat_diberikan',
        'status_kesehatan',
        'tanggal_jemput',
        'nama_penjemput',
        'estimasi_kembali',
        'catatan_penjemputan',
        'petugas_id',
    ];

    protected $casts = [
        'tanggal'          => 'date',
        'tanggal_jemput'   => 'datetime',
        'estimasi_kembali' => 'date',
    ];

    /**
     * Hitung durasi sakit santri (jumlah hari sejak tanggal pemeriksaan)
     */
    public function getLamaSakitAttribute(): int
    {
        if (!$this->tanggal) return 1;
        return (int) \Carbon\Carbon::parse($this->tanggal)->startOfDay()->diffInDays(now()->startOfDay()) + 1;
    }

    /**
     * Cek apakah santri sakit sudah 3 hari atau lebih dan belum sembuh / belum di rumah
     */
    public function getIsLebihTigaHariAttribute(): bool
    {
        return $this->lama_sakit >= 3 && in_array($this->status_kesehatan, ['istirahat_uks', 'rawat_inap', 'pantauan'], true);
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function petugas()
    {
        return $this->belongsTo(Admin::class, 'petugas_id');
    }
}
