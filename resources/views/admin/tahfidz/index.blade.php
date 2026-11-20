@extends('layouts.admin.app')

@section('title', 'Setoran Tahfidz Harian')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600">
                        <i class="ti ti-book text-2xl"></i>
                    </span>
                    Setoran Tahfidz Harian
                </h1>
                <p class="mt-1 text-sm text-gray-500">Input dan kelola setoran hafalan santri</p>
            </div>
            <button
                onclick="resetAndOpenModal()"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow transition-colors cursor-pointer"
                style="background-color: #16a34a !important; color: #ffffff !important;"
            >
                <i class="ti ti-plus text-lg"></i>
                + Tambah Setoran
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
        <form method="GET" action="{{ route('admin.tahfidz.index') }}" class="flex flex-wrap gap-4 items-end">
            {{-- Kolom Cari Nama Santri --}}
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-xs font-semibold text-gray-600 mb-1.5">
                    <i class="ti ti-search mr-1"></i>Cari Santri
                </label>
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ $search ?? '' }}"
                        placeholder="Ketik nama santri atau NIS..."
                        class="w-full text-sm border border-gray-200 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent bg-white"
                    />
                    <i class="ti ti-user absolute left-2.5 top-2.5 text-gray-400 text-sm"></i>
                </div>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label for="tanggal" class="block text-xs font-semibold text-gray-600 mb-1.5">
                    <i class="ti ti-calendar mr-1"></i>Tanggal
                </label>
                <input
                    type="date"
                    name="tanggal"
                    id="tanggal"
                    value="{{ $tanggal }}"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent"
                />
            </div>
            <div class="flex-1 min-w-[140px]">
                <label for="kelas" class="block text-xs font-semibold text-gray-600 mb-1.5">
                    <i class="ti ti-school mr-1"></i>Kelas
                </label>
                <select
                    name="kelas"
                    id="kelas"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent bg-white"
                >
                    <option value="">Semua Kelas</option>
                    <option value="1" {{ (string)$kelas === '1' ? 'selected' : '' }}>1 (kelas 7)</option>
                    <option value="2" {{ (string)$kelas === '2' ? 'selected' : '' }}>2 (kelas 8)</option>
                    <option value="3" {{ (string)$kelas === '3' ? 'selected' : '' }}>3 (kelas 9)</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition-colors cursor-pointer" style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-filter"></i> Filter
                </button>
                <a href="{{ route('admin.tahfidz.index') }}" class="inline-flex items-center gap-1.5 bg-gray-700 hover:bg-gray-800 text-white text-sm font-bold px-4 py-2 rounded-lg transition-colors" style="background-color: #374151 !important; color: #ffffff !important;">
                    <i class="ti ti-refresh"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        {{-- Total Setoran --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <i class="ti ti-stack text-2xl text-blue-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Setoran</p>
                <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $stats['total'] ?? 0 }}</p>
            </div>
        </div>

        {{-- Ziyadah --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <i class="ti ti-trending-up text-2xl text-emerald-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Ziyadah</p>
                <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $stats['ziyadah'] ?? 0 }}</p>
            </div>
        </div>

        {{-- Murojaah --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-sky-50 flex items-center justify-center flex-shrink-0">
                <i class="ti ti-repeat text-2xl text-sky-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Murojaah</p>
                <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $stats['murajaah'] ?? 0 }}</p>
            </div>
        </div>

        {{-- Ujian Juz'iyah --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
                <i class="ti ti-award text-2xl text-purple-600"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Ujian Juz'iyah</p>
                <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $stats['ujian_juziyah'] ?? 0 }}</p>
            </div>
        </div>

        {{-- Sangat Lancar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                <i class="ti ti-star text-2xl text-amber-500"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Sangat Lancar</p>
                <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $stats['sangat_lancar'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-700 flex items-center gap-2">
                <i class="ti ti-list-details text-emerald-500"></i>
                Daftar Setoran
                <span class="ml-1 text-xs font-normal text-gray-400">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</span>
            </h2>
            <span class="text-xs text-gray-400">{{ $setoranHariIni->total() }} data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-10">No</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Santri</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Surah</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Ayat</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">Juz</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jenis</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelancaran</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Catatan</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Ustadz</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($setoranHariIni as $index => $setoran)
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="px-4 py-3 text-gray-500 text-center">{{ $setoranHariIni->firstItem() + $index }}</td>

                            {{-- Santri --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-emerald-700">{{ strtoupper(substr($setoran->santri->nama_lengkap ?? '-', 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800 text-sm leading-tight">{{ $setoran->santri->nama_lengkap ?? '-' }}</p>
                                        <p class="text-xs text-gray-400">{{ $setoran->santri->kelas ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Surah --}}
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $setoran->nama_surah ?? '-' }}</p>
                            </td>

                            {{-- Ayat --}}
                            <td class="px-4 py-3 text-gray-600">
                                {{ $setoran->ayat_awal ?? '-' }} – {{ $setoran->ayat_akhir ?? '-' }}
                            </td>

                            {{-- Juz --}}
                            <td class="px-4 py-3 text-gray-600 text-center">{{ $setoran->juz ?? '-' }}</td>

                            {{-- Jenis --}}
                            <td class="px-4 py-3">
                                @if($setoran->jenis_setoran === 'ziyadah')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        <i class="ti ti-trending-up text-xs"></i> Ziyadah
                                    </span>
                                @elseif($setoran->jenis_setoran === 'murajaah')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        <i class="ti ti-repeat text-xs"></i> Murojaah
                                    </span>
                                @elseif($setoran->jenis_setoran === 'ujian_juziyah')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                                        <i class="ti ti-award text-xs"></i> Ujian Juz'iyah
                                    </span>
                                @else
                                    <span class="text-gray-500 text-xs font-medium">{{ $setoran->jenis_setoran_label ?? ucfirst($setoran->jenis_setoran) }}</span>
                                @endif
                            </td>

                            {{-- Kelancaran --}}
                            <td class="px-4 py-3">
                                @php
                                    $kelancaranConfig = [
                                        'sangat_lancar' => ['label' => 'Sangat Lancar', 'class' => 'bg-emerald-100 text-emerald-700', 'icon' => 'ti-star'],
                                        'lancar'        => ['label' => 'Lancar',        'class' => 'bg-blue-100 text-blue-700',    'icon' => 'ti-check'],
                                        'kurang_lancar' => ['label' => 'Kurang Lancar', 'class' => 'bg-yellow-100 text-yellow-700','icon' => 'ti-alert-triangle'],
                                        'mengulang'     => ['label' => 'Mengulang',     'class' => 'bg-red-100 text-red-700',      'icon' => 'ti-refresh-alert'],
                                    ];
                                    $kConfig = $kelancaranConfig[$setoran->kelancaran] ?? null;
                                @endphp
                                @if($kConfig)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $kConfig['class'] }}">
                                        <i class="ti {{ $kConfig['icon'] }} text-xs"></i>
                                        {{ $kConfig['label'] }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            {{-- Catatan --}}
                            <td class="px-4 py-3 max-w-[160px]">
                                @if($setoran->catatan_ustadz)
                                    <p class="text-xs text-gray-500 truncate" title="{{ $setoran->catatan_ustadz }}">
                                        {{ $setoran->catatan_ustadz }}
                                    </p>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Ustadz --}}
                            <td class="px-4 py-3 text-gray-600 text-sm">{{ $setoran->ustadz->name ?? '-' }}</td>

                            {{-- Tanggal --}}
                            <td class="px-4 py-3 text-gray-600 text-sm whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($setoran->tanggal)->format('d M Y') }}
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3 text-center">
                                <form action="{{ route('admin.tahfidz.destroy', $setoran->id) }}" method="POST"
                                    onsubmit="return confirm('Hapus setoran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                                        title="Hapus">
                                        <i class="ti ti-trash text-base"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <i class="ti ti-book-off text-5xl"></i>
                                    <p class="text-sm font-medium">Belum ada setoran pada tanggal ini</p>
                                    <button onclick="document.getElementById('modalTambahSetoran').classList.remove('hidden')"
                                        class="mt-1 text-xs text-emerald-600 hover:text-emerald-700 font-semibold underline underline-offset-2">
                                        + Tambah Setoran Pertama
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($setoranHariIni->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $setoranHariIni->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ============================================================
     MODAL TAMBAH SETORAN TAHFIDZ
     ============================================================ --}}
