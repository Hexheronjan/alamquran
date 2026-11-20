<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SppSetting extends Model
{
    protected $table = 'spp_settings';

    protected $fillable = [
        'nama_bank',
        'nomor_rekening',
        'atas_nama_rekening',
        'no_wa_bendahara',
        'biaya_admin',
        'nominal_kelas_1',
        'nominal_kelas_2',
        'nominal_kelas_3',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'biaya_admin'      => 'decimal:2',
        'nominal_kelas_1'  => 'decimal:2',
        'nominal_kelas_2'  => 'decimal:2',
        'nominal_kelas_3'  => 'decimal:2',
    ];

    /**
     * Ambil setting aktif (ambil baris aktif terbaru atau baris pertama).
     */
    public static function getActive(): ?self
    {
        return self::where('is_active', true)->latest('id')->first() ?? self::first();
    }
}
