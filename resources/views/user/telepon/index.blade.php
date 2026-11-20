<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white flex items-center justify-center shadow-md shadow-emerald-600/20 shrink-0">
                    <i class="fas fa-phone-alt text-lg"></i>
                </div>
                <div>
                    <h2 class="font-extrabold text-lg sm:text-xl text-gray-900 tracking-tight">
                        Jadwal Sambungan Telepon Ananda
                    </h2>
                    <p class="text-xs text-gray-500">Momen sambungan kasih dan sapaan rindu ananda bersama keluarga tercinta.</p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/60">
                    <i class="fas fa-shield-heart text-emerald-600"></i> Pengasuhan Santri
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 bg-gray-50/70 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-20">

            {{-- Multi-Santri Selector (Jika Wali Punya Lebih dari 1 Anak) --}}
            @if($santris->count() > 1)
                <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-gray-100 shadow-xs">
                    <div class="flex items-center justify-between gap-3 mb-2.5 px-1">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-users text-emerald-600"></i> Pilih Ananda:
                        </span>
                        <span class="text-[11px] text-gray-400">Total {{ $santris->count() }} Santri</span>
                    </div>
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin">
                        <a href="{{ route('user.telepon.index') }}"
                           class="shrink-0 inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ !request('santri_id') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-600/25' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            <i class="fas fa-layer-group text-xs"></i>
                            <span>Semua Ananda</span>
                        </a>
                        @foreach($santris as $s)
                            @php
                                $isActive = request('santri_id') == $s->id;
                                $initials = strtoupper(substr($s->nama_lengkap, 0, 2));
                            @endphp
                            <a href="{{ route('user.telepon.index', ['santri_id' => $s->id]) }}"
                               class="shrink-0 inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition-all {{ $isActive ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-600/25 ring-2 ring-emerald-500 ring-offset-1' : 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200/80 shadow-2xs' }}">
                                <span class="w-6 h-6 rounded-lg {{ $isActive ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }} flex items-center justify-center text-[10px] font-black">
                                    {{ $initials }}
                                </span>
                                <span>{{ $s->nama_lengkap }}</span>
                                <span class="text-[10px] {{ $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }} px-1.5 py-0.5 rounded-md font-medium">
                                    Kelas {{ $s->kelas }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- HIGHLIGHT CARD: JADWAL TERDEKAT (BOARDING PASS / TICKET STYLE) --}}
            @if($jadwalTerdekat)
                @php
                    $rawPosko = $jadwalTerdekat->no_hp_posko;
                    $poskoWaClean = \App\Services\WhatsappService::formatPhoneNumber($rawPosko);
                    $santriName = $jadwalTerdekat->santri?->nama_lengkap ?? 'Ananda Tercinta';
                    $santriInitials = strtoupper(substr($santriName, 0, 2));

                    // Generate link Google Calendar jika ada tanggal & jam
                    $calUrl = null;
                    if ($jadwalTerdekat->tanggal_telepon_raw && $jadwalTerdekat->jam_mulai && $jadwalTerdekat->jam_selesai) {
                        try {
                            $startUtc = \Carbon\Carbon::parse($jadwalTerdekat->tanggal_telepon_raw . ' ' . $jadwalTerdekat->jam_mulai, 'Asia/Jakarta')->setTimezone('UTC');
                            $endUtc = \Carbon\Carbon::parse($jadwalTerdekat->tanggal_telepon_raw . ' ' . $jadwalTerdekat->jam_selesai, 'Asia/Jakarta')->setTimezone('UTC');
                            $calTitle = urlencode("Sambungan Telepon Santri: {$santriName}");
                            $calDetails = urlencode("Jadwal giliran telepon ananda bersama keluarga di Pesantren Tahfizh Alam Qur'an.\n\nDidampingi: {$jadwalTerdekat->ustadz_pendamping}\nNomor Posko: {$rawPosko}\nTopik: " . ($jadwalTerdekat->topik ?: '-'));
                            $calUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$calTitle}&dates={$startUtc->format('Ymd\THis\Z')}/{$endUtc->format('Ymd\THis\Z')}&details={$calDetails}&location=" . urlencode("Pesantren Tahfizh Alam Qur'an Ponorogo");
                        } catch (\Throwable $e) {}
                    }
                @endphp

                <div class="relative rounded-3xl overflow-hidden shadow-xl text-white" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 45%, #047857 100%);">
                    {{-- Decorative Glow Circles --}}
                    <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-emerald-400/15 blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-12 -bottom-12 w-64 h-64 rounded-full bg-teal-400/10 blur-3xl pointer-events-none"></div>

                    <div class="relative p-6 sm:p-8">
                        {{-- Top Badge Row --}}
                        <div class="flex items-center justify-between gap-3 mb-5 flex-wrap">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black tracking-wide bg-white/20 backdrop-blur-md text-emerald-100 border border-white/20 uppercase">
                                    <span class="w-2 h-2 rounded-full bg-emerald-300 animate-ping"></span>
                                    Giliran Telepon Terdekat
                                </span>
                                @if($jadwalTerdekat->is_today)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-rose-500 text-white shadow-md shadow-rose-900/30 uppercase animate-pulse">
                                        <i class="fas fa-fire"></i> HARI INI
                                    </span>
                                @endif
                            </div>

                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-950/40 border border-white/10 text-emerald-200 backdrop-blur-xs">
                                <i class="fas fa-hourglass-half text-amber-300"></i> Status: {{ $jadwalTerdekat->status_label }}
                            </span>
                        </div>

                        {{-- Santri Identity Banner --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-white/15">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center font-black text-xl sm:text-2xl text-white shrink-0 shadow-inner">
                                    {{ $santriInitials }}
                                </div>
                                <div>
                                    <span class="text-xs text-emerald-200 font-medium tracking-wide uppercase block">Santri yang Dijadwalkan:</span>
                                    <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-0.5">
                                        {{ $santriName }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1 text-xs text-emerald-100">
                                        <span class="px-2 py-0.5 rounded-md bg-white/15 backdrop-blur-xs font-semibold">
                                            Kelas {{ $jadwalTerdekat->santri?->kelas ?? '-' }}
                                        </span>
                                        <span>•</span>
                                        <span>NIS: {{ $jadwalTerdekat->santri?->nis ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Stand By Widget --}}
                            <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 sm:max-w-xs shrink-0">
                                <div class="w-11 h-11 rounded-xl bg-amber-400 text-amber-950 flex items-center justify-center text-lg font-black shrink-0 shadow-md">
                                    <i class="fas fa-headphones"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-white">Stand By di Dekat Ponsel</div>
                                    <div class="text-[11px] text-emerald-100 leading-tight mt-0.5">
                                        Pastikan HP aktif dan sinyal stabil saat jam giliran tiba.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Detail Information Cards Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                            {{-- Card 1: Waktu & Jam --}}
                            <div class="p-4 sm:p-5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-emerald-200 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fas fa-calendar-alt text-amber-300"></i> Waktu Sambungan
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-400/20 text-amber-300 border border-amber-300/30">
                                        ⏱ Maksimal {{ $jadwalTerdekat->durasi_menit }} Menit
                                    </span>
                                </div>
                                <div>
                                    <div class="text-base sm:text-lg font-black text-white">
                                        {{ $jadwalTerdekat->formatted_tanggal }}
                                    </div>
                                    <div class="text-xl sm:text-2xl font-black text-amber-300 tracking-tight mt-0.5 flex items-center gap-2">
                                        <i class="fas fa-clock text-lg"></i>
                                        <span>{{ $jadwalTerdekat->formatted_jam }}</span>
                                    </div>
                                </div>
                                @if($calUrl)
                                    <a href="{{ $calUrl }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 text-xs text-emerald-100 hover:text-white underline transition">
                                        <i class="fas fa-calendar-plus text-amber-300"></i>
                                        <span>Pasang Pengingat di Google Calendar</span>
                                    </a>
                                @endif
                            </div>

                            {{-- Card 2: Petugas & Posko Penelepon --}}
                            <div class="p-4 sm:p-5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-emerald-200 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fas fa-headset text-amber-300"></i> Petugas & Posko Pondok
                                    </span>
                                    <span class="text-[11px] text-emerald-200 font-semibold">
                                        Didampingi Ustadz
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-white flex items-center gap-1.5">
                                        <i class="fas fa-user-check text-emerald-300"></i>
                                        <span>{{ $jadwalTerdekat->ustadz_pendamping }}</span>
                                    </div>
                                    <div class="text-lg sm:text-xl font-mono font-bold text-white mt-1">
                                        {{ $rawPosko }}
                                    </div>
                                </div>

                                {{-- Fast Action Buttons --}}
                                <div class="flex items-center gap-2 pt-1 flex-wrap">
                                    @if($poskoWaClean)
                                        <a href="https://wa.me/{{ $poskoWaClean }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-sm hover:opacity-95"
                                           style="background-color: #10b981 !important; color: #ffffff !important;">
                                            <i class="fab fa-whatsapp text-sm"></i>
                                            <span>Chat WA Posko</span>
                                        </a>
                                    @endif
                                    <a href="tel:{{ $rawPosko }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white/20 hover:bg-white/30 text-white border border-white/30 transition">
                                        <i class="fas fa-phone text-xs"></i>
                                        <span>Panggil Nomor</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Topik / Catatan Khusus --}}
                        @if($jadwalTerdekat->topik || $jadwalTerdekat->keterangan)
                            <div class="mt-4 p-3 rounded-2xl bg-emerald-950/40 border border-white/10 flex items-start gap-2.5 text-xs text-emerald-100">
                                <i class="fas fa-info-circle text-amber-300 mt-0.5 shrink-0 text-sm"></i>
                                <div>
                                    @if($jadwalTerdekat->topik)
                                        <strong class="text-white">Topik Pembicaraan:</strong> {{ $jadwalTerdekat->topik }}
                                    @endif
                                    @if($jadwalTerdekat->keterangan)
                                        <div class="text-emerald-200 mt-0.5 italic">"{{ $jadwalTerdekat->keterangan }}"</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- SECTION: DAFTAR JADWAL MENDATANG --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3 bg-gradient-to-r from-gray-50/50 to-emerald-50/30">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 tracking-tight">
                                Jadwal Sambungan Telepon Mendatang
                            </h3>
                            <p class="text-xs text-gray-500">Daftar giliran sambungan telepon ananda yang sudah teragendakan.</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                        {{ $jadwalMendatang->count() }} Jadwal Terjadwal
                    </span>
                </div>

                @if($jadwalMendatang->isEmpty())
                    <div class="py-16 text-center text-gray-400 px-4">
                        <div class="w-16 h-16 rounded-3xl bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-3 shadow-inner">
                            <i class="fas fa-phone-slash text-3xl"></i>
                        </div>
                        <h4 class="text-sm font-bold text-gray-700">Belum Ada Jadwal Telepon Mendatang</h4>
                        <p class="text-xs text-gray-400 max-w-sm mx-auto mt-1">
                            Ustadz pengasuhan pondok akan menginfokan giliran sambungan telepon ananda berikutnya melalui sistem dan notifikasi WhatsApp.
                        </p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($jadwalMendatang as $item)
                            @php
                                $itemPosko = $item->no_hp_posko;
                                $itemPoskoClean = \App\Services\WhatsappService::formatPhoneNumber($itemPosko);
                                $santriInitials = strtoupper(substr($item->santri?->nama_lengkap ?? 'A', 0, 2));
                            @endphp
                            <div class="p-5 sm:p-6 hover:bg-emerald-50/20 transition-all flex flex-col md:flex-row md:items-center justify-between gap-5">
                                {{-- Left: Date Badge + Main Info --}}
                                <div class="flex items-start gap-4">
                                    {{-- Modern Calendar Block --}}
                                    <div class="shrink-0 w-16 h-16 rounded-2xl {{ $item->is_today ? 'bg-gradient-to-br from-rose-500 to-rose-600 text-white shadow-md shadow-rose-500/20' : 'bg-gray-100 text-gray-700 border border-gray-200' }} flex flex-col items-center justify-center text-center leading-none">
                                        <span class="text-[10px] font-bold uppercase tracking-wider opacity-80">
                                            {{ $item->tanggal_telepon ? $item->tanggal_telepon->translatedFormat('M') : '-' }}
                                        </span>
                                        <span class="text-2xl font-black mt-1">
                                            {{ $item->tanggal_telepon ? $item->tanggal_telepon->format('d') : '-' }}
                                        </span>
                                        <span class="text-[9px] font-semibold mt-0.5 opacity-90">
                                            {{ $item->tanggal_telepon ? $item->tanggal_telepon->translatedFormat('D') : '-' }}
                                        </span>
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="font-bold text-gray-900 text-base">
                                                {{ $item->santri?->nama_lengkap }}
                                            </h4>
                                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                                Kelas {{ $item->santri?->kelas ?? '-' }}
                                            </span>
                                            @if($item->is_today)
                                                <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-rose-500 text-white animate-pulse">
                                                    HARI INI
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-600 flex-wrap">
                                            <span class="font-extrabold text-emerald-700 flex items-center gap-1.5 bg-emerald-50 px-2.5 py-1 rounded-lg">
                                                <i class="fas fa-clock text-emerald-600"></i>
                                                {{ $item->formatted_jam }}
                                            </span>
                                            <span class="text-gray-400">•</span>
                                            <span class="font-medium">Durasi: <strong>{{ $item->durasi_menit }} Menit</strong></span>
                                        </div>

                                        <div class="flex items-center gap-3 mt-2 text-xs text-gray-500 flex-wrap">
                                            <span>Pendamping: <strong class="text-gray-800">{{ $item->ustadz_pendamping }}</strong></span>
                                            <span>•</span>
                                            <span class="flex items-center gap-1.5">
                                                Posko:
                                                <a href="tel:{{ $itemPosko }}" class="font-mono font-bold text-emerald-700 hover:underline">
                                                    {{ $itemPosko }}
                                                </a>
                                                @if($itemPoskoClean)
                                                    <a href="https://wa.me/{{ $itemPoskoClean }}" target="_blank" rel="noopener noreferrer"
                                                       class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition"
                                                       title="Chat WA Posko">
                                                        <i class="fab fa-whatsapp text-xs"></i>
                                                    </a>
                                                @endif
                                            </span>
                                        </div>

                                        @if($item->topik || $item->keterangan)
                                            <div class="mt-2 text-xs text-gray-600 bg-gray-50 border border-gray-100 p-2.5 rounded-xl">
                                                @if($item->topik)
                                                    <span class="font-bold text-gray-800"><i class="fas fa-tag text-emerald-600 me-1"></i>{{ $item->topik }}</span>
                                                @endif
                                                @if($item->keterangan)
                                                    <span class="text-gray-500 italic block mt-0.5">{{ $item->keterangan }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Right: Status Badges --}}
                                <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 pt-3 sm:pt-0 border-t sm:border-t-0 border-gray-100">
                                    <div>{!! $item->status_badge !!}</div>
                                    @if($item->wa_reminder_sent)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-50 text-green-700 border border-green-200">
                                            <i class="fas fa-check-circle text-green-600"></i>
                                            <span>Pengingat WA Terkirim</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] text-gray-400">
                                            <i class="fas fa-clock text-[10px]"></i>
                                            <span>Reminder Otomatis (H-15m)</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- SECTION: RIWAYAT SAMBUNGAN TELEPON LALU --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center font-bold">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 tracking-tight">
                                Riwayat Sambungan Telepon Sebelumnya
                            </h3>
                            <p class="text-xs text-gray-500">Dokumentasi hasil panggilan dan catatan perkembangan ananda dari Ustadz.</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-gray-400">
                        Arsip Panggilan
                    </span>
                </div>

                @if($riwayatJadwal->isEmpty())
                    <div class="py-12 text-center text-gray-400 px-4">
                        <p class="text-xs font-semibold text-gray-500">Belum ada riwayat sambungan telepon sebelumnya.</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Setelah panggilan terlaksana, riwayat dan catatan Ustadz akan tercatat di sini.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($riwayatJadwal as $item)
                            <div class="p-5 sm:p-6 hover:bg-gray-50/60 transition flex flex-col md:flex-row md:items-start justify-between gap-4">
                                <div class="space-y-1.5 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-bold text-gray-900 text-sm sm:text-base">{{ $item->santri?->nama_lengkap }}</h4>
                                        <span class="text-xs font-semibold text-gray-500">
                                            • {{ $item->formatted_tanggal }}
                                        </span>
                                    </div>

                                    <div class="text-xs text-gray-600 flex items-center gap-3 flex-wrap">
                                        <span><i class="fas fa-clock text-gray-400 me-1"></i>{{ $item->formatted_jam }}</span>
                                        <span>•</span>
                                        <span>Durasi: <strong>{{ $item->durasi_menit }} Menit</strong></span>
                                        <span>•</span>
                                        <span>Pendamping: <strong class="text-gray-800">{{ $item->ustadz_pendamping }}</strong></span>
                                    </div>

                                    {{-- Catatan Panggilan dari Ustadz (Quote Style) --}}
                                    @if($item->catatan_panggilan)
                                        <div class="mt-3 p-3.5 rounded-2xl bg-gradient-to-r from-emerald-50/80 to-teal-50/40 border border-emerald-100/80 text-emerald-950 relative">
                                            <div class="flex items-start gap-2.5">
                                                <i class="fas fa-quote-left text-emerald-500 text-lg shrink-0 mt-0.5"></i>
                                                <div>
                                                    <strong class="text-xs text-emerald-900 font-extrabold block mb-0.5">
                                                        Catatan Ustadz Pendamping:
                                                    </strong>
                                                    <p class="text-xs text-emerald-800 italic leading-relaxed">
                                                        "{{ $item->catatan_panggilan }}"
                                                    </p>
                                                    @if($item->waktu_dihubungi)
                                                        <span class="text-[10px] text-emerald-600 font-medium block mt-1">
                                                            Terhubung pada: {{ $item->waktu_dihubungi->translatedFormat('d F Y, H:i') }} WIB
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="shrink-0">
                                    {!! $item->status_badge !!}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($riwayatJadwal->hasPages())
                        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                            {{ $riwayatJadwal->links() }}
                        </div>
                    @endif
                @endif
            </div>

            {{-- SECTION: TATA TERTIB & TIPS SAMBUNGAN KASIH (4 CARD GRID) --}}
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-emerald-100 shadow-sm space-y-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-xs">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm sm:text-base text-gray-900">
                            Adab & Panduan Sambungan Kasih Santri
                        </h4>
                        <p class="text-xs text-gray-500">Kiat agar sambungan telepon ananda penuh berkah, motivasi, dan kehangatan.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 pt-2">
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100/80 space-y-1.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <h5 class="font-bold text-xs text-emerald-950">1. Disiplin Durasi Waktu</h5>
                        <p class="text-[11px] text-emerald-800 leading-relaxed">
                            Patuhi batas waktu (10–15 menit) agar seluruh santri antrean berikutnya mendapatkan hak giliran yang sama.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-teal-50/70 border border-teal-100/80 space-y-1.5">
                        <div class="w-8 h-8 rounded-xl bg-teal-600 text-white flex items-center justify-center text-xs font-bold">
                            <i class="fas fa-pray"></i>
                        </div>
                        <h5 class="font-bold text-xs text-teal-950">2. Doa & Motivasi Moral</h5>
                        <p class="text-[11px] text-teal-800 leading-relaxed">
                            Utamakan kata-kata penyemangat, doa restu, dan pujian hafalan agar ananda semakin betah dan istiqomah.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-100/80 space-y-1.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xs font-bold">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h5 class="font-bold text-xs text-amber-950">3. Siaga di Dekat Ponsel</h5>
                        <p class="text-[11px] text-amber-800 leading-relaxed">
                            Pastikan nada dering ponsel aktif dan sinyal memadai 15 menit sebelum giliran dimulai agar panggilan langsung terangkat.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100/80 space-y-1.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs font-bold">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h5 class="font-bold text-xs text-blue-950">4. Konfirmasi Jika Ada Halangan</h5>
                        <p class="text-[11px] text-blue-800 leading-relaxed">
                            Jika berhalangan saat jam giliran, segera kabari nomor Ustadz posko via WhatsApp agar dapat dijadwalkan ulang.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
