<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelasHistory extends Model
{
    protected $table = 'kelas_histories';

    protected $fillable = [
        'santri_id',
        'kelas_dari',
        'kelas_ke',
        'tahun_ajaran',
        'tahun_kenaikan',
        'diproses_oleh',
        'keterangan',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function getKelasLabelDariAttribute(): string
    {
        return match ($this->kelas_dari) {
            '1'     => 'Kelas 1',
            '2'     => 'Kelas 2',
            '3'     => 'Kelas 3',
            'alumni' => 'Alumni',
            default => $this->kelas_dari ?? '-',
        };
    }

    public function getKelasLabelKeAttribute(): string
    {
        return match ($this->kelas_ke) {
            '1'     => 'Kelas 1',
            '2'     => 'Kelas 2',
            '3'     => 'Kelas 3',
            'alumni' => 'Alumni',
            default => $this->kelas_ke ?? '-',
        };
    }
}
