<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SppPembayaran extends Model
{
    protected $table = 'spp_pembayarans';

    protected $fillable = [
        'spp_tagihan_id',
        'santri_id',
        'submitted_by_user_id',
        'jumlah_bayar',
        'metode_bayar',
        'bukti_bayar',
        'catatan',
        'alasan_penolakan',
        'status',
        'verified_by_admin_id',
        'verified_at',
    ];

    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'verified_at'  => 'datetime',
    ];

    public function tagihan()
    {
        return $this->belongsTo(SppTagihan::class, 'spp_tagihan_id');
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Admin::class, 'verified_by_admin_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }
}
