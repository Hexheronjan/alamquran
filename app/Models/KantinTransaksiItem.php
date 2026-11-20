<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KantinTransaksiItem extends Model
{
    protected $table = 'kantin_transaksi_items';

    protected $fillable = [
        'transaksi_id',
        'produk_id',
        'nama_produk',
        'harga_modal',
        'harga_jual',
        'qty',
        'subtotal_modal',
        'subtotal_harga',
    ];

    protected $casts = [
        'harga_modal'    => 'decimal:2',
        'harga_jual'     => 'decimal:2',
        'qty'            => 'integer',
        'subtotal_modal' => 'decimal:2',
        'subtotal_harga' => 'decimal:2',
    ];

    public function transaksi()
    {
        return $this->belongsTo(KantinTransaksi::class, 'transaksi_id');
    }

    public function produk()
    {
        return $this->belongsTo(KantinProduk::class, 'produk_id');
    }
}
