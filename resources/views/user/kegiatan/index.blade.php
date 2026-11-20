<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-green-700">
                <i class="ti ti-calendar-time text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Jadwal Kegiatan Santri 24 Jam</h2>
                <p class="text-xs text-gray-500">Pantau rutinitas harian santri mulai dari bangun tidur hingga istirahat malam</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-20">

            {{-- 1. PILIH SANTRI / ANAK --}}
            @if ($santris->count() > 1)
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="ti ti-user-check text-green-600"></i>
                            <span>Pilih Santri / Anak:</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($santris as $s)
                                <a href="{{ route('user.kegiatan', ['tanggal' => $selectedDate->toDateString(), 'santri_id' => $s->id, 'kelas' => $s->calculated_kelas]) }}"
                                   class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 border {{ $selectedSantriId == $s->id ? 'bg-green-600 text-white border-green-600 shadow-sm' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                                    <i class="ti ti-user"></i>
                                    <span>{{ $s->nama_lengkap }}</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md {{ $selectedSantriId == $s->id ? 'bg-green-700 text-white' : 'bg-gray-200 text-gray-700' }}">
                                        Kelas {{ $s->calculated_kelas ?? '1' }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @elseif ($santris->count() === 1)
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                            {{ strtoupper(substr($santris->first()->nama_lengkap, 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-800">{{ $santris->first()->nama_lengkap }}</div>
                            <div class="text-xs text-gray-500">Kelas {{ $santris->first()->calculated_kelas ?? '1' }} • Jadwal disesuaikan otomatis</div>
                        </div>
                    </div>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Santri Aktif
                    </span>
                </div>
            @endif

            {{-- 2. NAVIGASI PEKAN & PEMILIH TANGGAL KALENDER --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="ti ti-calendar text-green-600 text-lg"></i>
                        <span class="font-bold text-gray-800 text-sm">Pilih Hari & Tanggal:</span>
                        <span class="text-xs text-gray-500 hidden sm:inline">• Klik hari atau pilih tanggal di kalender</span>
                    </div>

                    {{-- Kontrol Navigasi Tanggal & Kalender Modern --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="inline-flex items-center rounded-xl border border-gray-200 bg-gray-50/80 p-0.5 shadow-xs">
                            <a href="{{ route('user.kegiatan', ['tanggal' => $prevWeekDate, 'santri_id' => $selectedSantriId, 'kelas' => $selectedKelas]) }}"
                               class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-gray-700 hover:bg-white hover:text-gray-900 hover:shadow-xs transition flex items-center gap-1"
                               title="Lihat Minggu Sebelumnya">
                                <i class="ti ti-chevron-left text-sm"></i>
                                <span class="hidden sm:inline">Sebelumnya</span>
                            </a>

                            <a href="{{ route('user.kegiatan', ['tanggal' => $todayDateStr, 'santri_id' => $selectedSantriId, 'kelas' => $selectedKelas]) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $isViewingToday ? 'bg-emerald-600 text-white shadow-xs' : 'text-gray-700 hover:bg-white hover:text-emerald-700 hover:shadow-xs' }}"
                               title="Kembali ke Jadwal Hari Ini">
                                Hari Ini
                            </a>

                            <a href="{{ route('user.kegiatan', ['tanggal' => $nextWeekDate, 'santri_id' => $selectedSantriId, 'kelas' => $selectedKelas]) }}"
                               class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-gray-700 hover:bg-white hover:text-gray-900 hover:shadow-xs transition flex items-center gap-1"
                               title="Lihat Minggu Berikutnya">
                                <span class="hidden sm:inline">Berikutnya</span>
                                <i class="ti ti-chevron-right text-sm"></i>
                            </a>
                        </div>

                        {{-- Input Kalender Bebas --}}
                        <div class="relative flex items-center">
                            <input type="date"
                                   id="inputTanggalKalender"
                                   value="{{ $selectedDate->toDateString() }}"
                                   onchange="pilihTanggal(this.value)"
                                   title="Pilih tanggal spesifik dari kalender"
                                   class="text-xs border border-gray-200 bg-white rounded-xl px-3 py-1.5 text-gray-700 font-medium focus:ring-2 focus:ring-green-500 focus:border-transparent shadow-xs cursor-pointer hover:border-gray-300">
                        </div>
                    </div>
                </div>

                {{-- Tab 7 Hari Sepekan dengan Tanggal (Pasti Horizontal) --}}
                <div class="overflow-x-auto pb-1 -mx-1 px-1">
                    <div class="flex items-stretch gap-2 min-w-[620px]" style="display: flex !important; flex-direction: row !important; width: 100%;">
                        @foreach ($weekDays as $wDay)
                            @php
                                $isSelected = $wDay['is_selected'];
                                $isTodayDay = $wDay['is_today'];
                            @endphp
                            <a href="{{ route('user.kegiatan', ['tanggal' => $wDay['date'], 'santri_id' => $selectedSantriId, 'kelas' => $selectedKelas]) }}"
                               class="flex-1 py-2.5 px-2 rounded-xl text-center transition-all border flex flex-col items-center justify-center {{ $isSelected ? 'bg-green-600 text-white border-green-600 shadow-md font-bold' : ($isTodayDay ? 'bg-green-50/70 text-green-800 border-green-300 font-semibold hover:bg-green-100' : 'bg-gray-50/80 text-gray-700 border-gray-200 hover:bg-gray-100') }}"
                               style="flex: 1 1 0%; min-width: 80px; display: flex !important;">
                                <span class="text-xs sm:text-sm font-bold leading-tight">{{ $wDay['name'] }}</span>
                                <span class="text-[11px] mt-0.5 {{ $isSelected ? 'text-green-100' : 'text-gray-500' }}">
                                    {{ $wDay['formatted'] }}
                                </span>
                                @if ($isTodayDay)
                                    <span class="mt-1 px-1.5 py-0.5 rounded-full text-[9px] font-bold leading-none {{ $isSelected ? 'bg-white text-green-700' : 'bg-emerald-600 text-white' }}">
                                        Hari Ini
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 3. JADWAL KEGIATAN UNTUK HARI & TANGGAL TERPILIH --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50/70">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <i class="ti ti-clock text-green-600 text-lg"></i>
                            <h3 class="font-bold text-gray-800 text-base">
                                Rutinitas • {{ $selectedDayLabel }}, {{ $selectedDate->translatedFormat('d F Y') }}
                            </h3>
                            @if ($isViewingToday)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                    SEDANG BERLANGSUNG HARI INI
                                </span>
                            @elseif ($selectedDate->isPast())
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600">
                                    Riwayat Jadwal Lalu
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-blue-50 text-blue-700">
                                    Jadwal Mendatang
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Menampilkan seluruh jadwal kegiatan santri dari waktu subuh hingga malam hari
                            @if ($selectedKelas)
                                (Menyesuaikan Kelas {{ $selectedKelas }})
                            @endif
                        </p>
                    </div>

                    @if ($isViewingToday)
                        <div class="text-xs text-gray-500 flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Kotak bergaris hijau menandakan jam yang sedang berjalan</span>
                        </div>
                    @endif
                </div>

                <div class="p-5">
                    @if ($jadwalKegiatan->isEmpty())
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                <i class="ti ti-calendar-off text-2xl text-gray-400"></i>
                            </div>
                            <h4 class="font-semibold text-gray-700 text-sm">Belum ada jadwal kegiatan untuk {{ $selectedDayLabel }}, {{ $selectedDate->translatedFormat('d F Y') }}</h4>
                            <p class="text-xs text-gray-500 mt-1 max-w-sm">Jadwal kegiatan 24 jam belum ditambahkan oleh administrator untuk hari ini.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($jadwalKegiatan as $kegiatan)
                                @php
                                    $isCurrentlyActive = ($isViewingToday && $currentActivityId === $kegiatan->id);
                                    $isAgendaKhusus = (!$kegiatan->is_rutin_24jam);
                                @endphp
                                <div class="rounded-xl p-4 transition-all border {{ $isCurrentlyActive ? 'bg-emerald-50/60 border-emerald-400 shadow-sm ring-2 ring-emerald-300/40' : ($isAgendaKhusus ? 'bg-blue-50/40 border-blue-200' : 'bg-white border-gray-200 hover:border-gray-300 hover:shadow-sm') }}">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold {{ $isCurrentlyActive ? 'bg-emerald-600 text-white' : ($isAgendaKhusus ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700') }}">
                                                <i class="ti ti-clock"></i>
                                                <span>
                                                    {{ \Carbon\Carbon::parse($kegiatan->waktu_mulai)->format('H:i') }}
                                                    –
                                                    {{ \Carbon\Carbon::parse($kegiatan->waktu_selesai)->format('H:i') }}
                                                    WIB
                                                </span>
                                            </div>

                                            @if ($isCurrentlyActive)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500 text-white animate-pulse">
                                                    ● Sedang Berlangsung Sekarang
                                                </span>
                                            @endif

                                            @if ($isAgendaKhusus)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                                                    <i class="ti ti-star text-xs"></i> Agenda Khusus Hari Ini
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                            {{-- Badge Hari / Rutin --}}
                                            @if ($kegiatan->is_rutin_24jam)
                                                @if ($kegiatan->hari === 'semua')
                                                    <span class="px-2 py-0.5 rounded-md font-medium bg-gray-100 text-gray-600">
                                                        Setiap Hari
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-md font-medium bg-emerald-50 text-emerald-700">
                                                        Rutin Tiap {{ $kegiatan->hari_label }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="px-2 py-0.5 rounded-md font-medium bg-blue-100 text-blue-700">
                                                    Khusus Tanggal Ini
                                                </span>
                                            @endif

                                            {{-- Badge Kelas --}}
                                            @if ($kegiatan->kelas === 'semua')
                                                <span class="px-2 py-0.5 rounded-md font-medium bg-gray-100 text-gray-600">
                                                    Semua Kelas
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-md font-semibold bg-indigo-50 text-indigo-700">
                                                    {{ $kegiatan->kelas_label }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="font-bold text-gray-800 text-base {{ $isCurrentlyActive ? 'text-emerald-900' : '' }}">
                                            {{ $kegiatan->nama_kegiatan }}
                                        </h4>
                                        @if ($kegiatan->deskripsi)
                                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                                {{ $kegiatan->deskripsi }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- 4. KEGIATAN KHUSUS MENDATANG (EVENT / AGENDA KHUSUS) --}}
            @if ($kegiatanMendatang->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-blue-50/50">
                        <i class="ti ti-star text-blue-600 text-lg"></i>
                        <div>
                            <h3 class="font-bold text-gray-800 text-base">Agenda & Kegiatan Khusus Mendatang</h3>
                            <p class="text-xs text-gray-500">Jadwal kegiatan penting santri di tanggal-tanggal mendatang</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-left">
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kegiatan</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelas</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($kegiatanMendatang as $km)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-5 py-3 text-gray-700 whitespace-nowrap font-medium">
                                            <a href="{{ route('user.kegiatan', ['tanggal' => $km->tanggal_khusus ? $km->tanggal_khusus->toDateString() : '', 'santri_id' => $selectedSantriId, 'kelas' => $selectedKelas]) }}"
                                               class="text-blue-600 hover:underline flex items-center gap-1 font-semibold">
                                                <i class="ti ti-calendar-event"></i>
                                                {{ $km->tanggal_khusus ? $km->tanggal_khusus->translatedFormat('d M Y') : '-' }}
                                            </a>
                                        </td>
                                        <td class="px-5 py-3 text-gray-600 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($km->waktu_mulai)->format('H:i') }} – {{ \Carbon\Carbon::parse($km->waktu_selesai)->format('H:i') }} WIB
                                        </td>
                                        <td class="px-5 py-3 font-semibold text-gray-800">{{ $km->nama_kegiatan }}</td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $km->kelas === 'semua' ? 'bg-gray-100 text-gray-600' : 'bg-indigo-50 text-indigo-700' }}">
                                                {{ $km->kelas_label }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-gray-500 text-xs">
                                            {{ $km->deskripsi ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <script>
        function pilihTanggal(val) {
            if (!val) return;
            const url = new URL(window.location.href);
            url.searchParams.set('tanggal', val);
            url.searchParams.delete('hari'); // Utamakan param tanggal
            window.location.href = url.toString();
        }
    </script>
    @endpush
</x-app-layout>
