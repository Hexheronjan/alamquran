<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DaftarUlangTagihanAddon extends Model
{
    protected $table = 'daftar_ulang_tagihan_addons';

    protected $fillable = [
        'daftar_ulang_tagihan_id',
        'nama_biaya',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(DaftarUlangTagihan::class, 'daftar_ulang_tagihan_id');
    }
}
