<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <i class="ti ti-id-badge text-2xl text-green-700"></i>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kartu Santri Digital
            </h2>
        </div>
    </x-slot>

    <style>
        @media print {
            /* Sembunyikan semua elemen navigasi dan header halaman */
            header, nav, footer, .no-print, #navbar-user, .fixed, [class*="fixed"] {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            body, html {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .min-h-screen, .py-6, main {
                padding: 0 !important;
                margin: 0 !important;
                min-height: auto !important;
                background: #fff !important;
            }

            .max-w-2xl, .space-y-5, .px-4, .sm\:px-6, .lg\:px-8 {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }

            .print-card {
                box-shadow: none !important;
                border: 1px solid #d1d5db !important;
                border-radius: 1rem !important;
                max-width: 9.5cm !important;
                width: 100% !important;
                margin: 2cm auto !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .print-card * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- Santri Selector Pills --}}
            @if($santris->isNotEmpty())
                <div class="no-print flex flex-wrap gap-2">
                    @foreach($santris as $s)
                        <a href="{{ route('user.kartu_santri', ['santri_id' => $s->id]) }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium border transition
                                {{ $selectedSantriId == $s->id
                                    ? 'text-white border-green-600'
                                    : 'bg-white text-gray-700 border-gray-300 hover:border-green-500 hover:text-green-700' }}"
                            @if($selectedSantriId == $s->id) style="background-color:#16a34a" @endif>
                            <i class="ti ti-user-circle text-base"></i>
                            {{ $s->nama_lengkap }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- ID Card --}}
            @if($activeSantri)
                <div class="print-card bg-white rounded-2xl shadow-md overflow-hidden">

                    {{-- Card Header - Green Gradient --}}
                    <div class="relative px-6 pt-6 pb-16 text-center text-white"
                        style="background: linear-gradient(135deg, #15803d 0%, #16a34a 50%, #22c55e 100%);">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <i class="ti ti-building text-lg opacity-80"></i>
                            <p class="text-xs font-semibold uppercase tracking-widest opacity-90">Pesantren Tahfizh</p>
                        </div>
                        <h2 class="text-lg font-bold leading-tight">Pesantren Tahfizh Alam Qur'an</h2>
                        <p class="text-xs opacity-75 mt-0.5">Kartu Santri Digital</p>
                    </div>

                    {{-- Photo - overlapping header --}}
                    <div class="flex justify-center -mt-12 mb-3 relative z-10">
                        <img
                            src="{{ $activeSantri->foto_url }}"
                            alt="{{ $activeSantri->nama_lengkap }}"
                            class="w-24 h-24 rounded-full border-4 border-white object-cover shadow-md bg-gray-100"
                            onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($activeSantri->nama_lengkap) }}&background=16a34a&color=fff&size=96'"
                        >
                    </div>

                    {{-- Card Body --}}
                    <div class="px-6 pb-6 text-center space-y-4">

                        {{-- Name & Status --}}
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $activeSantri->nama_lengkap }}</h3>
                            <div class="mt-1 flex justify-center">
                                @if(strtolower($activeSantri->status ?? '') === 'aktif')
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-3 py-0.5 rounded-full">
                                        <i class="ti ti-circle-check"></i> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-xs font-semibold px-3 py-0.5 rounded-full">
                                        <i class="ti ti-circle-x"></i> {{ $activeSantri->status ?? 'Tidak Aktif' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Detail Info --}}
                        <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 text-green-600 text-center shrink-0"><i class="ti ti-id-badge-2"></i></div>
                                <div>
                                    <p class="text-xs text-gray-400">NIS</p>
                                    <p class="text-sm font-semibold text-gray-800 font-mono">{{ $activeSantri->nis ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 text-green-600 text-center shrink-0"><i class="ti ti-hash"></i></div>
                                <div>
                                    <p class="text-xs text-gray-400">NISN</p>
                                    <p class="text-sm font-semibold text-gray-800 font-mono">{{ $activeSantri->nisn ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 text-green-600 text-center shrink-0"><i class="ti ti-school"></i></div>
                                <div>
                                    <p class="text-xs text-gray-400">Kelas</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $activeSantri->kelas_label ?? $activeSantri->kelas ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 text-green-600 text-center shrink-0"><i class="ti ti-calendar"></i></div>
                                <div>
                                    <p class="text-xs text-gray-400">Tahun Ajaran</p>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $activeSantri->tahun_ajaran ?? ($activeSantri->angkatan->nama ?? '-') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- QR Code --}}
                        <div class="flex flex-col items-center gap-2 pt-1">
                            <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Scan untuk verifikasi</p>
                            <img
                                src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($activeSantri->nis ?? $activeSantri->id) }}"
                                onerror="this.src='https://quickchart.io/qr?size=160&text={{ urlencode($activeSantri->nis ?? $activeSantri->id) }}'"
                                alt="QR Santri"
                                class="rounded-lg border border-gray-200 shadow-sm"
                                width="130"
                                height="130"
                            >
                            <p class="font-mono text-xs text-gray-400">{{ $activeSantri->nis ?? $activeSantri->id }}</p>
                        </div>

                        {{-- Print Button --}}
                        <div class="no-print pt-1">
                            <button
                                onclick="window.print()"
                                class="inline-flex items-center gap-2 text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:opacity-90 transition"
                                style="background-color:#16a34a">
                                <i class="ti ti-printer"></i>
                                Cetak Kartu
                            </button>
                        </div>

                    </div>
                </div>

            @elseif($santris->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
                    <i class="ti ti-users-group text-5xl block mb-3 text-gray-300"></i>
                    <h3 class="font-semibold text-gray-600 mb-1">Belum Ada Data Santri</h3>
                    <p class="text-sm">Anda belum memiliki data santri yang terdaftar.</p>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
                    <i class="ti ti-id-badge text-5xl block mb-3 text-gray-300"></i>
                    <h3 class="font-semibold text-gray-600 mb-1">Pilih Santri</h3>
                    <p class="text-sm">Pilih nama santri di atas untuk menampilkan kartu santri digital.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
