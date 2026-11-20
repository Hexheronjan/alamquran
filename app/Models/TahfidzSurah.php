<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahfidzSurah extends Model
{
    protected $table = 'tahfidz_surahs';

    protected $fillable = [
        'nomor_surah',
        'nama_latin',
        'nama_arab',
        'arti',
        'jumlah_ayat',
        'tempat_turun',
    ];

    public static function getJuzForVerse(int $surahNumber, int $verseNumber = 1): int
    {
        $juzBoundaries = [
            1  => ['sS' => 1,  'sV' => 1,   'eS' => 2,  'eV' => 141],
            2  => ['sS' => 2,  'sV' => 142, 'eS' => 2,  'eV' => 252],
            3  => ['sS' => 2,  'sV' => 253, 'eS' => 3,  'eV' => 92],
            4  => ['sS' => 3,  'sV' => 93,  'eS' => 4,  'eV' => 23],
            5  => ['sS' => 4,  'sV' => 24,  'eS' => 4,  'eV' => 147],
            6  => ['sS' => 4,  'sV' => 148, 'eS' => 5,  'eV' => 81],
            7  => ['sS' => 5,  'sV' => 82,  'eS' => 6,  'eV' => 110],
            8  => ['sS' => 6,  'sV' => 111, 'eS' => 7,  'eV' => 87],
            9  => ['sS' => 7,  'sV' => 88,  'eS' => 8,  'eV' => 40],
            10 => ['sS' => 8,  'sV' => 41,  'eS' => 9,  'eV' => 92],
            11 => ['sS' => 9,  'sV' => 93,  'eS' => 11, 'eV' => 5],
            12 => ['sS' => 11, 'sV' => 6,   'eS' => 12, 'eV' => 52],
            13 => ['sS' => 12, 'sV' => 53,  'eS' => 14, 'eV' => 52],
            14 => ['sS' => 15, 'sV' => 1,   'eS' => 16, 'eV' => 128],
            15 => ['sS' => 17, 'sV' => 1,   'eS' => 18, 'eV' => 74],
            16 => ['sS' => 18, 'sV' => 75,  'eS' => 20, 'eV' => 135],
            17 => ['sS' => 21, 'sV' => 1,   'eS' => 22, 'eV' => 78],
            18 => ['sS' => 23, 'sV' => 1,   'eS' => 25, 'eV' => 20],
            19 => ['sS' => 25, 'sV' => 21,  'eS' => 27, 'eV' => 55],
            20 => ['sS' => 27, 'sV' => 56,  'eS' => 29, 'eV' => 45],
            21 => ['sS' => 29, 'sV' => 46,  'eS' => 33, 'eV' => 30],
            22 => ['sS' => 33, 'sV' => 31,  'eS' => 36, 'eV' => 27],
            23 => ['sS' => 36, 'sV' => 28,  'eS' => 39, 'eV' => 31],
            24 => ['sS' => 39, 'sV' => 32,  'eS' => 41, 'eV' => 46],
            25 => ['sS' => 41, 'sV' => 47,  'eS' => 45, 'eV' => 37],
            26 => ['sS' => 46, 'sV' => 1,   'eS' => 51, 'eV' => 30],
            27 => ['sS' => 51, 'sV' => 31,  'eS' => 57, 'eV' => 29],
            28 => ['sS' => 58, 'sV' => 1,   'eS' => 66, 'eV' => 12],
            29 => ['sS' => 67, 'sV' => 1,   'eS' => 77, 'eV' => 50],
            30 => ['sS' => 78, 'sV' => 1,   'eS' => 114,'eV' => 6],
        ];

        foreach ($juzBoundaries as $juz => $b) {
            $afterStart = ($surahNumber > $b['sS']) || ($surahNumber === $b['sS'] && $verseNumber >= $b['sV']);
            $beforeEnd = ($surahNumber < $b['eS']) || ($surahNumber === $b['eS'] && $verseNumber <= $b['eV']);
            if ($afterStart && $beforeEnd) {
                return $juz;
            }
        }

        return 30;
    }

    public function setorans()
    {
        return $this->hasMany(TahfidzSetoran::class, 'surah_id');
    }
}
