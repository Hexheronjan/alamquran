<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Uang Saku - {{ $santri->nama_lengkap }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #1f2937;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 15pt;
            margin: 0;
            color: #0d9488;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 12pt;
            margin: 3px 0 0 0;
            color: #374151;
            font-weight: normal;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .info-table td {
            padding: 6px 10px;
            font-size: 9.5pt;
        }
        .info-table td.label {
            width: 25%;
            font-weight: bold;
            color: #4b5563;
        }
        .trans-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .trans-table th {
            background: #0d9488;
            color: #ffffff;
            font-size: 9pt;
            padding: 7px 8px;
            text-align: left;
            text-transform: uppercase;
        }
        .trans-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9pt;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-masuk { color: #065f46; font-weight: bold; }
        .badge-keluar { color: #991b1b; font-weight: bold; }
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 200px;
            text-align: center;
            font-size: 9.5pt;
        }
        .signature-space {
            height: 50px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Pesantren Tahfizh Alam Qur'an</h1>
        <h2>Laporan Mutasi & Perincian Belanja Uang Saku Santri</h2>
        <p style="font-size: 8.5pt; color: #6b7280; margin: 3px 0 0 0;">Tanggal Cetak: {{ date('d F Y') }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Nama Santri</td>
            <td>: <strong>{{ $santri->nama_lengkap }}</strong></td>
            <td class="label">Saldo Terkini</td>
            <td>: <strong style="color: {{ $santri->saldo_uang_saku < 0 ? '#dc2626' : '#0d9488' }}; font-size: 11pt;">{{ $santri->saldo_uang_saku < 0 ? '-Rp ' . number_format(abs($santri->saldo_uang_saku), 0, ',', '.') . ' (Talangan Pondok)' : 'Rp ' . number_format($santri->saldo_uang_saku, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="label">NIK / NISN</td>
            <td>: {{ $santri->nik }} / {{ $santri->nisn }}</td>
            <td class="label">Nama Ayah</td>
            <td>: {{ $santri->orangTua->nama_ayah ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Sekolah Asal</td>
            <td>: {{ $santri->sekolah_asal }}</td>
            <td class="label">No. WA Ayah</td>
            <td>: {{ $santri->orangTua->no_wa_ayah ?? '-' }}</td>
        </tr>
    </table>

    <table class="trans-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 16%;">Tanggal & Waktu</th>
                <th style="width: 9%;" class="text-center">Jenis</th>
                <th style="width: 16%;" class="text-right">Mutasi (Rp)</th>
                <th>Catatan / Perincian Belanja</th>
                <th style="width: 16%;" class="text-right">Saldo Akhir</th>
                <th style="width: 14%;">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $index => $t)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $t->created_at->translatedFormat('d M Y, H:i') }}</td>
                <td class="text-center">
                    @if($t->jenis === 'masuk')
                        <span class="badge-masuk">Masuk</span>
                    @else
                        <span class="badge-keluar">Keluar</span>
                    @endif
                </td>
                <td class="text-right" style="font-weight: bold;">
                    @if($t->jenis === 'masuk')
                        <span style="color: #059669;">+ {{ number_format($t->jumlah, 0, ',', '.') }}</span>
                    @else
                        <span style="color: #dc2626;">- {{ number_format($t->jumlah, 0, ',', '.') }}</span>
                    @endif
                </td>
                <td>{{ $t->keterangan }}</td>
                <td class="text-right" style="font-weight: bold; color: {{ $t->saldo_akhir < 0 ? '#dc2626' : '#0d9488' }};">
                    {{ $t->saldo_akhir < 0 ? '-Rp ' . number_format(abs($t->saldo_akhir), 0, ',', '.') : 'Rp ' . number_format($t->saldo_akhir, 0, ',', '.') }}
                </td>
                <td style="font-size: 8.5pt; color: #4b5563;">
                    {{ $t->admin->name ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px; color: #9ca3af;">
                    Belum ada data transaksi uang saku.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <p>Pengelola Uang Saku,<br>Pesantren Tahfizh Alam Qur'an</p>
            <div class="signature-space"></div>
            <p>_______________________</p>
        </div>
    </div>

</body>
</html>
