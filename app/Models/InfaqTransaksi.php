<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfaqTransaksi extends Model
{
    protected $table = 'infaq_transaksis';

    protected $fillable = [
        'infaq_program_id',
        'user_id',
        'santri_id',
        'nama_donatur',
        'no_wa_donatur',
        'nominal',
        'metode_pembayaran',
        'bukti_transfer',
        'status',
        'doa_harapan',
        'verified_by_id',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function program()
    {
        return $this->belongsTo(InfaqProgram::class, 'infaq_program_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Admin::class, 'verified_by_id');
    }
}
