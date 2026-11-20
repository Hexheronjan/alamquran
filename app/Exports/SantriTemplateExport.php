<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Illuminate\Support\Collection;

/**
 * Template Excel untuk import data santri aktif.
 * Memberikan contoh data dan instruksi kolom.
 */
class SantriTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Template Import Santri';
    }

    public function collection(): Collection
    {
        // Contoh data
        return collect([
            [
                'Ahmad Fauzan Al-Hakim',
                'AQ-2026-001',
                '0071234567',
                'Surabaya',
                '2012-05-14',
                '1',
                '2026',
                '2026/2027',
                'Haji Syarifuddin',
                'Fatimah Azzahra',
                'Haji Syarifuddin',
                '081234567890',
                'Jl. Pesantren No. 12, Surabaya',
                'aktif',
            ],
            [
                'Muhammad Ridwan Habibi',
                'AQ-2026-002',
                '0079876543',
                'Malang',
                '2012-08-20',
                '1',
                '2026',
                '2026/2027',
                'Bambang Susanto',
                'Siti Aminah',
                'Bambang Susanto',
                '085712345678',
                'Jl. Merdeka No. 45, Malang',
                'aktif',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'nama_santri',       // Nama lengkap santri (WAJIB)
            'nis',               // Nomor Induk Santri (opsional, harus unik)
            'nisn',              // NISN 10 digit (opsional, harus unik)
            'tempat_lahir',      // Tempat lahir
            'tanggal_lahir',     // Format: YYYY-MM-DD (contoh: 2012-05-14)
            'kelas',             // 1 / 2 / 3
            'tahun_angkatan',    // Tahun masuk (contoh: 2026)
            'tahun_ajaran',      // Tahun ajaran (contoh: 2026/2027)
            'nama_ayah',         // Nama ayah / wali laki-laki
            'nama_ibu',          // Nama ibu / wali perempuan
            'nama_wali',         // Nama wali (jika berbeda dengan ayah)
            'no_wa_wali',        // No. WhatsApp wali (format: 08xxx / 628xxx)
            'alamat',            // Alamat lengkap
            'status_santri',     // aktif (biarkan kosong, otomatis aktif)
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0D9488'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
            2 => [
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF0FDF4'],
                ],
            ],
            3 => [
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF0FDF4'],
                ],
            ],
        ];
    }
}
