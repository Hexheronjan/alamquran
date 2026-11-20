<x-app-layout>
    <style>
        /* Paksa grid 7 kolom selalu horizontal di semua browser & CSS framework */
        .cal-grid-7 {
            display: grid !important;
            grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
            gap: 6px !important;
        }
        .cal-cell-large {
            min-height: 105px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 6px 8px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            position: relative;
        }
        .cal-cell-large:hover {
            border-color: #0d9488;
            box-shadow: 0 4px 14px rgba(13, 148, 136, 0.15);
            transform: translateY(-1px);
            z-index: 10;
        }
        @media (max-width: 640px) {
            .cal-grid-7 {
                gap: 3px !important;
            }
            .cal-cell-large {
                min-height: 48px !important;
                height: auto !important;
                border-radius: 8px !important;
                padding: 4px 2px !important;
                justify-content: flex-start !important;
                align-items: center !important;
            }
        }
        .cal-cell-mini {
            height: 38px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            font-weight: 700;
            font-size: 11px;
            transition: all 0.12s;
            border: 1px solid transparent;
        }
        .cal-cell-mini:hover {
            border-color: #0d9488;
            transform: scale(1.06);
        }
        .month-tab-wali.active {
            background-color: #0d9488 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(13, 148, 136, 0.25);
        }
        .scrollbar-thin::-webkit-scrollbar {
            height: 6px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 10px;
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-black text-xl text-gray-800 leading-tight flex items-center gap-2">
                    <i class="ti ti-calendar-event text-teal-600"></i> Kalender Akademik Pesantren
                </h2>
                <p class="text-xs text-gray-500 mt-1">Jadwal resmi kegiatan santri tahun ajaran (Juli - Juni): Ujian, Liburan, Sambangan, dan Agenda Pondok.</p>
            </div>
            <form method="GET" action="{{ route('user.kalender.index') }}" class="inline-flex items-center gap-2">
                <label class="text-xs font-bold text-gray-600">Tahun Ajaran:</label>
                <select name="tahun_ajaran" onchange="this.form.submit()" class="px-3 py-1.5 border border-teal-300 rounded-xl text-xs font-bold text-teal-900 bg-teal-50 focus:ring-2 focus:ring-teal-500">
                    @foreach($tahunAjaranList as $th)
                        <option value="{{ $th }}" {{ $selectedTahunAjaran === $th ? 'selected' : '' }}>{{ $th }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Info -->
            <div class="rounded-3xl p-6 shadow-md relative overflow-hidden"
                 style="background: linear-gradient(135deg, #0f766e 0%, #115e59 50%, #064e3b 100%) !important; color: #ffffff !important;">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold mb-2.5"
                              style="background: rgba(255, 255, 255, 0.2) !important; color: #f0fdfa !important; border: 1px solid rgba(255, 255, 255, 0.3) !important;">
                            <i class="ti ti-school"></i> Tahun Ajaran {{ $selectedTahunAjaran }}
                        </span>
                        <h3 class="text-xl font-black tracking-tight" style="color: #ffffff !important;">Agenda Resmi Pondok Pesantren Alam Qur'an</h3>
                        <p class="text-xs mt-1.5 max-w-2xl leading-relaxed" style="color: #ccfbf1 !important;">
                            Pantau jadwal libur kepulangan santri, pelaksanaan ujian PAS/PAT, kedatangan ke asrama, serta jadwal sambangan wali santri agar keluarga dapat mempersiapkan agenda dengan tepat.
                        </p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="px-5 py-3 rounded-2xl text-center"
                             style="background: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                            <div class="text-2xl font-black" style="color: #ffffff !important;">{{ $events->count() }}</div>
                            <div class="text-[10px] uppercase font-bold" style="color: #99f6e4 !important;">Total Agenda</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kategori Badges Legend -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-100 flex flex-wrap items-center gap-2 text-xs">
                <span class="font-bold text-gray-500 uppercase text-[11px] mr-1">Keterangan Warna:</span>
                @foreach($kategoriList as $k => $info)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border"
                          style="background-color: {{ $info['warna'] }}15; color: {{ $info['warna'] }}; border-color: {{ $info['warna'] }}40;">
                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $info['warna'] }};"></span>
                        {{ $info['label'] }}
                    </span>
                @endforeach
            </div>

            <!-- Horizontal Month Selector Tab Bar & Mode Switcher -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-100 space-y-3">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ti ti-calendar text-teal-600"></i> Pilih Bulan (Juli - Juni):
                    </span>
                    <!-- View Toggle -->
                    <div class="inline-flex rounded-xl p-1 bg-gray-100 border border-gray-200">
                        <button type="button" onclick="switchWaliViewMode('single')" id="btnWaliSingle"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-teal-600 text-white shadow-xs">
                            <i class="ti ti-layout-distribute-vertical"></i> Tampilan Penuh (Maksimal)
                        </button>
                        <button type="button" onclick="switchWaliViewMode('multi')" id="btnWaliMulti"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-gray-600 hover:text-gray-900">
                            <i class="ti ti-layout-grid"></i> Ringkasan 12 Bulan
                        </button>
                    </div>
                </div>

                <!-- Horizontal Tabs for All 12 Months -->
                @php
                    $currentM = (int) date('n');
                    $currentY = (int) date('Y');
                    $defaultActiveIdx = 0;
                    foreach($monthsAcademic as $mIdx => $mObj) {
                        if ($mObj['month'] == $currentM && $mObj['year'] == $currentY) {
                            $defaultActiveIdx = $mIdx;
                            break;
                        }
                    }
                @endphp
                <div class="overflow-x-auto scrollbar-thin pb-1">
                    <div class="flex items-center gap-2 min-w-max">
                        @foreach($monthsAcademic as $idx => $mInfo)
                            <button type="button" onclick="selectWaliMonthTab({{ $idx }})" id="waliMonthTab_{{ $idx }}"
                                    class="month-tab-wali px-3.5 py-2 rounded-xl text-xs font-bold transition border border-gray-200 bg-gray-50 text-gray-700 hover:bg-teal-50 hover:text-teal-700 {{ $idx === $defaultActiveIdx ? 'active' : '' }}">
                                {{ $mInfo['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- 1. TAMPILAN BULAN PENUH (HORIZONTAL & MAKSIMAL) -->
            <div id="sectionWaliSingleMonth">
                @foreach($monthsAcademic as $idx => $mInfo)
                    @php
                        $m = $mInfo['month'];
                        $y = $mInfo['year'];
                        $firstDay = \Carbon\Carbon::createFromDate($y, $m, 1);
                        $daysInMonth = $firstDay->daysInMonth;
                        $sundayOffset = $firstDay->dayOfWeek; // 0 for Sunday

                        $mStart = sprintf('%04d-%02d-01', $y, $m);
                        $mEnd = sprintf('%04d-%02d-%02d', $y, $m, $daysInMonth);
                        $monthEvents = $events->filter(function($e) use ($mStart, $mEnd) {
                            $start = $e->tanggal_mulai ? $e->tanggal_mulai->format('Y-m-d') : '';
                            $end = $e->tanggal_selesai ? $e->tanggal_selesai->format('Y-m-d') : '';
                            return $start <= $mEnd && $end >= $mStart;
                        });
                    @endphp

                    <div id="waliMonthBox_{{ $idx }}" class="wali-month-box {{ $idx === $defaultActiveIdx ? '' : 'hidden' }}">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <!-- Header Bar -->
                            <div class="p-3.5 sm:p-5 rounded-t-3xl text-white flex items-center justify-between gap-2"
                                 style="background: linear-gradient(135deg, #0f766e 0%, #134e4a 100%) !important; color: #ffffff !important;">
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <button type="button" onclick="prevWaliMonthTab({{ $idx }})" class="p-1.5 sm:p-2 rounded-xl bg-white/10 hover:bg-white/20 transition" title="Bulan Sebelumnya">
                                        <i class="ti ti-chevron-left text-base sm:text-lg"></i>
                                    </button>
                                    <div>
                                        <span class="text-[9px] sm:text-[10px] uppercase font-bold tracking-widest text-teal-200" style="color: #99f6e4 !important;">TA {{ $selectedTahunAjaran }}</span>
                                        <h3 class="text-base sm:text-xl font-black" style="color: #ffffff !important;">{{ $mInfo['label'] }}</h3>
                                    </div>
                                    <button type="button" onclick="nextWaliMonthTab({{ $idx }})" class="p-1.5 sm:p-2 rounded-xl bg-white/10 hover:bg-white/20 transition" title="Bulan Berikutnya">
                                        <i class="ti ti-chevron-right text-base sm:text-lg"></i>
                                    </button>
                                </div>
                                <span class="px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-xl text-[11px] sm:text-xs font-bold"
                                      style="background: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                                    <i class="ti ti-calendar-event"></i> {{ $monthEvents->count() }} <span class="hidden sm:inline">Kegiatan</span>
                                </span>
                            </div>

                            <!-- 7-Column Day Header (Horizontal) -->
                            <div class="p-2 sm:p-4 bg-teal-50/50 border-b border-teal-100">
                                <div class="cal-grid-7 text-center font-black text-[10px] sm:text-xs uppercase tracking-wider text-gray-600">
                                    <div class="py-1.5 sm:py-2 text-red-600 bg-red-100/60 rounded-lg sm:rounded-xl"><span class="hidden sm:inline">Minggu</span><span class="sm:hidden">Min</span></div>
                                    <div class="py-1.5 sm:py-2 bg-gray-100/80 rounded-lg sm:rounded-xl"><span class="hidden sm:inline">Senin</span><span class="sm:hidden">Sen</span></div>
                                    <div class="py-1.5 sm:py-2 bg-gray-100/80 rounded-lg sm:rounded-xl"><span class="hidden sm:inline">Selasa</span><span class="sm:hidden">Sel</span></div>
                                    <div class="py-1.5 sm:py-2 bg-gray-100/80 rounded-lg sm:rounded-xl"><span class="hidden sm:inline">Rabu</span><span class="sm:hidden">Rab</span></div>
                                    <div class="py-1.5 sm:py-2 bg-gray-100/80 rounded-lg sm:rounded-xl"><span class="hidden sm:inline">Kamis</span><span class="sm:hidden">Kam</span></div>
                                    <div class="py-1.5 sm:py-2 text-teal-700 bg-teal-100/60 rounded-lg sm:rounded-xl"><span class="hidden sm:inline">Jumat</span><span class="sm:hidden">Jum</span></div>
                                    <div class="py-1.5 sm:py-2 bg-gray-100/80 rounded-lg sm:rounded-xl"><span class="hidden sm:inline">Sabtu</span><span class="sm:hidden">Sab</span></div>
                                </div>
                            </div>

                            <!-- 7-Column Days Grid -->
                            <div class="p-2 sm:p-4 bg-gray-50/40">
                                <div class="cal-grid-7">
                                    @for($i = 0; $i < $sundayOffset; $i++)
                                        <div class="min-h-[48px] sm:min-h-[105px] rounded-lg sm:rounded-xl bg-gray-100/40 border border-dashed border-gray-200 opacity-40"></div>
                                    @endfor

                                    @for($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $currentDateStr = sprintf('%04d-%02d-%02d', $y, $m, $day);
                                            $dayEvents = $events->filter(function($e) use ($currentDateStr) {
                                                $start = $e->tanggal_mulai ? $e->tanggal_mulai->format('Y-m-d') : '';
                                                $end = $e->tanggal_selesai ? $e->tanggal_selesai->format('Y-m-d') : '';
                                                return $currentDateStr >= $start && $currentDateStr <= $end;
                                            });
                                            $dayNasional = $hariNasionalByDate[$currentDateStr] ?? [];
                                            $hasEvent = $dayEvents->isNotEmpty();
                                            $hasNasional = !empty($dayNasional);
                                            $firstEv = $hasEvent ? $dayEvents->first() : null;
                                            $primaryColor = $firstEv ? $firstEv->warna : null;
                                            $currDate = \Carbon\Carbon::createFromDate($y, $m, $day);
                                            $isToday = $currDate->isToday();
                                            $isSunday = $currDate->isSunday();
                                            $hasRedHoliday = collect($dayNasional)->contains('kategori', 'libur_nasional');
                                            $isRedDay = $isSunday || $hasRedHoliday;
                                        @endphp

                                        <div class="cal-cell-large {{ $isToday ? 'ring-2 ring-amber-400' : '' }}"
                                             @if($hasEvent || $hasNasional) 
                                                onclick="showDetailWaliAgenda({{ json_encode($dayEvents->values()) }}, {{ json_encode(array_values($dayNasional)) }}, '{{ $currDate->translatedFormat('l, d F Y') }}')" 
                                             @endif
                                             style="{{ $hasEvent ? "background-color: {$primaryColor}18 !important; border: 2px solid {$primaryColor} !important; cursor: pointer;" : ($hasRedHoliday ? 'background-color: #fef2f2 !important; border: 1px dashed #fca5a5 !important; cursor: pointer;' : ($isToday ? 'background-color: #fefce8 !important;' : '')) }}">
                                            
                                            <!-- Top Row (Header Tanggal) -->
                                            <div class="flex items-center justify-between w-full sm:mb-1">
                                                <!-- Badge Hari Ini / Dot untuk Desktop -->
                                                <div class="hidden sm:flex items-center gap-1">
                                                    @if($isToday)
                                                        <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black bg-amber-400 text-amber-950 uppercase">Hari Ini</span>
                                                    @elseif($hasEvent)
                                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $primaryColor }} !important;"></span>
                                                    @elseif($hasRedHoliday)
                                                        <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                                    @endif
                                                </div>

                                                <!-- Nomor Tanggal -->
                                                <div class="w-full sm:w-auto flex justify-center sm:justify-end">
                                                    @if($isToday)
                                                        <span class="sm:hidden w-6 h-6 rounded-full flex items-center justify-center bg-amber-400 text-amber-950 font-black text-xs shadow-xs">
                                                            {{ $day }}
                                                        </span>
                                                        <span class="hidden sm:inline text-xs font-black text-amber-950">
                                                            {{ $day }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs font-black {{ $hasEvent ? '' : ($isRedDay ? 'text-red-600' : 'text-gray-800') }}"
                                                              style="{{ $hasEvent ? "color: {$primaryColor} !important;" : ($isRedDay ? 'color: #dc2626 !important;' : '') }}">
                                                            {{ $day }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Tampilan Indikator Titik Warna (Khusus Mobile) -->
                                            <div class="sm:hidden flex items-center justify-center gap-1 mt-1 flex-wrap">
                                                @foreach($dayNasional as $hn)
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $hn['kategori'] === 'libur_nasional' ? 'bg-red-600' : 'bg-sky-500' }}" title="{{ $hn['judul_kegiatan'] }}"></span>
                                                @endforeach
                                                @foreach($dayEvents as $dev)
                                                    <span class="w-2 h-2 rounded-full ring-1 ring-white shadow-2xs" style="background-color: {{ $dev->warna }};" title="{{ $dev->judul_kegiatan }}"></span>
                                                @endforeach
                                            </div>

                                            <!-- Tampilan Kartu Agenda Lengkap (Khusus Desktop) -->
                                            <div class="hidden sm:block space-y-1 overflow-y-auto max-h-[70px] scrollbar-thin mt-1">
                                                {{-- 1. Hari Libur / Peringatan Nasional (Overlay Kalender Otomatis) --}}
                                                @foreach($dayNasional as $hn)
                                                    <div class="px-1.5 py-0.5 rounded text-[10px] font-bold truncate flex items-center gap-1 border shadow-2xs"
                                                         style="background-color: {{ $hn['kategori'] === 'libur_nasional' ? '#fee2e2' : '#f0f9ff' }} !important; 
                                                                color: {{ $hn['kategori'] === 'libur_nasional' ? '#b91c1c' : '#0369a1' }} !important; 
                                                                border-color: {{ $hn['kategori'] === 'libur_nasional' ? '#fca5a5' : '#bae6fd' }} !important;"
                                                         title="{{ $hn['judul_kegiatan'] }} ({{ $hn['keterangan'] ?? '' }})">
                                                        <i class="ti {{ $hn['kategori'] === 'libur_nasional' ? 'ti-flag-filled text-red-600' : 'ti-star-filled text-sky-600' }} text-[9px] shrink-0"></i>
                                                        <span class="truncate">{{ $hn['judul_kegiatan'] }}</span>
                                                    </div>
                                                @endforeach

                                                {{-- 2. Agenda Resmi Pesantren (Inputan Murni Admin) --}}
                                                @foreach($dayEvents as $dev)
                                                    <div class="px-2 py-1 rounded-lg text-[10px] font-black text-white truncate shadow-xs flex items-center gap-1"
                                                         style="background-color: {{ $dev->warna }} !important;"
                                                         title="{{ $dev->judul_kegiatan }} ({{ $dev->getKategoriLabel() }})">
                                                        <i class="ti ti-point-filled text-[8px]"></i> {{ $dev->judul_kegiatan }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- 2. TAMPILAN 12 BULAN GRID (SEBAGAI ALTERNATIF RINGKASAN) -->
            <div id="sectionWaliMultiMonth" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($monthsAcademic as $mInfo)
                        @php
                            $m = $mInfo['month'];
                            $y = $mInfo['year'];
                            $firstDay = \Carbon\Carbon::createFromDate($y, $m, 1);
                            $daysInMonth = $firstDay->daysInMonth;
                            $sundayOffset = $firstDay->dayOfWeek; // 0 for Sunday

                            $mStart = sprintf('%04d-%02d-01', $y, $m);
                            $mEnd = sprintf('%04d-%02d-%02d', $y, $m, $daysInMonth);
                            $monthEvents = $events->filter(function($e) use ($mStart, $mEnd) {
                                $start = $e->tanggal_mulai ? $e->tanggal_mulai->format('Y-m-d') : '';
                                $end = $e->tanggal_selesai ? $e->tanggal_selesai->format('Y-m-d') : '';
                                return $start <= $mEnd && $end >= $mStart;
                            });
                        @endphp

                        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden flex flex-col hover:shadow-md transition">
                            <div class="px-4 py-3 bg-teal-50/70 border-b border-teal-100 flex items-center justify-between">
                                <h4 class="font-black text-xs uppercase tracking-wider text-teal-950">{{ $mInfo['label'] }}</h4>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white text-teal-700 border border-teal-200">
                                    {{ $monthEvents->count() }} Kegiatan
                                </span>
                            </div>

                            <div class="p-3 text-xs flex-1">
                                <div class="cal-grid-7 text-center font-bold text-[10px] text-gray-400 mb-1"
                                     style="display: grid !important; grid-template-columns: repeat(7, minmax(0, 1fr)) !important; gap: 4px !important;">
                                    <span class="text-red-500">Min</span>
                                    <span>Sen</span>
                                    <span>Sel</span>
                                    <span>Rab</span>
                                    <span>Kam</span>
                                    <span class="text-teal-600">Jum</span>
                                    <span>Sab</span>
                                </div>

                                <div class="cal-grid-7 text-center"
                                     style="display: grid !important; grid-template-columns: repeat(7, minmax(0, 1fr)) !important; gap: 4px !important;">
                                    @for($i = 0; $i < $sundayOffset; $i++)
                                        <div class="h-8 rounded-lg"></div>
                                    @endfor

                                    @for($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $currentDateStr = sprintf('%04d-%02d-%02d', $y, $m, $day);
                                            $dayEvents = $events->filter(function($e) use ($currentDateStr) {
                                                $start = $e->tanggal_mulai ? $e->tanggal_mulai->format('Y-m-d') : '';
                                                $end = $e->tanggal_selesai ? $e->tanggal_selesai->format('Y-m-d') : '';
                                                return $currentDateStr >= $start && $currentDateStr <= $end;
                                            });
                                            $dayNasional = $hariNasionalByDate[$currentDateStr] ?? [];
                                            $hasEvent = $dayEvents->isNotEmpty();
                                            $hasNasional = !empty($dayNasional);
                                            $firstEv = $hasEvent ? $dayEvents->first() : null;
                                            $currDate = \Carbon\Carbon::createFromDate($y, $m, $day);
                                            $isToday = $currDate->isToday();
                                            $isSunday = $currDate->isSunday();
                                            $hasRedHoliday = collect($dayNasional)->contains('kategori', 'libur_nasional');
                                            $isRedDay = $isSunday || $hasRedHoliday;
                                        @endphp

                                        <div @if($hasEvent)
                                                onclick="showDetailWaliAgenda({{ json_encode($dayEvents->values()) }})"
                                                role="button"
                                                class="cal-cell-mini text-white shadow-2xs cursor-pointer"
                                                style="background-color: {{ $firstEv->warna }} !important; color: #ffffff !important; font-weight: 800 !important;"
                                                title="{{ $dayEvents->pluck('judul_kegiatan')->join('; ') }}"
                                             @else
                                                class="cal-cell-mini {{ $isToday ? 'bg-amber-100 text-amber-900 font-black border border-amber-300' : ($hasRedHoliday ? 'bg-red-50 text-red-600 font-bold border border-red-200' : ($isSunday ? 'text-red-600 hover:bg-gray-50' : 'text-gray-700 hover:bg-gray-50')) }}"
                                                title="{{ $hasNasional ? collect($dayNasional)->pluck('judul_kegiatan')->join('; ') : '' }}"
                                             @endif>
                                            <span>{{ $day }}</span>
                                            @if($dayEvents->count() > 1 || ($hasEvent && $hasNasional))
                                                <span class="w-1.5 h-1.5 bg-yellow-300 rounded-full absolute bottom-0.5"></span>
                                            @endif
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Daftar Seluruh Agenda Kronologis -->
            <div class="bg-white rounded-3xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 bg-teal-50/50 border-b border-teal-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-sm text-teal-950 uppercase tracking-wider flex items-center gap-2">
                            <i class="ti ti-list-details text-teal-600"></i> Daftar Seluruh Agenda TA {{ $selectedTahunAjaran }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Daftar kronologis kegiatan resmi pondok pesantren.</p>
                    </div>
                    <span class="text-xs font-bold text-teal-800 bg-teal-100 px-3 py-1 rounded-full">
                        {{ $events->count() }} Kegiatan
                    </span>
                </div>

                @if($events->isEmpty())
                    <div class="p-10 text-center">
                        <i class="ti ti-calendar-off text-4xl text-gray-300 mb-2"></i>
                        <p class="text-xs font-bold text-gray-500">Belum ada agenda akademik yang dipublikasikan untuk TA ini.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($events as $event)
                            <div class="p-4 sm:p-5 hover:bg-teal-50/30 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                         style="background-color: {{ $event->warna }}15; color: {{ $event->warna }};">
                                        <i class="ti ti-calendar-event text-xl"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-sm text-gray-900">{{ $event->judul_kegiatan }}</span>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                                  style="background-color: {{ $event->warna }}15; color: {{ $event->warna }}; border-color: {{ $event->warna }}40;">
                                                {{ $event->getKategoriLabel() }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                            <i class="ti ti-clock text-gray-400"></i>
                                            @if($event->tanggal_mulai->format('Y-m-d') === $event->tanggal_selesai->format('Y-m-d'))
                                                {{ $event->tanggal_mulai->translatedFormat('d F Y') }}
                                            @else
                                                {{ $event->tanggal_mulai->translatedFormat('d F Y') }} s.d. {{ $event->tanggal_selesai->translatedFormat('d F Y') }}
                                            @endif
                                        </p>
                                        @if($event->keterangan)
                                            <p class="text-xs text-gray-600 mt-1.5 bg-gray-50 p-2.5 rounded-lg border border-gray-100 whitespace-pre-line">{{ $event->keterangan }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Modal Detail Agenda -->
    <div id="modalDetailAgenda" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200">
            <div class="p-5 bg-teal-800 text-white flex items-center justify-between">
                <h3 class="font-black text-sm uppercase tracking-wider flex items-center gap-2">
                    <i class="ti ti-calendar-event"></i> Detail Agenda Akademik
                </h3>
                <button type="button" onclick="closeDetailAgendaModal()" class="text-teal-200 hover:text-white p-1 rounded-lg">
                    <i class="ti ti-x text-lg"></i>
                </button>
            </div>
            <div id="modalDetailAgendaContent" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                {{-- Injected via JS --}}
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-100 text-right">
                <button type="button" onclick="closeDetailAgendaModal()" class="px-4 py-2 bg-gray-200 text-gray-800 text-xs font-bold rounded-xl hover:bg-gray-300">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentWaliTabIndex = {{ $defaultActiveIdx }};
        const totalWaliMonths = {{ count($monthsAcademic) }};

        function selectWaliMonthTab(idx) {
            currentWaliTabIndex = idx;
            document.querySelectorAll('.month-tab-wali').forEach((btn, i) => {
                if (i === idx) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            document.querySelectorAll('.wali-month-box').forEach((box, i) => {
                if (i === idx) {
                    box.classList.remove('hidden');
                } else {
                    box.classList.add('hidden');
                }
            });
        }

        function prevWaliMonthTab(idx) {
            if (idx > 0) {
                selectWaliMonthTab(idx - 1);
            } else {
                selectWaliMonthTab(totalWaliMonths - 1);
            }
        }

        function nextWaliMonthTab(idx) {
            if (idx < totalWaliMonths - 1) {
                selectWaliMonthTab(idx + 1);
            } else {
                selectWaliMonthTab(0);
            }
        }

        function switchWaliViewMode(mode) {
            const singleSec = document.getElementById('sectionWaliSingleMonth');
            const multiSec = document.getElementById('sectionWaliMultiMonth');
            const btnSingle = document.getElementById('btnWaliSingle');
            const btnMulti = document.getElementById('btnWaliMulti');

            if (mode === 'single') {
                singleSec.classList.remove('hidden');
                multiSec.classList.add('hidden');
                btnSingle.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-teal-600 text-white shadow-xs";
                btnMulti.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-gray-600 hover:text-gray-900";
            } else {
                singleSec.classList.add('hidden');
                multiSec.classList.remove('hidden');
                btnSingle.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-gray-600 hover:text-gray-900";
                btnMulti.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-teal-600 text-white shadow-xs";
            }
        }

        function showDetailWaliAgenda(events, nationalEvents = [], dateLabel = '') {
            const container = document.getElementById('modalDetailAgendaContent');
            let html = '';

            if (dateLabel) {
                html += `<div class="text-xs font-black text-teal-800 bg-teal-50 px-3 py-2 rounded-xl border border-teal-200 mb-3 flex items-center gap-1.5"><i class="ti ti-calendar"></i> ${dateLabel}</div>`;
            }

            if (nationalEvents && nationalEvents.length > 0) {
                nationalEvents.forEach((hn) => {
                    const isLibur = hn.kategori === 'libur_nasional';
                    html += `
                        <div class="p-3.5 rounded-2xl border mb-3" style="border-color: ${isLibur ? '#fca5a5' : '#bae6fd'}; background-color: ${isLibur ? '#fef2f2' : '#f0f9ff'};">
                            <div class="flex items-center gap-1.5 mb-1">
                                <i class="ti ${isLibur ? 'ti-flag-filled text-red-600' : 'ti-star-filled text-sky-600'} text-xs"></i>
                                <span class="text-[10px] font-black uppercase tracking-wider ${isLibur ? 'text-red-700' : 'text-sky-700'}">
                                    ${isLibur ? 'HARI LIBUR NASIONAL' : 'PERINGATAN NASIONAL'}
                                </span>
                            </div>
                            <h4 class="font-black text-sm text-gray-900">${hn.judul_kegiatan}</h4>
                            ${hn.keterangan ? `<p class="text-xs text-gray-600 mt-1">${hn.keterangan}</p>` : ''}
                        </div>
                    `;
                });
            }

            if (events && events.length > 0) {
                events.forEach((ev, idx) => {
                    const startDate = new Date(ev.tanggal_mulai).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                    const endDate = new Date(ev.tanggal_selesai).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                    const dateStr = startDate === endDate ? startDate : `${startDate} s.d. ${endDate}`;

                    html += `
                        <div class="p-4 rounded-2xl border ${idx > 0 || (nationalEvents && nationalEvents.length > 0) ? 'mt-3' : ''}" style="border-color: ${ev.warna}40; background-color: ${ev.warna}08;">
                            <div class="flex items-center gap-2 mb-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                      style="background-color: ${ev.warna}25; color: ${ev.warna};">
                                    ${ev.kategori ? ev.kategori.toUpperCase() : 'AGENDA'}
                                </span>
                            </div>
                            <h4 class="font-black text-base text-gray-900">${ev.judul_kegiatan}</h4>
                            <div class="text-xs text-gray-600 mt-2 flex items-center gap-1.5 font-semibold">
                                <i class="ti ti-calendar text-teal-700"></i> ${dateStr}
                            </div>
                            ${ev.keterangan ? `
                                <div class="mt-3 text-xs text-gray-700 bg-white p-3 rounded-xl border border-gray-100 whitespace-pre-line leading-relaxed">
                                    ${ev.keterangan}
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
            }

            if (!html) {
                html = '<div class="text-xs text-gray-500 text-center py-4">Tidak ada rincian agenda pada tanggal ini.</div>';
            }

            container.innerHTML = html;
            document.getElementById('modalDetailAgenda').classList.remove('hidden');
        }

        function closeDetailAgendaModal() {
            document.getElementById('modalDetailAgenda').classList.add('hidden');
        }
    </script>
</x-app-layout>
