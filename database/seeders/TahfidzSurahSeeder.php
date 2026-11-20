<?php

namespace Database\Seeders;

use App\Models\TahfidzSurah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TahfidzSurahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suratData = null;

        // Coba ambil dari file JSON lokal terlebih dahulu
        $jsonPath = database_path('data/quran_data.json');
        if (file_exists($jsonPath)) {
            $json = json_decode(file_get_contents($jsonPath), true);
            $suratData = $json['surat'] ?? null;
        }

        // Jika belum ada lokal, coba tarik dari API MyQuran
        if (empty($suratData)) {
            try {
                $response = Http::timeout(10)->get('https://api.myquran.com/v2/quran/surat/semua');
                if ($response->successful()) {
                    $suratData = $response->json('data');
                }
            } catch (\Exception $e) {
                // Abaikan jika offline
            }
        }

        if (empty($suratData)) {
            return;
        }

        foreach ($suratData as $s) {
            $nomor = (int) ($s['number'] ?? $s['nomor'] ?? 0);
            if ($nomor < 1) continue;

            TahfidzSurah::updateOrCreate(
                ['nomor_surah' => $nomor],
                [
                    'nama_latin'   => $s['name_id'] ?? $s['nama_latin'] ?? "Surah {$nomor}",
                    'nama_arab'    => $s['name_short'] ?? $s['nama_arab'] ?? '',
                    'arti'         => $s['translation_id'] ?? $s['arti'] ?? '',
                    'jumlah_ayat'  => (int) ($s['number_of_verses'] ?? $s['jumlah_ayat'] ?? 0),
                    'tempat_turun' => $s['revelation_id'] ?? $s['tempat_turun'] ?? 'Makkiyyah',
                ]
            );
        }
    }
}
