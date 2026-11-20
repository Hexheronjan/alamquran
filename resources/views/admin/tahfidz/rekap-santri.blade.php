@extends('layouts.admin.app')

@section('title', 'Rekap Tahfidz: ' . $santri->nama_lengkap)

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                {{-- Back Button --}}
                <a href="{{ route('admin.tahfidz.index') }}"
                    class="mt-1 w-9 h-9 rounded-xl bg-white border border-gray-200 shadow-sm flex items-center justify-center text-gray-500 hover:text-emerald-600 hover:border-emerald-300 transition-colors flex-shrink-0">
                    <i class="ti ti-arrow-left text-lg"></i>
                </a>

                {{-- Title --}}
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-2xl font-bold text-gray-900">Rekap Tahfidz</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                            {{ $santri->kelas ?? 'Santri' }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-base font-semibold text-emerald-700">{{ $santri->nama_lengkap }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">NIS: {{ $santri->nis ?? '-' }} &bull; {{ $santri->jenis_kelamin ?? '' }}</p>
                </div>
            </div>

            {{-- Avatar / Initials --}}
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center shadow-md flex-shrink-0">
                <span class="text-xl font-extrabold text-white">{{ strtoupper(substr($santri->nama_lengkap, 0, 2)) }}</span>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        {{-- Total Setoran --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i class="ti ti-stack text-xl text-blue-500"></i>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide leading-tight">Total<br>Setoran</p>
            </div>
            <p class="text-3xl font-extrabold text-gray-800">{{ $stats['total_setoran'] ?? $stats['total'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">sesi setoran</p>
        </div>

        {{-- Ziyadah --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <i class="ti ti-trending-up text-xl text-emerald-500"></i>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide leading-tight">Ziyadah<br>(Hafalan Baru)</p>
            </div>
            <p class="text-3xl font-extrabold text-gray-800">{{ $stats['ziyadah'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">sesi ziyadah</p>
        </div>

        {{-- Murojaah --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center">
                    <i class="ti ti-repeat text-xl text-sky-500"></i>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide leading-tight">Murojaah<br>(Ulangan)</p>
            </div>
            <p class="text-3xl font-extrabold text-gray-800">{{ $stats['murajaah'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">sesi murojaah</p>
        </div>

        {{-- Ujian Juz'iyah --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center">
                    <i class="ti ti-award text-xl text-purple-600"></i>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide leading-tight">Ujian<br>Juz'iyah</p>
            </div>
            <p class="text-3xl font-extrabold text-gray-800">{{ $stats['ujian_juziyah'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">sesi ujian</p>
        </div>

        {{-- Total Ayat --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                    <i class="ti ti-bookmark text-xl text-amber-500"></i>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide leading-tight">Total Ayat<br>Hafalan</p>
            </div>
            <p class="text-3xl font-extrabold text-gray-800">{{ number_format($stats['total_ayat'] ?? 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">ayat dihafal</p>
        </div>
    </div>

    {{-- Month / Year Filter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
        <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[140px]">
                <label for="bulan" class="block text-xs font-semibold text-gray-600 mb-1.5">
                    <i class="ti ti-calendar-month mr-1"></i>Bulan
                </label>
                <select name="bulan" id="bulan"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent bg-white">
                    @php
                        $bulanList = [
                            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
                            4  => 'April',    5  => 'Mei',      6  => 'Juni',
                            7  => 'Juli',     8  => 'Agustus',  9  => 'September',
                            10 => 'Oktober',  11 => 'November', 12 => 'Desember',
                        ];
                    @endphp
                    @foreach($bulanList as $num => $nama)
                        <option value="{{ $num }}" {{ (int)$bulan === $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[100px]">
                <label for="tahun" class="block text-xs font-semibold text-gray-600 mb-1.5">
                    <i class="ti ti-calendar-event mr-1"></i>Tahun
                </label>
                <select name="tahun" id="tahun"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent bg-white">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
                    <i class="ti ti-filter"></i> Tampilkan
                </button>
                <a href="{{ url()->current() }}"
                    class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
                    <i class="ti ti-refresh"></i> Reset
                </a>
            </div>

            {{-- Period label --}}
            <div class="ml-auto self-end">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-100 px-3 py-2 rounded-lg">
                    <i class="ti ti-calendar-stats"></i>
                    {{ $bulanList[(int)$bulan] ?? '' }} {{ $tahun }}
                </span>
            </div>
        </form>
    </div>

    {{-- Setoran Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-700 flex items-center gap-2">
                <i class="ti ti-list-details text-emerald-500"></i>
                Riwayat Setoran
            </h2>
            <span class="text-xs text-gray-400">{{ $setoran->count() }} data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-10">No</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Surah</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Ayat</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">Juz</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jenis</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelancaran</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Catatan Ustadz</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($setoran as $i => $item)
                        @php
                            $kelancaranConfig = [
                                'sangat_lancar' => ['label' => 'Sangat Lancar', 'class' => 'bg-emerald-100 text-emerald-700', 'icon' => 'ti-star'],
                                'lancar'        => ['label' => 'Lancar',        'class' => 'bg-blue-100 text-blue-700',    'icon' => 'ti-check'],
                                'kurang_lancar' => ['label' => 'Kurang Lancar', 'class' => 'bg-yellow-100 text-yellow-700','icon' => 'ti-alert-triangle'],
                                'mengulang'     => ['label' => 'Mengulang',     'class' => 'bg-red-100 text-red-700',      'icon' => 'ti-refresh-alert'],
                            ];
                            $kConfig = $kelancaranConfig[$item->kelancaran] ?? null;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            {{-- No --}}
                            <td class="px-4 py-3 text-gray-400 text-center">{{ $i + 1 }}</td>

                            {{-- Tanggal --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm">{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l') }}</p>
                                </div>
                            </td>

                            {{-- Surah --}}
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $item->nama_surah ?? '-' }}</p>
                            </td>

                            {{-- Ayat --}}
                            <td class="px-4 py-3 text-gray-600">
                                <span class="inline-flex items-center gap-1 text-sm">
                                    <span class="font-semibold text-gray-700">{{ $item->ayat_awal ?? '-' }}</span>
                                    <i class="ti ti-arrow-right text-xs text-gray-400"></i>
                                    <span class="font-semibold text-gray-700">{{ $item->ayat_akhir ?? '-' }}</span>
                                </span>
                                @if($item->ayat_awal && $item->ayat_akhir)
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ abs($item->ayat_akhir - $item->ayat_awal) + 1 }} ayat
                                    </p>
                                @endif
                            </td>

                            {{-- Juz --}}
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 text-gray-600 text-xs font-bold">
                                    {{ $item->juz ?? '-' }}
                                </span>
                            </td>

                            {{-- Jenis --}}
                            <td class="px-4 py-3">
                                @if($item->jenis_setoran === 'ziyadah')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        <i class="ti ti-trending-up text-xs"></i> Ziyadah
                                    </span>
                                @elseif($item->jenis_setoran === 'murajaah')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        <i class="ti ti-repeat text-xs"></i> Murojaah
                                    </span>
                                @elseif($item->jenis_setoran === 'ujian_juziyah')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                                        <i class="ti ti-award text-xs"></i> Ujian Juz'iyah
                                    </span>
                                @else
                                    <span class="text-gray-500 text-xs font-medium">{{ $item->jenis_setoran_label ?? ucfirst($item->jenis_setoran) }}</span>
                                @endif
                            </td>

                            {{-- Kelancaran --}}
                            <td class="px-4 py-3">
                                @if($kConfig)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $kConfig['class'] }}">
                                        <i class="ti {{ $kConfig['icon'] }} text-xs"></i>
                                        {{ $kConfig['label'] }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            {{-- Catatan Ustadz --}}
                            <td class="px-4 py-3 max-w-[220px]">
                                @if($item->catatan_ustadz)
                                    <p class="text-xs text-gray-600 leading-relaxed">{{ $item->catatan_ustadz }}</p>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <i class="ti ti-book-off text-5xl"></i>
                                    <p class="text-sm font-medium">Belum ada setoran di periode ini</p>
                                    <p class="text-xs text-gray-300">
                                        {{ $bulanList[(int)$bulan] ?? '' }} {{ $tahun }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Summary Footer --}}
        @if($setoran->count() > 0)
        <div class="px-6 py-4 border-t border-gray-50 bg-gray-50/50">
            <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>
                    Total: <strong class="text-gray-700">{{ $setoran->count() }}</strong> setoran
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                    Ziyadah: <strong class="text-gray-700">{{ $setoran->where('jenis_setoran', 'ziyadah')->count() }}</strong>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-sky-400 inline-block"></span>
                    Murajaah: <strong class="text-gray-700">{{ $setoran->where('jenis_setoran', 'murajaah')->count() }}</strong>
                </span>
                <span class="flex items-center gap-1.5 ml-auto">
                    <i class="ti ti-bookmark text-amber-500"></i>
                    Total Ayat: <strong class="text-gray-700">
                        {{ $setoran->sum(fn($s) => ($s->ayat_akhir && $s->ayat_awal) ? abs($s->ayat_akhir - $s->ayat_awal) + 1 : 0) }}
                    </strong> ayat
                </span>
            </div>
        </div>
        @endif
    </div>

    {{-- Progress Section --}}
    @if($setoran->count() > 0)
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Kelancaran Distribution --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="ti ti-chart-bar text-emerald-500"></i>
                Distribusi Kelancaran
            </h3>
            @php
                $kelancaranLabels = [
                    'sangat_lancar' => ['Sangat Lancar', 'bg-emerald-400'],
                    'lancar'        => ['Lancar',        'bg-blue-400'],
                    'kurang_lancar' => ['Kurang Lancar', 'bg-yellow-400'],
                    'mengulang'     => ['Mengulang',     'bg-red-400'],
                ];
                $total = $setoran->count();
            @endphp
            <div class="space-y-3">
                @foreach($kelancaranLabels as $key => [$label, $barColor])
                    @php
                        $count = $setoran->where('kelancaran', $key)->count();
                        $pct   = $total > 0 ? round($count / $total * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-gray-600 font-medium">{{ $label }}</span>
                            <span class="text-xs text-gray-400">{{ $count }} ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Jenis Distribution --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="ti ti-chart-donut text-emerald-500"></i>
                Jenis Setoran Bulan Ini
            </h3>
            @php
                $ziyadahCount  = $setoran->where('jenis_setoran', 'ziyadah')->count();
                $murajaahCount = $setoran->where('jenis_setoran', 'murajaah')->count();
                $ujianCount    = $setoran->where('jenis_setoran', 'ujian_juziyah')->count();
                $ziyadahPct    = $total > 0 ? round($ziyadahCount / $total * 100) : 0;
                $murajaahPct   = $total > 0 ? round($murajaahCount / $total * 100) : 0;
                $ujianPct      = $total > 0 ? round($ujianCount / $total * 100) : 0;
            @endphp
            <div class="flex items-center justify-center gap-6 py-4 flex-wrap">
                {{-- Ziyadah circle --}}
                <div class="flex flex-col items-center gap-2">
                    <div class="relative w-20 h-20">
                        <svg class="w-20 h-20 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f0fdf4" stroke-width="3.8"/>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#10b981"
                                stroke-width="3.8"
                                stroke-dasharray="{{ $ziyadahPct }} {{ 100 - $ziyadahPct }}"
                                stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-sm font-bold text-gray-800">{{ $ziyadahPct }}%</span>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-semibold text-emerald-700">Ziyadah</p>
                        <p class="text-xs text-gray-400">{{ $ziyadahCount }} sesi</p>
                    </div>
                </div>

                {{-- Murojaah circle --}}
                <div class="flex flex-col items-center gap-2">
                    <div class="relative w-20 h-20">
                        <svg class="w-20 h-20 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#eff6ff" stroke-width="3.8"/>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#3b82f6"
                                stroke-width="3.8"
                                stroke-dasharray="{{ $murajaahPct }} {{ 100 - $murajaahPct }}"
                                stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-sm font-bold text-gray-800">{{ $murajaahPct }}%</span>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-semibold text-blue-700">Murojaah</p>
                        <p class="text-xs text-gray-400">{{ $murajaahCount }} sesi</p>
                    </div>
                </div>

                {{-- Ujian Juz'iyah circle --}}
                <div class="flex flex-col items-center gap-2">
                    <div class="relative w-20 h-20">
                        <svg class="w-20 h-20 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#faf5ff" stroke-width="3.8"/>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#9333ea"
                                stroke-width="3.8"
                                stroke-dasharray="{{ $ujianPct }} {{ 100 - $ujianPct }}"
                                stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-sm font-bold text-gray-800">{{ $ujianPct }}%</span>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-semibold text-purple-700">Ujian Juz'iyah</p>
                        <p class="text-xs text-gray-400">{{ $ujianCount }} sesi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
