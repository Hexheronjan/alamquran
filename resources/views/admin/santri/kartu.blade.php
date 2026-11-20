<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if(!$isMassal && $santri)
            Kartu Santri - {{ $santri->nama_lengkap }}
        @else
            Cetak Kartu Santri Massal ({{ $santris->count() }} Santri)
        @endif
    </title>
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body, html {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .page-break {
                page-break-after: always;
                break-after: page;
            }
            .print-card-box {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            @page {
                size: A4 portrait;
                margin: 0.8cm 0.5cm;
            }
        }

        .id-card-frame {
            width: 8.6cm;
            min-height: 12.8cm;
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin: 0 auto;
        }

        @media print {
            .id-card-frame {
                box-shadow: none !important;
                border: 1px dashed #9ca3af !important;
                margin: 0.3cm auto !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800 antialiased">

    <!-- Top Action Toolbar (Hidden during print) -->
    <header class="no-print sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-gray-200 shadow-sm px-6 py-3.5">
        <div class="max-w-6xl mx-auto flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.santri.index') }}"
                   class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition flex items-center gap-1.5">
                    <i class="ti ti-arrow-left"></i> Kembali ke Data Santri
                </a>
                <div class="h-5 w-px bg-gray-300"></div>
                <h1 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <i class="ti ti-id-badge text-green-600 text-lg"></i>
                    @if(!$isMassal && $santri)
                        Kartu Santri: <span class="text-teal-700">{{ $santri->nama_lengkap }}</span>
                    @else
                        Cetak Kartu Santri Massal (Total: {{ $santris->count() }} Santri)
                    @endif
                </h1>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="window.print()"
                        class="px-5 py-2 rounded-lg text-white text-xs font-bold shadow transition flex items-center gap-1.5 cursor-pointer hover:opacity-90"
                        style="background-color: #16a34a !important;">
                    <i class="ti ti-printer text-base"></i> Cetak Sekarang (Ctrl+P)
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="py-8 px-4">
        @if($santris->isEmpty())
            <div class="max-w-md mx-auto bg-white rounded-2xl p-8 text-center shadow-sm border border-gray-200">
                <i class="ti ti-user-x text-5xl text-gray-300 mb-3 block"></i>
                <h2 class="text-base font-bold text-gray-800">Tidak Ada Data Santri</h2>
                <p class="text-xs text-gray-500 mt-1">Tidak ditemukan santri aktif yang sesuai dengan kriteria filter.</p>
                <a href="{{ route('admin.santri.index') }}" class="mt-4 inline-block px-4 py-2 bg-teal-600 text-white rounded-lg text-xs font-bold">Kembali</a>
            </div>
        @elseif(!$isMassal)
            {{-- SINGLE CARD VIEW (CENTERED) --}}
            <div class="max-w-md mx-auto print-card-box">
                @php $s = $santri; @endphp
                <div class="id-card-frame">
                    {{-- Card Header - Green Gradient --}}
                    <div class="relative px-5 pt-5 pb-14 text-center text-white"
                        style="background: linear-gradient(135deg, #15803d 0%, #16a34a 50%, #22c55e 100%);">
                        <div class="flex items-center justify-center gap-1.5 mb-1">
                            <i class="ti ti-building text-base opacity-85"></i>
                            <p class="text-[10px] font-semibold uppercase tracking-widest opacity-90">Pesantren Tahfizh</p>
                        </div>
                        <h2 class="text-base font-bold leading-tight">Pesantren Tahfizh Alam Qur'an</h2>
                        <p class="text-[11px] opacity-80 mt-0.5">Kartu Santri Digital</p>
                    </div>

                    {{-- Photo - overlapping header --}}
                    <div class="flex justify-center -mt-10 mb-2 relative z-10">
                        <img
                            src="{{ $s->foto_url ?? asset('assets/images/profile/user-1.jpg') }}"
                            alt="{{ $s->nama_lengkap ?? 'Santri' }}"
                            class="w-20 h-20 rounded-full border-4 border-white object-cover shadow bg-gray-100"
                            onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($s->nama_lengkap ?? 'Santri') }}&background=16a34a&color=fff&size=80'"
                        >
                    </div>

                    {{-- Card Body --}}
                    <div class="px-5 pb-5 text-center flex-1 flex flex-col justify-between space-y-3">
                        {{-- Name & Status --}}
                        <div>
                            <h3 class="text-base font-bold text-gray-900 leading-snug line-clamp-1">{{ $s->nama_lengkap ?? '-' }}</h3>
                            <div class="mt-1 flex justify-center">
                                @if(strtolower($s->status_santri ?? $s->status ?? '') === 'aktif' || strtolower($s->status ?? '') === 'diterima')
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-green-200">
                                        <i class="ti ti-circle-check text-xs"></i> Santri Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-[10px] font-semibold px-2.5 py-0.5 rounded-full border border-gray-200">
                                        <i class="ti ti-circle-x text-xs"></i> {{ ucfirst($s->status_santri ?? $s->status ?? 'Santri') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Detail Info Table --}}
                        <div class="bg-gray-50 rounded-xl p-3 text-left space-y-1.5 border border-gray-100 text-xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 text-green-600 text-center shrink-0"><i class="ti ti-id-badge-2 text-sm"></i></div>
                                <div class="flex-1 min-w-0 flex justify-between items-center">
                                    <span class="text-[11px] text-gray-400">NIS</span>
                                    <span class="text-xs font-bold text-gray-800 font-mono">{{ $s->nis ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 text-green-600 text-center shrink-0"><i class="ti ti-hash text-sm"></i></div>
                                <div class="flex-1 min-w-0 flex justify-between items-center">
                                    <span class="text-[11px] text-gray-400">NISN</span>
                                    <span class="text-xs font-bold text-gray-800 font-mono">{{ $s->nisn ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 text-green-600 text-center shrink-0"><i class="ti ti-school text-sm"></i></div>
                                <div class="flex-1 min-w-0 flex justify-between items-center">
                                    <span class="text-[11px] text-gray-400">Jenjang</span>
                                    <span class="text-xs font-bold text-gray-800">{{ $s->kelas_label ?? $s->kelas ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 text-green-600 text-center shrink-0"><i class="ti ti-calendar text-sm"></i></div>
                                <div class="flex-1 min-w-0 flex justify-between items-center">
                                    <span class="text-[11px] text-gray-400">Tahun Ajaran</span>
                                    <span class="text-xs font-bold text-gray-800">
                                        {{ $s->tahun_ajaran ?? ($s->angkatan?->nama_angkatan ?? ($s->tahun_masuk ? 'Tahun ' . $s->tahun_masuk : '-')) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- QR Code & Footer --}}
                        <div class="flex flex-col items-center gap-1 pt-1">
                            <img
                                src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($s->nis ?? (string)$s->id) }}"
                                onerror="this.src='https://quickchart.io/qr?size=140&text={{ urlencode($s->nis ?? (string)$s->id) }}'"
                                alt="QR Santri"
                                class="rounded-lg border border-gray-200 bg-white p-1"
                                width="100"
                                height="100"
                            >
                            <p class="font-mono text-[10px] text-gray-500 font-semibold">{{ $s->nis ?? $s->id }}</p>
                            <p class="text-[9px] text-gray-400 uppercase tracking-widest mt-0.5">Scan untuk Verifikasi Resmi</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- BULK CARD VIEW (A4 GRID 2 COLUMNS) --}}
            <div class="max-w-5xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 print:grid print:grid-cols-2 print:gap-4 print:gap-y-6">
                    @foreach($santris as $index => $s)
                        <div class="print-card-box flex justify-center {{ ($index > 0 && $index % 4 === 0) ? 'page-break' : '' }}">
                            <div class="id-card-frame">
                                {{-- Card Header - Green Gradient --}}
                                <div class="relative px-5 pt-5 pb-14 text-center text-white"
                                    style="background: linear-gradient(135deg, #15803d 0%, #16a34a 50%, #22c55e 100%);">
                                    <div class="flex items-center justify-center gap-1.5 mb-1">
                                        <i class="ti ti-building text-base opacity-85"></i>
                                        <p class="text-[10px] font-semibold uppercase tracking-widest opacity-90">Pesantren Tahfizh</p>
                                    </div>
                                    <h2 class="text-base font-bold leading-tight">Pesantren Tahfizh Alam Qur'an</h2>
                                    <p class="text-[11px] opacity-80 mt-0.5">Kartu Santri Digital</p>
                                </div>

                                {{-- Photo - overlapping header --}}
                                <div class="flex justify-center -mt-10 mb-2 relative z-10">
                                    <img
                                        src="{{ $s->foto_url ?? asset('assets/images/profile/user-1.jpg') }}"
                                        alt="{{ $s->nama_lengkap ?? 'Santri' }}"
                                        class="w-20 h-20 rounded-full border-4 border-white object-cover shadow bg-gray-100"
                                        onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($s->nama_lengkap ?? 'Santri') }}&background=16a34a&color=fff&size=80'"
                                    >
                                </div>

                                {{-- Card Body --}}
                                <div class="px-5 pb-5 text-center flex-1 flex flex-col justify-between space-y-3">
                                    {{-- Name & Status --}}
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900 leading-snug line-clamp-1">{{ $s->nama_lengkap ?? '-' }}</h3>
                                        <div class="mt-1 flex justify-center">
                                            @if(strtolower($s->status_santri ?? $s->status ?? '') === 'aktif' || strtolower($s->status ?? '') === 'diterima')
                                                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-green-200">
                                                    <i class="ti ti-circle-check text-xs"></i> Santri Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-[10px] font-semibold px-2.5 py-0.5 rounded-full border border-gray-200">
                                                    <i class="ti ti-circle-x text-xs"></i> {{ ucfirst($s->status_santri ?? $s->status ?? 'Santri') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Detail Info Table --}}
                                    <div class="bg-gray-50 rounded-xl p-3 text-left space-y-1.5 border border-gray-100 text-xs">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-6 text-green-600 text-center shrink-0"><i class="ti ti-id-badge-2 text-sm"></i></div>
                                            <div class="flex-1 min-w-0 flex justify-between items-center">
                                                <span class="text-[11px] text-gray-400">NIS</span>
                                                <span class="text-xs font-bold text-gray-800 font-mono">{{ $s->nis ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-6 text-green-600 text-center shrink-0"><i class="ti ti-hash text-sm"></i></div>
                                            <div class="flex-1 min-w-0 flex justify-between items-center">
                                                <span class="text-[11px] text-gray-400">NISN</span>
                                                <span class="text-xs font-bold text-gray-800 font-mono">{{ $s->nisn ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-6 text-green-600 text-center shrink-0"><i class="ti ti-school text-sm"></i></div>
                                            <div class="flex-1 min-w-0 flex justify-between items-center">
                                                <span class="text-[11px] text-gray-400">Jenjang</span>
                                                <span class="text-xs font-bold text-gray-800">{{ $s->kelas_label ?? $s->kelas ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-6 text-green-600 text-center shrink-0"><i class="ti ti-calendar text-sm"></i></div>
                                            <div class="flex-1 min-w-0 flex justify-between items-center">
                                                <span class="text-[11px] text-gray-400">Tahun Ajaran</span>
                                                <span class="text-xs font-bold text-gray-800">
                                                    {{ $s->tahun_ajaran ?? ($s->angkatan?->nama_angkatan ?? ($s->tahun_masuk ? 'Tahun ' . $s->tahun_masuk : '-')) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- QR Code & Footer --}}
                                    <div class="flex flex-col items-center gap-1 pt-1">
                                        <img
                                            src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($s->nis ?? (string)$s->id) }}"
                                            onerror="this.src='https://quickchart.io/qr?size=140&text={{ urlencode($s->nis ?? (string)$s->id) }}'"
                                            alt="QR Santri"
                                            class="rounded-lg border border-gray-200 bg-white p-1"
                                            width="100"
                                            height="100"
                                        >
                                        <p class="font-mono text-[10px] text-gray-500 font-semibold">{{ $s->nis ?? $s->id }}</p>
                                        <p class="text-[9px] text-gray-400 uppercase tracking-widest mt-0.5">Scan untuk Verifikasi Resmi</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </main>

</body>
</html>
