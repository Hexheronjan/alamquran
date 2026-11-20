<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class InvoiceSetting extends Model
{
    use HasFactory;

    protected $table = 'invoice_settings';

    protected $fillable = [
        'nama_lembaga',
        'tagline_kop',
        'alamat',
        'telepon',
        'email',
        'website',
        'logo',
        'nama_bendahara',
        'jabatan_penandatangan',
        'catatan_bawah',
    ];

    /**
     * Dapatkan setting aktif dengan fallback aman jika tabel belum ada atau kosong.
     */
    public static function getSetting(): self
    {
        if (Schema::hasTable('invoice_settings')) {
            $setting = self::first();
            if ($setting) {
                return $setting;
            }
        }

        // Ambil data dari tabel schools (informasi madrasah) jika ada
        $school = School::first();

        $default = new self([
            'nama_lembaga'          => $school->name ?? 'Pesantren Tahfizh Alam Qur\'an',
            'tagline_kop'           => 'Lembaga Pendidikan & Pembinaan Penghafal Al-Qur\'an',
            'alamat'                => $school->address ?? 'JL. Pandanarum - Winong - Jetis - Ponorogo, Jawa Timur',
            'telepon'               => $school->phone ?? '087756563507 / 085853410025',
            'email'                 => $school->email ?? 'info@alamquran.sch.id',
            'website'               => 'alamquran.sch.id',
            'nama_bendahara'        => 'Bendahara Pesantren',
            'jabatan_penandatangan' => 'Bendahara Pesantren',
            'catatan_bawah'         => 'Simpan tanda bukti pembayaran ini sebagai bukti transaksi resmi.',
        ]);

        if (Schema::hasTable('invoice_settings')) {
            $default->save();
        }

        return $default;
    }
}
