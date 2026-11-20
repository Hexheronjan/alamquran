<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <i class="ti ti-book text-green-600"></i>
            Tahfidz Al-Qur'an
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-20">

            {{-- Santri Selector (if parent has multiple children) --}}
            @if($santris->count() > 1)
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wide block mb-2">Pilih Santri:</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach($santris as $s)
                            <a href="{{ route('user.tahfidz', ['santri_id' => $s->id]) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-bold transition
                                      {{ $selectedSantriId == $s->id
                                          ? 'text-white shadow-sm'
                                          : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                               @if($selectedSantriId == $s->id) style="background-color: #16a34a !important; color: #ffffff !important;" @endif>
                                {{ $s->nama_lengkap }}
                                @if($s->kelas) <span class="opacity-75">(Kelas {{ $s->kelas }})</span> @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($activeSantri)

                {{-- Santri Info Card --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-lg"
                             style="background-color: #dcfce7; color: #16a34a;">
                            <i class="ti ti-user-circle text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">{{ $activeSantri->nama_lengkap }}</h3>
                            <span class="text-xs text-gray-500 font-mono">
                                Kelas {{ $activeSantri->kelas ?? '-' }}
                                @if($activeSantri->nis) | NIS: {{ $activeSantri->nis }} @endif
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-green-700 bg-green-50 px-3 py-2 rounded-xl border border-green-100">
                        <i class="ti ti-book-open text-base"></i>
                        <span>Rekap Tahfidz</span>
                    </div>
                </div>

                {{-- Stats Cards --}}
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    {{-- Total Setoran --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <i class="ti ti-stack text-xl text-blue-500"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide leading-none mb-0.5">Total Setoran</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] ?? 0 }}</p>
                        </div>
                    </div>

                    {{-- Ziyadah --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                            <i class="ti ti-trending-up text-xl text-green-500"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide leading-none mb-0.5">Ziyadah</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $stats['ziyadah'] ?? 0 }}</p>
                        </div>
                    </div>

                    {{-- Murojaah --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-sky-50 flex items-center justify-center flex-shrink-0">
                            <i class="ti ti-repeat text-xl text-sky-500"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide leading-none mb-0.5">Murojaah</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $stats['murajaah'] ?? 0 }}</p>
                        </div>
                    </div>

                    {{-- Ujian Juz'iyah --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
                            <i class="ti ti-award text-xl text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide leading-none mb-0.5">Ujian Juz'iyah</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $stats['ujian_juziyah'] ?? 0 }}</p>
                        </div>
                    </div>

                    {{-- Total Ayat Dihafal --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                            <i class="ti ti-sparkles text-xl text-amber-500"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide leading-none mb-0.5">Total Ayat</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $stats['total_ayat'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                {{-- Filter Form --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                        <i class="ti ti-filter text-base text-gray-400"></i> Filter Data
                    </h3>
                    <form method="GET" action="{{ route('user.tahfidz') }}" class="flex flex-wrap gap-3 items-end">
                        {{-- Hidden santri_id --}}
                        <input type="hidden" name="santri_id" value="{{ $selectedSantriId }}">

                        {{-- Bulan --}}
                        <div class="flex flex-col gap-1 min-w-[140px]">
                            <label class="text-xs font-semibold text-gray-600">Bulan</label>
                            <select name="bulan"
                                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent">
                                @php
                                    $namaBulan = [
                                        1  => 'Januari',   2  => 'Februari', 3  => 'Maret',
                                        4  => 'April',     5  => 'Mei',      6  => 'Juni',
                                        7  => 'Juli',      8  => 'Agustus',  9  => 'September',
                                        10 => 'Oktober',   11 => 'November', 12 => 'Desember',
                                    ];
                                @endphp
                                @foreach($namaBulan as $num => $label)
                                    <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tahun --}}
                        <div class="flex flex-col gap-1 min-w-[100px]">
                            <label class="text-xs font-semibold text-gray-600">Tahun</label>
                            <input type="number" name="tahun" value="{{ $tahun }}"
                                   min="2020" max="2099" step="1"
                                   class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent">
                        </div>

                        {{-- Submit --}}
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 text-white text-sm font-bold px-4 py-2 rounded-xl shadow-sm transition cursor-pointer"
                                style="background-color: #16a34a !important; color: #ffffff !important;">
                            <i class="ti ti-filter"></i> Tampilkan
                        </button>
                    </form>
                </div>

                {{-- Setoran Table --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                            <i class="ti ti-list-details text-green-600"></i>
                            Riwayat Setoran
                        </h3>
                        <span class="text-xs text-gray-400">{{ $setorans->total() }} data</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-left">
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Tanggal</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Surah</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Ayat</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Juz</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jenis</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelancaran</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Catatan Ustadz</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($setorans as $setoran)
                                    <tr class="hover:bg-gray-50 transition-colors duration-100">
                                        {{-- Tanggal --}}
                                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap font-mono text-xs">
                                            {{ \Carbon\Carbon::parse($setoran->tanggal)->translatedFormat('d M Y') }}
                                        </td>

                                        {{-- Surah --}}
                                        <td class="px-4 py-3 font-semibold text-gray-800">
                                            {{ $setoran->nama_surah ?? ($setoran->surah->nama_latin ?? $setoran->surah->nama ?? '-') }}
                                        </td>

                                        {{-- Ayat --}}
                                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                            {{ $setoran->ayat_awal ?? '-' }} &ndash; {{ $setoran->ayat_akhir ?? '-' }}
                                        </td>

                                        {{-- Juz --}}
                                        <td class="px-4 py-3 text-gray-600 text-center font-mono">
                                            {{ $setoran->juz ?? '-' }}
                                        </td>

                                        {{-- Jenis --}}
                                        <td class="px-4 py-3">
                                            @if($setoran->jenis_setoran === 'ziyadah')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
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
                                                $kelancaranMap = [
                                                    'sangat_lancar' => ['label' => 'Sangat Lancar', 'class' => 'bg-green-100 text-green-700',  'icon' => 'ti-star'],
                                                    'lancar'        => ['label' => 'Lancar',        'class' => 'bg-blue-100 text-blue-700',    'icon' => 'ti-check'],
                                                    'kurang_lancar' => ['label' => 'Kurang Lancar', 'class' => 'bg-yellow-100 text-yellow-700','icon' => 'ti-alert-triangle'],
                                                    'mengulang'     => ['label' => 'Mengulang',     'class' => 'bg-red-100 text-red-700',      'icon' => 'ti-refresh-alert'],
                                                ];
                                                $kc = $kelancaranMap[$setoran->kelancaran] ?? null;
                                            @endphp
                                            @if($kc)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $kc['class'] }}">
                                                    <i class="ti {{ $kc['icon'] }} text-xs"></i>
                                                    {{ $kc['label'] }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>

                                        {{-- Catatan Ustadz --}}
                                        <td class="px-4 py-3 max-w-[180px]">
                                            @if($setoran->catatan_ustadz)
                                                <p class="text-xs text-gray-500 truncate" title="{{ $setoran->catatan_ustadz }}">
                                                    {{ $setoran->catatan_ustadz }}
                                                </p>
                                            @else
                                                <span class="text-gray-300 text-xs">&mdash;</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-16 text-center">
                                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                                <i class="ti ti-book-off text-5xl"></i>
                                                <p class="text-sm font-medium">Belum ada data setoran untuk periode ini</p>
                                                <p class="text-xs text-gray-300">Coba pilih bulan atau tahun yang berbeda</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($setorans->hasPages())
                        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
                            {{ $setorans->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>

                {{-- Info Box --}}
                <div class="rounded-xl border border-green-200 bg-green-50/70 p-4 text-xs text-green-900">
                    <div class="font-bold flex items-center gap-1.5 text-green-800 mb-1">
                        <i class="ti ti-info-circle text-base text-green-600"></i>
                        Informasi
                    </div>
                    <p>Data tahfidz otomatis tercatat dari catatan ustadz pengampu. Wali santri hanya dapat melihat data ini.</p>
                </div>

            @else
                {{-- No santri state --}}
                <div class="bg-white rounded-2xl p-12 text-center border border-gray-200 shadow-sm">
                    <div class="w-16 h-16 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3 text-3xl">
                        <i class="ti ti-book text-green-400"></i>
                    </div>
                    <p class="text-gray-600 font-semibold">Tidak ada santri yang terdaftar</p>
                    <p class="text-gray-400 text-xs mt-1">Hubungi pesantren untuk informasi lebih lanjut.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
