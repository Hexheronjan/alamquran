<?php

namespace App\Services;

class InvoiceService
{
    /**
     * Konversi angka nominal rupiah menjadi kalimat terbilang bahasa Indonesia.
     */
    public static function terbilang(float|int $angka): string
    {
        $angka = abs((float) $angka);
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $hasil = '';

        if ($angka < 12) {
            $hasil = ' ' . $baca[(int) $angka];
        } elseif ($angka < 20) {
            $hasil = self::terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            $hasil = self::terbilang((int)($angka / 10)) . ' Puluh' . self::terbilang($angka % 10);
        } elseif ($angka < 200) {
            $hasil = ' Seratus' . self::terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $hasil = self::terbilang((int)($angka / 100)) . ' Ratus' . self::terbilang($angka % 100);
        } elseif ($angka < 2000) {
            $hasil = ' Seribu' . self::terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $hasil = self::terbilang((int)($angka / 1000)) . ' Ribu' . self::terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            $hasil = self::terbilang((int)($angka / 1000000)) . ' Juta' . self::terbilang(fmod($angka, 1000000));
        } elseif ($angka < 1000000000000) {
            $hasil = self::terbilang((int)($angka / 1000000000)) . ' Miliar' . self::terbilang(fmod($angka, 1000000000));
        }

        return trim($hasil);
    }
}
