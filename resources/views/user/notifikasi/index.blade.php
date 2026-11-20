<x-app-layout>
    <x-slot name="title">Notifikasi</x-slot>

    <div class="max-w-3xl mx-auto px-4 py-6 space-y-6">

        {{-- Header --}}
        <div class="flex items-center gap-3">
            <div class="bg-green-100 text-green-600 rounded-xl p-3">
                <i class="ti ti-bell text-2xl"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-800">Notifikasi</h1>
                    @if ($notifikasis->isNotEmpty())
                        <span class="inline-flex items-center justify-center bg-green-600 text-white text-xs font-bold rounded-full w-6 h-6">
                            {{ $notifikasis->count() }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500">Informasi terkini seputar aktivitas santri</p>
            </div>
        </div>

        {{-- Filter Tabs --}}
        @php
            $tabs = [
                'semua'      => ['label' => 'Semua',      'icon' => 'ti-layout-grid'],
                'spp'        => ['label' => 'SPP',         'icon' => 'ti-receipt'],
                'uang_saku'  => ['label' => 'Uang Saku',  'icon' => 'ti-wallet'],
                'tahfidz'    => ['label' => 'Tahfidz',    'icon' => 'ti-book'],
                'absensi'    => ['label' => 'Absensi',    'icon' => 'ti-calendar-check'],
                'perizinan'  => ['label' => 'Perizinan',  'icon' => 'ti-door-exit'],
                'infaq'      => ['label' => 'Infaq',      'icon' => 'ti-heart-handshake'],
            ];
            $activeTab = request('tipe', 'semua');
        @endphp

        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none -mx-4 px-4">
            @foreach ($tabs as $key => $tab)
                @php
                    $isActive = $activeTab === $key;
                @endphp
                <a href="{{ request()->fullUrlWithQuery(['tipe' => $key]) }}"
                   class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium transition-colors
                          {{ $isActive
                              ? 'bg-green-600 text-white shadow-sm'
                              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    <i class="{{ $tab['icon'] }} text-sm"></i>
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        {{-- Notification List --}}
        @php
            $colorMap = [
                'green'  => ['bg' => 'bg-green-100',  'text' => 'text-green-600'],
                'red'    => ['bg' => 'bg-red-100',    'text' => 'text-red-600'],
                'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600'],
                'blue'   => ['bg' => 'bg-blue-100',   'text' => 'text-blue-600'],
                'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
                'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600'],
            ];

            $filtered = $activeTab === 'semua'
                ? $notifikasis
                : $notifikasis->filter(fn($n) => ($n['tipe'] ?? '') === $activeTab)->values();
        @endphp

        @if ($filtered->isEmpty())
            {{-- Empty State --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-20 flex flex-col items-center gap-4 text-gray-400">
                <div class="bg-gray-100 rounded-full p-5">
                    <i class="ti ti-bell-off text-4xl text-gray-400"></i>
                </div>
                <div class="text-center">
                    <p class="text-base font-semibold text-gray-600">Tidak ada notifikasi</p>
                    <p class="text-sm text-gray-400 mt-1">
                        @if ($activeTab !== 'semua')
                            Tidak ada notifikasi untuk kategori ini.
                        @else
                            Semua informasi terbaru akan muncul di sini.
                        @endif
                    </p>
                </div>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($filtered as $notif)
                    @php
                        $colors = $colorMap[$notif['warna']] ?? $colorMap['blue'];
                        $tgl = \Carbon\Carbon::parse($notif['tanggal']);
                    @endphp
                    <a href="{{ $notif['link'] }}"
                       class="flex items-start gap-4 bg-white rounded-2xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-green-100 transition-all group">

                        {{-- Icon --}}
                        <div class="shrink-0 {{ $colors['bg'] }} {{ $colors['text'] }} rounded-xl p-3 mt-0.5">
                            <i class="ti {{ $notif['icon'] }} text-xl"></i>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-semibold text-gray-800 text-sm leading-snug group-hover:text-green-700 transition-colors">
                                    {{ $notif['judul'] }}
                                </p>
                                <span class="shrink-0 text-xs text-gray-400 whitespace-nowrap mt-0.5">
                                    {{ $tgl->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5 leading-relaxed line-clamp-2">
                                {{ $notif['pesan'] }}
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="inline-flex items-center gap-1 text-xs {{ $colors['text'] }} font-medium">
                                    <i class="ti ti-tag text-xs"></i>
                                    {{ ucfirst(str_replace('_', ' ', $notif['tipe'] ?? '-')) }}
                                </span>
                                <span class="text-gray-300">·</span>
                                <span class="text-xs text-gray-400">
                                    {{ $tgl->translatedFormat('d F Y, H:i') }}
                                </span>
                            </div>
                        </div>

                        {{-- Arrow --}}
                        <div class="shrink-0 text-gray-300 group-hover:text-green-500 transition-colors mt-1">
                            <i class="ti ti-chevron-right text-lg"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </div>
</x-app-layout>
