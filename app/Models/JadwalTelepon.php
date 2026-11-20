<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalTelepon extends Model
{
    use HasFactory;

    protected $table = 'jadwal_telepons';

    protected $fillable = [
        'santri_id',
        'tanggal_telepon',
        'jam_mulai',
        'jam_selesai',
        'durasi_menit',
        'ustadz_pendamping',
        'no_hp_posko',
        'topik',
        'keterangan',
        'status',
        'catatan_panggilan',
        'waktu_dihubungi',
        'wa_reminder_sent',
        'wa_reminder_sent_at',
        'wa_reminder_status',
        'wa_reminder_error',
        'created_by_admin_id',
    ];

    protected $casts = [
        'tanggal_telepon'     => 'date:Y-m-d',
        'waktu_dihubungi'     => 'datetime',
        'wa_reminder_sent'    => 'boolean',
        'wa_reminder_sent_at' => 'datetime',
        'durasi_menit'        => 'integer',
    ];

    protected $appends = [
        'tanggal_telepon_raw',
    ];

    public function getTanggalTeleponRawAttribute(): string
    {
        return $this->getRawOriginal('tanggal_telepon') ?: ($this->tanggal_telepon ? $this->tanggal_telepon->format('Y-m-d') : date('Y-m-d'));
    }

    public const STATUS_MENUNGGU          = 'menunggu';
    public const STATUS_TERHUBUNG         = 'terhubung';
    public const STATUS_TIDAK_TERANGKAT   = 'tidak_terangkat';
    public const STATUS_DIJADWALKAN_ULANG = 'dijadwalkan_ulang';
    public const STATUS_DIBATALKAN        = 'dibatalkan';

    public static function getStatusList(): array
    {
        return [
            self::STATUS_MENUNGGU          => 'Menunggu Giliran',
            self::STATUS_TERHUBUNG         => 'Berhasil Terhubung',
            self::STATUS_TIDAK_TERANGKAT   => 'Tidak Terangkat / Sibuk',
            self::STATUS_DIJADWALKAN_ULANG => 'Dijadwalkan Ulang',
            self::STATUS_DIBATALKAN        => 'Dibatalkan',
        ];
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    /**
     * Dapatkan label status yang mudah dibaca.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Badge status panggilan untuk Tailwind UI.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_TERHUBUNG => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fas fa-check-circle text-emerald-600"></i> Terhubung</span>',
            self::STATUS_TIDAK_TERANGKAT => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200"><i class="fas fa-phone-slash text-rose-600"></i> Tidak Terangkat</span>',
            self::STATUS_DIJADWALKAN_ULANG => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200"><i class="fas fa-clock-rotate-left text-amber-600"></i> Jadwal Ulang</span>',
            self::STATUS_DIBATALKAN => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200"><i class="fas fa-ban text-gray-500"></i> Dibatalkan</span>',
            default => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200"><i class="fas fa-hourglass-half text-blue-600"></i> Menunggu Giliran</span>',
        };
    }

    /**
     * Format jam tampilan (contoh: 16:00 - 16:15 WIB).
     */
    public function getFormattedJamAttribute(): string
    {
        $mulai = substr((string)$this->jam_mulai, 0, 5);
        $selesai = substr((string)$this->jam_selesai, 0, 5);
        return "{$mulai} - {$selesai} WIB";
    }

    /**
     * Format tanggal Indonesia lengkap.
     */
    public function getFormattedTanggalAttribute(): string
    {
        return $this->tanggal_telepon ? $this->tanggal_telepon->translatedFormat('l, d F Y') : '-';
    }

    /**
     * Cek apakah jadwal berlangsung hari ini.
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->tanggal_telepon && $this->tanggal_telepon->isToday();
    }

    /**
     * Cek apakah jadwal masih akan datang (hari ini atau masa depan dan belum selesai).
     */
    public function getIsUpcomingAttribute(): bool
    {
        if ($this->status !== self::STATUS_MENUNGGU) {
            return false;
        }

        if (!$this->tanggal_telepon) {
            return false;
        }

        if ($this->tanggal_telepon->isFuture()) {
            return true;
        }

        if ($this->tanggal_telepon->isToday()) {
            $jamSelesaiStr = substr((string)$this->jam_selesai, 0, 5);
            return now()->format('H:i') <= $jamSelesaiStr;
        }

        return false;
    }
}
