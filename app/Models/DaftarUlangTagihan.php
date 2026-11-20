<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DaftarUlangTagihan extends Model
{
    use HasFactory;

    protected $table = 'daftar_ulang_tagihans';

    protected $fillable = [
        'santri_id',
        'tahun_angkatan',
        'gelombang',
        'is_beasiswa',
        'nominal_biaya',
        'nominal_addon',
        'nominal_terbayar',
        'sisa_tagihan',
        'status',
        'nomor_va',
        'keterangan',
        'is_ready',
    ];

    protected $casts = [
        'nominal_biaya'    => 'decimal:2',
        'nominal_addon'    => 'decimal:2',
        'nominal_terbayar' => 'decimal:2',
        'sisa_tagihan'     => 'decimal:2',
        'is_ready'         => 'boolean',
        'is_beasiswa'      => 'boolean',
    ];

    public function getNomorInvoiceAttribute(): string
    {
        return 'INV-DU-' . str_pad($this->id, 5, '0', STR_PAD_LEFT) . '-' . ($this->tahun_angkatan ?: date('Y'));
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(DaftarUlangPembayaran::class)->orderBy('created_at', 'asc');
    }

    public function addons(): HasMany
    {
        return $this->hasMany(DaftarUlangTagihanAddon::class, 'daftar_ulang_tagihan_id');
    }

    /**
     * Total tagihan = Biaya Pokok + Total Add-on
     */
    public function getTotalTagihanAttribute(): float
    {
        return (float) $this->nominal_biaya + (float) ($this->nominal_addon ?? 0);
    }

    /**
     * Hitung ulang total terbayar dan sisa tagihan berdasarkan pembayaran berstatus 'diterima' & add-on.
     */
    public function syncTotals(): void
    {
        $totalAddon = (float) $this->addons()->sum('nominal');
        $this->nominal_addon = $totalAddon;

        if ($this->is_beasiswa) {
            $this->nominal_biaya    = 0;
            $this->nominal_terbayar = 0;
            $this->sisa_tagihan     = 0;
            $this->status           = 'lunas';
            $this->save();
            return;
        }

        $totalBiaya = (float) $this->nominal_biaya + $totalAddon;

        $totalDiterima = (float) $this->pembayarans()
            ->where('status', 'diterima')
            ->sum('jumlah_bayar');

        $sisa = max(0, $totalBiaya - $totalDiterima);

        $status = 'belum_lunas';
        if ($totalBiaya > 0 && $totalDiterima >= $totalBiaya) {
            $status = 'lunas';
        } elseif ($totalDiterima > 0) {
            $status = 'cicilan';
        }

        $this->nominal_terbayar = min($totalBiaya, $totalDiterima);
        $this->sisa_tagihan     = $sisa;
        $this->status           = $status;
        $this->save();
    }

    public function updateStatusAndBalance(): void
    {
        $this->syncTotals();
    }
}
