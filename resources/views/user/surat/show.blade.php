<x-app-layout>
    <x-slot name="title">Surat: {{ $surat->nomor_surat }}</x-slot>

    <style>
        @media print {
            /* Sembunyikan navbar user, bottom navigation, footer, dan tombol aksi saat print */
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
            }
            .min-h-screen {
                padding: 0 !important;
                margin: 0 !important;
                min-height: auto !important;
                background: #fff !important;
            }
            main, .max-w-4xl, .space-y-6, .py-6, .px-4 {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            #printable-letter {
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 1.2cm 1.5cm !important;
                width: 100% !important;
                min-height: auto !important;
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
        #printable-letter {
            font-family: 'Times New Roman', Times, serif;
        }
    </style>

    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">

        {{-- Action Bar (no-print) --}}
        <div class="flex flex-wrap items-center justify-between gap-3 no-print">
            <a href="{{ route('user.surat') }}"
               class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-xl transition shadow-xs">
                <i class="ti ti-arrow-left text-base"></i>
                Kembali ke Daftar Surat
            </a>

            <div class="flex items-center gap-2">
                @if ($surat->file_lampiran)
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($surat->file_lampiran) }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl shadow-xs transition">
                        <i class="ti ti-paperclip text-base"></i>
                        Unduh Lampiran
                    </a>
                @endif

                <button onclick="window.print()"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl shadow-xs transition cursor-pointer">
                    <i class="ti ti-printer text-base"></i>
                    Cetak / Simpan PDF
                </button>
            </div>
        </div>

        {{-- Surat Paper --}}
        <div id="printable-letter" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" style="min-height: 29.7cm;">

            {{-- Kop Surat Resmi dari Data Sekolah --}}
            <div class="border-b-4 border-double border-gray-800 px-8 sm:px-12 pt-8 pb-4">
                <div class="text-center">
                    <p class="text-[11px] sm:text-xs font-bold tracking-widest text-gray-700 uppercase">YAYASAN PENDIDIKAN ISLAM</p>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-wide uppercase leading-tight font-serif">
                        {{ $school->name ?? "PONDOK PESANTREN TAHFIZH ALAM QUR'AN" }}
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-700 mt-1 font-sans">
                        {{ $school->address ?? "Jl. Pandanarum - Winong - Jetis - Ponorogo" }}
                    </p>
                    <p class="text-[11px] sm:text-xs text-gray-600 mt-0.5 font-sans">
                        @if(!empty($school->phone))
                            Telp/WA: {{ $school->phone }}
                        @endif
                        @if(!empty($school->email))
                            &nbsp;|&nbsp; Email: {{ $school->email }}
                        @endif
                        &nbsp;|&nbsp; Web: www.alamquran.sch.id
                    </p>
                </div>
            </div>

            {{-- Isi Surat --}}
            <div class="px-8 sm:px-14 pt-8 pb-10">

                {{-- Judul Surat --}}
                <div class="text-center mb-6">
                    <h2 class="text-base font-bold text-gray-900 underline uppercase tracking-widest">SURAT KELUAR RESMI</h2>
                </div>

                {{-- Metadata Surat --}}
                <table class="text-sm text-gray-800 mb-6 w-auto">
                    <tbody>
                        <tr>
                            <td class="pr-4 pb-1.5 font-semibold align-top w-32 sm:w-36">Nomor</td>
                            <td class="pr-2 pb-1.5 align-top">:</td>
                            <td class="pb-1.5 font-mono font-bold text-blue-900">{{ $surat->nomor_surat }}</td>
                        </tr>
                        <tr>
                            <td class="pr-4 pb-1.5 font-semibold align-top">Tanggal</td>
                            <td class="pr-2 pb-1.5 align-top">:</td>
                            <td class="pb-1.5">{{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="pr-4 pb-1.5 font-semibold align-top">Lampiran</td>
                            <td class="pr-2 pb-1.5 align-top">:</td>
                            <td class="pb-1.5">
                                @if($surat->file_lampiran)
                                    1 (satu) berkas lampiran
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="pr-4 pb-1.5 font-semibold align-top">Perihal</td>
                            <td class="pr-2 pb-1.5 align-top">:</td>
                            <td class="pb-1.5 font-semibold text-gray-900">{{ $surat->perihal }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Ditujukan Ke --}}
                <div class="mb-6">
                    <p class="text-sm font-semibold text-gray-800">Kepada Yth.</p>
                    <p class="text-sm text-gray-800 pl-4 font-medium">{{ $surat->tujuan_surat }}</p>
                    <p class="text-sm text-gray-800 pl-4">di Tempat</p>
                </div>

                {{-- Salam Pembuka --}}
                <div class="mb-4">
                    <p class="text-sm text-gray-800 italic">Assalamu'alaikum Warahmatullahi Wabarakatuh,</p>
                </div>

                {{-- Body Isi Surat --}}
                <div class="text-sm text-gray-800 leading-relaxed text-justify mb-8 font-serif whitespace-pre-wrap">{{ $surat->isi_surat }}</div>

                {{-- Salam Penutup --}}
                <div class="mb-8">
                    <p class="text-sm text-gray-800 italic">Wassalamu'alaikum Warahmatullahi Wabarakatuh,</p>
                </div>

                {{-- Tanda Tangan --}}
                <div class="flex justify-end">
                    <div class="text-center text-sm text-gray-800 w-72">
                        <p class="font-medium">Ponorogo, {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }}</p>
                        <p class="font-semibold mt-2 leading-tight">Pimpinan Pesantren<br>{{ $school->name ?? "MTS Tahfizh Alam Quran" }}</p>
                        
                        <div class="my-5 py-2.5 px-3 border border-dashed border-gray-300 rounded-lg bg-gray-50/50 inline-block">
                            <p class="text-[11px] text-gray-500 italic leading-snug">
                                Dokumen diterbitkan melalui<br>
                                <strong class="text-gray-700 font-semibold not-italic">Sistem Informasi Pondok</strong>
                            </p>
                        </div>
                    </div>
                </div>

            </div>


        </div>

    </div>
</x-app-layout>