<div id="modalTambahSetoran"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
    x-data>

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
         onclick="document.getElementById('modalTambahSetoran').classList.add('hidden')"></div>

    {{-- Modal Panel --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl z-10">
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <i class="ti ti-book-upload text-emerald-600 text-base"></i>
                </span>
                Tambah Setoran Tahfidz
            </h3>
            <button onclick="document.getElementById('modalTambahSetoran').classList.add('hidden')"
                class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <form action="{{ route('admin.tahfidz.store') }}" method="POST" class="px-6 py-5 space-y-5">
            @csrf

            {{-- Row: Santri & Tanggal --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Santri dengan pencarian cepat --}}
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="santri_id" class="block text-xs font-semibold text-gray-600">
                            <i class="ti ti-user mr-1"></i>Nama Santri <span class="text-red-500">*</span>
                        </label>
                        <span id="santriSearchCount" class="text-[11px] text-gray-400">Total: {{ $santriAktif->count() }} santri</span>
                    </div>

                    {{-- Kolom Ketik Pencarian Cepat --}}
                    <div class="relative mb-2">
                        <i class="ti ti-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        <input
                            type="text"
                            id="searchSantriInput"
                            placeholder="Ketik nama santri di sini untuk cari cepat..."
                            oninput="filterSantriOptions(this.value)"
                            autocomplete="off"
                            class="w-full text-sm border border-emerald-300 rounded-xl pl-9 pr-8 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent bg-emerald-50/40 text-gray-800 placeholder-gray-400 font-medium"
                        />
                        <button type="button" onclick="clearSantriSearch()" id="clearSantriBtn" class="hidden absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-sm p-0.5">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>

                    <select name="santri_id" id="santri_id" required
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent bg-white">
                        <option value="">-- Pilih Santri --</option>
                        @foreach($santriAktif as $santri)
                            <option value="{{ $santri->id }}" data-nama="{{ strtolower($santri->nama_lengkap) }}" data-kelas="{{ strtolower($santri->kelas_label ?? $santri->kelas) }}">
                                {{ $santri->nama_lengkap }} ({{ $santri->kelas_label ?? 'Kelas ' . $santri->kelas }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal --}}
                <div class="sm:col-span-2">
                    <label for="tanggal_setoran" class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="ti ti-calendar mr-1"></i>Tanggal Setoran <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal" id="tanggal_setoran" required
                        value="{{ \Carbon\Carbon::today()->toDateString() }}"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent" />
                </div>
            </div>

            {{-- Divider --}}
            <div class="border-t border-gray-100"></div>

            {{-- Row: Surah & Nama Surah --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Surah ID --}}
                <div>
                    <label for="surah_id" class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="ti ti-books mr-1"></i>Pilih Surah (114 Surat) <span class="text-red-500">*</span>
                    </label>
                    <select name="surah_id" id="surah_id" required
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent bg-white"
                        onchange="fillNamaSurah(this)">
                        <option value="">-- Pilih Surah Al-Qur'an --</option>
                        @foreach($surahList as $surah)
                            <option value="{{ $surah->id }}"
                                    data-nomor="{{ $surah->nomor_surah }}"
                                    data-nama="{{ $surah->nama_latin ?? $surah->nama }}"
                                    data-arab="{{ $surah->nama_arab }}"
                                    data-ayat="{{ $surah->jumlah_ayat }}"
                                    data-arti="{{ $surah->arti }}"
                                    data-turun="{{ $surah->tempat_turun }}">
                                {{ $surah->nomor_surah }}. {{ $surah->nama_latin ?? $surah->nama }} ({{ $surah->nama_arab }}) - {{ $surah->jumlah_ayat }} Ayat
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Nama Surah (Otomatis Terisi) --}}
                <div>
                    <label for="nama_surah" class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="ti ti-pencil mr-1"></i>Nama Surah <span class="text-[11px] text-emerald-600 font-normal">(Otomatis Terisi)</span> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_surah" id="nama_surah" required readonly
                        placeholder="Pilih surat di sebelah kiri..."
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 text-gray-800 font-semibold focus:outline-none cursor-default" />
                </div>
            </div>

            {{-- Info Badge Surah --}}
            <div id="surahInfoBadge" class="hidden text-xs bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-emerald-900 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span id="badgeSurahNomor" class="px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-800 font-bold text-[10px]"></span>
                    <span id="badgeSurahDetail" class="font-medium text-[11px]"></span>
                </div>
                <div id="badgeSurahArab" class="text-base font-bold text-emerald-700 font-serif"></div>
            </div>

            {{-- Row: Ayat & Juz --}}
            <div class="grid grid-cols-3 gap-4">
                {{-- Ayat Awal --}}
                <div>
                    <label for="ayat_awal" class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="ti ti-arrow-right mr-1"></i>Ayat Awal <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="ayat_awal" id="ayat_awal" required min="1"
                        placeholder="1" oninput="updateAutoJuz()"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent" />
                </div>

                {{-- Ayat Akhir --}}
                <div>
                    <label for="ayat_akhir" class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="ti ti-arrow-left mr-1"></i>Ayat Akhir <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="ayat_akhir" id="ayat_akhir" required min="1"
                        placeholder="10"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent" />
                </div>

                {{-- Juz --}}
                <div>
                    <label for="juz" class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="ti ti-layers mr-1"></i>Juz <span class="text-[11px] text-emerald-600 font-bold">(Otomatis)</span> <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="juz" id="juz" required min="1" max="30"
                        placeholder="1 - 30"
                        class="w-full text-sm border border-emerald-300 rounded-xl px-3 py-2.5 bg-emerald-50/50 font-bold text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent" />
                </div>
            </div>

            {{-- Row: Jenis & Kelancaran --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Jenis Setoran --}}
                <div>
                    <label for="jenis_setoran" class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="ti ti-category mr-1"></i>Jenis Setoran <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_setoran" id="jenis_setoran" required
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent bg-white">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="ziyadah">Ziyadah (Hafalan Baru)</option>
                        <option value="murajaah">Murojaah (Mengulang Hafalan)</option>
                        <option value="ujian_juziyah">Ujian Juz'iyah (Ujian Per Juz)</option>
                    </select>
                </div>

                {{-- Kelancaran --}}
                <div>
                    <label for="kelancaran" class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="ti ti-gauge mr-1"></i>Kelancaran <span class="text-red-500">*</span>
                    </label>
                    <select name="kelancaran" id="kelancaran" required
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent bg-white">
                        <option value="">-- Pilih Kelancaran --</option>
                        <option value="sangat_lancar">⭐ Sangat Lancar</option>
                        <option value="lancar">✅ Lancar</option>
                        <option value="kurang_lancar">⚠️ Kurang Lancar</option>
                        <option value="mengulang">🔄 Mengulang</option>
                    </select>
                </div>
            </div>

            {{-- Catatan Ustadz --}}
            <div>
                <label for="catatan_ustadz" class="block text-xs font-semibold text-gray-600 mb-1.5">
                    <i class="ti ti-notes mr-1"></i>Catatan Ustadz
                </label>
                <textarea name="catatan_ustadz" id="catatan_ustadz" rows="3"
                    placeholder="Catatan tambahan untuk santri (opsional)..."
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent resize-none"></textarea>
            </div>

            {{-- Modal Footer --}}
            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 sticky bottom-0 bg-white pb-1">
                <button type="button"
                    onclick="document.getElementById('modalTambahSetoran').classList.add('hidden')"
                    class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow transition-colors cursor-pointer"
                    style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-device-floppy"></i>
                    Simpan Setoran Baru
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Database Pemetaan 30 Juz Al-Qur'an (Surat & Ayat Awal - Akhir)
    const JUZ_BOUNDARIES = [
        { juz: 1,  sS: 1,  sV: 1,  eS: 2,  eV: 141 },
        { juz: 2,  sS: 2,  sV: 142, eS: 2,  eV: 252 },
        { juz: 3,  sS: 2,  sV: 253, eS: 3,  eV: 92 },
        { juz: 4,  sS: 3,  sV: 93,  eS: 4,  eV: 23 },
        { juz: 5,  sS: 4,  sV: 24,  eS: 4,  eV: 147 },
        { juz: 6,  sS: 4,  sV: 148, eS: 5,  eV: 81 },
        { juz: 7,  sS: 5,  sV: 82,  eS: 6,  eV: 110 },
        { juz: 8,  sS: 6,  sV: 111, eS: 7,  eV: 87 },
        { juz: 9,  sS: 7,  sV: 88,  eS: 8,  eV: 40 },
        { juz: 10, sS: 8,  sV: 41,  eS: 9,  eV: 92 },
        { juz: 11, sS: 9,  sV: 93,  eS: 11, eV: 5 },
        { juz: 12, sS: 11, sV: 6,   eS: 12, eV: 52 },
        { juz: 13, sS: 12, sV: 53,  eS: 14, eV: 52 },
        { juz: 14, sS: 15, sV: 1,   eS: 16, eV: 128 },
        { juz: 15, sS: 17, sV: 1,   eS: 18, eV: 74 },
        { juz: 16, sS: 18, sV: 75,  eS: 20, eV: 135 },
        { juz: 17, sS: 21, sV: 1,   eS: 22, eV: 78 },
        { juz: 18, sS: 23, sV: 1,   eS: 25, eV: 20 },
        { juz: 19, sS: 25, sV: 21,  eS: 27, eV: 55 },
        { juz: 20, sS: 27, sV: 56,  eS: 29, eV: 45 },
        { juz: 21, sS: 29, sV: 46,  eS: 33, eV: 30 },
        { juz: 22, sS: 33, sV: 31,  eS: 36, eV: 27 },
        { juz: 23, sS: 36, sV: 28,  eS: 39, eV: 31 },
        { juz: 24, sS: 39, sV: 32,  eS: 41, eV: 46 },
        { juz: 25, sS: 41, sV: 47,  eS: 45, eV: 37 },
        { juz: 26, sS: 46, sV: 1,   eS: 51, eV: 30 },
        { juz: 27, sS: 51, sV: 31,  eS: 57, eV: 29 },
        { juz: 28, sS: 58, sV: 1,   eS: 66, eV: 12 },
        { juz: 29, sS: 67, sV: 1,   eS: 77, eV: 50 },
        { juz: 30, sS: 78, sV: 1,   eS: 114, eV: 6 },
    ];

    function calculateJuz(surahNumber, verseNumber) {
        const sNum = parseInt(surahNumber);
        const vNum = parseInt(verseNumber) || 1;
        if (!sNum) return '';
        for (const item of JUZ_BOUNDARIES) {
            const isAfterStart = (sNum > item.sS) || (sNum === item.sS && vNum >= item.sV);
            const isBeforeEnd = (sNum < item.eS) || (sNum === item.eS && vNum <= item.eV);
            if (isAfterStart && isBeforeEnd) {
                return item.juz;
            }
        }
        return '';
    }

    function fillNamaSurah(selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            document.getElementById('nama_surah').value = '';
            document.getElementById('surahInfoBadge').classList.add('hidden');
            return;
        }

        const nomorSurah = parseInt(selectedOption.getAttribute('data-nomor')) || 0;
        const namaSurah = selectedOption.getAttribute('data-nama') || '';
        const namaArab = selectedOption.getAttribute('data-arab') || '';
        const jumlahAyat = parseInt(selectedOption.getAttribute('data-ayat')) || 1;
        const arti = selectedOption.getAttribute('data-arti') || '';
        const tempat = selectedOption.getAttribute('data-turun') || '';

        // Otomatis isi nama surat
        document.getElementById('nama_surah').value = namaSurah;

        // Atur batas input ayat
        const ayatAwalInput = document.getElementById('ayat_awal');
        const ayatAkhirInput = document.getElementById('ayat_akhir');
        
        ayatAwalInput.max = jumlahAyat;
        ayatAkhirInput.max = jumlahAyat;
        ayatAwalInput.placeholder = '1 - ' + jumlahAyat;
        ayatAkhirInput.placeholder = '1 - ' + jumlahAyat;

        if (!ayatAwalInput.value || parseInt(ayatAwalInput.value) > jumlahAyat) {
            ayatAwalInput.value = 1;
        }

        // Tampilkan info badge surah
        const badge = document.getElementById('surahInfoBadge');
        if (badge) {
            document.getElementById('badgeSurahNomor').textContent = 'Surat ke-' + nomorSurah;
            document.getElementById('badgeSurahArab').textContent = namaArab;
            document.getElementById('badgeSurahDetail').textContent = tempat + ' • ' + jumlahAyat + ' Ayat' + (arti ? ' (' + arti + ')' : '');
            badge.classList.remove('hidden');
        }

        // Hitung otomatis nomor Juz
        updateAutoJuz();
    }

    function updateAutoJuz() {
        const selectEl = document.getElementById('surah_id');
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        const nomorSurah = parseInt(selectedOption.getAttribute('data-nomor')) || 0;
        const ayatAwal = parseInt(document.getElementById('ayat_awal').value) || 1;
        
        const autoJuz = calculateJuz(nomorSurah, ayatAwal);
        if (autoJuz) {
            document.getElementById('juz').value = autoJuz;
        }
    }

    // Fungsi pencarian cepat santri di modal
    function filterSantriOptions(keyword) {
        const query = keyword.toLowerCase().trim();
        const select = document.getElementById('santri_id');
        const clearBtn = document.getElementById('clearSantriBtn');
        const countSpan = document.getElementById('santriSearchCount');
        const options = select.getElementsByTagName('option');
        
        if (query.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }

        let matchCount = 0;
        let firstMatch = null;

        for (let i = 1; i < options.length; i++) { // lewati option 0 (-- Pilih Santri --)
            const opt = options[i];
            const nama = opt.getAttribute('data-nama') || '';
            const kelas = opt.getAttribute('data-kelas') || '';

            if (query === '' || nama.includes(query) || kelas.includes(query)) {
                opt.style.display = '';
                opt.disabled = false;
                matchCount++;
                if (!firstMatch) {
                    firstMatch = opt;
                }
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
            }
        }

        if (query !== '') {
            countSpan.textContent = matchCount + ' santri ditemukan';
            // Otomatis sorot opsi pertama yang cocok jika ada
            if (firstMatch) {
                select.value = firstMatch.value;
            }
        } else {
            countSpan.textContent = 'Total: ' + (options.length - 1) + ' santri';
        }
    }

    function clearSantriSearch() {
        const input = document.getElementById('searchSantriInput');
        input.value = '';
        filterSantriOptions('');
        input.focus();
    }

    // Reset input search saat modal dibuka
    function resetAndOpenModal() {
        clearSantriSearch();
        document.getElementById('modalTambahSetoran').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('searchSantriInput').focus();
        }, 100);
    }

    // Auto-open modal if there are validation errors from a previous POST
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('modalTambahSetoran').classList.remove('hidden');
        });
    @endif
</script>
@endpush

@endsection
