@extends('layouts.admin.app')

@section('title', 'Rekap Absensi Bulanan')

@section('content')
<div class="p-6 space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="ti ti-chart-bar text-blue-600 text-3xl"></i>
                Rekap Absensi Bulanan
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                @php
                    $namaBulan = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                        4 => 'April', 5 => 'Mei', 6 => 'Juni',
                        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                        10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                @endphp
                Kelas {{ $kelas }} &mdash;
                <span class="font-medium text-gray-700">{{ $namaBulan[$bulan] ?? '-' }} {{ $tahun }}</span>
            </p>
        </div>
        <a href="{{ route('admin.absensi.index') }}"
           class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="ti ti-arrow-left"></i>
            Kembali ke Absensi
        </a>
    </div>

    {{-- Alert Session --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3">
            <i class="ti ti-circle-check text-green-500 text-xl shrink-0"></i>
            <span class="text-sm">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filter Form --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('admin.absensi.rekap') }}" class="flex flex-wrap items-end gap-4">

            {{-- Bulan --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Bulan</label>
                <select name="bulan"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach($namaBulan as $num => $nama)
                        <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tahun --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tahun</label>
                <input type="number"
                       name="tahun"
                       value="{{ $tahun }}"
                       min="2020"
                       max="2099"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 w-24 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            {{-- Kelas --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelas</label>
                <select name="kelas"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="1" {{ $kelas == '1' ? 'selected' : '' }}>Kelas 1</option>
                    <option value="2" {{ $kelas == '2' ? 'selected' : '' }}>Kelas 2</option>
                    <option value="3" {{ $kelas == '3' ? 'selected' : '' }}>Kelas 3</option>
                </select>
            </div>

            <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="ti ti-filter"></i>
                Tampilkan Rekap
            </button>
        </form>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap items-center gap-3">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide mr-2">Keterangan:</span>
        <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">
            <span class="w-4 h-4 rounded bg-green-500 text-white flex items-center justify-center text-xs font-bold">H</span>
            Hadir
        </span>
        <span class="inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1 rounded-full">
            <span class="w-4 h-4 rounded bg-yellow-400 text-white flex items-center justify-center text-xs font-bold">S</span>
            Sakit
        </span>
        <span class="inline-flex items-center gap-1.5 bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">
            <span class="w-4 h-4 rounded bg-blue-500 text-white flex items-center justify-center text-xs font-bold">I</span>
            Izin
        </span>
        <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full">
            <span class="w-4 h-4 rounded bg-red-500 text-white flex items-center justify-center text-xs font-bold">A</span>
            Alpa
        </span>
        <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-500 text-xs font-bold px-3 py-1 rounded-full">
            <span class="w-4 h-4 rounded bg-gray-300 text-white flex items-center justify-center text-xs font-bold">&mdash;</span>
            Tidak Ada Data
        </span>
    </div>

    {{-- Rekap Table --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="text-xs border-collapse min-w-full">
                <thead>
                    <tr class="bg-gray-800 text-white">
                        {{-- Santri Column Header --}}
                        <th class="sticky left-0 z-20 bg-gray-800 px-4 py-3 text-left font-semibold whitespace-nowrap min-w-[180px] border-r border-gray-700">
                            <div class="flex items-center gap-2">
                                <i class="ti ti-users text-gray-300"></i>
                                Nama Santri
                            </div>
                        </th>
                        {{-- Day Headers --}}
                        @foreach($dates as $date)
                            @php
                                $dayNum = \Carbon\Carbon::parse($date)->day;
                                $dayName = \Carbon\Carbon::parse($date)->translatedFormat('D');
                                $isWeekend = \Carbon\Carbon::parse($date)->isWeekend();
                            @endphp
                            <th class="px-1.5 py-3 text-center font-semibold border-r border-gray-700 min-w-[36px] {{ $isWeekend ? 'bg-gray-700' : '' }}">
                                <div class="flex flex-col items-center">
                                    <span class="text-gray-300 text-[10px] leading-none">{{ $dayName }}</span>
                                    <span class="text-white font-bold">{{ $dayNum }}</span>
                                </div>
                            </th>
                        @endforeach
                        {{-- Summary Headers --}}
                        <th class="px-3 py-3 text-center font-semibold bg-green-700 border-r border-gray-700 min-w-[40px]">H</th>
                        <th class="px-3 py-3 text-center font-semibold bg-yellow-600 border-r border-gray-700 min-w-[40px]">S</th>
                        <th class="px-3 py-3 text-center font-semibold bg-blue-700 border-r border-gray-700 min-w-[40px]">I</th>
                        <th class="px-3 py-3 text-center font-semibold bg-red-700 min-w-[40px]">A</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        // Grand totals
                        $grandTotals = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                    @endphp

                    @forelse($santriList as $idx => $santri)
                        @php
                            $santriAbsensi = $absensiData[$santri->id] ?? collect([]);
                            $totals = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                        @endphp
                        <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition">
                            {{-- Santri Name --}}
                            <td class="sticky left-0 z-10 {{ $idx % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 px-4 py-2 font-medium text-gray-800 whitespace-nowrap border-r border-gray-200 min-w-[180px]">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0 uppercase">
                                        {{ substr($santri->nama_lengkap ?? $santri->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="truncate max-w-[130px]" title="{{ $santri->nama_lengkap ?? $santri->name ?? '-' }}">
                                        {{ $santri->nama_lengkap ?? $santri->name ?? '-' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Day Cells --}}
                            @foreach($dates as $date)
                                @php
                                    $record = $santriAbsensi->first(function($item) use ($date) {
                                        $t = $item->tanggal instanceof \Carbon\Carbon ? $item->tanggal->format('Y-m-d') : substr((string)$item->tanggal, 0, 10);
                                        return $t === $date;
                                    });
                                    $status = $record?->status_kehadiran;
                                    $isWeekend = \Carbon\Carbon::parse($date)->isWeekend();
                                    if ($status && array_key_exists($status, $totals)) {
                                        $totals[$status]++;
                                    }
                                @endphp
                                <td class="px-1 py-2 text-center border-r border-gray-100 {{ $isWeekend ? 'bg-gray-100' : '' }}">
                                    @if($status === 'hadir')
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-green-500 text-white font-bold text-[10px]">H</span>
                                    @elseif($status === 'sakit')
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-yellow-400 text-white font-bold text-[10px]">S</span>
                                    @elseif($status === 'izin')
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-blue-500 text-white font-bold text-[10px]">I</span>
                                    @elseif($status === 'alpa')
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-red-500 text-white font-bold text-[10px]">A</span>
                                    @else
                                        <span class="text-gray-300 text-[10px]">&mdash;</span>
                                    @endif
                                </td>
                            @endforeach

                            {{-- Per-Santri Totals --}}
                            @php
                                $grandTotals['hadir'] += $totals['hadir'];
                                $grandTotals['sakit'] += $totals['sakit'];
                                $grandTotals['izin']  += $totals['izin'];
                                $grandTotals['alpa']  += $totals['alpa'];
                            @endphp
                            <td class="px-2 py-2 text-center font-bold text-green-700 bg-green-50 border-r border-gray-200">{{ $totals['hadir'] }}</td>
                            <td class="px-2 py-2 text-center font-bold text-yellow-700 bg-yellow-50 border-r border-gray-200">{{ $totals['sakit'] }}</td>
                            <td class="px-2 py-2 text-center font-bold text-blue-700 bg-blue-50 border-r border-gray-200">{{ $totals['izin'] }}</td>
                            <td class="px-2 py-2 text-center font-bold text-red-700 bg-red-50">{{ $totals['alpa'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($dates) + 5 }}" class="px-4 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="ti ti-users-off text-4xl text-gray-300"></i>
                                    <span class="text-sm">Tidak ada data santri untuk kelas ini</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- Summary / Total Row --}}
                @if(count($santriList) > 0)
                <tfoot>
                    <tr class="bg-gray-800 text-white font-semibold text-xs">
                        <td class="sticky left-0 z-10 bg-gray-800 px-4 py-3 border-r border-gray-700 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <i class="ti ti-sum text-gray-300"></i>
                                Total Per Hari
                            </div>
                        </td>
                        @foreach($dates as $date)
                            @php
                                $dayTotals = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                                foreach ($santriList as $s) {
                                    $sAbsensi = $absensiData[$s->id] ?? collect([]);
                                    $rec = $sAbsensi->first(function($item) use ($date) {
                                        $t = $item->tanggal instanceof \Carbon\Carbon ? $item->tanggal->format('Y-m-d') : substr((string)$item->tanggal, 0, 10);
                                        return $t === $date;
                                    });
                                    $st = $rec?->status_kehadiran;
                                    if ($st && array_key_exists($st, $dayTotals)) {
                                        $dayTotals[$st]++;
                                    }
                                }
                                $isWeekend = \Carbon\Carbon::parse($date)->isWeekend();
                            @endphp
                            <td class="px-1 py-2 text-center border-r border-gray-700 {{ $isWeekend ? 'bg-gray-700' : '' }}">
                                <div class="flex flex-col items-center gap-0.5">
                                    @if($dayTotals['hadir'] > 0)
                                        <span class="text-green-400 text-[10px] leading-none">{{ $dayTotals['hadir'] }}</span>
                                    @endif
                                    @if($dayTotals['sakit'] > 0)
                                        <span class="text-yellow-300 text-[10px] leading-none">{{ $dayTotals['sakit'] }}</span>
                                    @endif
                                    @if($dayTotals['izin'] > 0)
                                        <span class="text-blue-300 text-[10px] leading-none">{{ $dayTotals['izin'] }}</span>
                                    @endif
                                    @if($dayTotals['alpa'] > 0)
                                        <span class="text-red-400 text-[10px] leading-none">{{ $dayTotals['alpa'] }}</span>
                                    @endif
                                    @if($dayTotals['hadir'] === 0 && $dayTotals['sakit'] === 0 && $dayTotals['izin'] === 0 && $dayTotals['alpa'] === 0)
                                        <span class="text-gray-500 text-[10px]">&mdash;</span>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                        {{-- Grand Totals --}}
                        <td class="px-2 py-3 text-center text-green-400 bg-green-900 border-r border-gray-700">{{ $grandTotals['hadir'] }}</td>
                        <td class="px-2 py-3 text-center text-yellow-300 bg-yellow-900 border-r border-gray-700">{{ $grandTotals['sakit'] }}</td>
                        <td class="px-2 py-3 text-center text-blue-300 bg-blue-900 border-r border-gray-700">{{ $grandTotals['izin'] }}</td>
                        <td class="px-2 py-3 text-center text-red-300 bg-red-900">{{ $grandTotals['alpa'] }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- Table Footer Info --}}
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
            <span class="flex items-center gap-1">
                <i class="ti ti-info-circle text-blue-400"></i>
                Scroll ke kanan untuk melihat semua tanggal dalam bulan ini.
            </span>
            <span>
                Total {{ count($santriList) }} santri &middot; {{ $daysInMonth }} hari
            </span>
        </div>
    </div>

    {{-- Print Button --}}
    <div class="flex justify-end">
        <button onclick="window.print()"
                class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="ti ti-printer"></i>
            Cetak Rekap
        </button>
    </div>

</div>

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        body { font-size: 10px; }
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
    }
</style>
@endpush

@endsection
