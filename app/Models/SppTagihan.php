<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SppTagihan extends Model
{
    protected $table = 'spp_tagihans';

    protected $fillable = [
        'santri_id',
        'bulan',
        'tahun',
        'nominal_spp',
        'nominal_addon',
        'nominal_tagihan',
        'nominal_terbayar',
        'sisa_tagihan',
        'status',
        'nomor_va',
        'keterangan',
        'broadcast_wa_at',
    ];

    protected $casts = [
        'bulan'            => 'integer',
        'tahun'            => 'integer',
        'nominal_spp'      => 'decimal:2',
        'nominal_addon'    => 'decimal:2',
        'nominal_tagihan'  => 'decimal:2',
        'nominal_terbayar' => 'decimal:2',
        'sisa_tagihan'     => 'decimal:2',
        'broadcast_wa_at'  => 'datetime',
    ];

    public static function getNamaBulan(int $bulan): string
    {
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $bulanList[$bulan] ?? "Bulan {$bulan}";
    }

    public function getBulanLabelAttribute(): string
    {
        return self::getNamaBulan($this->bulan) . ' ' . $this->tahun;
    }

    public function getNomorInvoiceAttribute(): string
    {
        return 'INV-SPP-' . str_pad($this->id, 5, '0', STR_PAD_LEFT) . '-' . $this->tahun . str_pad($this->bulan, 2, '0', STR_PAD_LEFT);
    }

    public function getIsBeasiswaAttribute(): bool
    {
        return (float) $this->nominal_spp === 0.0;
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(SppPembayaran::class, 'spp_tagihan_id');
    }

    public function addons()
    {
        return $this->hasMany(SppTagihanAddon::class, 'spp_tagihan_id');
    }

    public function updateStatusAndBalance(): void
    {
        $terbayar = (float) $this->pembayarans()
            ->where('status', 'diterima')
            ->sum('jumlah_bayar');

        $this->nominal_terbayar = min((float) $this->nominal_tagihan, $terbayar);
        $this->sisa_tagihan = max(0, (float) $this->nominal_tagihan - $this->nominal_terbayar);

        if ($this->sisa_tagihan <= 0) {
            $this->status = 'lunas';
        } elseif ($this->nominal_terbayar > 0) {
            $this->status = 'cicilan';
        } else {
            $this->status = 'belum_lunas';
        }

        $this->save();
    }

    public function syncTotals(): void
    {
        $totalAddon = (float) $this->addons()->sum('nominal');
        $this->nominal_addon = $totalAddon;
        $this->nominal_tagihan = (float) $this->nominal_spp + $totalAddon;

        $terbayar = (float) $this->pembayarans()
            ->where('status', 'diterima')
            ->sum('jumlah_bayar');

        $this->nominal_terbayar = min((float) $this->nominal_tagihan, $terbayar);
        $this->sisa_tagihan = max(0, (float) $this->nominal_tagihan - $this->nominal_terbayar);

        if ($this->sisa_tagihan <= 0) {
            $this->status = 'lunas';
        } elseif ($this->nominal_terbayar > 0) {
            $this->status = 'cicilan';
        } else {
            $this->status = 'belum_lunas';
        }

        $this->save();
    }
}
