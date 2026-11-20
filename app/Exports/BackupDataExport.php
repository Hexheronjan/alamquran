<?php

namespace App\Exports;

use App\Models\AbsensiSantri;
use App\Models\DaftarUlangTagihan;
use App\Models\InfaqTransaksi;
use App\Models\Santri;
use App\Models\SantriKesehatan;
use App\Models\SppTagihan;
use App\Models\TahfidzSetoran;
use App\Models\UangSaku;
use App\Models\UangSakuManual;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BackupDataExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new SantriBackupSheet(),
            new SppBackupSheet(),
            new DaftarUlangBackupSheet(),
            new UangSakuBackupSheet(),
            new TahfidzBackupSheet(),
            new KesehatanBackupSheet(),
            new AbsensiBackupSheet(),
            new InfaqBackupSheet(),
        ];
    }
}

// -------------------------------------------------------------
// 1. SHEET SANTRI
// -------------------------------------------------------------
class SantriBackupSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'Data Santri';
    }

    public function headings(): array
    {
        return [
            'ID', 'NIS', 'NISN', 'NIK', 'Nama Lengkap', 'Panggilan', 'Gender',
            'Kelas', 'Angkatan', 'Tgl Lahir', 'Tempat Lahir', 'Nama Wali/Ayah',
            'No WA Ayah', 'Nama Ibu', 'No WA Ibu', 'Alamat', 'Status Santri'
        ];
    }

    public function collection()
    {
        return Santri::with(['orangTua', 'angkatan'])->orderBy('id')->get()->map(function ($s) {
            return [
                $s->id,
                $s->nis ? "'" . $s->nis : '-',
                $s->nisn ? "'" . $s->nisn : '-',
                $s->nik ? "'" . $s->nik : '-',
                $s->nama_lengkap,
                $s->nama_panggilan ?? '-',
                $s->jenis_kelamin === 'L' ? 'Laki-laki' : ($s->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                $s->kelas_label ?? ('Kelas ' . $s->kelas),
                $s->angkatan?->nama_angkatan ?? '-',
                $s->tanggal_lahir ? date('d/m/Y', strtotime($s->tanggal_lahir)) : '-',
                $s->tempat_lahir ?? '-',
                $s->orangTua?->nama_ayah ?? '-',
                $s->orangTua?->no_wa_ayah ? "'" . $s->orangTua->no_wa_ayah : '-',
                $s->orangTua?->nama_ibu ?? '-',
                $s->orangTua?->no_wa_ibu ? "'" . $s->orangTua->no_wa_ibu : '-',
                $s->alamat ?? '-',
                ucfirst($s->status_santri ?? 'aktif'),
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:Q1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:Q1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');
        $sheet->getStyle('A1:Q1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}

// -------------------------------------------------------------
// 2. SHEET SPP
// -------------------------------------------------------------
class SppBackupSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'SPP & Bulanan';
    }

    public function headings(): array
    {
        return [
            'ID', 'Tahun', 'Bulan', 'Nama Santri', 'Kelas', 'Nomor SPP/VA',
            'Nominal Tagihan', 'Nominal Terbayar', 'Sisa Tagihan', 'Status',
            'Tgl Lunas/Bayar', 'Catatan'
        ];
    }

    public function collection()
    {
        return SppTagihan::with('santri')->latest('id')->get()->map(function ($t) {
            return [
                $t->id,
                $t->tahun,
                $t->bulan,
                $t->santri?->nama_lengkap ?? '-',
                $t->santri?->kelas ?? '-',
                $t->nomor_va ? "'" . $t->nomor_va : '-',
                (float) $t->nominal_tagihan,
                (float) $t->nominal_terbayar,
                (float) $t->sisa_tagihan,
                ucfirst(str_replace('_', ' ', $t->status)),
                $t->tanggal_lunas ? date('d/m/Y', strtotime($t->tanggal_lunas)) : '-',
                $t->keterangan ?? '-',
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:L1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('047857');
        $sheet->getStyle('A1:L1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}

// -------------------------------------------------------------
// 3. SHEET DAFTAR ULANG / BIAYA MASUK
// -------------------------------------------------------------
class DaftarUlangBackupSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'Biaya Masuk (Daftar Ulang)';
    }

    public function headings(): array
    {
        return [
            'ID', 'Tahun Angkatan', 'Gelombang', 'Nama Santri', 'Nominal Biaya',
            'Nominal Terbayar', 'Sisa Tagihan', 'Status', 'Beasiswa', 'Nomor VA', 'Keterangan'
        ];
    }

    public function collection()
    {
        return DaftarUlangTagihan::with('santri')->latest('id')->get()->map(function ($d) {
            return [
                $d->id,
                $d->tahun_angkatan,
                $d->gelombang ?? '-',
                $d->santri?->nama_lengkap ?? '-',
                (float) $d->nominal_biaya,
                (float) $d->nominal_terbayar,
                (float) $d->sisa_tagihan,
                ucfirst(str_replace('_', ' ', $d->status)),
                $d->is_beasiswa ? 'YA (Beasiswa)' : 'TIDAK',
                $d->nomor_va ? "'" . $d->nomor_va : '-',
                $d->keterangan ?? '-',
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:K1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0369A1');
        $sheet->getStyle('A1:K1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}

// -------------------------------------------------------------
// 4. SHEET UANG SAKU
// -------------------------------------------------------------
class UangSakuBackupSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'Uang Saku Santri';
    }

    public function headings(): array
    {
        return [
            'ID', 'Nama Santri', 'Kelas', 'Saldo Saat Ini', 'Limit Harian', 'Status Terkunci', 'Terakhir Update'
        ];
    }

    public function collection()
    {
        return UangSaku::with('santri')->get()->map(function ($u) {
            return [
                $u->id,
                $u->santri?->nama_lengkap ?? '-',
                $u->santri?->kelas ?? '-',
                (float) ($u->saldo ?? 0),
                (float) ($u->daily_limit ?? 0),
                $u->is_locked ? 'Terkunci' : 'Aktif',
                $u->updated_at ? date('d/m/Y H:i', strtotime($u->updated_at)) : '-',
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B45309');
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}

// -------------------------------------------------------------
// 5. SHEET TAHFIDZ
// -------------------------------------------------------------
class TahfidzBackupSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'Setoran Tahfidz';
    }

    public function headings(): array
    {
        return [
            'ID', 'Tanggal', 'Nama Santri', 'Kelas', 'Surat', 'Ayat', 'Juz',
            'Jenis Setoran', 'Kualitas / Hasil', 'Catatan Ustadz', 'Ustadz Penguji'
        ];
    }

    public function collection()
    {
        return TahfidzSetoran::with(['santri', 'ustadz'])->latest('tanggal')->get()->map(function ($t) {
            $ayatRentang = ($t->ayat_awal && $t->ayat_akhir) ? ($t->ayat_awal . ' - ' . $t->ayat_akhir) : ($t->ayat_awal ?: '-');
            return [
                $t->id,
                $t->tanggal ? date('d/m/Y', strtotime($t->tanggal)) : '-',
                $t->santri?->nama_lengkap ?? '-',
                $t->santri?->kelas ?? '-',
                $t->nama_surah ?? '-',
                $ayatRentang,
                $t->juz ? ('Juz ' . $t->juz) : '-',
                ucfirst($t->jenis_setoran ?? 'ziyadah'),
                ucfirst($t->kelancaran ?? 'lancar'),
                $t->catatan_ustadz ?? '-',
                $t->ustadz?->name ?? '-',
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:K1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('065F46');
        $sheet->getStyle('A1:K1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}

// -------------------------------------------------------------
// 6. SHEET KESEHATAN & UKS
// -------------------------------------------------------------
class KesehatanBackupSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'Kesehatan & UKS';
    }

    public function headings(): array
    {
        return [
            'ID', 'Tanggal Periksa', 'Nama Santri', 'Kelas', 'Keluhan Sakit',
            'Tindakan Perawatan', 'Obat Diberikan', 'Status Kesehatan', 'Lama Sakit (Hari)',
            'Waktu Jemput', 'Nama Penjemput', 'Estimasi Kembali', 'Catatan Penjemputan', 'Petugas UKS'
        ];
    }

    public function collection()
    {
        return SantriKesehatan::with(['santri', 'petugas'])->latest('tanggal')->get()->map(function ($k) {
            return [
                $k->id,
                $k->tanggal ? date('d/m/Y', strtotime($k->tanggal)) : '-',
                $k->santri?->nama_lengkap ?? '-',
                $k->santri?->kelas ?? '-',
                $k->keluhan_sakit,
                $k->tindakan_perawatan ?? '-',
                $k->obat_diberikan ?? '-',
                ucfirst(str_replace('_', ' ', $k->status_kesehatan)),
                $k->lama_sakit,
                $k->tanggal_jemput ? date('d/m/Y H:i', strtotime($k->tanggal_jemput)) : '-',
                $k->nama_penjemput ?? '-',
                $k->estimasi_kembali ? date('d/m/Y', strtotime($k->estimasi_kembali)) : '-',
                $k->catatan_penjemputan ?? '-',
                $k->petugas?->name ?? '-',
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:N1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:N1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('BE123C');
        $sheet->getStyle('A1:N1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}

// -------------------------------------------------------------
// 7. SHEET ABSENSI
// -------------------------------------------------------------
class AbsensiBackupSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'Absensi Santri';
    }

    public function headings(): array
    {
        return [
            'ID', 'Tanggal', 'Nama Santri', 'Kelas', 'Status Kehadiran', 'Catatan Keterangan', 'Petugas'
        ];
    }

    public function collection()
    {
        return AbsensiSantri::with(['santri', 'guru'])->latest('tanggal')->limit(5000)->get()->map(function ($a) {
            return [
                $a->id,
                $a->tanggal ? date('d/m/Y', strtotime($a->tanggal)) : '-',
                $a->santri?->nama_lengkap ?? '-',
                $a->kelas ?? '-',
                ucfirst($a->status_kehadiran),
                $a->catatan ?? '-',
                $a->guru?->name ?? '-',
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4338CA');
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}

// -------------------------------------------------------------
// 8. SHEET INFAQ
// -------------------------------------------------------------
class InfaqBackupSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'Infaq & Donasi';
    }

    public function headings(): array
    {
        return [
            'ID', 'Tanggal Transaksi', 'Nama Donatur', 'No WA Donatur', 'Program Infaq', 'Nominal', 'Metode Pembayaran', 'Status', 'Doa / Harapan'
        ];
    }

    public function collection()
    {
        return InfaqTransaksi::with('program')->latest('created_at')->get()->map(function ($i) {
            return [
                $i->id,
                $i->created_at ? $i->created_at->format('d/m/Y H:i') : '-',
                $i->nama_donatur ?? '-',
                $i->no_wa_donatur ? "'" . $i->no_wa_donatur : '-',
                $i->program?->nama_program ?? '-',
                (float) $i->nominal,
                ucfirst($i->metode_pembayaran ?? 'transfer'),
                ucfirst($i->status ?? 'menunggu_verifikasi'),
                $i->doa_harapan ?? '-',
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:I1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('6D28D9');
        $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}
