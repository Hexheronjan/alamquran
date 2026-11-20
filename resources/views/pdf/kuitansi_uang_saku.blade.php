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
    <title>Kuitansi Titipan Uang Saku - {{ $transaksi->santri->nama_lengkap }} ({{ $transaksi->nomor_invoice }})</title>
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

        .kuitansi-title-bar {
            margin-bottom: 14px;
            width: 100%;
            border-collapse: collapse;
        }
        .kuitansi-title-bar td {
            vertical-align: middle;
            padding: 4px 0;
        }
        .kuitansi-title {
            font-size: 12pt;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kuitansi-subtitle {
            font-size: 8.5pt;
            color: #6b7280;
            margin-top: 2px;
        }
        .kuitansi-number {
            text-align: right;
            font-size: 9pt;
            color: #4b5563;
            line-height: 1.4;
        }

        /* Stamp Badge Status */
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

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }
        .content-table td {
            padding: 7px 10px;
            vertical-align: top;
            font-size: 9pt;
            border-bottom: 1px solid #f3f4f6;
        }
        .content-table tr:last-child td {
            border-bottom: none;
        }
        .content-table td.label {
            width: 25%;
            font-weight: bold;
            color: #4b5563;
        }
        .content-table td.colon {
            width: 2%;
            text-align: center;
            color: #9ca3af;
        }

        .terbilang-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 9px 12px;
            border-radius: 6px;
            margin: 12px 0;
            font-size: 9pt;
            color: #166534;
            line-height: 1.4;
        }

        .saldo-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 9pt;
            margin-bottom: 20px;
        }

        .footer {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
        }
        .nominal-badge {
            vertical-align: bottom;
            width: 50%;
        }
        .nominal-amount {
            background: #0d9488;
            color: #ffffff;
            font-size: 13pt;
            font-weight: bold;
            padding: 8px 18px;
            border-radius: 6px;
            display: inline-block;
        }
        .signature-box {
            text-align: center;
            width: 50%;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .signature-space {
            height: 50px;
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
            .kuitansi-title-bar td,
            .invoice-title-bar td {
                display: block !important;
                width: 100% !important;
                text-align: left !important;
                padding: 2px 0 !important;
            }
            .kuitansi-number,
            .invoice-number {
                text-align: left !important;
                margin-top: 4px !important;
                padding-top: 4px !important;
                border-top: 1px dashed #e5e7eb !important;
                font-size: 8pt !important;
                line-height: 1.35 !important;
            }
            .kuitansi-title,
            .invoice-title {
                font-size: 10pt !important;
            }
            .kuitansi-subtitle,
            .invoice-subtitle {
                font-size: 7.5pt !important;
            }
            .content-table td.label,
            .info-table td.label {
                width: 32% !important;
                font-size: 8pt !important;
            }
            .content-table td,
            .info-table td {
                font-size: 8pt !important;
                padding: 5px 6px !important;
            }
            .terbilang-box {
                padding: 6px 8px !important;
                font-size: 8pt !important;
                margin: 8px 0 !important;
            }
            .saldo-box {
                padding: 8px 8px !important;
                margin-bottom: 12px !important;
            }
            .saldo-box td {
                display: block !important;
                width: 100% !important;
                text-align: left !important;
                padding: 3px 0 !important;
                border-bottom: 1px dashed #e5e7eb !important;
                font-size: 8pt !important;
            }
            .saldo-box td:last-child {
                border-bottom: none !important;
            }
            .footer {
                margin-top: 16px !important;
                width: 100% !important;
            }
            .footer td.nominal-badge {
                display: table-cell !important;
                width: 52% !important;
                text-align: left !important;
                vertical-align: bottom !important;
                padding-right: 6px !important;
            }
            .footer td.signature-box {
                display: table-cell !important;
                width: 48% !important;
                text-align: center !important;
                vertical-align: top !important;
                font-size: 7.5pt !important;
                padding-left: 6px !important;
            }
            .nominal-amount {
                font-size: 10.5pt !important;
                padding: 6px 12px !important;
            }
            .stamp-badge {
                font-size: 8pt !important;
                padding: 2px 8px !important;
                letter-spacing: 1px !important;
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
                <span class="bar-title">Review Kuitansi Uang Saku</span>
                <span class="bar-badge" style="background: #e0f2fe; color: #0369a1; border-radius: 4px;">
                    {{ $transaksi->nomor_invoice }}
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

        <!-- TITLE BAR & NO KUITANSI -->
        <table class="kuitansi-title-bar">
            <tr>
                <td style="width: 55%;">
                    <div class="kuitansi-title">Tanda Bukti Titipan Uang Saku</div>
                    <div class="kuitansi-subtitle">Bukti setor uang saku & tabungan harian santri</div>
                </td>
                <td style="width: 45%;" class="kuitansi-number">
                    <strong>No. Kuitansi:</strong> <span style="font-family: monospace; font-weight: bold; color: #0d9488;">{{ $transaksi->nomor_invoice }}</span><br>
                    <strong>Waktu Transaksi:</strong> {{ $transaksi->created_at->translatedFormat('d F Y, H:i') }} WIB
                </td>
            </tr>
        </table>

        <!-- DATA TRANSAKSI -->
        <table class="content-table">
            <tr>
                <td class="label">Telah Diterima Dari</td>
                <td class="colon">:</td>
                <td><strong>{{ $transaksi->santri->orangTua->nama_ayah ?? 'Wali Santri' }}</strong> (Wali Santri)</td>
            </tr>
            <tr>
                <td class="label">Nama Santri</td>
                <td class="colon">:</td>
                <td><strong>{{ $transaksi->santri->nama_lengkap }}</strong></td>
            </tr>
            <tr>
                <td class="label">Kelas & Tahun Ajaran</td>
                <td class="colon">:</td>
                <td>{{ $transaksi->kelas ?: $transaksi->santri->kelas_label }} (TA {{ $transaksi->tahun_ajaran ?: ($transaksi->santri->tahun_ajaran ?? '-') }})</td>
            </tr>
            <tr>
                <td class="label">Untuk Pembayaran</td>
                <td class="colon">:</td>
                <td>Titipan Uang Saku & Belanja Santri</td>
            </tr>
            <tr>
                <td class="label">Keterangan / Catatan</td>
                <td class="colon">:</td>
                <td>{{ $transaksi->keterangan }}</td>
            </tr>
        </table>

        <!-- TERBILANG -->
        <div class="terbilang-box">
            <strong>Terbilang:</strong> <em># {{ \App\Services\InvoiceService::terbilang($transaksi->jumlah) }} Rupiah #</em>
        </div>

        <!-- SALDO -->
        <div class="saldo-box">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 33%;">Nominal Titipan:<br><strong style="color: #059669; font-size: 10.5pt;">+ Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</strong></td>
                    <td style="width: 33%;">Saldo Akhir Terkini:<br><strong style="color: {{ $transaksi->saldo_akhir < 0 ? '#dc2626' : '#0d9488' }}; font-size: 10.5pt;">
                        {{ $transaksi->saldo_akhir < 0 ? '-Rp ' . number_format(abs($transaksi->saldo_akhir), 0, ',', '.') : 'Rp ' . number_format($transaksi->saldo_akhir, 0, ',', '.') }}
                    </strong></td>
                    <td style="width: 34%; text-align: right;">Status Titipan:<br><strong style="color: #059669; font-size: 9.5pt;">[ LUNAS ] Masuk Saldo</strong></td>
                </tr>
            </table>
        </div>

        <!-- FOOTER & TANDA TANGAN -->
        <table class="footer">
            <tr>
                <td class="nominal-badge">
                    <div style="margin-bottom: 8px;">
                        <div class="stamp-badge stamp-lunas">L U N A S</div>
                    </div>
                    <div class="nominal-amount">
                        Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}
                    </div>
                    @if($setting->catatan_bawah)
                        <div style="font-size: 8pt; color: #6b7280; font-style: italic; margin-top: 6px;">
                            * {{ $setting->catatan_bawah }}
                        </div>
                    @endif
                </td>
                <td class="signature-box">
                    <p style="margin: 0;">Ponorogo, {{ $transaksi->created_at->translatedFormat('d F Y') }}<br>{{ $setting->jabatan_penandatangan ?: 'Pengelola Uang Saku' }},</p>
                    <div class="signature-space"></div>
                    @php
                        $namaPenerima = $setting->nama_bendahara ?: ($transaksi->admin->name ?? 'Pengelola Uang Saku');
                    @endphp
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $namaPenerima }}</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
