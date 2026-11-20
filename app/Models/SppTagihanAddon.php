<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SppTagihanAddon extends Model
{
    protected $table = 'spp_tagihan_addons';

    protected $fillable = [
        'spp_tagihan_id',
        'nama_biaya',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function tagihan()
    {
        return $this->belongsTo(SppTagihan::class, 'spp_tagihan_id');
    }
}
