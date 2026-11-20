<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahfidzSetoran extends Model
{
    protected $table = 'tahfidz_setorans';

    protected $fillable = [
        'santri_id',
        'tanggal',
        'surah_id',
        'nama_surah',
        'ayat_awal',
        'ayat_akhir',
        'juz',
        'jenis_setoran',
        'kelancaran',
        'catatan_ustadz',
        'ustadz_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'ayat_awal' => 'integer',
        'ayat_akhir' => 'integer',
        'juz' => 'integer',
    ];

    public const JENIS_ZIYADAH = 'ziyadah';
    public const JENIS_MURAJAAH = 'murajaah';
    public const JENIS_UJIAN_JUZIYAH = 'ujian_juziyah';

    public static function getJenisList(): array
    {
        return [
            self::JENIS_ZIYADAH => 'Ziyadah',
            self::JENIS_MURAJAAH => 'Murojaah',
            self::JENIS_UJIAN_JUZIYAH => "Ujian Juz'iyah",
        ];
    }

    public function getJenisSetoranLabelAttribute(): string
    {
        return match ($this->jenis_setoran) {
            'ziyadah'       => 'Ziyadah',
            'murajaah'      => 'Murojaah',
            'ujian_juziyah' => "Ujian Juz'iyah",
            default         => ucfirst(str_replace('_', ' ', $this->jenis_setoran ?? '')),
        };
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function surah()
    {
        return $this->belongsTo(TahfidzSurah::class, 'surah_id');
    }

    public function ustadz()
    {
        return $this->belongsTo(Admin::class, 'ustadz_id');
    }
}
