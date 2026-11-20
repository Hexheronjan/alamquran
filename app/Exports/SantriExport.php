<?php

namespace App\Exports;

use App\Models\Santri;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class SantriExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithCustomValueBinder, WithColumnFormatting
{
    protected ?string $kelas;
    protected ?string $angkatanId;
    protected ?string $tahunAjaran;
    protected ?string $tahunMasuk;
    protected ?string $statusSantri;
    protected ?int $santriId;

    public function __construct(
        ?string $kelas = null,
        ?string $angkatanId = null,
        ?string $tahunAjaran = null,
        ?string $statusSantri = 'aktif',
        ?string $tahunMasuk = null,
        ?int $santriId = null
    ) {
        $this->kelas        = $kelas;
        $this->angkatanId   = $angkatanId;
        $this->tahunAjaran  = $tahunAjaran;
        $this->statusSantri = $statusSantri;
        $this->tahunMasuk   = $tahunMasuk;
        $this->santriId     = $santriId;
    }

    public function bindValue(Cell $cell, $value)
    {
        // Paksa kolom NIS (C), NISN (D), NIK (E), dan No WA (O) menjadi STRING murni agar tidak terpotong / menjadi 3.50216E+15 di Excel
        $column = $cell->getColumn();
        if (in_array($column, ['C', 'D', 'E', 'O']) && $value !== null && $value !== '-') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'O' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function title(): string
    {
        return 'Data Santri';
    }

    public function query()
    {
        $query = Santri::with(['orangTua', 'angkatan']);

        if ($this->santriId) {
            return $query->where('id', $this->santriId);
        }

        $query->where('status', 'diterima');

        // Filter status santri
        if ($this->statusSantri && $this->statusSantri !== 'semua') {
            $query->where('status_santri', $this->statusSantri);
        }

        // Filter kelas
        if ($this->kelas) {
            $query->where('kelas', $this->kelas);
        } else {
            // Default: hanya santri aktif (bukan alumni)
            if (!$this->statusSantri || $this->statusSantri === 'aktif') {
                $query->whereIn('kelas', ['1', '2', '3']);
            }
        }

        // Filter angkatan
        if ($this->angkatanId) {
            $query->where('angkatan_id', $this->angkatanId);
        }

        // Filter tahun ajaran
        if ($this->tahunAjaran) {
            $query->where('tahun_ajaran', $this->tahunAjaran);
        }

        if ($this->tahunMasuk === '__belum_ditentukan__') {
            $query->whereNull('tahun_masuk');
        } elseif ($this->tahunMasuk) {
            $query->where('tahun_masuk', $this->tahunMasuk);
        }

        return $query->orderBy('kelas')->orderBy('nama_lengkap');
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Santri',
            'NIS',
            'NISN',
            'NIK',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Kelas',
            'Status Santri',
            'Tahun Masuk',
            'Tahun Ajaran',
            'Angkatan',
            'Nama Ayah/Wali',
            'Nama Ibu',
            'No. WhatsApp Wali',
            'Alamat',
            'Sekolah Asal',
        ];
    }

    private static int $rowIndex = 1;

    public function map($santri): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $santri->nama_lengkap,
            $santri->nis ? (string) $santri->nis : '-',
            $santri->nisn ? (string) $santri->nisn : '-',
            $santri->nik ? (string) $santri->nik : '-',
            $santri->tempat_lahir ?? '-',
            $santri->tanggal_lahir ? $santri->tanggal_lahir->format('d/m/Y') : '-',
            $santri->kelas_label,
            ucfirst($santri->status_santri ?? '-'),
            $santri->tahun_masuk ? (string) $santri->tahun_masuk : '-',
            $santri->tahun_ajaran ?? '-',
            $santri->angkatan?->nama_angkatan ?? '-',
            $santri->orangTua?->nama_ayah ?? '-',
            $santri->orangTua?->nama_ibu ?? '-',
            $santri->orangTua?->no_wa_ayah ? (string) $santri->orangTua->no_wa_ayah : '-',
            $santri->orangTua?->alamat_domisili_ayah ?? '-',
            $santri->sekolah_asal ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Pastikan kolom NIS, NISN, NIK, dan No WA berformat Text (@) di seluruh baris
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('E')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('O')->getNumberFormat()->setFormatCode('@');

        return [
            // Header row styling
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0D9488'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
