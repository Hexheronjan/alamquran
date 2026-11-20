<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <i class="ti ti-heart-rate-monitor text-2xl text-green-600"></i>
            <h2 class="text-xl font-semibold text-gray-800">Kondisi Santri</h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-20">

            {{-- ============================================================ --}}
            {{-- SANTRI SELECTOR PILLS --}}
            {{-- ============================================================ --}}
            @if ($santris->count() > 1)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <p class="text-xs text-gray-500 font-medium mb-3 uppercase tracking-wide">Pilih Santri</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($santris as $s)
                            <a href="{{ route('user.kondisi', ['santri_id' => $s->id]) }}"
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

            {{-- ============================================================ --}}
            {{-- KONDISI TERKINI --}}
            {{-- ============================================================ --}}
            @if ($kondisiTerbaru)
                @php
                    $statusConfig = [
                        'baik'              => ['label' => 'Baik',              'bg' => 'bg-green-100',  'text' => 'text-green-700',  'icon' => 'ti-circle-check'],
                        'sakit'             => ['label' => 'Sakit',             'bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => 'ti-alert-circle'],
                        'istirahat_uks'     => ['label' => 'Istirahat di UKS',  'bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'icon' => 'ti-bed'],
                        'pantauan'          => ['label' => 'Dalam Pantauan',    'bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'icon' => 'ti-eye'],
                        'rawat_inap'        => ['label' => 'Rawat Inap / RS',   'bg' => 'bg-red-100',    'text' => 'text-red-700',    'icon' => 'ti-building-hospital'],
                        'istirahat_dirumah' => ['label' => 'Istirahat di Rumah', 'bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'icon' => 'ti-home-heart'],
                        'sembuh'            => ['label' => 'Sembuh',            'bg' => 'bg-emerald-100','text' => 'text-emerald-700','icon' => 'ti-circle-check'],
                        'perlu_perhatian'   => ['label' => 'Perlu Perhatian',   'bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'icon' => 'ti-eye'],
                    ];
                    $sc = $statusConfig[$kondisiTerbaru->status_kesehatan] ?? ['label' => ucfirst(str_replace('_', ' ', $kondisiTerbaru->status_kesehatan)), 'bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'icon' => 'ti-point'];
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 {{ $kondisiTerbaru->status_kesehatan === 'istirahat_dirumah' ? 'bg-purple-50' : 'bg-blue-50' }}">
                        <i class="ti {{ $kondisiTerbaru->status_kesehatan === 'istirahat_dirumah' ? 'ti-home-heart text-purple-600' : 'ti-stethoscope text-blue-600' }} text-xl"></i>
                        <h3 class="font-semibold text-gray-800 text-base">Kondisi Terkini</h3>
                    </div>

                    <div class="p-5 space-y-4">
                        {{-- Status & Tanggal --}}
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold {{ $sc['bg'] }} {{ $sc['text'] }}">
                                <i class="ti {{ $sc['icon'] }} text-base"></i>
                                {{ $sc['label'] }}
                            </span>
                            <div class="flex items-center gap-1.5 text-gray-500 text-sm">
                                <i class="ti ti-calendar text-base"></i>
                                <span>{{ \Carbon\Carbon::parse($kondisiTerbaru->tanggal)->translatedFormat('d F Y') }}</span>
                            </div>
                        </div>

                        {{-- Info Khusus Istirahat di Rumah --}}
                        @if($kondisiTerbaru->status_kesehatan === 'istirahat_dirumah')
                            <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 space-y-2">
                                <div class="flex items-center gap-2 font-bold text-sm text-purple-900">
                                    <i class="ti ti-car text-lg"></i> Informasi Penjemputan Santri
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-1">
                                    <div>
                                        <span class="text-purple-600 block">Penjemput:</span>
                                        <strong class="text-gray-800">{{ $kondisiTerbaru->nama_penjemput ?: '-' }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-purple-600 block">Waktu Jemput:</span>
                                        <strong class="text-gray-800">{{ $kondisiTerbaru->tanggal_jemput ? \Carbon\Carbon::parse($kondisiTerbaru->tanggal_jemput)->translatedFormat('d M Y, H:i') : '-' }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-purple-600 block">Estimasi Kembali:</span>
                                        <strong class="text-gray-800">{{ $kondisiTerbaru->estimasi_kembali ? \Carbon\Carbon::parse($kondisiTerbaru->estimasi_kembali)->translatedFormat('d M Y') : 'Menyesuaikan kesembuhan' }}</strong>
                                    </div>
                                </div>
                                @if($kondisiTerbaru->catatan_penjemputan)
                                    <p class="text-xs text-purple-800 bg-white/70 p-2.5 rounded-lg border border-purple-100 mt-2">
                                        <strong>Pesan / Catatan:</strong> {{ $kondisiTerbaru->catatan_penjemputan }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        {{-- Detail grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Keluhan Sakit</p>
                                <p class="text-gray-700 text-sm leading-relaxed">{{ $kondisiTerbaru->keluhan_sakit ?: '-' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Tindakan Perawatan</p>
                                <p class="text-gray-700 text-sm leading-relaxed">{{ $kondisiTerbaru->tindakan_perawatan ?: '-' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Obat Diberikan</p>
                                <p class="text-gray-700 text-sm leading-relaxed">{{ $kondisiTerbaru->obat_diberikan ?: '-' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Dicatat oleh</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                        <i class="ti ti-user-check text-green-600 text-sm"></i>
                                    </div>
                                    <p class="text-gray-700 text-sm font-medium">{{ $kondisiTerbaru->petugas?->name ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-green-50 border border-green-200 rounded-2xl p-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="ti ti-heart text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-green-800 text-sm">Alhamdulillah</p>
                        <p class="text-green-700 text-sm mt-0.5">Tidak ada catatan kondisi kesehatan untuk santri ini.</p>
                    </div>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- RIWAYAT KONDISI --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
                    <i class="ti ti-history text-xl text-gray-500"></i>
                    <h3 class="font-semibold text-gray-800 text-base">Riwayat Kondisi Kesehatan</h3>
                </div>

                @php
                    $statusMap = [
                        'baik'              => ['label' => 'Baik',              'bg' => 'bg-green-100',  'text' => 'text-green-700'],
                        'sakit'             => ['label' => 'Sakit',             'bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
                        'istirahat_uks'     => ['label' => 'Istirahat di UKS',  'bg' => 'bg-amber-100',  'text' => 'text-amber-700'],
                        'pantauan'          => ['label' => 'Dalam Pantauan',    'bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
                        'rawat_inap'        => ['label' => 'Rawat Inap / RS',   'bg' => 'bg-red-100',    'text' => 'text-red-700'],
                        'istirahat_dirumah' => ['label' => 'Istirahat di Rumah', 'bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
                        'sembuh'            => ['label' => 'Sembuh',            'bg' => 'bg-emerald-100','text' => 'text-emerald-700'],
                        'perlu_perhatian'   => ['label' => 'Perlu Perhatian',   'bg' => 'bg-orange-100', 'text' => 'text-orange-700'],
                    ];
                @endphp

                @forelse ($kondisiList as $kondisi)
                    @php
                        $sm = $statusMap[$kondisi->status_kesehatan] ?? ['label' => ucfirst($kondisi->status_kesehatan), 'bg' => 'bg-gray-100', 'text' => 'text-gray-600'];
                    @endphp
                    {{-- Mobile card (sm:hidden) --}}
                    <div class="sm:hidden border-b border-gray-100 last:border-b-0 p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($kondisi->tanggal)->translatedFormat('d M Y') }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sm['bg'] }} {{ $sm['text'] }}">
                                {{ $sm['label'] }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-800"><span class="font-medium">Keluhan:</span> {{ $kondisi->keluhan_sakit ?: '-' }}</p>
                        <p class="text-sm text-gray-700"><span class="font-medium">Tindakan:</span> {{ $kondisi->tindakan_perawatan ?: '-' }}</p>
                        <p class="text-sm text-gray-700"><span class="font-medium">Obat:</span> {{ $kondisi->obat_diberikan ?: '-' }}</p>
                        <p class="text-xs text-gray-400 flex items-center gap-1">
                            <i class="ti ti-user-check"></i> {{ $kondisi->petugas?->name ?? '-' }}
                        </p>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-12 text-center px-5">
                        <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                            <i class="ti ti-clipboard-off text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 text-sm">Belum ada riwayat kondisi kesehatan</p>
                    </div>
                @endforelse

                {{-- Desktop table (hidden on mobile) --}}
                @if ($kondisiList->isNotEmpty())
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-left">
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Keluhan</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tindakan</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Obat Diberikan</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Petugas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($kondisiList as $kondisi)
                                    @php
                                        $sm = $statusMap[$kondisi->status_kesehatan] ?? ['label' => ucfirst($kondisi->status_kesehatan), 'bg' => 'bg-gray-100', 'text' => 'text-gray-600'];
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-5 py-3 text-gray-700 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($kondisi->tanggal)->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="px-5 py-3 text-gray-700 max-w-[160px]">
                                            <span class="line-clamp-2">{{ $kondisi->keluhan_sakit ?: '-' }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-gray-700 max-w-[160px]">
                                            <span class="line-clamp-2">{{ $kondisi->tindakan_perawatan ?: '-' }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-gray-700 max-w-[140px]">
                                            <span class="line-clamp-2">{{ $kondisi->obat_diberikan ?: '-' }}</span>
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sm['bg'] }} {{ $sm['text'] }}">
                                                {{ $sm['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-gray-700 whitespace-nowrap">
                                            {{ $kondisi->petugas?->name ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Pagination --}}
                @if ($kondisiList->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $kondisiList->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>

            {{-- ============================================================ --}}
            {{-- INFO BOX --}}
            {{-- ============================================================ --}}
            <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4">
                <i class="ti ti-info-circle text-xl text-blue-500 flex-shrink-0 mt-0.5"></i>
                <p class="text-blue-700 text-sm leading-relaxed">
                    Data kondisi santri dicatat oleh petugas kesehatan pesantren.
                    Hubungi pesantren untuk informasi lebih lanjut.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>
