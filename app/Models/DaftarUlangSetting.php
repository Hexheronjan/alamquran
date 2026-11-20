<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DaftarUlangSetting extends Model
{
    use HasFactory;

    protected $table = 'daftar_ulang_settings';

    protected $fillable = [
        'status',           // buka, tutup, otomatis
        'tanggal_buka',     // Tanggal buka otomatis (YYYY-MM-DD)
        'pesan_tutup',      // Pesan saat pembayaran terkunci/ditutup
        'auto_notif_wa',    // Apakah kirim WA otomatis saat dibuka
        'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'auto_notif_wa' => 'boolean',
        'tanggal_buka'  => 'date',
    ];

    /**
     * Ambil setting aktif dengan fallback aman (auto-create tabel jika belum ada).
     */
    public static function getActive(): self
    {
        try {
            if (!Schema::hasTable('daftar_ulang_settings')) {
                Schema::create('daftar_ulang_settings', function (Blueprint $table) {
                    $table->id();
                    $table->string('status', 20)->default('buka');
                    $table->date('tanggal_buka')->nullable();
                    $table->text('pesan_tutup')->nullable();
                    $table->boolean('auto_notif_wa')->default(false);
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                });

                return self::create([
                    'status'        => 'buka',
                    'tanggal_buka'  => null,
                    'pesan_tutup'   => 'Pembayaran daftar ulang saat ini belum dibuka. Silakan menunggu informasi resmi dari pesantren.',
                    'auto_notif_wa' => false,
                    'is_active'     => true,
                ]);
            }

            $setting = self::where('is_active', true)->latest('id')->first();
            if (!$setting) {
                $setting = self::create([
                    'status'        => 'buka',
                    'tanggal_buka'  => null,
                    'pesan_tutup'   => 'Pembayaran daftar ulang saat ini belum dibuka. Silakan menunggu informasi resmi dari pesantren.',
                    'auto_notif_wa' => false,
                    'is_active'     => true,
                ]);
            }
            return $setting;
        } catch (\Throwable $e) {
            $fallback = new self();
            $fallback->status = 'buka';
            $fallback->tanggal_buka = null;
            $fallback->pesan_tutup = 'Pembayaran daftar ulang saat ini belum dibuka. Silakan menunggu informasi resmi dari pesantren.';
            $fallback->auto_notif_wa = false;
            $fallback->is_active = true;
            return $fallback;
        }
    }

    /**
     * Cek apakah saat ini pembayaran dibuka untuk wali santri.
     */
    public function isOpen(): bool
    {
        if ($this->status === 'buka') {
            return true;
        }

        if ($this->status === 'tutup') {
            return false;
        }

        // Status 'otomatis'
        if (empty($this->tanggal_buka)) {
            return true;
        }

        // Begitu tanggal sekarang >= tanggal buka, otomatis terbuka terus tanpa batas waktu!
        return date('Y-m-d') >= $this->tanggal_buka->format('Y-m-d');
    }

    /**
     * Ambil waktu mulai sesi pembukaan saat ini.
     * Digunakan untuk memastikan notifikasi dikirimkan pada setiap sesi buka baru,
     * termasuk jika sempat ditutup lalu dibuka kembali.
     */
    public function getOpeningSessionTimestamp(): ?\Illuminate\Support\Carbon
    {
        if (!$this->isOpen()) {
            return null;
        }

        if ($this->status === 'otomatis' && $this->tanggal_buka) {
            $tanggalBukaStart = $this->tanggal_buka->copy()->startOfDay();
            if ($this->updated_at && $this->updated_at->gt($tanggalBukaStart)) {
                return $this->updated_at;
            }
            return $tanggalBukaStart;
        }

        return $this->updated_at ?? now()->startOfDay();
    }
}
