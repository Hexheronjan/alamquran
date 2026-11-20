<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DaftarUlangPembayaran extends Model
{
    use HasFactory;

    protected $table = 'daftar_ulang_pembayarans';

    protected $fillable = [
        'daftar_ulang_tagihan_id',
        'santri_id',
        'jumlah_bayar',
        'metode_bayar',
        'bukti_bayar',
        'catatan',
        'status',
        'alasan_penolakan',
        'verified_by_admin_id',
        'verified_at',
        'submitted_by_user_id',
    ];

    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'verified_at'  => 'datetime',
    ];

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(DaftarUlangTagihan::class, 'daftar_ulang_tagihan_id');
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'verified_by_admin_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }
}
