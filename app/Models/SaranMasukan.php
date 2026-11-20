<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaranMasukan extends Model
{
    use HasFactory;

    protected $table = 'saran_masukans';

    protected $fillable = [
        'user_id',
        'admin_id',
        'nama_pengirim',
        'tipe_pengirim',
        'kategori',
        'judul',
        'pesan',
        'lampiran',
        'status',
        'tanggapan_admin',
        'responded_by_admin_id',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function responder()
    {
        return $this->belongsTo(Admin::class, 'responded_by_admin_id');
    }

    public static function getKategoriList(): array
    {
        return [
            'fitur_baru'   => 'Usulan Fitur Baru',
            'tampilan_ui'  => 'Desain & Tampilan (UI/UX)',
            'kendala_bug'  => 'Kendala / Error / Bug',
            'pelayanan'    => 'Pelayanan & Pembinaan Pondok',
            'saran'        => 'Saran & Masukan Umum',
        ];
    }

    public static function getKategoriIcon(string $kategori, string $set = 'fa'): string
    {
        if ($set === 'ti' || $set === 'tabler') {
            return match ($kategori) {
                'fitur_baru'   => 'ti ti-bulb',
                'tampilan_ui'  => 'ti ti-palette',
                'kendala_bug'  => 'ti ti-alert-triangle',
                'pelayanan'    => 'ti ti-building-community',
                'saran'        => 'ti ti-message-dots',
                default        => 'ti ti-tag',
            };
        }

        return match ($kategori) {
            'fitur_baru'   => 'fas fa-lightbulb',
            'tampilan_ui'  => 'fas fa-palette',
            'kendala_bug'  => 'fas fa-triangle-exclamation',
            'pelayanan'    => 'fas fa-mosque',
            'saran'        => 'fas fa-comment-dots',
            default        => 'fas fa-tag',
        };
    }

    public static function getStatusList(): array
    {
        return [
            'menunggu_review' => 'Menunggu Review',
            'sedang_diproses' => 'Sedang Diproses',
            'diterapkan'      => 'Selesai / Diterapkan',
            'diarsipkan'      => 'Diarsipkan',
        ];
    }
}
