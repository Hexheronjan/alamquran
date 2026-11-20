<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SantriAddonTemplate extends Model
{
    use HasFactory;

    protected $table = 'santri_addon_templates';

    protected $fillable = [
        'santri_id',
        'nama_biaya',
        'nominal',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'nominal'   => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }
}

