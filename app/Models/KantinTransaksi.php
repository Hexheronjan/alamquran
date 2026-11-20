<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KantinTransaksi extends Model
{
    protected $table = 'kantin_transaksis';

    protected $fillable = [
        'no_transaksi',
        'toko',
        'santri_id',
        'kasir_id',
        'total_modal',
        'total_harga',
        'laba_bersih',
        'metode_pembayaran',
        'catatan',
    ];

    protected $casts = [
        'total_modal' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'laba_bersih' => 'decimal:2',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function kasir()
    {
        return $this->belongsTo(Admin::class, 'kasir_id');
    }

    public function items()
    {
        return $this->hasMany(KantinTransaksiItem::class, 'transaksi_id');
    }

    public function getTokoLabelAttribute(): string
    {
        return KantinProduk::getTokoLabels()[$this->toko] ?? ucfirst($this->toko);
    }

    public static function generateNoTransaksi(string $toko): string
    {
        $prefix = match ($toko) {
            'kantin_1' => 'KNT1',
            'kantin_2' => 'KNT2',
            default    => 'AQM',
        };
        return $prefix . '-' . date('YmdHis') . '-' . rand(100, 999);
    }
}
