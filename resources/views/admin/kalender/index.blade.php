@extends('layouts.admin.app')

@section('title', 'Kalender Akademik Alam Qur\'an')

@section('content')
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
        border-radius: 12px;
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
    .month-tab.active {
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

<div class="bg-gray-100 min-h-screen py-6 px-4 lg:px-8">

    {{-- Alerts --}}
    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-xl shadow-xs flex items-start gap-3">
            <i class="ti ti-circle-check text-2xl text-green-600 shrink-0 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-bold text-green-800">Berhasil!</h4>
                <p class="text-xs text-green-700 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <i class="ti ti-calendar-event text-teal-600"></i> Kalender Akademik Pesantren
            </h1>
            <p class="text-xs text-gray-500 mt-1">Agenda resmi tahun ajaran (Juli s.d. Juni): Ujian, Libur Semester, Kedatangan Santri, dan Sambangan Wali.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <form method="GET" action="{{ route('admin.kalender.index') }}" class="inline-flex items-center gap-2">
                <select name="tahun_ajaran" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-xl text-xs font-bold text-teal-900 bg-teal-50 focus:ring-2 focus:ring-teal-500">
                    @foreach($tahunAjaranList as $th)
                        <option value="{{ $th }}" {{ $selectedTahunAjaran === $th ? 'selected' : '' }}>Tahun Ajaran {{ $th }}</option>
                    @endforeach
                </select>
            </form>
            <button type="button" onclick="openTambahAgendaModal()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition hover:opacity-95 cursor-pointer"
                    style="background-color: #0d9488 !important;">
                <i class="ti ti-plus text-base"></i> Tambah Agenda
            </button>
        </div>
    </div>

    <!-- Kategori Legend Badges -->
    <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-200 mb-6 flex flex-wrap items-center gap-2 text-xs">
        <span class="font-bold text-gray-500 uppercase text-[11px] mr-1">Kategori:</span>
        @foreach($kategoriList as $k => $info)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border"
                  style="background-color: {{ $info['warna'] }}15; color: {{ $info['warna'] }}; border-color: {{ $info['warna'] }}40;">
                <span class="w-2 h-2 rounded-full" style="background-color: {{ $info['warna'] }};"></span>
                {{ $info['label'] }}
            </span>
        @endforeach
    </div>

    <!-- Controls: Tab Horizontal 12 Bulan & Mode Switcher -->
    <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-200 mb-6 space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="ti ti-calendar text-teal-600"></i> Pilih Bulan (Juli - Juni):
                </span>
            </div>
            <!-- Mode Toggle: 1 Bulan Maksimal vs 12 Bulan Grid -->
            <div class="inline-flex rounded-xl p-1 bg-gray-100 border border-gray-200 self-start md:self-auto">
                <button type="button" onclick="switchViewMode('single')" id="btnViewSingle"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-teal-600 text-white shadow-xs">
                    <i class="ti ti-layout-distribute-vertical"></i> Tampilan Penuh (Maksimal)
                </button>
                <button type="button" onclick="switchViewMode('multi')" id="btnViewMulti"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-gray-600 hover:text-gray-900">
                    <i class="ti ti-layout-grid"></i> Ringkasan 12 Bulan
                </button>
            </div>
        </div>

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

        <!-- Horizontal Month Selector Tabs (Juli s.d. Juni) -->
        <div class="overflow-x-auto scrollbar-thin pb-1">
            <div class="flex items-center gap-2 min-w-max">
                @foreach($monthsAcademic as $idx => $mInfo)
                    <button type="button" onclick="selectMonthTab({{ $idx }})" id="monthTab_{{ $idx }}"
                            class="month-tab px-3.5 py-2 rounded-xl text-xs font-bold transition border border-gray-200 bg-gray-50 text-gray-700 hover:bg-teal-50 hover:text-teal-700 {{ $idx === $defaultActiveIdx ? 'active' : '' }}">
                        {{ $mInfo['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 1. TAMPILAN BULAN PENUH (HORIZONTAL & MAKSIMAL) -->
    <div id="sectionSingleMonth" class="space-y-6">
        @foreach($monthsAcademic as $idx => $mInfo)
            @php
                $m = $mInfo['month'];
                $y = $mInfo['year'];
                $firstDay = \Carbon\Carbon::createFromDate($y, $m, 1);
                $daysInMonth = $firstDay->daysInMonth;
                $sundayOffset = $firstDay->dayOfWeek; // 0 Sun, 1 Mon, ... 6 Sat

                $mStart = sprintf('%04d-%02d-01', $y, $m);
                $mEnd = sprintf('%04d-%02d-%02d', $y, $m, $daysInMonth);
                $monthEvents = $events->filter(function($e) use ($mStart, $mEnd) {
                    $start = $e->tanggal_mulai ? $e->tanggal_mulai->format('Y-m-d') : '';
                    $end = $e->tanggal_selesai ? $e->tanggal_selesai->format('Y-m-d') : '';
                    return $start <= $mEnd && $end >= $mStart;
                });
            @endphp

            <div id="singleMonthBox_{{ $idx }}" class="single-month-box {{ $idx === $defaultActiveIdx ? '' : 'hidden' }}">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Month Title Header -->
                    <div class="p-5 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                         style="background: linear-gradient(135deg, #0f766e 0%, #134e4a 100%) !important; color: #ffffff !important;">
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="prevMonthTab({{ $idx }})" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 transition" title="Bulan Sebelumnya">
                                <i class="ti ti-chevron-left text-lg"></i>
                            </button>
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-widest text-teal-200" style="color: #99f6e4 !important;">Bulan Akademik TA {{ $selectedTahunAjaran }}</span>
                                <h2 class="text-xl font-black" style="color: #ffffff !important;">{{ $mInfo['label'] }}</h2>
                            </div>
                            <button type="button" onclick="nextMonthTab({{ $idx }})" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 transition" title="Bulan Berikutnya">
                                <i class="ti ti-chevron-right text-lg"></i>
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold border"
                                  style="background: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; border-color: rgba(255, 255, 255, 0.25) !important;">
                                <i class="ti ti-calendar-event"></i> {{ $monthEvents->count() }} Kegiatan Terjadwal
                            </span>
                            <button type="button" onclick="openTambahAgendaModal('{{ sprintf('%04d-%02d-01', $y, $m) }}')"
                                    class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs transition">
                                <i class="ti ti-plus"></i> Tambah di Bulan Ini
                            </button>
                        </div>
                    </div>

                    <!-- Horizontal 7-Day Header -->
                    <div class="p-4 bg-teal-50/50 border-b border-teal-100">
                        <div class="cal-grid-7 text-center font-black text-xs uppercase tracking-wider text-gray-600"
                             style="display: grid !important; grid-template-columns: repeat(7, minmax(0, 1fr)) !important; gap: 6px !important;">
                            <div class="py-2 text-red-600 bg-red-100/60 rounded-xl">Minggu</div>
                            <div class="py-2 bg-gray-100/80 rounded-xl">Senin</div>
                            <div class="py-2 bg-gray-100/80 rounded-xl">Selasa</div>
                            <div class="py-2 bg-gray-100/80 rounded-xl">Rabu</div>
                            <div class="py-2 bg-gray-100/80 rounded-xl">Kamis</div>
                            <div class="py-2 text-teal-700 bg-teal-100/60 rounded-xl">Jumat</div>
                            <div class="py-2 bg-gray-100/80 rounded-xl">Sabtu</div>
                        </div>
                    </div>

                    <!-- Horizontal 7-Column Days Grid -->
                    <div class="p-4 bg-gray-50/50">
                        <div class="cal-grid-7"
                             style="display: grid !important; grid-template-columns: repeat(7, minmax(0, 1fr)) !important; gap: 6px !important;">
                            {{-- Offsets for Sunday start --}}
                            @for($i = 0; $i < $sundayOffset; $i++)
                                <div class="min-h-[105px] rounded-xl bg-gray-100/40 border border-dashed border-gray-200 opacity-40"></div>
                            @endfor

                            {{-- Days of this month --}}
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $currentDateStr = sprintf('%04d-%02d-%02d', $y, $m, $d);
                                    $dayEvents = $events->filter(function($e) use ($currentDateStr) {
                                        $start = $e->tanggal_mulai ? $e->tanggal_mulai->format('Y-m-d') : '';
                                        $end = $e->tanggal_selesai ? $e->tanggal_selesai->format('Y-m-d') : '';
                                        return $currentDateStr >= $start && $currentDateStr <= $end;
                                    });
                                    $dayNasional = $hariNasionalByDate[$currentDateStr] ?? [];
                                    $hasEvents = $dayEvents->isNotEmpty();
                                    $hasNasional = !empty($dayNasional);
                                    $firstEv = $hasEvents ? $dayEvents->first() : null;
                                    $primaryColor = $firstEv ? $firstEv->warna : null;
                                    $currentDate = \Carbon\Carbon::createFromDate($y, $m, $d);
                                    $isToday = $currentDate->isToday();
                                    $isSunday = $currentDate->isSunday();
                                    $hasRedHoliday = collect($dayNasional)->contains('kategori', 'libur_nasional');
                                    $isRedDay = $isSunday || $hasRedHoliday;
                                @endphp

                                <div class="cal-cell-large {{ $isToday ? 'ring-2 ring-amber-400' : '' }}"
                                     onclick="handleClickDate('{{ $currentDateStr }}', {{ json_encode($dayEvents->values()) }})"
                                     style="{{ $hasEvents ? "background-color: {$primaryColor}18 !important; border: 2px solid {$primaryColor} !important;" : ($hasRedHoliday ? 'background-color: #fef2f2 !important; border: 1px dashed #fca5a5 !important;' : ($isToday ? 'background-color: #fefce8 !important;' : '')) }}">
                                    <!-- Top Row: Date Number -->
                                    <div class="flex items-center justify-between mb-1">
                                        @if($isToday)
                                            <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black bg-amber-400 text-amber-950 uppercase">Hari Ini</span>
                                        @elseif($hasEvents)
                                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $primaryColor }} !important;"></span>
                                        @elseif($hasRedHoliday)
                                            <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                        @else
                                            <span></span>
                                        @endif
                                        <span class="text-xs font-black {{ $hasEvents ? '' : ($isRedDay ? 'text-red-600' : 'text-gray-800') }}"
                                              style="{{ $hasEvents ? "color: {$primaryColor} !important;" : ($isRedDay ? 'color: #dc2626 !important;' : '') }}">
                                            {{ $d }}
                                        </span>
                                    </div>

                                    <!-- Event Badges Inside the Date Box -->
                                    <div class="space-y-1 overflow-y-auto max-h-[70px] scrollbar-thin">
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
                                            <div class="px-2 py-1 rounded-lg text-[11px] font-black text-white truncate shadow-xs flex items-center gap-1"
                                                 style="background-color: {{ $dev->warna }} !important;"
                                                 title="{{ $dev->judul_kegiatan }} ({{ $dev->getKategoriLabel() }})">
                                                <i class="ti ti-point-filled text-[9px]"></i> {{ $dev->judul_kegiatan }}
                                            </div>
                                        @endforeach
                                    </div>

                                    @if(!$hasEvents)
                                        <div class="mt-auto pt-1 opacity-0 hover:opacity-100 transition text-[10px] text-teal-600 font-semibold flex items-center gap-0.5">
                                            <i class="ti ti-plus"></i> Tambah
                                        </div>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Agenda List for this Month Below the Grid -->
                    @if($monthEvents->isNotEmpty())
                        <div class="p-5 bg-white border-t border-gray-200">
                            <h4 class="text-xs font-black uppercase text-gray-700 tracking-wider mb-3 flex items-center gap-2">
                                <i class="ti ti-list text-teal-600"></i> Rincian Agenda {{ $mInfo['label'] }} ({{ $monthEvents->count() }})
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($monthEvents as $mev)
                                    <div class="p-3.5 rounded-2xl border flex flex-col justify-between"
                                         style="border-color: {{ $mev->warna }}40; background-color: {{ $mev->warna }}08;">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                      style="background-color: {{ $mev->warna }}25; color: {{ $mev->warna }};">
                                                    {{ $mev->getKategoriLabel() }}
                                                </span>
                                                @if($mev->is_public)
                                                    <span class="text-[10px] text-teal-700 font-bold ml-auto flex items-center gap-1">
                                                        <i class="ti ti-eye"></i> Publik
                                                    </span>
                                                @endif
                                            </div>
                                            <h5 class="font-bold text-xs text-gray-900">{{ $mev->judul_kegiatan }}</h5>
                                            <p class="text-[11px] text-gray-500 mt-1">
                                                <i class="ti ti-clock"></i>
                                                {{ $mev->tanggal_mulai->format('d/m/Y') }}
                                                @if($mev->tanggal_mulai != $mev->tanggal_selesai)
                                                    s.d. {{ $mev->tanggal_selesai->format('d/m/Y') }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="mt-3 pt-2 border-t border-gray-200/60 flex items-center justify-end gap-2">
                                            <button type="button" onclick="openEditAgendaModal({{ json_encode($mev) }})"
                                                    class="px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-blue-600 hover:bg-blue-50 text-[11px] font-bold">
                                                <i class="ti ti-edit"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('admin.kalender.destroy', $mev->id) }}" onsubmit="return confirm('Hapus agenda ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 rounded-lg bg-white border border-gray-200 text-red-600 hover:bg-red-50 text-[11px] font-bold">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- 2. TAMPILAN 12 BULAN GRID (SEBAGAI ALTERNATIF RINGKASAN) -->
    <div id="sectionMultiMonth" class="hidden mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($monthsAcademic as $mInfo)
                @php
                    $m = $mInfo['month'];
                    $y = $mInfo['year'];
                    $firstDay = \Carbon\Carbon::createFromDate($y, $m, 1);
                    $daysInMonth = $firstDay->daysInMonth;
                    $sundayOffset = $firstDay->dayOfWeek; // 0 for Sunday

                    $monthEvents = $events->filter(function($e) use ($y, $m, $daysInMonth) {
                        $start = $e->tanggal_mulai;
                        $end = $e->tanggal_selesai;
                        $mStart = \Carbon\Carbon::createFromDate($y, $m, 1)->startOfDay();
                        $mEnd = \Carbon\Carbon::createFromDate($y, $m, $daysInMonth)->endOfDay();
                        return $start->lte($mEnd) && $end->gte($mStart);
                    });
                @endphp

                <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden flex flex-col">
                    <div class="px-4 py-3 bg-teal-50/70 border-b border-teal-100 flex items-center justify-between">
                        <h3 class="font-black text-xs uppercase tracking-wider text-teal-950">{{ $mInfo['label'] }}</h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white text-teal-700 border border-teal-200">
                            {{ $monthEvents->count() }} Kegiatan
                        </span>
                    </div>

                    <!-- Mini Calendar Grid: Guaranteed 7 Columns Horizontal -->
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
                            {{-- Offsets --}}
                            @for($i = 0; $i < $sundayOffset; $i++)
                                <div class="h-8 rounded-lg"></div>
                            @endfor

                            {{-- Days --}}
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $currentDateStr = sprintf('%04d-%02d-%02d', $y, $m, $d);
                                    $dayEvents = $events->filter(function($e) use ($currentDateStr) {
                                        $start = $e->tanggal_mulai ? $e->tanggal_mulai->format('Y-m-d') : '';
                                        $end = $e->tanggal_selesai ? $e->tanggal_selesai->format('Y-m-d') : '';
                                        return $currentDateStr >= $start && $currentDateStr <= $end;
                                    });
                                    $dayNasional = $hariNasionalByDate[$currentDateStr] ?? [];
                                    $hasEvents = $dayEvents->isNotEmpty();
                                    $hasNasional = !empty($dayNasional);
                                    $primaryColor = $hasEvents ? $dayEvents->first()->warna : null;
                                    $currentDate = \Carbon\Carbon::createFromDate($y, $m, $d);
                                    $isSunday = $currentDate->isSunday();
                                    $isToday = $currentDate->isToday();
                                    $hasRedHoliday = collect($dayNasional)->contains('kategori', 'libur_nasional');
                                    $isRedDay = $isSunday || $hasRedHoliday;
                                @endphp

                                <button type="button"
                                        onclick='handleClickDate("{{ $currentDateStr }}", {{ json_encode($dayEvents->values()) }})'
                                        class="cal-cell-mini {{ $hasEvents ? 'shadow-xs text-white' : ($isToday ? 'border-amber-400 bg-amber-100 text-amber-900 font-black' : ($isRedDay ? 'text-red-600 font-bold hover:bg-red-50' : 'text-gray-700 hover:bg-gray-100')) }}"
                                        style="{{ $hasEvents ? "background-color: {$primaryColor} !important; color: #ffffff !important; font-weight: 800 !important;" : ($hasRedHoliday && !$hasEvents ? "background-color: #fee2e2 !important; color: #dc2626 !important;" : "") }}"
                                        title="{{ $hasEvents ? $dayEvents->pluck('judul_kegiatan')->join('; ') : ($hasNasional ? collect($dayNasional)->pluck('judul_kegiatan')->join('; ') : 'Klik untuk tambah kegiatan') }}">
                                    <span>{{ $d }}</span>
                                    @if($dayEvents->count() > 1 || ($hasEvents && $hasNasional))
                                        <span class="w-1.5 h-1.5 bg-yellow-300 rounded-full absolute bottom-0.5"></span>
                                    @endif
                                </button>
                            @endfor
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3. DAFTAR SELURUH AGENDA KRONOLOGIS TA (SEPERTI DI PORTAL WALI) -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="px-6 py-4 bg-teal-50/50 border-b border-teal-100 flex items-center justify-between">
            <div>
                <h4 class="font-black text-sm uppercase tracking-wider text-teal-950 flex items-center gap-2">
                    <i class="ti ti-list-details text-teal-600"></i> Daftar Seluruh Agenda TA {{ $selectedTahunAjaran }}
                </h4>
                <p class="text-xs text-gray-500">Daftar kronologis seluruh kegiatan resmi pesantren selama 1 tahun ajaran.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-black bg-teal-100 text-teal-800">
                {{ $events->count() }} Kegiatan
            </span>
        </div>

        @if($events->isEmpty())
            <div class="p-8 text-center text-gray-400">
                <i class="ti ti-calendar-off text-4xl block mb-2 opacity-50"></i>
                <p class="text-xs font-bold text-gray-500">Belum ada agenda akademik yang ditambahkan untuk TA ini.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($events as $ev)
                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50 transition">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 text-base"
                                 style="background-color: {{ $ev->warna }}20; color: {{ $ev->warna }};">
                                <i class="ti ti-calendar"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <h5 class="font-black text-sm text-gray-900">{{ $ev->judul_kegiatan }}</h5>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                          style="background-color: {{ $ev->warna }}20; color: {{ $ev->warna }};">
                                        {{ $ev->getKategoriLabel() }}
                                    </span>
                                    @if($ev->is_public)
                                        <span class="text-[10px] text-teal-700 bg-teal-50 border border-teal-200 px-2 py-0.5 rounded-md font-bold flex items-center gap-1">
                                            <i class="ti ti-eye"></i> Publik (Wali)
                                        </span>
                                    @else
                                        <span class="text-[10px] text-gray-600 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded-md font-bold flex items-center gap-1">
                                            <i class="ti ti-lock"></i> Internal Admin
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 flex items-center gap-1.5 font-semibold">
                                    <i class="ti ti-clock"></i>
                                    {{ $ev->tanggal_mulai ? $ev->tanggal_mulai->translatedFormat('d F Y') : '-' }}
                                    @if($ev->tanggal_mulai != $ev->tanggal_selesai)
                                        s.d. {{ $ev->tanggal_selesai ? $ev->tanggal_selesai->translatedFormat('d F Y') : '-' }}
                                    @endif
                                </div>
                                @if($ev->keterangan)
                                    <p class="text-xs text-gray-600 mt-1.5 line-clamp-2">{{ $ev->keterangan }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                            <button type="button" onclick="openEditAgendaModal({{ json_encode($ev) }})"
                                    class="px-3 py-1.5 rounded-xl bg-white border border-gray-200 text-blue-600 hover:bg-blue-50 text-xs font-bold transition flex items-center gap-1">
                                <i class="ti ti-edit"></i> Edit
                            </button>
                            <form method="POST" action="{{ route('admin.kalender.destroy', $ev->id) }}" onsubmit="return confirm('Hapus agenda ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-xl bg-white border border-gray-200 text-red-600 hover:bg-red-50 text-xs font-bold transition" title="Hapus">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah Agenda -->
<div id="modalTambahAgenda" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200">
        <div class="p-5 text-white flex items-center justify-between" style="background-color: #0d9488 !important;">
            <h3 class="font-black text-sm uppercase tracking-wider flex items-center gap-2">
                <i class="ti ti-calendar-plus"></i> Tambah Agenda Akademik
            </h3>
            <button type="button" onclick="closeTambahAgendaModal()" class="text-teal-100 hover:text-white p-1 rounded-lg">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.kalender.store') }}" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                <input type="text" name="tahun_ajaran" value="{{ $selectedTahunAjaran }}" required
                       class="w-full text-xs rounded-xl border-gray-300 focus:ring-teal-500 focus:border-teal-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Judul Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="judul_kegiatan" required placeholder="Contoh: Libur Semester Ganjil"
                       class="w-full text-xs rounded-xl border-gray-300 focus:ring-teal-500 focus:border-teal-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kategori Agenda <span class="text-red-500">*</span></label>
                    <select name="kategori" id="inputKategori" onchange="autoColor(this.value)" required
                            class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:ring-teal-500 focus:border-teal-500">
                        @foreach($kategoriList as $k => $info)
                            <option value="{{ $k }}" data-color="{{ $info['warna'] }}">{{ $info['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Warna Label (Otomatis)</label>
                    <div class="flex items-center gap-2.5 h-[38px] px-3 bg-gray-50 border border-gray-300 rounded-xl">
                        <span id="previewColorDot" class="w-4 h-4 rounded-full shadow-xs shrink-0" style="background-color: #ef4444;"></span>
                        <span id="previewColorText" class="text-xs font-bold text-gray-800 truncate">Libur Semester / Libur Pondok</span>
                        <span class="ml-auto text-[10px] text-teal-700 bg-teal-100/70 px-2 py-0.5 rounded-md font-bold shrink-0">Auto</span>
                    </div>
                    <input type="hidden" name="warna" id="inputWarna" value="#ef4444">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_mulai" id="inputTglMulai" required
                           class="w-full text-xs rounded-xl border-gray-300 focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_selesai" id="inputTglSelesai" required
                           class="w-full text-xs rounded-xl border-gray-300 focus:ring-teal-500 focus:border-teal-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Keterangan / Catatan</label>
                <textarea name="keterangan" rows="3" placeholder="Informasi detail kegiatan untuk santri dan wali..."
                          class="w-full text-xs rounded-xl border-gray-300 focus:ring-teal-500 focus:border-teal-500"></textarea>
            </div>

            <div class="p-3 bg-teal-50 border border-teal-200 rounded-xl flex items-center justify-between">
                <div>
                    <strong class="text-teal-950 block text-xs">Tampilkan ke Portal Wali Santri</strong>
                    <span class="text-[11px] text-teal-700">Wali santri dapat melihat agenda ini di kalender portal wali.</span>
                </div>
                <input type="checkbox" name="is_public" value="1" checked class="w-4 h-4 text-teal-600 rounded-md focus:ring-teal-500 cursor-pointer">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeTambahAgendaModal()" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-xl text-xs hover:bg-gray-200">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-white font-bold rounded-xl text-xs shadow-md transition hover:opacity-95"
                        style="background-color: #0d9488 !important;">
                    <i class="ti ti-check"></i> Simpan Agenda
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Agenda -->
<div id="modalEditAgenda" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200">
        <div class="p-5 text-white flex items-center justify-between" style="background-color: #d97706 !important;">
            <h3 class="font-black text-sm uppercase tracking-wider flex items-center gap-2">
                <i class="ti ti-edit"></i> Edit Agenda Akademik
            </h3>
            <button type="button" onclick="closeEditAgendaModal()" class="text-amber-100 hover:text-white p-1 rounded-lg">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>
        <form id="formEditAgenda" method="POST" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
            @csrf
            @method('PUT')
            <input type="hidden" name="tahun_ajaran" value="{{ $selectedTahunAjaran }}">

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Judul Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="judul_kegiatan" id="editJudul" required
                       class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kategori Agenda <span class="text-red-500">*</span></label>
                    <select name="kategori" id="editKategori" onchange="autoColorEdit(this.value)" required
                            class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                        @foreach($kategoriList as $k => $info)
                            <option value="{{ $k }}" data-color="{{ $info['warna'] }}">{{ $info['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Warna Label (Otomatis)</label>
                    <div class="flex items-center gap-2.5 h-[38px] px-3 bg-gray-50 border border-gray-300 rounded-xl">
                        <span id="editPreviewColorDot" class="w-4 h-4 rounded-full shadow-xs shrink-0" style="background-color: #ef4444;"></span>
                        <span id="editPreviewColorText" class="text-xs font-bold text-gray-800 truncate">Libur Semester / Libur Pondok</span>
                        <span class="ml-auto text-[10px] text-amber-700 bg-amber-100/70 px-2 py-0.5 rounded-md font-bold shrink-0">Auto</span>
                    </div>
                    <input type="hidden" name="warna" id="editWarna" value="#ef4444">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_mulai" id="editTglMulai" required
                           class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_selesai" id="editTglSelesai" required
                           class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Keterangan / Catatan</label>
                <textarea name="keterangan" id="editKeterangan" rows="3"
                          class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500"></textarea>
            </div>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between">
                <div>
                    <strong class="text-amber-950 block text-xs">Tampilkan ke Portal Wali Santri</strong>
                    <span class="text-[11px] text-amber-700">Wali santri dapat melihat agenda ini di kalender portal wali.</span>
                </div>
                <input type="checkbox" name="is_public" id="editIsPublic" value="1" class="w-4 h-4 text-amber-600 rounded-md focus:ring-amber-500 cursor-pointer">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeEditAgendaModal()" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-xl text-xs hover:bg-gray-200">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-white font-bold rounded-xl text-xs shadow-md transition hover:opacity-95"
                        style="background-color: #d97706 !important;">
                    <i class="ti ti-check"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const kategoriColors = @json(collect($kategoriList)->mapWithKeys(fn($v, $k) => [$k => $v['warna']]));
    const kategoriLabels = @json(collect($kategoriList)->mapWithKeys(fn($v, $k) => [$k => $v['label']]));
    let currentTabIndex = {{ $defaultActiveIdx }};
    const totalMonths = {{ count($monthsAcademic) }};

    function selectMonthTab(idx) {
        currentTabIndex = idx;
        // Update tab buttons style
        document.querySelectorAll('.month-tab').forEach((btn, i) => {
            if (i === idx) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Show corresponding single-month box
        document.querySelectorAll('.single-month-box').forEach((box, i) => {
            if (i === idx) {
                box.classList.remove('hidden');
            } else {
                box.classList.add('hidden');
            }
        });
    }

    function prevMonthTab(idx) {
        if (idx > 0) {
            selectMonthTab(idx - 1);
        } else {
            selectMonthTab(totalMonths - 1);
        }
    }

    function nextMonthTab(idx) {
        if (idx < totalMonths - 1) {
            selectMonthTab(idx + 1);
        } else {
            selectMonthTab(0);
        }
    }

    function switchViewMode(mode) {
        const singleSec = document.getElementById('sectionSingleMonth');
        const multiSec = document.getElementById('sectionMultiMonth');
        const btnSingle = document.getElementById('btnViewSingle');
        const btnMulti = document.getElementById('btnViewMulti');

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

    function autoColor(kategori) {
        const color = kategoriColors[kategori] || '#0d9488';
        const label = kategoriLabels[kategori] || kategori;
        const input = document.getElementById('inputWarna');
        if (input) input.value = color;
        const dot = document.getElementById('previewColorDot');
        if (dot) dot.style.backgroundColor = color;
        const text = document.getElementById('previewColorText');
        if (text) text.textContent = label;
    }

    function autoColorEdit(kategori) {
        const color = kategoriColors[kategori] || '#0d9488';
        const label = kategoriLabels[kategori] || kategori;
        const input = document.getElementById('editWarna');
        if (input) input.value = color;
        const dot = document.getElementById('editPreviewColorDot');
        if (dot) dot.style.backgroundColor = color;
        const text = document.getElementById('editPreviewColorText');
        if (text) text.textContent = label;
    }

    function openTambahAgendaModal(defaultDate = null) {
        const defaultKat = document.getElementById('inputKategori') ? document.getElementById('inputKategori').value : 'libur';
        autoColor(defaultKat);
        if (defaultDate) {
            document.getElementById('inputTglMulai').value = defaultDate;
            document.getElementById('inputTglSelesai').value = defaultDate;
        } else {
            const today = new Date().toISOString().substring(0, 10);
            document.getElementById('inputTglMulai').value = today;
            document.getElementById('inputTglSelesai').value = today;
        }
        document.getElementById('modalTambahAgenda').classList.remove('hidden');
    }
    function closeTambahAgendaModal() {
        document.getElementById('modalTambahAgenda').classList.add('hidden');
    }

    function handleClickDate(dateStr, events) {
        if (events && events.length > 0) {
            openEditAgendaModal(events[0]);
        } else {
            openTambahAgendaModal(dateStr);
        }
    }

    function openEditAgendaModal(data) {
        document.getElementById('formEditAgenda').action = '/admin/kalender/' + data.id;
        document.getElementById('editJudul').value = data.judul_kegiatan || '';
        document.getElementById('editKategori').value = data.kategori || 'kegiatan';
        autoColorEdit(data.kategori || 'kegiatan');
        document.getElementById('editTglMulai').value = data.tanggal_mulai ? data.tanggal_mulai.substring(0, 10) : '';
        document.getElementById('editTglSelesai').value = data.tanggal_selesai ? data.tanggal_selesai.substring(0, 10) : '';
        document.getElementById('editKeterangan').value = data.keterangan || '';
        document.getElementById('editIsPublic').checked = Boolean(data.is_public);
        document.getElementById('modalEditAgenda').classList.remove('hidden');
    }
    function closeEditAgendaModal() {
        document.getElementById('modalEditAgenda').classList.add('hidden');
    }
</script>
@endsection
