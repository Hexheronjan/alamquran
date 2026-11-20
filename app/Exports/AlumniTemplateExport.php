<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;

class AlumniTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Template Import Alumni';
    }

    public function collection(): Collection
    {
        return collect([
            [
                'Ahmad Fauzan Al-Hakim',
                'AQ-2023-001',
                '0071234567',
                '2021',
                '2024',
                'Haji Syarifuddin',
                '081234567890',
                'Surabaya',
                '2008-05-14',
                'Jl. Pesantren No. 12, Surabaya',
            ],
            [
                'Muhammad Ridwan Habibi',
                'AQ-2023-002',
                '0079876543',
                '2021',
                '2024',
                'Bambang Susanto',
                '085712345678',
                'Malang',
                '2008-08-20',
                'Jl. Merdeka No. 45, Malang',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'nama_lengkap',   // Nama lengkap alumni (WAJIB)
            'nis',            // Nomor Induk Santri (opsional, unik)
            'nisn',           // NISN 10 digit (opsional, unik)
            'tahun_masuk',    // Tahun masuk pesantren (4 digit, contoh: 2021)
            'tahun_lulus',    // Tahun lulus (WAJIB, 4 digit, contoh: 2024)
            'nama_ayah',      // Nama ayah / wali
            'no_wa_ayah',     // No. WhatsApp wali (format: 08xxx atau 628xxx)
            'tempat_lahir',   // Tempat lahir (opsional)
            'tanggal_lahir',  // Format: YYYY-MM-DD (contoh: 2008-05-14)
            'alamat',         // Alamat lengkap (opsional)
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF7C3AED'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
            2 => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFAF5FF']],
            ],
            3 => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFAF5FF']],
            ],
        ];
    }
}
