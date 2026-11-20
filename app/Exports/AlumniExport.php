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

class AlumniExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithCustomValueBinder, WithColumnFormatting
{
    protected ?string $tahunLulus = null;
    protected ?string $angkatanId = null;
    protected ?string $search = null;

    public function __construct($tahunLulus = null, ?string $angkatanId = null, ?string $search = null)
    {
        if ($tahunLulus instanceof \Illuminate\Http\Request) {
            $this->tahunLulus = $tahunLulus->input('tahun_lulus') ? (string) $tahunLulus->input('tahun_lulus') : null;
            $this->angkatanId = $tahunLulus->input('angkatan_id') ? (string) $tahunLulus->input('angkatan_id') : null;
            $this->search     = $tahunLulus->input('search') ? (string) $tahunLulus->input('search') : null;
        } else {
            $this->tahunLulus = $tahunLulus ? (string) $tahunLulus : null;
            $this->angkatanId = $angkatanId ? (string) $angkatanId : null;
            $this->search     = $search ? (string) $search : null;
        }
    }

    public function bindValue(Cell $cell, $value)
    {
        $column = $cell->getColumn();
        if (in_array($column, ['C', 'D', 'K']) && $value !== null && $value !== '-') {
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
            'K' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function title(): string
    {
        return 'Data Alumni';
    }

    public function query()
    {
        $query = Santri::with(['orangTua', 'angkatan'])
            ->where(function ($q) {
                $q->where('kelas', 'alumni')->orWhere('status_santri', 'lulus');
            });

        if ($this->tahunLulus) {
            $query->where('tahun_lulus', $this->tahunLulus);
        }

        if ($this->angkatanId) {
            $query->where('angkatan_id', $this->angkatanId);
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhereHas('orangTua', function ($oq) use ($search) {
                      $oq->where('nama_ayah', 'like', "%{$search}%")
                        ->orWhere('no_wa_ayah', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderByDesc('tahun_lulus')->orderBy('nama_lengkap');
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'NIS',
            'NISN',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Tahun Masuk',
            'Tahun Lulus',
            'Angkatan',
            'Nama Ayah/Wali',
            'No. WhatsApp Wali',
            'Alamat',
        ];
    }

    public function map($alumni): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $alumni->nama_lengkap,
            $alumni->nis ?? '-',
            $alumni->nisn ?? '-',
            $alumni->tempat_lahir ?? '-',
            $alumni->tanggal_lahir ? $alumni->tanggal_lahir->format('d/m/Y') : '-',
            $alumni->tahun_masuk ?? '-',
            $alumni->tahun_lulus ?? '-',
            $alumni->angkatan?->nama_angkatan ?? '-',
            $alumni->orangTua?->nama_ayah ?? '-',
            $alumni->orangTua?->no_wa_ayah ?? '-',
            $alumni->orangTua?->alamat_domisili_ayah ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF7C3AED'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
