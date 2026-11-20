<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Pendaftaran - {{ $santri->nama_lengkap }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            color: #1f2937;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16pt;
            margin: 0;
            color: #0d9488;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 13pt;
            margin: 4px 0 0 0;
            color: #374151;
            font-weight: normal;
        }
        .header p {
            font-size: 9pt;
            color: #6b7280;
            margin: 4px 0 0 0;
        }
        .section-title {
            background-color: #f0fdf4;
            color: #065f46;
            font-size: 11pt;
            font-weight: bold;
            padding: 6px 10px;
            border-left: 4px solid #0d9488;
            margin-top: 15px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.data-table td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 10pt;
        }
        table.data-table td.label {
            width: 32%;
            color: #4b5563;
            font-weight: bold;
        }
        table.data-table td.colon {
            width: 3%;
            color: #4b5563;
        }
        table.data-table td.value {
            width: 65%;
            color: #111827;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9pt;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-menunggu { background-color: #fef3c7; color: #92400e; }
        .badge-diverifikasi { background-color: #dbeafe; color: #1e40af; }
        .badge-diterima { background-color: #d1fae5; color: #065f46; }
        .badge-ditolak { background-color: #fee2e2; color: #991b1b; }
        
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 200px;
            text-align: center;
            font-size: 10pt;
        }
        .signature-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Pesantren Tahfizh Alam Qur'an</h1>
        <h2>Formulir Pendaftaran Santri Baru</h2>
        <p>Tanggal Cetak: {{ date('d F Y') }}</p>
    </div>

    <!-- STATUS PENDAFTARAN -->
    <table class="data-table" style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 15px;">
        <tr>
            <td class="label">ID Pendaftaran</td>
            <td class="colon">:</td>
            <td class="value"><strong>#{{ str_pad($santri->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
        </tr>
        <tr>
            <td class="label">Tanggal Pendaftaran</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->created_at->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="label">Status Pendaftaran</td>
            <td class="colon">:</td>
            <td class="value">
                @switch($santri->status)
                    @case('diverifikasi')
                        <span class="badge badge-diverifikasi">Diverifikasi</span>
                        @break
                    @case('diterima')
                        <span class="badge badge-diterima">Diterima</span>
                        @break
                    @case('ditolak')
                        <span class="badge badge-ditolak">Ditolak</span>
                        @break
                    @default
                        <span class="badge badge-menunggu">Menunggu Verifikasi</span>
                @endswitch
            </td>
        </tr>
    </table>

    <!-- DATA SANTRI -->
    <div class="section-title">I. Data Calon Santri</div>
    <table class="data-table">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td class="colon">:</td>
            <td class="value"><strong>{{ $santri->nama_lengkap }}</strong></td>
        </tr>
        <tr>
            <td class="label">Tempat, Tanggal Lahir</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->tempat_lahir }}, {{ $santri->tanggal_lahir ? $santri->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIK Santri</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->nik }}</td>
        </tr>
        <tr>
            <td class="label">NISN</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->nisn }}</td>
        </tr>
        <tr>
            <td class="label">Sekolah Asal</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->sekolah_asal }}</td>
        </tr>
        <tr>
            <td class="label">NPSN / NSM</td>
            <td class="colon">:</td>
            <td class="value">NPSN: {{ $santri->npsn }} | NSM: {{ $santri->nsm ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Anak Ke-</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->anak_ke }} dari {{ $santri->dari_bersaudara }} bersaudara</td>
        </tr>
        <tr>
            <td class="label">Hobi</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->hobi ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Ekstrakurikuler Pilihan</td>
            <td class="colon">:</td>
            <td class="value">
                {{ is_array($santri->ekstrakurikuler) ? implode(', ', $santri->ekstrakurikuler) : ($santri->ekstrakurikuler ?? '-') }}
            </td>
        </tr>
        <tr>
            <td class="label">Prestasi</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->prestasi ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Riwayat Penyakit / Alergi</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->riwayat_penyakit ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tujuan Mendaftar</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->tujuan_mendaftar }}</td>
        </tr>
    </table>

    <!-- DATA AYAH -->
    @if($santri->orangTua)
    <div class="section-title">II. Data Ayah Kandung</div>
    <table class="data-table">
        <tr>
            <td class="label">Nama Ayah</td>
            <td class="colon">:</td>
            <td class="value"><strong>{{ $santri->orangTua->nama_ayah }}</strong></td>
        </tr>
        <tr>
            <td class="label">No. WhatsApp Ayah</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->orangTua->no_wa_ayah }}</td>
        </tr>
        <tr>
            <td class="label">No. Kartu Keluarga (KK)</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->orangTua->no_kk }}</td>
        </tr>
        <tr>
            <td class="label">NIK Ayah</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->orangTua->nik_ayah }}</td>
        </tr>
        <tr>
            <td class="label">Tempat, Tanggal Lahir</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->orangTua->tempat_lahir_ayah }}, {{ $santri->orangTua->tanggal_lahir_ayah ? $santri->orangTua->tanggal_lahir_ayah->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pekerjaan Ayah</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->orangTua->pekerjaan_ayah }}</td>
        </tr>
        <tr>
            <td class="label">Penghasilan Ayah</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->orangTua->penghasilan_ayah }}</td>
        </tr>
        <tr>
            <td class="label">Alamat Domisili Ayah</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->orangTua->alamat_domisili_ayah }}</td>
        </tr>
    </table>

    <!-- DATA IBU -->
    <div class="section-title">III. Data Ibu Kandung</div>
    <table class="data-table">
        <tr>
            <td class="label">Nama Ibu</td>
            <td class="colon">:</td>
            <td class="value"><strong>{{ $santri->orangTua->nama_ibu }}</strong></td>
        </tr>
        <tr>
            <td class="label">NIK Ibu</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->orangTua->nik_ibu }}</td>
        </tr>
        <tr>
            <td class="label">Tempat, Tanggal Lahir</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->orangTua->tempat_lahir_ibu }}, {{ $santri->orangTua->tanggal_lahir_ibu ? $santri->orangTua->tanggal_lahir_ibu->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pekerjaan Ibu</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->orangTua->pekerjaan_ibu }}</td>
        </tr>
        <tr>
            <td class="label">Penghasilan Ibu</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->orangTua->penghasilan_ibu }}</td>
        </tr>
        <tr>
            <td class="label">Alamat Domisili Ibu</td>
            <td class="colon">:</td>
            <td class="value">{{ $santri->orangTua->alamat_domisili_ibu }}</td>
        </tr>
    </table>
    @endif

    <div class="footer">
        <div class="signature-box">
            <p>Panitia PPDB,<br>Pesantren Tahfizh Alam Qur'an</p>
            <div class="signature-space"></div>
            <p>_______________________</p>
        </div>
    </div>

</body>
</html>
