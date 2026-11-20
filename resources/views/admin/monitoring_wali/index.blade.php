@extends('layouts.admin.app')

@section('title', 'Monitoring Online & Aktivitas Wali Santri')

@section('content')
<div class="bg-gray-100 min-h-screen py-6 px-4 lg:px-8">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <i class="ti ti-activity-heartbeat text-emerald-600"></i> Monitoring Online Wali Santri
            </h1>
            <p class="text-xs text-gray-500 mt-1">Pantau status online real-time, frekuensi login, dan keaktifan akun wali santri dalam memantau perkembangan ananda.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.monitoring.wali') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 shadow-xs transition">
                <i class="ti ti-refresh"></i> Refresh Data
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-200 flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shrink-0 relative">
                <i class="ti ti-circle-filled text-emerald-500 animate-pulse"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Online Sekarang</span>
                <span class="text-xl font-black text-emerald-600">{{ number_format($onlineNowCount) }}</span>
                <span class="text-[10px] text-gray-500 block">Aktif dlm 10 menit terakhir</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-200 flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-2xl shrink-0">
                <i class="ti ti-calendar-time"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Login Hari Ini</span>
                <span class="text-xl font-black text-teal-700">{{ number_format($loginHariIniCount) }}</span>
                <span class="text-[10px] text-gray-500 block">Wali mengakses hari ini</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-200 flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shrink-0">
                <i class="ti ti-users"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Akun Wali</span>
                <span class="text-xl font-black text-gray-900">{{ number_format($totalWali) }}</span>
                <span class="text-[10px] text-gray-500 block">{{ number_format($pernahLoginCount) }} pernah login</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-200 flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-2xl shrink-0">
                <i class="ti ti-user-x"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Belum Pernah Login</span>
                <span class="text-xl font-black text-red-600">{{ number_format($belumPernahCount) }}</span>
                <span class="text-[10px] text-gray-500 block">Perlu pendampingan / WA</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-200 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Filter Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('admin.monitoring.wali', ['filter' => 'all', 'search' => request('search')]) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ $filter === 'all' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Semua ({{ $totalWali }})
            </a>
            <a href="{{ route('admin.monitoring.wali', ['filter' => 'online', 'search' => request('search')]) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 flex items-center gap-1.5 {{ $filter === 'online' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Online ({{ $onlineNowCount }})
            </a>
            <a href="{{ route('admin.monitoring.wali', ['filter' => 'hari_ini', 'search' => request('search')]) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ $filter === 'hari_ini' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Hari Ini ({{ $loginHariIniCount }})
            </a>
            <a href="{{ route('admin.monitoring.wali', ['filter' => 'paling_aktif', 'search' => request('search')]) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ $filter === 'paling_aktif' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Paling Sering Login
            </a>
            <a href="{{ route('admin.monitoring.wali', ['filter' => 'belum_pernah', 'search' => request('search')]) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ $filter === 'belum_pernah' ? 'bg-red-600 text-white shadow-xs' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">
                Belum Pernah ({{ $belumPernahCount }})
            </a>
        </div>

        <!-- Search Input -->
        <form method="GET" action="{{ route('admin.monitoring.wali') }}" class="flex items-center gap-2">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari wali / no WA / anak..."
                   class="text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 w-60">
            <button type="submit" class="px-3 py-2 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer"
                    style="background-color: #059669 !important; color: #ffffff !important;">
                <i class="ti ti-search" style="color: #ffffff !important;"></i>
            </button>
            @if(request('search'))
                <a href="{{ route('admin.monitoring.wali', ['filter' => $filter]) }}" class="px-2.5 py-2 bg-gray-100 text-gray-600 rounded-xl text-xs hover:bg-gray-200" title="Reset">
                    <i class="ti ti-x"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-black text-sm text-gray-900 uppercase tracking-wider flex items-center gap-2">
                <i class="ti ti-list text-emerald-600"></i> Daftar Akun & Riwayat Aktivitas Wali Santri
            </h3>
            <span class="text-xs text-gray-500 font-semibold">Menampilkan {{ $walis->firstItem() ?? 0 }}-{{ $walis->lastItem() ?? 0 }} dari {{ $walis->total() }} Wali</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50/80 text-gray-700 uppercase font-black text-[11px] tracking-wider border-b border-gray-200">
                    <tr>
                        <th class="py-3.5 px-4 text-center w-12">#</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Nama Wali Santri</th>
                        <th class="py-3.5 px-4">Santri / Anak</th>
                        <th class="py-3.5 px-4 text-center">Frekuensi Login</th>
                        <th class="py-3.5 px-4">Terakhir Aktif / Login</th>
                        <th class="py-3.5 px-4 text-center w-28">Hubungi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($walis as $index => $wali)
                        @php
                            $isOnline = $wali->isOnline();
                            $cleanWa = preg_replace('/[^0-9]/', '', $wali->no_wa ?? '');
                            if (str_starts_with($cleanWa, '0')) {
                                $cleanWa = '62' . substr($cleanWa, 1);
                            }
                        @endphp
                        <tr class="hover:bg-emerald-50/30 transition">
                            <td class="py-3.5 px-4 text-center font-bold text-gray-400">
                                {{ $walis->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($isOnline)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        ONLINE
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        Offline
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900 text-sm">{{ $wali->name }}</div>
                                <div class="text-[11px] text-gray-500 font-mono mt-0.5">
                                    <i class="ti ti-brand-whatsapp text-emerald-600"></i> {{ $wali->no_wa ?? '-' }}
                                    @if($wali->email)
                                        • <span class="text-gray-400 font-sans">{{ $wali->email }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($wali->santri && $wali->santri->isNotEmpty())
                                    <div class="space-y-1">
                                        @foreach($wali->santri as $s)
                                            <div class="font-bold text-gray-900 text-xs">
                                                {{ $s->nama_lengkap }}
                                                <span class="text-[10px] font-medium text-gray-500">({{ $s->nis ?? '-' }} / {{ $s->kelas ?? 'Kelas -' }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-[11px]">Belum terhubung santri</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if(($wali->login_count ?? 0) > 0)
                                    <span class="inline-block px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-800 border border-teal-200">
                                        {{ number_format($wali->login_count) }} kali
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-xl text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                        0 kali (Belum)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($wali->last_activity_at || $wali->last_login_at)
                                    <div class="font-bold text-gray-900 text-xs">
                                        {{ ($wali->last_activity_at ?? $wali->last_login_at)->diffForHumans() }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">
                                        {{ ($wali->last_login_at ?? $wali->last_activity_at)->translatedFormat('d M Y, H:i') }} WIB
                                        @if($wali->last_login_ip)
                                            • <span class="font-mono text-gray-400">{{ $wali->last_login_ip }}</span>
                                        @endif
                                    </div>
                                @else
                                    @if($wali->otp_created_at)
                                        <div class="text-[11px] text-amber-700 font-semibold flex items-center gap-1">
                                            <i class="ti ti-brand-whatsapp text-emerald-600"></i> OTP dikirim {{ $wali->otp_created_at->diffForHumans() }}
                                        </div>
                                        <div class="text-[10px] text-gray-400">Menunggu verifikasi masuk</div>
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">Belum pernah login</span>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if(!empty($cleanWa))
                                    @php
                                        $salamNama = rawurlencode("Assalamu'alaikum Warahmatullahi Wabarakatuh, Bapak/Ibu {$wali->name}. Kami dari Admin Pondok Pesantren Alam Qur'an ingin menginformasikan perkembangan santri...");
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanWa }}?text={{ $salamNama }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 shadow-xs transition" title="Kirim WhatsApp">
                                        <i class="ti ti-brand-whatsapp text-sm"></i> Chat WA
                                    </a>
                                @else
                                    <span class="text-gray-300 text-[11px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                <i class="ti ti-user-search text-4xl block mb-2 text-gray-300"></i>
                                Tidak ditemukan data wali santri dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($walis->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $walis->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
