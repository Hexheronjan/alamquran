@php
    $setting = $setting ?? \App\Models\InvoiceSetting::getSetting();
    $isPdf = isset($isPdf) ? (bool)$isPdf : (request('download') === 'pdf');
    
    // Resolve logo path safely for DomPDF (local absolute path) vs Browser (HTTP URL)
    $logoSrc = null;
    if (!empty($setting->logo)) {
        $storagePath = storage_path('app/public/' . $setting->logo);
        if (file_exists($storagePath)) {
            $logoSrc = $isPdf ? $storagePath : asset('storage/' . $setting->logo);
        }
    }
    if (!$logoSrc) {
        $defaultPath = public_path('assets/images/logos/icon-logo-ppdb.png');
        if (file_exists($defaultPath)) {
            $logoSrc = $isPdf ? $defaultPath : asset('assets/images/logos/icon-logo-ppdb.png');
        }
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Daftar Ulang - {{ $tagihan->santri->nama_lengkap }} (TA {{ $tagihan->tahun_angkatan }})</title>
    <style>
        * {
            box-sizing: border-box;
        }
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5pt;
            color: #1f2937;
            line-height: 1.45;
            margin: 0;
            padding: 0;
            @if($isPdf)
                background: #ffffff;
            @else
                background-color: #f3f4f6;
            @endif
        }
        .page-container {
            width: 100%;
            @if($isPdf)
                max-width: 100%;
                margin: 0;
                padding: 0;
                background: #ffffff;
            @else
                max-width: 820px;
                margin: 25px auto;
                background: #ffffff;
                padding: 35px 40px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                border-radius: 8px;
                border: 1px solid #e5e7eb;
            @endif
        }
        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #0d9488;
            padding-bottom: 10px;
            margin-bottom: 16px;
            border-collapse: collapse;
        }
        .header-logo {
            width: 70px;
            vertical-align: middle;
            text-align: left;
            padding-right: 14px;
        }
        .header-logo img {
            max-height: 65px;
            max-width: 65px;
            object-fit: contain;
        }
        .header-text {
            vertical-align: middle;
            text-align: left;
        }
        .header-text h1 {
            font-size: 14pt;
            margin: 0;
            color: #0d9488;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .header-text h2 {
            font-size: 9.5pt;
            margin: 2px 0 0 0;
            color: #374151;
            font-weight: 600;
        }
        .header-text p {
            font-size: 8pt;
            color: #6b7280;
            margin: 3px 0 0 0;
            line-height: 1.35;
        }

        /* Invoice Title Bar - Stamp diposisikan di sini, TIDAK menutupi Kop */
        .invoice-title-bar {
            margin-bottom: 14px;
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-title-bar td {
            vertical-align: middle;
            padding: 4px 0;
        }
        .invoice-title {
            font-size: 12pt;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .invoice-subtitle {
            font-size: 8.5pt;
            color: #6b7280;
            margin-top: 2px;
        }
        .invoice-number {
            text-align: right;
            font-size: 9pt;
            color: #4b5563;
            line-height: 1.4;
        }

        /* Stamp Badge yang elegan dan bersih */
        .stamp-badge {
            display: inline-block;
            border: 2px solid;
            padding: 4px 12px;
            font-size: 9.5pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-radius: 6px;
            text-align: center;
        }
        .stamp-lunas {
            color: #059669;
            border-color: #059669;
            background: #ecfdf5;
        }
        .stamp-cicilan {
            color: #d97706;
            border-color: #d97706;
            background: #fffbeb;
        }
        .stamp-belum {
            color: #dc2626;
            border-color: #dc2626;
            background: #fef2f2;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }
        .info-table td {
            padding: 6px 10px;
            font-size: 9pt;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-table tr:last-child td {
            border-bottom: none;
        }
        .info-table td.label {
            width: 20%;
            font-weight: bold;
            color: #4b5563;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }
        .table-data th {
            background: #0d9488;
            color: #ffffff;
            font-size: 8.5pt;
            padding: 7px 10px;
            text-align: left;
            text-transform: uppercase;
        }
        .table-data td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8.5pt;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .summary-box {
            width: 100%;
            margin-top: 10px;
            overflow: hidden;
        }
        .summary-table {
            width: 48%;
            float: right;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 4px 8px;
            font-size: 9pt;
        }
        .summary-table tr.total td {
            border-top: 1.5px solid #0d9488;
            font-weight: bold;
            font-size: 9.5pt;
            padding-top: 6px;
        }
        .terbilang-box {
            width: 48%;
            float: left;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 8.5pt;
            color: #166534;
            line-height: 1.4;
        }
        .footer-section {
            margin-top: 25px;
            width: 100%;
            clear: both;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-note {
            font-size: 8pt;
            color: #6b7280;
            font-style: italic;
            vertical-align: bottom;
            padding-right: 20px;
        }
        .signature-box {
            width: 220px;
            text-align: center;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .signature-space {
            height: 48px;
        }

        /* Top Action Bar untuk Review di Browser */
        .top-action-bar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .top-bar-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            white-space: nowrap;
        }
        .btn-print {
            background-color: #0d9488;
            color: #ffffff;
        }
        .btn-print:hover {
            background-color: #0f766e;
        }
        .btn-download {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-download:hover {
            background-color: #1d4ed8;
        }
        .btn-close {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        .btn-close:hover {
            background-color: #e5e7eb;
        }

        /* Responsif Khusus Layar HP / Mobile */
        @media screen and (max-width: 640px) {
            body {
                background-color: #f3f4f6 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .top-action-bar {
                padding: 8px 10px !important;
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 6px !important;
            }
            .top-bar-info {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                width: 100% !important;
                gap: 6px !important;
            }
            .top-bar-info .bar-title {
                font-size: 11.5px !important;
                font-weight: bold !important;
                color: #111827 !important;
                white-space: nowrap !important;
            }
            .top-bar-info .bar-badge {
                font-size: 10.5px !important;
                padding: 2px 7px !important;
                white-space: nowrap !important;
                font-family: monospace !important;
                font-weight: bold !important;
            }
            .top-bar-actions {
                display: flex !important;
                align-items: center !important;
                gap: 6px !important;
                width: 100% !important;
            }
            .top-bar-actions .action-btn {
                padding: 7px 6px !important;
                font-size: 11px !important;
                white-space: nowrap !important;
            }
            .top-bar-actions .btn-download {
                flex: 1.2 !important;
            }
            .top-bar-actions .btn-print {
                flex: 1 !important;
            }
            .top-bar-actions .btn-close {
                flex: 0 0 auto !important;
                padding: 7px 10px !important;
            }
            .page-container {
                margin: 6px 4px 20px 4px !important;
                padding: 14px 10px !important;
                border-radius: 8px !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
                width: auto !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            .header-table {
                margin-bottom: 10px !important;
                padding-bottom: 8px !important;
            }
            .header-logo {
                width: 44px !important;
                padding-right: 6px !important;
            }
            .header-logo img {
                max-height: 42px !important;
                max-width: 42px !important;
            }
            .header-text h1 {
                font-size: 10pt !important;
                line-height: 1.2 !important;
                word-break: break-word !important;
            }
            .header-text h2 {
                font-size: 7.5pt !important;
                margin-top: 1px !important;
            }
            .header-text p {
                font-size: 6.8pt !important;
                line-height: 1.25 !important;
                margin-top: 2px !important;
            }
            .invoice-title-bar td {
                display: block !important;
                width: 100% !important;
                text-align: left !important;
                padding: 2px 0 !important;
            }
            .invoice-number {
                text-align: left !important;
                margin-top: 4px !important;
                padding-top: 4px !important;
                border-top: 1px dashed #e5e7eb !important;
                font-size: 8pt !important;
                line-height: 1.35 !important;
            }
            .invoice-title {
                font-size: 10pt !important;
            }
            .invoice-subtitle {
                font-size: 7.5pt !important;
            }
            .info-table td.label {
                width: 32% !important;
                font-size: 8pt !important;
            }
            .info-table td {
                font-size: 8pt !important;
                padding: 5px 6px !important;
            }
            .table-data th,
            .table-data td {
                padding: 5px 4px !important;
                font-size: 7.5pt !important;
            }
            .summary-box {
                margin-top: 8px !important;
                overflow: visible !important;
            }
            .summary-table {
                width: 100% !important;
                float: none !important;
                margin-bottom: 10px !important;
            }
            .summary-table td {
                padding: 3px 6px !important;
                font-size: 8pt !important;
            }
            .terbilang-box {
                width: 100% !important;
                float: none !important;
                padding: 6px 8px !important;
                font-size: 8pt !important;
                margin: 8px 0 !important;
                box-sizing: border-box !important;
            }
            .footer-section {
                margin-top: 16px !important;
                width: 100% !important;
            }
            .footer-section .footer-table td.footer-note {
                display: table-cell !important;
                width: 52% !important;
                text-align: left !important;
                vertical-align: bottom !important;
                padding-right: 6px !important;
                font-size: 7pt !important;
            }
            .footer-section .footer-table td.signature-box {
                display: table-cell !important;
                width: 48% !important;
                text-align: center !important;
                vertical-align: top !important;
                padding-left: 6px !important;
                font-size: 7.5pt !important;
            }
            .signature-space {
                height: 35px !important;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .page-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 10px 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>
<body>

    @if(!$isPdf)
        <!-- Review Action Bar (Hanya muncul saat dibuka di browser) -->
        <div class="top-action-bar no-print">
            <div class="top-bar-info">
                <span class="bar-title">Review Invoice Daftar Ulang</span>
                <span class="bar-badge" style="background: #e0f2fe; color: #0369a1; border-radius: 4px;">
                    {{ $tagihan->nomor_invoice }}
                </span>
            </div>
            <div class="top-bar-actions">
                <a href="?download=pdf" class="action-btn btn-download" title="Unduh file PDF resmi">
                    📥 Download PDF
                </a>
                <button type="button" onclick="window.print()" class="action-btn btn-print" title="Cetak langsung menggunakan printer">
                    🖨 Cetak
                </button>
                <button type="button" onclick="window.close()" class="action-btn btn-close" title="Tutup halaman review">
                    ✕ Tutup
                </button>
            </div>
        </div>
    @endif

    <div class="page-container">
        <!-- KOP SURAT (Dinamis dari Pengaturan Invoice & Tidak tertutupi stamp) -->
        <table class="header-table">
            <tr>
                @if($logoSrc)
                    <td class="header-logo">
                        <img src="{{ $logoSrc }}" alt="Logo">
                    </td>
                @endif
                <td class="header-text" style="{{ !$logoSrc ? 'text-align: center;' : '' }}">
                    <h1>{{ $setting->nama_lembaga }}</h1>
                    @if($setting->tagline_kop)
                        <h2>{{ $setting->tagline_kop }}</h2>
                    @endif
                    <p>
                        {{ $setting->alamat }}
                        @if($setting->telepon) · Telp/WA: {{ $setting->telepon }} @endif
                        @if($setting->email) · Email: {{ $setting->email }} @endif
                        @if($setting->website) · Web: {{ $setting->website }} @endif
                    </p>
                </td>
            </tr>
        </table>

        <!-- TITLE BAR & NOMOR INVOICE & STAMP (Di bawah Kop Surat) -->
        <table class="invoice-title-bar">
            <tr>
                <td style="width: 45%;">
                    <div class="invoice-title">Bukti Pembayaran Masuk & Daftar Ulang</div>
                    <div class="invoice-subtitle">Tanda bukti resmi pembayaran biaya santri baru / daftar ulang</div>
                </td>
                <td style="width: 25%; text-align: center;">
                    @if($tagihan->status === 'lunas')
                        <div class="stamp-badge stamp-lunas">L U N A S</div>
                    @elseif($tagihan->status === 'cicilan')
                        <div class="stamp-badge stamp-cicilan">A N G S U R A N</div>
                    @else
                        <div class="stamp-badge stamp-belum">BELUM LUNAS</div>
                    @endif
                </td>
                <td style="width: 30%;" class="invoice-number">
                    <strong>No. Invoice:</strong> <span style="font-family: monospace; font-weight: bold; color: #0d9488;">{{ $tagihan->nomor_invoice }}</span><br>
                    <strong>Tanggal Cetak:</strong> {{ date('d F Y') }}
                </td>
            </tr>
        </table>

        <!-- DATA SANTRI & PERIODE -->
        <table class="info-table">
            <tr>
                <td class="label">Nama Santri</td>
                <td>: <strong>{{ $tagihan->santri->nama_lengkap }}</strong></td>
                <td class="label">Tahun Masuk / TA</td>
                <td>: <strong>{{ $tagihan->tahun_angkatan }}</strong></td>
            </tr>
            <tr>
                <td class="label">Jenjang / Kelas</td>
                <td>: {{ $tagihan->santri->kelas_label }}</td>
                <td class="label">Nama Wali</td>
                <td>: {{ $tagihan->santri->orangTua->nama_ayah ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">NISN / NIK</td>
                <td>: {{ $tagihan->santri->nisn ?? '-' }} / {{ $tagihan->santri->nik ?? '-' }}</td>
                <td class="label">Status Pelunasan</td>
                <td>: 
                    @if($tagihan->status === 'lunas')
                        <span style="color: #059669; font-weight: bold;">Lunas Penuh</span>
                    @elseif($tagihan->status === 'cicilan')
                        <span style="color: #d97706; font-weight: bold;">Cicilan / Angsuran</span>
                    @else
                        <span style="color: #dc2626; font-weight: bold;">Belum Lunas</span>
                    @endif
                </td>
            </tr>
        </table>

        <!-- RINCIAN KOMPONEN BIAYA -->
        <p style="font-weight: bold; font-size: 9pt; margin-bottom: 4px; color: #374151;">1. Rincian Biaya Masuk & Daftar Ulang:</p>
        <table class="table-data">
            <thead>
                <tr>
                    <th style="width: 8%;" class="text-center">No</th>
                    <th>Deskripsi Komponen Biaya</th>
                    <th style="width: 25%;" class="text-right">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td>Biaya Masuk / Daftar Ulang Pokok (Tahun Masuk {{ $tagihan->tahun_angkatan }})</td>
                    <td class="text-right">Rp {{ number_format($tagihan->nominal_biaya, 0, ',', '.') }}</td>
                </tr>
                @foreach($tagihan->addons as $idx => $addon)
                <tr>
                    <td class="text-center">{{ $idx + 2 }}</td>
                    <td>{{ $addon->nama_addon }} <span style="font-size: 8pt; color: #6b7280;">(Biaya Tambahan / Add-on)</span></td>
                    <td class="text-right">Rp {{ number_format($addon->nominal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- RIWAYAT PEMBAYARAN MASUK -->
        <p style="font-weight: bold; font-size: 9pt; margin-bottom: 4px; color: #374151;">2. Riwayat Pembayaran Masuk (Diverifikasi):</p>
        <table class="table-data">
            <thead>
                <tr>
                    <th style="width: 8%;" class="text-center">No</th>
                    <th style="width: 26%;">Tanggal & Waktu</th>
                    <th style="width: 18%;">Metode</th>
                    <th style="width: 23%;">Petugas Penerima</th>
                    <th style="width: 25%;" class="text-right">Jumlah Masuk</th>
                </tr>
            </thead>
            <tbody>
                @php $pembayaranDiterima = $tagihan->pembayarans->where('status', 'diterima'); @endphp
                @forelse($pembayaranDiterima as $pIndex => $p)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $p->verified_at ? $p->verified_at->translatedFormat('d M Y, H:i') : $p->created_at->translatedFormat('d M Y, H:i') }}</td>
                    <td>{{ strtoupper($p->metode_bayar ?? 'Transfer') }}</td>
                    <td>{{ $p->verifiedBy->name ?? 'Bagian Keuangan' }}</td>
                    <td class="text-right" style="font-weight: bold; color: #059669;">
                        Rp {{ number_format($p->jumlah_bayar, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #9ca3af; padding: 10px;">
                        Belum ada riwayat pembayaran yang diverifikasi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- SUMMARY & TERBILANG -->
        <div class="summary-box">
            <div class="terbilang-box">
                <strong>Terbilang Pembayaran:</strong><br>
                @php
                    $terbilangNominal = $pembayaranDiterima->sum('jumlah_bayar');
                @endphp
                <em># {{ \App\Services\InvoiceService::terbilang($terbilangNominal) }} Rupiah #</em>
            </div>

            <table class="summary-table">
                <tr>
                    <td>Total Biaya Daftar Ulang</td>
                    <td class="text-right" style="font-weight: bold;">Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Sudah Dibayar</td>
                    <td class="text-right" style="font-weight: bold; color: #059669;">Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }}</td>
                </tr>
                <tr class="total">
                    <td>Sisa Tagihan</td>
                    <td class="text-right" style="color: {{ $tagihan->sisa_tagihan > 0 ? '#d97706' : '#059669' }};">
                        Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- FOOTER & TANDA TANGAN -->
        <div class="footer-section">
            <table class="footer-table">
                <tr>
                    <td class="footer-note">
                        @if($setting->catatan_bawah)
                            * {{ $setting->catatan_bawah }}
                        @else
                            * Simpan tanda bukti pembayaran ini sebagai bukti transaksi resmi.
                        @endif
                    </td>
                    <td class="signature-box">
                        <p style="margin: 0;">Ponorogo, {{ date('d F Y') }}<br>{{ $setting->jabatan_penandatangan ?: 'Bendahara Pesantren' }},</p>
                        <div class="signature-space"></div>
                        @php
                            $petugasTerakhir = $pembayaranDiterima->last()?->verifiedBy?->name;
                            $namaPenandatangan = $setting->nama_bendahara ?: ($petugasTerakhir ?: 'Bendahara Pesantren');
                        @endphp
                        <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $namaPenandatangan }}</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
