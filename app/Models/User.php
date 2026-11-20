<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const PLACEHOLDER_EMAIL_DOMAIN = '@wali.alamquran.local';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'must_change_password',
        'no_wa',
        'qr_token',
        'otp_code',
        'otp_created_at',
        'otp_expired_at',
        'otp_delivery_status',
        'otp_delivery_error',
        'last_login_at',
        'last_login_ip',
        'last_activity_at',
        'login_count',
    ];

    public function ensureQrToken(): string
    {
        if (!$this->qr_token) {
            $this->qr_token = 'WALI-' . strtoupper(substr(md5($this->id . '-' . $this->no_wa . '-' . config('app.key')), 0, 16));
            $this->save();
        }
        return $this->qr_token;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'     => 'datetime',
            'password'              => 'hashed',
            'must_change_password'  => 'boolean',
            'otp_created_at'        => 'datetime',
            'otp_expired_at'        => 'datetime',
            'last_login_at'         => 'datetime',
            'last_activity_at'      => 'datetime',
            'login_count'           => 'integer',
        ];
    }

    public function isOnline(): bool
    {
        return $this->last_activity_at && $this->last_activity_at->gte(now()->subMinutes(10));
    }

    public function saranMasukans()
    {
        return $this->hasMany(SaranMasukan::class);
    }

    /**
     * Cek apakah OTP user sedang aktif (memiliki kode dan belum kedaluwarsa).
     */
    public function isOtpActive(): bool
    {
        return !empty($this->otp_code) && $this->otp_expired_at && $this->otp_expired_at->isFuture();
    }

    /**
     * Cek apakah OTP user sudah kedaluwarsa.
     */
    public function isOtpExpired(): bool
    {
        return !empty($this->otp_code) && $this->otp_expired_at && $this->otp_expired_at->isPast();
    }

    public function santri()
    {
        return $this->hasMany(Santri::class);
    }
}
