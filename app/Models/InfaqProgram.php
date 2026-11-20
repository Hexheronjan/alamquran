<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfaqProgram extends Model
{
    protected $table = 'infaq_programs';

    protected $fillable = [
        'nama_program',
        'deskripsi',
        'target_dana',
        'dana_terkumpul',
        'dana_tersalurkan',
        'foto_banner',
        'is_active',
    ];

    protected $casts = [
        'target_dana'      => 'decimal:2',
        'dana_terkumpul'   => 'decimal:2',
        'dana_tersalurkan' => 'decimal:2',
        'is_active'        => 'boolean',
    ];

    public function transaksis()
    {
        return $this->hasMany(InfaqTransaksi::class, 'infaq_program_id');
    }
}
