<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SantriCorrectionNote extends Model
{
    protected $fillable = [
        'santri_id', 'admin_id', 'catatan', 'diberikan_pada',
        'diselesaikan_pada', 'diselesaikan_oleh_user_id',
    ];

    protected $casts = [
        'diberikan_pada' => 'datetime',
        'diselesaikan_pada' => 'datetime',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }
}
