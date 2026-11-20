<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UangSaku extends Model
{
    protected $table = 'uang_saku';

    protected $fillable = [
        'santri_id',
        'jenis',
        'jumlah',
        'saldo_akhir',
        'keterangan',
        'kelas',
        'tahun_ajaran',
        'admin_id'
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
    ];

    public function getNomorInvoiceAttribute(): string
    {
        return 'KWT-US-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public static function recalculateBalance($santriId)
    {
        $allTrans = self::where('santri_id', $santriId)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = 0;
        foreach ($allTrans as $t) {
            if ($t->jenis === 'masuk') {
                $runningBalance += $t->jumlah;
            } else {
                $runningBalance -= $t->jumlah;
            }
            $t->timestamps = false;
            $t->saldo_akhir = $runningBalance;
            $t->save();
        }

        return $runningBalance;
    }
}
