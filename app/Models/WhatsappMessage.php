<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'reference_id',
        'gateway_message_id',
        'recipient_number',
        'recipient_name',
        'user_id',
        'santri_id',
        'modul',
        'template_kode',
        'message_body',
        'priority',
        'status',
        'attempts',
        'max_attempts',
        'error_message',
        'provider',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
        'delivered_at' => 'datetime',
        'read_at'      => 'datetime',
        'attempts'     => 'integer',
        'max_attempts' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function template()
    {
        return $this->belongsTo(WhatsappTemplate::class, 'template_kode', 'kode');
    }

    /**
     * Cache memori sementara untuk pencocokan nomor telepon ke nama kontak.
     */
    protected static array $phoneNameCache = [];

    /**
     * Resolusi nama penerima berdasarkan nomor telepon.
     * Mencocokkan secara pintar ke: User (Akun Wali), Orang Tua (no_wa_ayah), atau Bendahara.
     */
    public static function resolveRecipientNameByPhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        if (array_key_exists($digits, static::$phoneNameCache)) {
            return static::$phoneNameCache[$digits];
        }

        try {
            $variants = [$digits];
            if (str_starts_with($digits, '628')) {
                $variants[] = '08' . substr($digits, 2);
                $variants[] = '+' . $digits;
                $variants[] = '8' . substr($digits, 2);
            } elseif (str_starts_with($digits, '08')) {
                $variants[] = '628' . substr($digits, 2);
                $variants[] = '+628' . substr($digits, 2);
                $variants[] = substr($digits, 1);
            }

            // 1. Cek User (Akun Wali / Pengguna)
            $user = User::whereIn('no_wa', $variants)->first();
            if ($user && !empty($user->name)) {
                return static::$phoneNameCache[$digits] = $user->name;
            }

            // 2. Cek Orang Tua (no_wa_ayah)
            $ortu = OrangTua::with('santri')->whereIn('no_wa_ayah', $variants)->first();
            if ($ortu) {
                $nama = $ortu->nama_ayah;
                $santri = $ortu->santri?->nama_lengkap;
                $res = $nama ? ($santri ? "{$nama} ({$santri})" : $nama) : ($santri ? "Wali {$santri}" : null);
                if ($res) {
                    return static::$phoneNameCache[$digits] = $res;
                }
            }

            // 3. Cek Bendahara di SppSetting
            $sppSetting = SppSetting::first();
            if ($sppSetting && !empty($sppSetting->no_wa_bendahara)) {
                $bDigits = preg_replace('/\D/', '', $sppSetting->no_wa_bendahara);
                if ($bDigits === $digits || in_array($bDigits, $variants)) {
                    return static::$phoneNameCache[$digits] = 'Bendahara Pesantren';
                }
            }

            // 4. Cek dari pesan lain di database yang nomornya sama dan sudah memiliki nama
            $existing = static::whereIn('recipient_number', $variants)
                ->whereNotNull('recipient_name')
                ->where('recipient_name', '!=', '')
                ->where('recipient_name', '!=', '-')
                ->orderByDesc('id')
                ->value('recipient_name');
            if ($existing) {
                return static::$phoneNameCache[$digits] = $existing;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal resolveRecipientNameByPhone: " . $e->getMessage());
        }

        return static::$phoneNameCache[$digits] = null;
    }

    /**
     * Dapatkan nama penerima yang rapi untuk ditampilkan di tabel log.
     */
    public function getDisplayRecipientNameAttribute(): string
    {
        if (!empty($this->recipient_name) && trim($this->recipient_name) !== '-') {
            return $this->recipient_name;
        }

        if ($this->santri && !empty($this->santri->nama_lengkap)) {
            return $this->santri->nama_lengkap;
        }

        if ($this->user && !empty($this->user->name)) {
            return $this->user->name;
        }

        $resolved = static::resolveRecipientNameByPhone($this->recipient_number);
        if ($resolved) {
            return $resolved;
        }

        return '-';
    }
}
