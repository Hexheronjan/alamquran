<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KalenderAkademik extends Model
{
    use HasFactory;

    protected $table = 'kalender_akademiks';

    protected $fillable = [
        'tahun_ajaran',
        'judul_kegiatan',
        'kategori',
        'warna',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'is_public',
        'admin_id',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'is_public'       => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public static function getKategoriList(): array
    {
        return [
            'libur'          => ['label' => 'Libur Semester / Libur Pondok', 'warna' => '#ef4444'],
            'libur_nasional' => ['label' => 'Hari Libur Nasional & Keagamaan', 'warna' => '#dc2626'],
            'peringatan'     => ['label' => 'Hari Peringatan Nasional & Penting', 'warna' => '#0284c7'],
            'ujian'          => ['label' => 'Ujian / Imtihan / Penilaian',  'warna' => '#f59e0b'],
            'kedatangan'     => ['label' => 'Kedatangan Santri / Masuk Asrama', 'warna' => '#0d9488'],
            'sambangan'      => ['label' => 'Kunjungan / Sambangan Wali',   'warna' => '#10b981'],
            'phbi'           => ['label' => 'PHBI & Acara Besar Islam',     'warna' => '#8b5cf6'],
            'kegiatan'       => ['label' => 'Kegiatan Rutin Pesantren',     'warna' => '#3b82f6'],
            'asrama'         => ['label' => 'Kegiatan Khusus Asrama',       'warna' => '#6366f1'],
            'lainnya'        => ['label' => 'Lain-lain',                    'warna' => '#64748b'],
        ];
    }

    public static function getMasterHariNasional(int $startYear): array
    {
        $nextYear = $startYear + 1;
        $items = [];

        // 1. Hari Libur & Peringatan Tetap (Semester Ganjil: Juli - Desember)
        $items[] = [
            'judul_kegiatan'  => 'Hari Anak Nasional',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$startYear}-07-23",
            'tanggal_selesai' => "{$startYear}-07-23",
            'keterangan'      => 'Peringatan Hari Anak Nasional.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Kemerdekaan Republik Indonesia (HUT RI)',
            'kategori'        => 'libur_nasional',
            'tanggal_mulai'   => "{$startYear}-08-17",
            'tanggal_selesai' => "{$startYear}-08-17",
            'keterangan'      => 'Hari Libur Nasional Peringatan Kemerdekaan Republik Indonesia.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Kesaktian Pancasila',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$startYear}-10-01",
            'tanggal_selesai' => "{$startYear}-10-01",
            'keterangan'      => 'Peringatan Hari Kesaktian Pancasila.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Tentara Nasional Indonesia (HUT TNI)',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$startYear}-10-05",
            'tanggal_selesai' => "{$startYear}-10-05",
            'keterangan'      => 'Peringatan Hari Tentara Nasional Indonesia.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Santri Nasional',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$startYear}-10-22",
            'tanggal_selesai' => "{$startYear}-10-22",
            'keterangan'      => 'Resolusi Jihad & Peringatan Hari Santri Nasional Republik Indonesia.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Sumpah Pemuda',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$startYear}-10-28",
            'tanggal_selesai' => "{$startYear}-10-28",
            'keterangan'      => 'Peringatan Sumpah Pemuda Indonesia.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Pahlawan Nasional',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$startYear}-11-10",
            'tanggal_selesai' => "{$startYear}-11-10",
            'keterangan'      => 'Peringatan Hari Pahlawan Nasional.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Ayah & Hari Kesehatan Nasional',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$startYear}-11-12",
            'tanggal_selesai' => "{$startYear}-11-12",
            'keterangan'      => 'Peringatan Hari Ayah Nasional dan Hari Kesehatan Nasional.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Guru Nasional (HUT PGRI)',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$startYear}-11-25",
            'tanggal_selesai' => "{$startYear}-11-25",
            'keterangan'      => 'Peringatan Hari Guru Nasional dan HUT PGRI.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Ibu Nasional',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$startYear}-12-22",
            'tanggal_selesai' => "{$startYear}-12-22",
            'keterangan'      => 'Peringatan Hari Ibu Nasional.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Raya Natal',
            'kategori'        => 'libur_nasional',
            'tanggal_mulai'   => "{$startYear}-12-25",
            'tanggal_selesai' => "{$startYear}-12-25",
            'keterangan'      => 'Hari Libur Nasional Hari Raya Natal.',
        ];

        // 2. Hari Libur & Peringatan Tetap (Semester Genap: Januari - Juni)
        $items[] = [
            'judul_kegiatan'  => 'Tahun Baru Masehi',
            'kategori'        => 'libur_nasional',
            'tanggal_mulai'   => "{$nextYear}-01-01",
            'tanggal_selesai' => "{$nextYear}-01-01",
            'keterangan'      => 'Hari Libur Nasional Tahun Baru Masehi.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Amal Bakti (HAB) Kementerian Agama',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$nextYear}-01-03",
            'tanggal_selesai' => "{$nextYear}-01-03",
            'keterangan'      => 'Peringatan Hari Amal Bakti Kementerian Agama RI.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Kartini',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$nextYear}-04-21",
            'tanggal_selesai' => "{$nextYear}-04-21",
            'keterangan'      => 'Peringatan Hari Kartini.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Buruh Internasional (May Day)',
            'kategori'        => 'libur_nasional',
            'tanggal_mulai'   => "{$nextYear}-05-01",
            'tanggal_selesai' => "{$nextYear}-05-01",
            'keterangan'      => 'Hari Libur Nasional Hari Buruh Internasional.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Pendidikan Nasional (Hardiknas)',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$nextYear}-05-02",
            'tanggal_selesai' => "{$nextYear}-05-02",
            'keterangan'      => 'Peringatan Hari Pendidikan Nasional.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Kebangkitan Nasional (Harkitnas)',
            'kategori'        => 'peringatan',
            'tanggal_mulai'   => "{$nextYear}-05-20",
            'tanggal_selesai' => "{$nextYear}-05-20",
            'keterangan'      => 'Peringatan Hari Kebangkitan Nasional.',
        ];
        $items[] = [
            'judul_kegiatan'  => 'Hari Lahir Pancasila',
            'kategori'        => 'libur_nasional',
            'tanggal_mulai'   => "{$nextYear}-06-01",
            'tanggal_selesai' => "{$nextYear}-06-01",
            'keterangan'      => 'Hari Libur Nasional Hari Lahir Pancasila.',
        ];

        // 3. Hari Libur Keagamaan & Penanggalan Hijriyah / Imlek / Saka
        $variableMap = [
            2024 => [
                ['judul' => 'Tahun Baru Islam (1 Muharram 1446 H)', 'kat' => 'libur_nasional', 'start' => '2024-07-07'],
                ['judul' => 'Maulid Nabi Muhammad SAW (12 Rabiul Awal 1446 H)', 'kat' => 'libur_nasional', 'start' => '2024-09-16'],
                ['judul' => 'Isra Mi\'raj Nabi Muhammad SAW 1446 H', 'kat' => 'libur_nasional', 'start' => '2025-01-27'],
                ['judul' => 'Tahun Baru Imlek 2576 Kongzili', 'kat' => 'libur_nasional', 'start' => '2025-01-29'],
                ['judul' => 'Hari Suci Nyepi (Tahun Baru Saka 1947)', 'kat' => 'libur_nasional', 'start' => '2025-03-29'],
                ['judul' => 'Hari Raya Idul Fitri 1446 H', 'kat' => 'libur_nasional', 'start' => '2025-03-31', 'end' => '2025-04-01'],
                ['judul' => 'Wafat Isa Almasih / Jumat Agung', 'kat' => 'libur_nasional', 'start' => '2025-04-18'],
                ['judul' => 'Hari Raya Waisak 2569 BE', 'kat' => 'libur_nasional', 'start' => '2025-05-12'],
                ['judul' => 'Kenaikan Isa Almasih', 'kat' => 'libur_nasional', 'start' => '2025-05-29'],
                ['judul' => 'Hari Raya Idul Adha (10 Dzulhijjah 1446 H)', 'kat' => 'libur_nasional', 'start' => '2025-06-06'],
            ],
            2025 => [
                ['judul' => 'Tahun Baru Islam (1 Muharram 1447 H)', 'kat' => 'libur_nasional', 'start' => '2025-06-27'],
                ['judul' => 'Maulid Nabi Muhammad SAW (12 Rabiul Awal 1447 H)', 'kat' => 'libur_nasional', 'start' => '2025-09-05'],
                ['judul' => 'Isra Mi\'raj Nabi Muhammad SAW 1447 H', 'kat' => 'libur_nasional', 'start' => '2026-01-16'],
                ['judul' => 'Tahun Baru Imlek 2577 Kongzili', 'kat' => 'libur_nasional', 'start' => '2026-02-17'],
                ['judul' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)', 'kat' => 'libur_nasional', 'start' => '2026-03-19'],
                ['judul' => 'Hari Raya Idul Fitri 1447 H', 'kat' => 'libur_nasional', 'start' => '2026-03-20', 'end' => '2026-03-21'],
                ['judul' => 'Wafat Isa Almasih / Paskah', 'kat' => 'libur_nasional', 'start' => '2026-04-03'],
                ['judul' => 'Kenaikan Isa Almasih', 'kat' => 'libur_nasional', 'start' => '2026-05-14'],
                ['judul' => 'Hari Raya Idul Adha (10 Dzulhijjah 1447 H)', 'kat' => 'libur_nasional', 'start' => '2026-05-27'],
                ['judul' => 'Hari Raya Waisak 2570 BE', 'kat' => 'libur_nasional', 'start' => '2026-05-31'],
            ],
            2026 => [
                ['judul' => 'Tahun Baru Islam (1 Muharram 1448 H)', 'kat' => 'libur_nasional', 'start' => '2026-06-16'],
                ['judul' => 'Maulid Nabi Muhammad SAW (12 Rabiul Awal 1448 H)', 'kat' => 'libur_nasional', 'start' => '2026-08-25'],
                ['judul' => 'Isra Mi\'raj Nabi Muhammad SAW 1448 H', 'kat' => 'libur_nasional', 'start' => '2027-02-05'],
                ['judul' => 'Tahun Baru Imlek 2578 Kongzili', 'kat' => 'libur_nasional', 'start' => '2027-02-06'],
                ['judul' => 'Hari Suci Nyepi (Tahun Baru Saka 1949)', 'kat' => 'libur_nasional', 'start' => '2027-03-09'],
                ['judul' => 'Hari Raya Idul Fitri 1448 H', 'kat' => 'libur_nasional', 'start' => '2027-03-10', 'end' => '2027-03-11'],
                ['judul' => 'Wafat Isa Almasih / Paskah', 'kat' => 'libur_nasional', 'start' => '2027-03-26'],
                ['judul' => 'Kenaikan Isa Almasih', 'kat' => 'libur_nasional', 'start' => '2027-05-06'],
                ['judul' => 'Hari Raya Idul Adha (10 Dzulhijjah 1448 H)', 'kat' => 'libur_nasional', 'start' => '2027-05-16'],
                ['judul' => 'Hari Raya Waisak 2571 BE', 'kat' => 'libur_nasional', 'start' => '2027-05-20'],
            ],
            2027 => [
                ['judul' => 'Tahun Baru Islam (1 Muharram 1449 H)', 'kat' => 'libur_nasional', 'start' => '2027-06-06'],
                ['judul' => 'Maulid Nabi Muhammad SAW (12 Rabiul Awal 1449 H)', 'kat' => 'libur_nasional', 'start' => '2027-08-14'],
                ['judul' => 'Isra Mi\'raj Nabi Muhammad SAW 1449 H', 'kat' => 'libur_nasional', 'start' => '2028-01-25'],
                ['judul' => 'Tahun Baru Imlek 2579 Kongzili', 'kat' => 'libur_nasional', 'start' => '2028-01-26'],
                ['judul' => 'Hari Raya Idul Fitri 1449 H', 'kat' => 'libur_nasional', 'start' => '2028-02-27', 'end' => '2028-02-28'],
                ['judul' => 'Hari Suci Nyepi (Tahun Baru Saka 1950)', 'kat' => 'libur_nasional', 'start' => '2028-03-26'],
                ['judul' => 'Wafat Isa Almasih / Paskah', 'kat' => 'libur_nasional', 'start' => '2028-04-14'],
                ['judul' => 'Hari Raya Idul Adha 1449 H', 'kat' => 'libur_nasional', 'start' => '2028-05-05'],
                ['judul' => 'Hari Raya Waisak 2572 BE', 'kat' => 'libur_nasional', 'start' => '2028-05-08'],
                ['judul' => 'Kenaikan Isa Almasih', 'kat' => 'libur_nasional', 'start' => '2028-05-25'],
            ],
        ];

        if (isset($variableMap[$startYear])) {
            foreach ($variableMap[$startYear] as $v) {
                $items[] = [
                    'judul_kegiatan'  => $v['judul'],
                    'kategori'        => $v['kat'],
                    'tanggal_mulai'   => $v['start'],
                    'tanggal_selesai' => $v['end'] ?? $v['start'],
                    'keterangan'      => 'Hari Besar / Libur Nasional Keagamaan.',
                ];
            }
        }

        return $items;
    }

    public function getKategoriLabel(): string
    {
        $list = self::getKategoriList();
        return $list[$this->kategori]['label'] ?? ucfirst($this->kategori);
    }
}
