<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KantinProduk extends Model
{
    protected $table = 'kantin_produks';

    protected $fillable = [
        'toko',
        'nama_produk',
        'kode_produk',
        'deskripsi',
        'harga_modal',
        'harga_jual',
        'stok',
        'foto_produk',
        'is_active',
    ];

    protected $casts = [
        'harga_modal' => 'decimal:2',
        'harga_jual'  => 'decimal:2',
        'stok'        => 'integer',
        'is_active'   => 'boolean',
    ];

    public static function getTokoLabels(): array
    {
        return [
            'kantin_1' => 'Kantin 1',
            'kantin_2' => 'Kantin 2',
            'aqumart'  => 'Minimarket Aqumart',
        ];
    }

    public function getTokoLabelAttribute(): string
    {
        return self::getTokoLabels()[$this->toko] ?? ucfirst($this->toko);
    }

    public function getMarginAttribute(): float
    {
        return (float) ($this->harga_jual - $this->harga_modal);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeToko($query, string $toko)
    {
        return $query->where('toko', $toko);
    }

    public static function generateKodeProduk(?string $toko = 'aqumart'): string
    {
        $prefixes = [
            'kantin_1' => 'KT1',
            'kantin_2' => 'KT2',
            'aqumart'  => 'AQM',
        ];

        $prefix = $prefixes[$toko ?? 'aqumart'] ?? 'PRD';

        $existingCodes = self::where('kode_produk', 'like', "{$prefix}-%")
            ->pluck('kode_produk');

        $maxNum = 0;
        foreach ($existingCodes as $code) {
            if (preg_match('/' . preg_quote($prefix, '/') . '-(\d+)/i', $code, $m)) {
                $num = (int) $m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;

        do {
            $candidate = sprintf('%s-%03d', $prefix, $nextNum);
            $exists = self::where('kode_produk', $candidate)->exists();
            if ($exists) {
                $nextNum++;
            }
        } while ($exists);

        return $candidate;
    }
}
