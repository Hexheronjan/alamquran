@extends('layouts.admin.app')

@section('title', 'Absensi Santri & Guru')

@section('content')
<div class="p-6 space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="ti ti-clipboard-check text-green-600 text-3xl"></i>
                Absensi Santri &amp; Guru
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Rekap kehadiran harian &mdash;
                <span class="font-medium text-gray-700">{{ $tanggal->translatedFormat('l, d F Y') }}</span>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.absensi.input') }}"
               class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-2 rounded-lg shadow transition"
               style="background-color: #16a34a !important; color: #ffffff !important;">
                <i class="ti ti-pencil-plus"></i>
                Input Absensi Harian
            </a>
            <a href="{{ route('admin.absensi.rekap') }}"
               class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-2 rounded-lg shadow transition"
               style="background-color: #16a34a !important; color: #ffffff !important;">
                <i class="ti ti-chart-bar"></i>
                Rekap Bulanan
            </a>
        </div>
    </div>

    {{-- Alert Session --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3">
            <i class="ti ti-circle-check text-green-500 text-xl shrink-0"></i>
            <span class="text-sm">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3">
            <i class="ti ti-alert-circle text-red-500 text-xl shrink-0"></i>
            <span class="text-sm">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('admin.absensi.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</label>
                <input type="date"
                       name="tanggal"
                       value="{{ $tanggal->format('Y-m-d') }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelas</label>
                <select name="kelas"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="all" {{ $kelas === 'all' ? 'selected' : '' }}>Semua Kelas</option>
                    <option value="1" {{ $kelas == '1' ? 'selected' : '' }}>Kelas 1</option>
                    <option value="2" {{ $kelas == '2' ? 'selected' : '' }}>Kelas 2</option>
                    <option value="3" {{ $kelas == '3' ? 'selected' : '' }}>Kelas 3</option>
                </select>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-bold px-4 py-2 rounded-lg transition"
                    style="background-color: #374151 !important; color: #ffffff !important;">
                <i class="ti ti-filter"></i>
                Terapkan Filter
            </button>
        </form>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        {{-- Hadir --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                <i class="ti ti-user-check text-green-600 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Hadir</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['hadir'] ?? 0 }}</p>
                <p class="text-xs text-gray-400">Santri</p>
            </div>
        </div>

        {{-- Sakit --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center shrink-0">
                <i class="ti ti-heart-broken text-yellow-600 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Sakit</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $stats['sakit'] ?? 0 }}</p>
                <p class="text-xs text-gray-400">Santri</p>
            </div>
        </div>

        {{-- Izin --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                <i class="ti ti-file-description text-blue-600 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Izin</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['izin'] ?? 0 }}</p>
                <p class="text-xs text-gray-400">Santri</p>
            </div>
        </div>

        {{-- Alpa --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <i class="ti ti-user-x text-red-600 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Alpa</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['alpa'] ?? 0 }}</p>
                <p class="text-xs text-gray-400">Santri</p>
            </div>
        </div>

        {{-- Guru Hadir --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                <i class="ti ti-school text-purple-600 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Guru Hadir</p>
                <p class="text-2xl font-bold text-purple-600">{{ $stats['guru_hadir'] ?? 0 }}</p>
                <p class="text-xs text-gray-400">Ustadz</p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ activeTab: 'santri' }" class="space-y-4">

        {{-- Tab Buttons --}}
        <div class="flex gap-2 border-b border-gray-200">
            <button @click="activeTab = 'santri'"
                    :class="activeTab === 'santri'
                        ? 'border-b-2 border-green-600 text-green-700 font-semibold'
                        : 'text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-2 px-4 py-3 text-sm transition">
                <i class="ti ti-users"></i>
                Absensi Santri Hari Ini
                <span class="ml-1 bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full">
                    {{ $absensiSantri->total() }}
                </span>
            </button>
            <button @click="activeTab = 'guru'"
                    :class="activeTab === 'guru'
                        ? 'border-b-2 border-purple-600 text-purple-700 font-semibold'
                        : 'text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-2 px-4 py-3 text-sm transition">
                <i class="ti ti-chalkboard"></i>
                Absensi Guru / Ustadz
                <span class="ml-1 bg-purple-100 text-purple-700 text-xs font-bold px-2 py-0.5 rounded-full">
                    {{ $absensiGuru->count() }}
                </span>
            </button>
        </div>

        {{-- Tab: Santri --}}
        <div x-show="activeTab === 'santri'" x-transition>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Santri</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kelas</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Catatan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Guru Pencatat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($absensiSantri as $index => $absensi)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-gray-500">
                                        {{ $absensiSantri->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                                                <i class="ti ti-user text-green-600 text-sm"></i>
                                            </div>
                                            <span class="font-medium text-gray-800">{{ $absensi->santri->nama_lengkap ?? $absensi->santri->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        <span class="bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1 rounded">
                                            Kelas {{ $absensi->santri->kelas ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php $status = $absensi->status_kehadiran; @endphp
                                        @if($status === 'hadir')
                                            <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                <i class="ti ti-circle-check text-sm"></i> Hadir
                                            </span>
                                        @elseif($status === 'sakit')
                                            <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                <i class="ti ti-heart-broken text-sm"></i> Sakit
                                            </span>
                                        @elseif($status === 'izin')
                                            <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                <i class="ti ti-file-description text-sm"></i> Izin
                                            </span>
                                        @elseif($status === 'alpa')
                                            <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                <i class="ti ti-x text-sm"></i> Alpa
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                &mdash;
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-sm max-w-xs truncate">
                                        {{ $absensi->catatan ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 text-sm">
                                        {{ $absensi->guru->nama_lengkap ?? $absensi->guru->name ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="ti ti-clipboard-off text-4xl text-gray-300"></i>
                                            <span class="text-sm">Belum ada data absensi untuk tanggal ini</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($absensiSantri->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                        {{ $absensiSantri->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Tab: Guru --}}
        <div x-show="activeTab === 'guru'" x-transition>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Ustadz</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jam Pulang</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($absensiGuru as $index => $absensiG)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                                                <i class="ti ti-chalkboard text-purple-600 text-sm"></i>
                                            </div>
                                            <span class="font-medium text-gray-800">{{ $absensiG->guru->nama_lengkap ?? $absensiG->guru->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="ti ti-clock-in text-green-500 text-sm"></i>
                                            {{ $absensiG->jam_masuk ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="ti ti-clock-out text-red-400 text-sm"></i>
                                            {{ $absensiG->jam_pulang ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php $statusG = $absensiG->status_kehadiran; @endphp
                                        @if($statusG === 'hadir')
                                            <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                <i class="ti ti-circle-check text-sm"></i> Hadir
                                            </span>
                                        @elseif($statusG === 'sakit')
                                            <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                <i class="ti ti-heart-broken text-sm"></i> Sakit
                                            </span>
                                        @elseif($statusG === 'izin')
                                            <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                <i class="ti ti-file-description text-sm"></i> Izin
                                            </span>
                                        @elseif($statusG === 'alpa')
                                            <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                <i class="ti ti-x text-sm"></i> Alpa
                                            </span>
                                        @else
                                            <span class="text-gray-400">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-sm max-w-xs truncate">
                                        {{ $absensiG->catatan ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="ti ti-clipboard-off text-4xl text-gray-300"></i>
                                            <span class="text-sm">Belum ada data absensi guru untuk tanggal ini</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>{{-- end x-data tabs --}}

</div>
@endsection
