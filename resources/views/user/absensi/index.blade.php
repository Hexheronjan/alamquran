<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <i class="ti ti-calendar-check text-2xl text-green-700"></i>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Absensi Kehadiran Santri
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. Santri Selector Pills --}}
            @if ($santris->count() > 1)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <p class="text-xs text-gray-500 font-medium mb-3 uppercase tracking-wide">Pilih Santri</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($santris as $s)
                            <a href="{{ route('user.absensi', ['santri_id' => $s->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium transition-all
                                      {{ $s->id == $selectedSantriId
                                            ? 'bg-green-600 text-white shadow-sm'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                <i class="ti ti-user text-base"></i>
                                {{ $s->nama_lengkap }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 2. Filter Month & Year --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-gray-800 text-base">
                        {{ $activeSantri ? $activeSantri->nama_lengkap : 'Santri' }}
                    </h3>
                    <p class="text-xs text-gray-500">Rekapitulasi kehadiran bulan {{ $bulanList[$bulan] ?? $bulan }} {{ $tahun }}</p>
                </div>
                <form method="GET" action="{{ route('user.absensi') }}" class="flex items-center gap-2">
                    <input type="hidden" name="santri_id" value="{{ $selectedSantriId }}">
                    <select name="bulan" class="border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-green-500 outline-none">
                        @foreach ($bulanList as $bNum => $bNama)
                            <option value="{{ $bNum }}" {{ $bNum == $bulan ? 'selected' : '' }}>{{ $bNama }}</option>
                        @endforeach
                    </select>
                    <select name="tahun" class="border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-green-500 outline-none">
                        @for ($y = date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-4 py-1.5 rounded-lg transition">
                        Filter
                    </button>
                </form>
            </div>

            {{-- 3. Rekap Badges Card --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 text-center">
                    <span class="text-xs text-gray-400 font-medium block">Total Hari</span>
                    <span class="text-xl font-bold text-gray-800">{{ $rekap['total'] }}</span>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-center">
                    <span class="text-xs text-green-600 font-medium block">Hadir</span>
                    <span class="text-xl font-bold text-green-700">{{ $rekap['hadir'] }}</span>
                </div>
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-center">
                    <span class="text-xs text-yellow-600 font-medium block">Sakit</span>
                    <span class="text-xl font-bold text-yellow-700">{{ $rekap['sakit'] }}</span>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-center">
                    <span class="text-xs text-blue-600 font-medium block">Izin</span>
                    <span class="text-xl font-bold text-blue-700">{{ $rekap['izin'] }}</span>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center col-span-2 sm:col-span-1">
                    <span class="text-xs text-red-600 font-medium block">Alpa</span>
                    <span class="text-xl font-bold text-red-700">{{ $rekap['alpa'] }}</span>
                </div>
            </div>

            {{-- 4. Absensi List Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100">
                    <i class="ti ti-list-check text-xl text-green-600"></i>
                    <h3 class="font-semibold text-gray-800 text-base">Riwayat Kehadiran Harian</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-5 py-3 text-left">Tanggal</th>
                                <th class="px-5 py-3 text-left">Status Kehadiran</th>
                                <th class="px-5 py-3 text-left">Keterangan / Catatan</th>
                                <th class="px-5 py-3 text-left">Guru / Ustadz</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($absensiList as $absensi)
                                @php
                                    $badgeStyle = match(strtolower($absensi->status_kehadiran)) {
                                        'hadir' => 'bg-green-100 text-green-700',
                                        'sakit' => 'bg-yellow-100 text-yellow-700',
                                        'izin'  => 'bg-blue-100 text-blue-700',
                                        'alpa'  => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3.5 text-gray-800 font-medium whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($absensi->tanggal)->translatedFormat('l, d F Y') }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize {{ $badgeStyle }}">
                                            {{ $absensi->status_kehadiran }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600">
                                        {{ $absensi->keterangan ?: '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-500 text-xs whitespace-nowrap">
                                        {{ $absensi->guru?->name ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center text-gray-400">
                                        <i class="ti ti-calendar-off text-4xl block mb-2"></i>
                                        <p class="font-medium text-gray-600">Belum ada catatan absensi</p>
                                        <p class="text-xs">Data absensi bulan {{ $bulanList[$bulan] ?? $bulan }} {{ $tahun }} belum tersedia.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
