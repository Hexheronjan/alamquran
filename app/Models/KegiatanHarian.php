<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class KegiatanHarian extends Model
{
    protected $table = 'kegiatan_harians';

    protected $fillable = [
        'waktu_mulai',
        'waktu_selesai',
        'nama_kegiatan',
        'deskripsi',
        'hari',
        'kelas',
        'is_rutin_24jam',
        'tanggal_khusus',
        'admin_id',
    ];

    protected $casts = [
        'is_rutin_24jam' => 'boolean',
        'tanggal_khusus' => 'date',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Label Hari dalam Bahasa Indonesia
     */
    public function getHariLabelAttribute(): string
    {
        $h = strtolower($this->hari ?? 'semua');
        return match ($h) {
            'senin'  => 'Senin',
            'selasa' => 'Selasa',
            'rabu'   => 'Rabu',
            'kamis'  => 'Kamis',
            'jumat'  => 'Jumat',
            'sabtu'  => 'Sabtu',
            'minggu' => 'Minggu',
            default  => 'Semua Hari (Senin - Minggu)',
        };
    }

    /**
     * Label Kelas
     */
    public function getKelasLabelAttribute(): string
    {
        $k = strtolower($this->kelas ?? 'semua');
        return match ($k) {
            '1'     => 'Kelas 1',
            '2'     => 'Kelas 2',
            '3'     => 'Kelas 3',
            default => 'Semua Kelas (1, 2 & 3)',
        };
    }

    /**
     * Dapatkan Kegiatan yang sedang berlangsung saat ini & kegiatan berikutnya
     * Berdasarkan Hari ini, Jam saat ini, dan Kelas Santri.
     */
    public static function getCurrentAndNextActivity(?string $kelas = null, ?Carbon $now = null): array
    {
        $now = $now ?? Carbon::now();
        $currentTime = $now->format('H:i:s');
        $todayDate = $now->toDateString();
        
        // Nama hari hari ini dalam huruf kecil indonesia (senin, selasa, ...)
        $dayNames = [
            0 => 'minggu',
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ];
        $currentDay = $dayNames[$now->dayOfWeek] ?? 'senin';

        // Ambil semua kegiatan kandidat untuk hari ini & kelas ini
        $kegiatans = self::query()
            ->where(function ($q) use ($kelas) {
                $q->where('kelas', 'semua');
                if (!empty($kelas)) {
                    $q->orWhere('kelas', (string) $kelas);
                }
            })
            ->where(function ($q) use ($todayDate, $currentDay) {
                // Khusus hari ini
                $q->where('tanggal_khusus', $todayDate)
                  // Atau rutin yang cocok harinya
                  ->orWhere(function ($sub) use ($currentDay) {
                      $sub->where('is_rutin_24jam', true)
                          ->where(function ($d) use ($currentDay) {
                              $d->where('hari', 'semua')
                                ->orWhere('hari', $currentDay);
                          });
                  });
            })
            ->orderBy('waktu_mulai')
            ->get();

        $currentActivity = null;
        $nextActivity = null;

        foreach ($kegiatans as $k) {
            $mulai = $k->waktu_mulai;
            $selesai = $k->waktu_selesai;

            $isOngoing = false;
            if ($mulai <= $selesai) {
                // Rentang normal misal 08:00 s/d 10:00
                $isOngoing = ($currentTime >= $mulai && $currentTime <= $selesai);
            } else {
                // Rentang melewati tengah malam misal 22:00 s/d 04:00
                $isOngoing = ($currentTime >= $mulai || $currentTime <= $selesai);
            }

            if ($isOngoing) {
                // Jika sudah ada tapi yang ini lebih spesifik (hari tertentu vs semua hari, atau kelas tertentu vs semua)
                if (!$currentActivity) {
                    $currentActivity = $k;
                } else {
                    // Prioritaskan yang spesifik hari atau kelas
                    $currScore = ($currentActivity->hari !== 'semua' ? 2 : 0) + ($currentActivity->kelas !== 'semua' ? 1 : 0);
                    $newScore  = ($k->hari !== 'semua' ? 2 : 0) + ($k->kelas !== 'semua' ? 1 : 0);
                    if ($newScore > $currScore) {
                        $currentActivity = $k;
                    }
                }
            } elseif ($mulai > $currentTime && !$nextActivity) {
                $nextActivity = $k;
            }
        }

        // Jika next belum ketemu (mungkin next adalah kegiatan pertama besok pagi atau setelah midnight)
        if (!$nextActivity && $kegiatans->isNotEmpty()) {
            $nextActivity = $kegiatans->first(fn($item) => $item->waktu_mulai > $currentTime);
            if (!$nextActivity && $currentActivity) {
                // Ambil kegiatan pertama di hari berikutnya
                $nextActivity = $kegiatans->first();
            }
        }

        return [
            'current'    => $currentActivity,
            'next'       => $nextActivity,
            'currentDay' => ucfirst($currentDay),
            'currentTime'=> $now->format('H:i'),
        ];
    }
}
