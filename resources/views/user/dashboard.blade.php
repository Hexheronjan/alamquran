<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <i class="ti ti-home text-green-600"></i> Dashboard Wali Santri
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-20">

            {{-- Alert: Izin Aktif --}}
            @if($dashboardSummary && $dashboardSummary['izinAktif'])
                @php $izin = $dashboardSummary['izinAktif']; @endphp
                <div class="rounded-xl border-2 border-orange-400 bg-orange-50 p-4 flex items-start gap-3">
                    <i class="ti ti-alert-triangle text-orange-500 text-2xl mt-0.5"></i>
                    <div>
                        <p class="font-bold text-orange-800 text-sm">Izin Aktif — Santri Sedang di Luar</p>
                        <p class="text-orange-700 text-xs mt-0.5">
                            <strong>{{ $izin->santri->nama_lengkap }}</strong> —
                            {{ $izin->jenis_izin === 'pulang' ? 'Pulang ke Rumah' : 'Keluar Sementara' }}
                            @if($izin->batas_jam_kembali)
                                • Batas kembali: <strong>{{ \Carbon\Carbon::parse($izin->batas_jam_kembali)->format('d M Y H:i') }}</strong>
                                @if(\Carbon\Carbon::parse($izin->batas_jam_kembali)->isPast())
                                    <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded ms-1">TERLAMBAT!</span>
                                @endif
                            @endif
                        </p>
                        <a href="{{ route('user.perizinan') }}" class="text-orange-600 text-xs underline font-medium">Lihat Detail Perizinan →</a>
                    </div>
                </div>
            @endif

            {{-- Alert: Jadwal Telepon Mendatang --}}
            @if($dashboardSummary && !empty($dashboardSummary['jadwalTeleponMendatang']))
                @php $jt = $dashboardSummary['jadwalTeleponMendatang']; @endphp
                <div class="rounded-2xl border-2 border-emerald-400 bg-gradient-to-r from-emerald-50 via-teal-50 to-green-50 p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md">
                            <i class="fas fa-phone-volume text-lg animate-pulse"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="bg-emerald-600 text-white text-[11px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Jadwal Telepon</span>
                                @if($jt->is_today)
                                    <span class="bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-bounce">HARI INI</span>
                                @endif
                                <span class="text-xs text-gray-500 font-medium">{{ $jt->santri?->nama_lengkap }} (Kelas {{ $jt->santri?->kelas }})</span>
                            </div>
                            <h4 class="font-bold text-gray-900 text-sm sm:text-base mt-1">
                                {{ $jt->formatted_tanggal }} • Pukul {{ $jt->formatted_jam }}
                            </h4>
                            <p class="text-xs text-gray-600 mt-0.5">
                                Pendamping: <span class="font-semibold text-gray-800">{{ $jt->ustadz_pendamping }}</span> | No. Penelepon: <span class="font-semibold text-emerald-700">{{ $jt->no_hp_posko }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 w-full sm:w-auto">
                        <a href="{{ route('user.telepon.index', ['santri_id' => $jt->santri_id]) }}" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                            <span>Lihat Jadwal & Info</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Selamat Datang --}}
            <div style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%);" class="rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-xs font-medium mb-1">Selamat datang,</p>
                        <h3 class="text-xl font-bold">{{ Auth::user()->name }}</h3>
                        <p class="text-green-200 text-xs mt-1">{{ $schools->name }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center">
                        <i class="ti ti-user text-white text-2xl"></i>
                    </div>
                </div>

                {{-- Santri Summary --}}
                @if($santris->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($santris as $s)
                            <div class="bg-white/20 rounded-lg px-3 py-1.5 text-xs">
                                <span class="font-bold">{{ $s->nama_lengkap }}</span> — Kelas {{ $s->kelas }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Summary Cards (jika ada santri) --}}
            @if($dashboardSummary)
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <a href="{{ route('user.uang_saku') }}" class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                                <i class="ti ti-wallet text-green-600"></i>
                            </div>
                            <span class="text-xs text-gray-500 font-medium">Total Saldo</span>
                        </div>
                        <p class="font-bold text-gray-900 text-sm">Rp {{ number_format($dashboardSummary['totalSaldo'], 0, ',', '.') }}</p>
                    </a>

                    <a href="{{ route('user.spp.index') }}" class="bg-white rounded-xl p-4 border {{ $dashboardSummary['sppPendingCount'] > 0 ? 'border-orange-200 bg-orange-50' : 'border-gray-100' }} shadow-sm hover:shadow-md transition">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg {{ $dashboardSummary['sppPendingCount'] > 0 ? 'bg-orange-100' : 'bg-blue-50' }} flex items-center justify-center">
                                <i class="ti ti-receipt {{ $dashboardSummary['sppPendingCount'] > 0 ? 'text-orange-600' : 'text-blue-600' }}"></i>
                            </div>
                            <span class="text-xs text-gray-500 font-medium">SPP Pending</span>
                        </div>
                        <p class="font-bold text-sm {{ $dashboardSummary['sppPendingCount'] > 0 ? 'text-orange-700' : 'text-gray-900' }}">
                            {{ $dashboardSummary['sppPendingCount'] > 0 ? $dashboardSummary['sppPendingCount'] . ' Tagihan' : 'Lunas ✓' }}
                        </p>
                    </a>

                    <a href="{{ route('user.tahfidz') }}" class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center">
                                <i class="ti ti-book text-teal-600"></i>
                            </div>
                            <span class="text-xs text-gray-500 font-medium">Setoran Bulan Ini</span>
                        </div>
                        <p class="font-bold text-gray-900 text-sm">{{ $dashboardSummary['setoranBulanIni'] }} Setoran</p>
                    </a>

                    <a href="{{ route('user.absensi') }}" class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                                <i class="ti ti-calendar-check text-purple-600"></i>
                            </div>
                            <span class="text-xs text-gray-500 font-medium">Absensi Hari Ini</span>
                        </div>
                        @if($dashboardSummary['absensiHariIni']->isNotEmpty())
                            @foreach($dashboardSummary['absensiHariIni'] as $abs)
                                <p class="text-xs font-bold
                                    {{ $abs->status_kehadiran === 'hadir' ? 'text-green-700' : ($abs->status_kehadiran === 'alpa' ? 'text-red-700' : 'text-yellow-700') }}">
                                    {{ ucfirst($abs->status_kehadiran) }}
                                </p>
                            @endforeach
                        @else
                            <p class="text-xs text-gray-400">Belum tercatat</p>
                        @endif
                    </a>

                    <a href="{{ route('user.perizinan') }}" class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                                <i class="ti ti-qrcode text-indigo-600"></i>
                            </div>
                            <span class="text-xs text-gray-500 font-medium">QR / Perizinan</span>
                        </div>
                        <p class="font-bold text-gray-900 text-sm">
                            {{ $dashboardSummary['izinAktif'] ? '⚠️ Izin Aktif' : 'Lihat QR →' }}
                        </p>
                    </a>

                    <a href="{{ route('user.notifikasi') }}" class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                                <i class="ti ti-bell text-orange-600"></i>
                            </div>
                            <span class="text-xs text-gray-500 font-medium">Notifikasi</span>
                        </div>
                        <p class="font-bold text-gray-900 text-sm">
                            {{ ($dashboardSummary['sppPendingCount'] + $dashboardSummary['infaqPendingCount']) > 0
                                ? ($dashboardSummary['sppPendingCount'] + $dashboardSummary['infaqPendingCount']) . ' Perlu Tindakan'
                                : 'Lihat Semua →' }}
                        </p>
                    </a>
                </div>

                {{-- Quick Access Menu --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Akses Cepat</h3>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                        @php
                            $menus = [
                                ['route' => 'user.uang_saku',    'icon' => 'ti-wallet',              'label' => 'Uang Saku',    'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
                                ['route' => 'user.spp.index',     'icon' => 'ti-receipt',             'label' => 'SPP',          'bg' => 'bg-blue-50',    'text' => 'text-blue-600'],
                                ['route' => 'user.tahfidz',       'icon' => 'ti-book',                'label' => 'Tahfidz',      'bg' => 'bg-teal-50',    'text' => 'text-teal-600'],
                                ['route' => 'user.absensi',       'icon' => 'ti-calendar-check',      'label' => 'Absensi',      'bg' => 'bg-purple-50',  'text' => 'text-purple-600'],
                                ['route' => 'user.kegiatan',      'icon' => 'ti-calendar-event',      'label' => 'Kegiatan',     'bg' => 'bg-green-50',   'text' => 'text-green-600'],
                                ['route' => 'user.kondisi',       'icon' => 'ti-heart-rate-monitor',  'label' => 'Kondisi',      'bg' => 'bg-rose-50',    'text' => 'text-rose-600'],
                                ['route' => 'user.perizinan',     'icon' => 'ti-qrcode',              'label' => 'Perizinan',    'bg' => 'bg-indigo-50',  'text' => 'text-indigo-600'],
                                ['route' => 'user.infaq',         'icon' => 'ti-heart',               'label' => 'Infaq',        'bg' => 'bg-pink-50',    'text' => 'text-pink-600'],
                                ['route' => 'user.kartu_santri',  'icon' => 'ti-id-badge',            'label' => 'Kartu Santri', 'bg' => 'bg-sky-50',     'text' => 'text-sky-600'],
                                ['route' => 'user.surat',         'icon' => 'ti-mail',                'label' => 'Surat',        'bg' => 'bg-violet-50',  'text' => 'text-violet-600'],
                                ['route' => 'user.notifikasi',    'icon' => 'ti-bell',                'label' => 'Notifikasi',   'bg' => 'bg-amber-50',   'text' => 'text-amber-600'],
                                ['route' => 'user.profile.edit',  'icon' => 'ti-user-edit',           'label' => 'Profil',       'bg' => 'bg-gray-100',   'text' => 'text-gray-600'],
                            ];
                        @endphp
                        @foreach($menus as $menu)
                            <a href="{{ route($menu['route']) }}"
                               class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-gray-50 transition text-center group">
                                <div class="w-10 h-10 rounded-xl {{ $menu['bg'] }} flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i class="ti {{ $menu['icon'] }} {{ $menu['text'] }} text-xl"></i>
                                </div>
                                <span class="text-[11px] font-medium text-gray-700 leading-tight">{{ $menu['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Pendaftaran Santri Baru --}}
            <div style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%); color: #ffffff;" class="shadow-xl rounded-2xl overflow-hidden">
                <div class="p-6 sm:p-8 text-center">
                    <h3 style="color: #ffffff;" class="text-2xl font-extrabold mb-2">Pendaftaran Santri Baru</h3>
                    <p style="color: #e0f2fe;" class="text-sm mb-5">Daftarkan ananda untuk tahun ajaran baru di {{ $schools->name }}</p>

                    <div class="flex justify-center mb-5">
                        @if($angkatanAktif)
                            <div class="inline-flex flex-col sm:flex-row sm:items-center gap-x-2 gap-y-1 bg-teal-800 bg-opacity-40 rounded-xl px-5 py-3 text-sm border border-teal-600 border-opacity-40">
                                <span class="font-bold flex items-center gap-1.5"><i class="fas fa-calendar-check text-green-300"></i> Pendaftaran DIBUKA:</span>
                                <span><strong>{{ $angkatanAktif->tanggal_mulai_daftar->translatedFormat('d M Y • H:i') }} WIB</strong> s/d <strong>{{ $angkatanAktif->tanggal_tutup_daftar->translatedFormat('d M Y • H:i') }} WIB</strong></span>
                            </div>
                        @elseif($angkatanMendatang)
                            <div class="inline-flex flex-col sm:flex-row sm:items-center gap-x-2 gap-y-1 bg-yellow-800 bg-opacity-35 rounded-xl px-5 py-3 text-sm border border-yellow-600 border-opacity-30">
                                <span class="font-bold flex items-center gap-1.5 text-yellow-300"><i class="fas fa-calendar-alt"></i> Pendaftaran AKAN DIBUKA:</span>
                                <span><strong>{{ $angkatanMendatang->tanggal_mulai_daftar->translatedFormat('d M Y • H:i') }} WIB</strong></span>
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1.5 bg-red-850 bg-opacity-25 rounded-xl px-4 py-2 text-xs border border-red-500 border-opacity-20 text-red-200">
                                <i class="fas fa-info-circle text-red-300"></i> Jadwal pendaftaran belum ditentukan.
                            </div>
                        @endif
                    </div>

                    <div>
                        <a href="{{ route('santri.registration') }}"
                            style="background-color: #ffffff; color: #059669;"
                            class="inline-flex items-center gap-2 font-extrabold py-3 px-7 rounded-xl shadow hover:bg-gray-100 transition text-base">
                            <i class="fas fa-user-plus"></i> {{ $angkatanAktif ? 'Daftar Sekarang' : 'Lihat Detail' }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Formulir / Form Groups --}}
            @if($formGroup->isNotEmpty())
                <div>
                    <h3 class="text-sm font-bold text-gray-700 mb-3">Formulir Pendaftaran</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($formGroup as $form)
                            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-5 hover:shadow-md transition">
                                <h4 class="text-sm font-bold text-gray-800">{{ $form->name }}</h4>
                                <p class="text-gray-500 mt-1.5 text-xs">{{ $form->description ?? 'Tidak ada deskripsi.' }}</p>
                                <div class="mt-3">
                                    <a href="{{ route('user.form.show', ['id_form' => $form->id_form]) }}"
                                       class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg text-white transition"
                                       style="background-color:#16a34a;">
                                        <i class="ti ti-external-link"></i> Lihat Form
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
