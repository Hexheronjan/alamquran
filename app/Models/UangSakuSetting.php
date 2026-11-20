<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UangSakuSetting extends Model
{
    protected $table = 'uang_saku_settings';

    protected $fillable = [
        'batas_minimal_saldo',
        'is_active',
        'template_pesan',
        'bank_deposit',
        'nomor_rekening_deposit',
        'atas_nama_rekening',
    ];

    protected $casts = [
        'batas_minimal_saldo' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
