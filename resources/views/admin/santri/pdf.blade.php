<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Santri - Pesantren Tahfizh Alam Qur'an</title>
    <style>
        @page {
            margin: 20px 25px 25px 25px;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            color: #1f2937;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #0f766e;
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            color: #374151;
            margin: 3px 0 0 0;
            font-size: 13px;
            font-weight: 600;
        }
        .meta-table {
            width: 100%;
            margin-top: 8px;
            font-size: 10px;
            border-collapse: collapse;
        }
        .meta-table td {
            border: none;
            padding: 2px 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, 
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            vertical-align: middle;
        }
        table.data-table th {
            background-color: #0f766e;
            color: #ffffff;
            font-weight: bold;
            font-size: 9.5px;
            text-align: center;
            text-transform: uppercase;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .font-mono {
            font-family: monospace;
            font-size: 9.5px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: bold;
            text-align: center;
        }
        .status-menunggu_verifikasi {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-diverifikasi {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-diterima {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-ditolak {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            margin-top: 15px;
            width: 100%;
            font-size: 9px;
            color: #64748b;
        }
        .footer table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer td {
            border: none;
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pesantren Tahfizh Alam Qur'an</h1>
        <h2>Laporan Data Santri</h2>
        <table class="meta-table">
            <tr>
                <td style="text-align: left; width: 33%;"><strong>Tahun Masuk / Ajaran:</strong> {{ $tahunAjaran }}</td>
                <td style="text-align: center; width: 34%;"><strong>Total Santri:</strong> {{ $santri->count() }} orang</td>
                <td style="text-align: right; width: 33%;"><strong>Dicetak pada:</strong> {{ date('d/m/Y H:i') }} WIB</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 130px; text-align: left;">Nama Santri</th>
                <th style="width: 55px;">Kelas</th>
                <th style="width: 90px; text-align: left;">NIS / NISN</th>
                <th style="width: 100px; text-align: left;">NIK</th>
                <th style="width: 130px; text-align: left;">Sekolah Asal</th>
                <th style="width: 110px; text-align: left;">Nama Ayah</th>
                <th style="width: 90px; text-align: left;">No. WhatsApp</th>
                <th style="width: 75px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($santri as $index => $s)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $s->nama_lengkap }}</strong></td>
                <td class="text-center">{{ $s->kelas_label ?? ($s->kelas ? 'Kelas ' . $s->kelas : '-') }}</td>
                <td class="font-mono">
                    <div>NIS: {{ $s->nis ?: '-' }}</div>
                    <div>NISN: {{ $s->nisn ?: '-' }}</div>
                </td>
                <td class="font-mono">{{ $s->nik ?: '-' }}</td>
                <td>{{ $s->sekolah_asal ?: '-' }}</td>
                <td>{{ $s->orangTua->nama_ayah ?? '-' }}</td>
                <td>{{ $s->orangTua->no_wa_ayah ?? ($s->user->no_wa ?? '-') }}</td>
                <td class="text-center">
                    <span class="status-badge status-{{ $s->status }}">
                        @switch($s->status)
                            @case('menunggu_verifikasi')
                                Menunggu Verif
                                @break
                            @case('diverifikasi')
                                Diverifikasi
                                @break
                            @case('diterima')
                                Diterima
                                @break
                            @case('ditolak')
                                Ditolak
                                @break
                            @default
                                {{ ucfirst(str_replace('_', ' ', $s->status ?? '-')) }}
                        @endswitch
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px; color: #64748b;">
                    Tidak ada data santri ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table>
            <tr>
                <td style="text-align: left;">Sistem Informasi Manajemen Santri & PPDB · Pesantren Tahfizh Alam Qur'an</td>
                <td style="text-align: right;">Total Data: {{ $santri->count() }} santri</td>
            </tr>
        </table>
    </div>
</body>
</html>
