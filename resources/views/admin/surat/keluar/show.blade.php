@extends('layouts.admin.app')

@section('title', 'Surat Keluar – ' . $surat->nomor_surat)

@push('styles')
<style>
    @media print {
        /* Sembunyikan semua elemen layout admin saat print */
        header, aside, footer, .left-sidebar, .page-wrapper > header, .no-print, nav, #headerCollapse {
            display: none !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            visibility: hidden !important;
        }

        body, html {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        .page-wrapper {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }

        .content-container, .container-xl {
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

        /* Hilangkan header & footer bawaan browser (tanggal, judul, url) secara otomatis */
        @page {
            size: A4 portrait;
            margin: 0;
        }
    }

    #printable-letter {
        font-family: 'Times New Roman', Times, serif;
    }
</style>
@endpush

@section('content')
<div class="container-xl max-w-4xl">

    {{-- Action Bar (no-print) --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5 no-print">
        <a href="{{ route('admin.surat.keluar.index') }}"
           class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-lg transition">
            <i class="ti ti-arrow-left text-base"></i>
            Kembali ke Arsip
        </a>

        <div class="flex items-center gap-2">
            {{-- Download Lampiran --}}
            @if ($surat->file_lampiran)
                <a href="{{ Storage::url($surat->file_lampiran) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg shadow-sm transition">
                    <i class="ti ti-paperclip text-base"></i>
                    Unduh Lampiran
                </a>
            @endif

            {{-- Print --}}
            <button onclick="window.print()"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg shadow-sm transition cursor-pointer">
                <i class="ti ti-printer text-base"></i>
                Cetak Surat
            </button>
        </div>
    </div>

    {{-- Letter Paper --}}
    <div id="printable-letter"
         class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
         style="min-height: 29.7cm;">

        {{-- === LETTERHEAD (KOP SURAT DARI DATA SEKOLAH) === --}}
        <div class="border-b-4 border-double border-gray-800 px-12 pt-8 pb-4">
            <div class="text-center">
                <p class="text-xs font-bold tracking-widest text-gray-700 uppercase">YAYASAN PENDIDIKAN ISLAM</p>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-wide uppercase leading-tight font-serif">
                    {{ $school->name ?? "PONDOK PESANTREN TAHFIZH ALAM QUR'AN" }}
                </h1>
                <p class="text-xs sm:text-sm text-gray-700 mt-1 font-sans">
                    {{ $school->address ?? "Jl. Pandanarum - Winong - Jetis - Ponorogo" }}
                </p>
                <p class="text-xs text-gray-600 mt-0.5 font-sans">
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

        {{-- === LETTER BODY === --}}
        <div class="px-14 pt-8 pb-10">

            {{-- Title --}}
            <div class="text-center mb-7">
                <h2 class="text-base font-bold text-gray-900 underline uppercase tracking-widest">Surat Keluar</h2>
            </div>

            {{-- Metadata Table --}}
            <table class="text-sm text-gray-800 mb-6 w-auto">
                <tbody>
                    <tr>
                        <td class="pr-4 pb-1.5 font-semibold align-top w-36">Nomor</td>
                        <td class="pr-2 pb-1.5 align-top">:</td>
                        <td class="pb-1.5 font-mono font-bold text-blue-800">{{ $surat->nomor_surat }}</td>
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
                                1 (satu) berkas
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="pr-4 pb-1.5 font-semibold align-top">Perihal</td>
                        <td class="pr-2 pb-1.5 align-top">:</td>
                        <td class="pb-1.5 font-semibold">{{ $surat->perihal }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Recipient --}}
            <div class="mb-7">
                <p class="text-sm font-semibold text-gray-800">Kepada Yth.</p>
                <p class="text-sm text-gray-800 pl-4">{{ $surat->tujuan_surat }}</p>
                <p class="text-sm text-gray-800 pl-4">di Tempat</p>
            </div>

            {{-- Salutation --}}
            <div class="mb-5">
                <p class="text-sm text-gray-800 italic">Assalamu'alaikum Warahmatullahi Wabarakatuh,</p>
            </div>

            {{-- Body --}}
            <div class="text-sm text-gray-800 leading-relaxed space-y-3 text-justify mb-10"
                 style="white-space: pre-wrap;">{{ $surat->isi_surat }}</div>

            {{-- Closing --}}
            <div class="mb-8">
                <p class="text-sm text-gray-800 italic">Wassalamu'alaikum Warahmatullahi Wabarakatuh,</p>
            </div>

            {{-- Signature Block --}}
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

    {{-- Metadata Card (no-print) --}}
    <div class="mt-5 bg-white rounded-xl shadow-sm border border-gray-200 p-5 no-print">
        <h3 class="text-sm font-semibold text-gray-600 mb-3 flex items-center gap-2">
            <i class="ti ti-info-circle text-gray-400"></i> Informasi Arsip
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-0.5">Dibuat Oleh</p>
                <p class="font-semibold text-gray-700">{{ $surat->creator->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-0.5">Tanggal Input</p>
                <p class="font-semibold text-gray-700">
                    {{ \Carbon\Carbon::parse($surat->created_at)->translatedFormat('d M Y, H:i') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-0.5">Status Lampiran</p>
                <p class="font-semibold">
                    @if($surat->file_lampiran)
                        <span class="text-emerald-600">Ada Lampiran</span>
                    @else
                        <span class="text-gray-400">Tidak Ada</span>
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-0.5">Nomor Surat</p>
                <p class="font-mono font-bold text-blue-700">{{ $surat->nomor_surat }}</p>
            </div>
        </div>
    </div>

</div>
@endsection
