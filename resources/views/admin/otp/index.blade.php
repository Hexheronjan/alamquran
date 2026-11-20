@extends('layouts.admin.app')

@section('title', 'Monitoring OTP')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8">

        {{-- Header Halaman --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2.5">
                    <i class="ti ti-shield-lock text-teal-600 text-3xl"></i> Monitoring OTP Pendaftar
                </h1>
                <p class="text-sm text-gray-600 mt-1">
                    Pantau kode OTP aktif, status pengiriman WhatsApp Gateway, dan fallback bantuan manual untuk pendaftar.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.otp.index') }}" class="px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm inline-flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> Refresh Data
                </a>
            </div>
        </div>

        {{-- Alert Notifikasi --}}
        @if(session('success'))
            <div class="mb-5 rounded-xl bg-green-50 border border-green-200 p-4 text-sm font-medium text-green-800 flex items-start gap-3 shadow-sm">
                <i class="fas fa-check-circle text-green-600 text-lg mt-0.5"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 rounded-xl bg-red-50 border border-red-200 p-4 text-sm font-medium text-red-800 flex items-start gap-3 shadow-sm">
                <i class="fas fa-exclamation-circle text-red-600 text-lg mt-0.5"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        {{-- Banner Informasi Fallback / Emergency --}}
        <div class="mb-6 rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-50 to-orange-50 border border-amber-200 p-5 shadow-sm">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 text-xl font-bold shadow">
                    <i class="fas fa-life-ring"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-amber-900 uppercase tracking-wide">
                        Panduan Bantuan Darurat (Fallback WhatsApp Gateway)
                    </h3>
                    <p class="text-xs sm:text-sm text-amber-800 mt-1 leading-relaxed">
                        Jika pendaftar <strong>tidak menerima OTP</strong> karena WhatsApp Gateway, server, atau nomor tujuan sedang bermasalah:
                        admin dapat melihat kode OTP yang masih aktif di bawah, lalu klik tombol <strong>"Salin OTP"</strong> dan berikan kode tersebut secara manual kepada pendaftar melalui telepon, SMS, atau jalur alternatif. Pendaftar tetap memverifikasi di halaman verifikasi yang sama.
                    </p>
                </div>
            </div>
        </div>

        {{-- 4 Kartu Ringkasan (Metric Cards) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Total OTP Aktif --}}
            <a href="{{ route('admin.otp.index', ['filter' => 'active', 'search' => request('search')]) }}"
               class="p-5 rounded-2xl bg-white border transition shadow-sm hover:shadow-md {{ $filter === 'active' ? 'border-teal-500 ring-2 ring-teal-200' : 'border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total OTP Aktif</span>
                    <span class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg">
                        <i class="fas fa-key"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-teal-700">{{ $totalActive }}</span>
                    <span class="text-xs text-gray-500">kode aktif</span>
                </div>
                <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                    <i class="fas fa-circle text-[8px] text-teal-500"></i> Siap digunakan pendaftar
                </p>
            </a>

            {{-- Berhasil Terkirim --}}
            <a href="{{ route('admin.otp.index', ['filter' => 'success', 'search' => request('search')]) }}"
               class="p-5 rounded-2xl bg-white border transition shadow-sm hover:shadow-md {{ $filter === 'success' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Terkirim ke WhatsApp</span>
                    <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                        <i class="fab fa-whatsapp"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-blue-700">{{ $totalSuccess }}</span>
                    <span class="text-xs text-gray-500">sukses</span>
                </div>
                <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                    <i class="fas fa-check text-xs text-blue-500"></i> Gateway sukses mengirim
                </p>
            </a>

            {{-- Gagal Terkirim --}}
            <a href="{{ route('admin.otp.index', ['filter' => 'failed', 'search' => request('search')]) }}"
               class="p-5 rounded-2xl bg-white border transition shadow-sm hover:shadow-md {{ $filter === 'failed' ? 'border-red-500 ring-2 ring-red-200' : 'border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Gagal Terkirim</span>
                    <span class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-lg">
                        <i class="fas fa-exclamation-triangle"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-red-600">{{ $totalFailed }}</span>
                    <span class="text-xs text-red-500 font-semibold">perlu bantuan</span>
                </div>
                <p class="text-xs text-red-400 mt-1.5 flex items-center gap-1">
                    <i class="fas fa-hand-holding-heart text-xs text-red-500"></i> Fallback manual diperlukan
                </p>
            </a>

            {{-- OTP Kedaluwarsa --}}
            <a href="{{ route('admin.otp.index', ['filter' => 'expired', 'search' => request('search')]) }}"
               class="p-5 rounded-2xl bg-white border transition shadow-sm hover:shadow-md {{ $filter === 'expired' ? 'border-gray-500 ring-2 ring-gray-200' : 'border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">OTP Kedaluwarsa</span>
                    <span class="w-9 h-9 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center text-lg">
                        <i class="fas fa-history"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-gray-600">{{ $totalExpired }}</span>
                    <span class="text-xs text-gray-500">hangus</span>
                </div>
                <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                    <i class="fas fa-clock text-xs text-gray-400"></i> Melewati batas waktu 5 menit
                </p>
            </a>
        </div>

        {{-- Filter & Search Toolbar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('admin.otp.index') }}" class="flex flex-col md:flex-row gap-3 items-center justify-between">
                {{-- Input Pencarian --}}
                <div class="w-full md:w-96 relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama pendaftar, WhatsApp, atau email..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition">
                    <i class="fas fa-search absolute left-3.5 top-3.5 text-gray-400 text-sm"></i>
                </div>

                {{-- Tab Filter Status --}}
                <div class="w-full md:w-auto flex flex-wrap items-center gap-2">
                    <input type="hidden" name="filter" value="{{ $filter }}">

                    <a href="{{ route('admin.otp.index', ['filter' => 'active', 'search' => request('search')]) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $filter === 'active' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Aktif Saja ({{ $totalActive }})
                    </a>

                    <a href="{{ route('admin.otp.index', ['filter' => 'failed', 'search' => request('search')]) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $filter === 'failed' ? 'bg-red-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Gagal Kirim ({{ $totalFailed }})
                    </a>

                    <a href="{{ route('admin.otp.index', ['filter' => 'success', 'search' => request('search')]) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $filter === 'success' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Terkirim ({{ $totalSuccess }})
                    </a>

                    <a href="{{ route('admin.otp.index', ['filter' => 'all', 'search' => request('search')]) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $filter === 'all' ? 'bg-gray-800 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Semua Riwayat
                    </a>

                    <button type="submit"
                            style="background-color: #0d9488; color: #ffffff;"
                            class="px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 transition border-0 cursor-pointer inline-flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-search"></i> Cari
                    </button>

                    @if(request('search') || request('filter'))
                        <a href="{{ route('admin.otp.index') }}" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition text-xs font-bold">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Data OTP (Desktop) & Cards (Mobile) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-gray-50/50">
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-gray-800 text-base">Daftar Kode OTP</h2>
                    <span class="text-xs bg-teal-100 text-teal-800 px-2.5 py-0.5 rounded-full font-bold">
                        {{ $otpUsers->total() }} Data
                    </span>
                </div>
                <div class="text-xs text-gray-500">
                    Waktu Server Saat Ini: <strong class="font-mono text-gray-700">{{ now()->translatedFormat('d M Y H:i:s') }} WIB</strong>
                </div>
            </div>

            {{-- Desktop Table View --}}
            <div class="overflow-x-auto desktop-only-table">
                <table class="w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-600 tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 text-center" style="width: 4%;">No</th>
                            <th class="px-4 py-3.5" style="width: 18%;">Nama Pendaftar</th>
                            <th class="px-4 py-3.5" style="width: 14%;">Kontak (WA & Email)</th>
                            <th class="px-4 py-3.5 text-center" style="width: 14%;">Kode OTP</th>
                            <th class="px-4 py-3.5 text-center" style="width: 10%;">Status OTP</th>
                            <th class="px-4 py-3.5 text-center" style="width: 12%;">Pengiriman WA</th>
                            <th class="px-4 py-3.5 text-center" style="width: 12%;">Masa Berlaku</th>
                            <th class="px-4 py-3.5 text-center" style="width: 16%;">Aksi Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($otpUsers as $index => $u)
                        @php
                            $isActive = $u->isOtpActive();
                            $isExpired = $u->isOtpExpired();
                            $timeLeft = $isActive && $u->otp_expired_at ? now()->diffInSeconds($u->otp_expired_at, false) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition">
                            {{-- No --}}
                            <td class="px-4 py-4 text-center font-medium text-gray-400">
                                {{ $otpUsers->firstItem() + $index }}
                            </td>

                            {{-- Nama Pendaftar --}}
                            <td class="px-4 py-4">
                                <div class="font-bold text-gray-900">{{ $u->name }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    Dibuat: {{ $u->otp_created_at ? $u->otp_created_at->translatedFormat('d M Y H:i') : $u->updated_at->translatedFormat('d M Y H:i') }} WIB
                                </div>
                            </td>

                            {{-- Kontak --}}
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <div class="font-mono text-xs font-semibold text-gray-800 flex items-center gap-1">
                                        <i class="fab fa-whatsapp text-green-600 text-sm"></i> {{ $u->no_wa ?? '-' }}
                                    </div>
                                    <button type="button"
                                            onclick="openEditWaModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->no_wa }}')"
                                            class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition cursor-pointer"
                                            title="Ubah nomor WhatsApp wali ini">
                                        <i class="fas fa-edit"></i> Ganti
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500 truncate max-w-[180px] mt-0.5" title="{{ $u->email }}">
                                    <i class="far fa-envelope text-gray-400 mr-0.5"></i> {{ $u->email }}
                                </div>
                            </td>

                            {{-- Kode OTP --}}
                            <td class="px-4 py-4 text-center">
                                <div class="inline-flex items-center gap-1.5 bg-gray-100 border border-gray-300 rounded-xl px-2.5 py-1.5 shadow-inner">
                                    <span id="otp-display-{{ $u->id }}"
                                          data-otp="{{ $u->otp_code }}"
                                          class="font-mono text-base font-extrabold tracking-widest text-teal-700">
                                        {{ $u->otp_code }}
                                    </span>
                                    <button type="button"
                                            onclick="copyOtp('{{ $u->otp_code }}', '{{ $u->name }}')"
                                            title="Salin Kode OTP"
                                            class="p-1 rounded text-gray-500 hover:text-teal-700 hover:bg-white transition border-0 bg-transparent cursor-pointer">
                                        <i class="far fa-copy text-xs"></i>
                                    </button>
                                </div>
                            </td>

                            {{-- Status OTP --}}
                            <td class="px-4 py-4 text-center">
                                @if($isActive)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-300 shadow-sm">
                                        <i class="fas fa-check-circle text-[10px]"></i> Aktif
                                    </span>
                                @elseif($isExpired)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-300">
                                        <i class="fas fa-clock text-[10px]"></i> Kedaluwarsa
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                        -
                                    </span>
                                @endif
                            </td>

                            {{-- Status Pengiriman WA --}}
                            <td class="px-4 py-4 text-center">
                                @if($u->otp_delivery_status === 'berhasil')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200" title="Berhasil dikirim via WhatsApp Gateway">
                                        <i class="fas fa-paper-plane text-[10px]"></i> Terkirim WA
                                    </span>
                                @elseif($u->otp_delivery_status === 'gagal')
                                    <button type="button"
                                            onclick="showErrorModal('{{ addslashes($u->name) }}', '{{ addslashes($u->otp_delivery_error ?? 'Pengiriman gagal dari gateway.') }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-300 hover:bg-red-200 transition cursor-pointer"
                                            title="Klik untuk melihat detail error">
                                        <i class="fas fa-exclamation-triangle text-[10px] text-red-600"></i> Gagal Kirim
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fas fa-hourglass-half text-[10px]"></i> Menunggu
                                    </span>
                                @endif
                            </td>

                            {{-- Masa Berlaku --}}
                            <td class="px-4 py-4 text-center text-xs">
                                @if($isActive && $u->otp_expired_at)
                                    <div class="font-bold text-teal-700 font-mono">
                                        {{ $u->otp_expired_at->translatedFormat('H:i') }} WIB
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        ({{ now()->diffForHumans($u->otp_expired_at, ['parts' => 1, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) }} lagi)
                                    </div>
                                @elseif($u->otp_expired_at)
                                    <div class="text-gray-400 font-mono">
                                        {{ $u->otp_expired_at->translatedFormat('H:i') }} WIB
                                    </div>
                                    <div class="text-[10px] text-red-400 font-semibold">
                                        Sudah habis
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            {{-- Aksi Admin --}}
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    {{-- Tombol Auto Login (Khusus Super Admin Tanpa Kirim OTP) --}}
                                    @if(auth('admin')->check() && auth('admin')->user()->isSuperAdmin())
                                        <form method="POST" action="{{ route('admin.impersonate', $u->id) }}" class="inline"
                                              onsubmit="return confirm('Buka portal sebagai wali ananda {{ addslashes($u->name) }} tanpa kirim OTP?')">
                                            @csrf
                                            <button type="submit"
                                                    style="background-color: #d97706; color: #ffffff;"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition shadow-sm border-0 cursor-pointer"
                                                    title="Login langsung ke portal akun ini tanpa kirim OTP">
                                                <i class="fas fa-sign-in-alt"></i> Auto Login
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Tombol Ubah No WA --}}
                                    <button type="button"
                                            onclick="openEditWaModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->no_wa }}')"
                                            style="background-color: #f59e0b; color: #ffffff;"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition shadow-sm border-0 cursor-pointer"
                                            title="Ubah nomor WhatsApp wali untuk terima kode OTP">
                                        <i class="fas fa-phone-alt"></i> Ganti WA
                                    </button>

                                    {{-- Tombol Salin OTP --}}
                                    <button type="button"
                                            onclick="copyOtp('{{ $u->otp_code }}', '{{ $u->name }}')"
                                            style="background-color: #0d9488; color: #ffffff;"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition shadow-sm border-0 cursor-pointer"
                                            title="Salin kode OTP ini untuk diberikan kepada pendaftar">
                                        <i class="far fa-copy"></i> Salin OTP
                                    </button>

                                    {{-- Tombol Kirim Ulang WA --}}
                                    @if($isActive)
                                        <form method="POST" action="{{ route('admin.otp.resend', $u->id) }}" class="inline"
                                              onsubmit="return confirm('Kirim ulang kode OTP ({{ $u->otp_code }}) ke nomor WhatsApp {{ $u->no_wa }}?')">
                                            @csrf
                                            <button type="submit"
                                                    style="background-color: #2563eb; color: #ffffff;"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition shadow-sm border-0 cursor-pointer"
                                                    title="Kirim ulang via WhatsApp Gateway">
                                                <i class="fab fa-whatsapp"></i> Kirim WA
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Tombol Detail Modal --}}
                                    <button type="button"
                                            onclick="openDetailModal({{ json_encode([
                                                'name' => $u->name,
                                                'email' => $u->email,
                                                'no_wa' => $u->no_wa,
                                                'otp_code' => $u->otp_code,
                                                'created_at' => $u->otp_created_at ? $u->otp_created_at->translatedFormat('d M Y H:i:s') . ' WIB' : '-',
                                                'expired_at' => $u->otp_expired_at ? $u->otp_expired_at->translatedFormat('d M Y H:i:s') . ' WIB' : '-',
                                                'is_active' => $isActive,
                                                'status_pengiriman' => $u->otp_delivery_status,
                                                'error_pengiriman' => $u->otp_delivery_error,
                                            ]) }})"
                                            class="p-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition text-xs border-0 cursor-pointer"
                                            title="Lihat Detail Lengkap">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                                    <i class="ti ti-shield-check"></i>
                                </div>
                                <p class="text-base font-bold text-gray-700">Tidak ada data OTP yang sesuai filter</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Saat pendaftar meminta OTP, kodenya akan otomatis muncul di halaman ini.
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards View --}}
            <div class="mobile-data-cards p-4 space-y-3">
                @forelse($otpUsers as $index => $u)
                @php
                    $isActive = $u->isOtpActive();
                    $isExpired = $u->isOtpExpired();
                @endphp
                <article class="rounded-2xl border border-gray-200 p-4 bg-white shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-3">
                        <div>
                            <span class="text-xs text-gray-400">#{{ $otpUsers->firstItem() + $index }}</span>
                            <h3 class="font-bold text-gray-900 text-base">{{ $u->name }}</h3>
                            <p class="text-xs text-gray-500">{{ $u->email }}</p>
                        </div>
                        <div>
                            @if($isActive)
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-300">
                                    Aktif
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                    Kedaluwarsa
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Kode OTP Box Mobile --}}
                    <div class="flex items-center justify-between bg-teal-50/70 border border-teal-200 rounded-xl p-3">
                        <div>
                            <span class="text-[11px] font-bold text-teal-700 uppercase tracking-wider block">Kode OTP Aktif</span>
                            <span class="font-mono text-2xl font-black text-teal-800 tracking-widest">{{ $u->otp_code }}</span>
                        </div>
                        <button type="button"
                                onclick="copyOtp('{{ $u->otp_code }}', '{{ $u->name }}')"
                                style="background-color: #0d9488; color: #ffffff;"
                                class="px-3.5 py-2 rounded-lg text-xs font-bold hover:opacity-90 transition border-0 cursor-pointer flex items-center gap-1.5 shadow">
                            <i class="far fa-copy"></i> Salin
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 pt-1">
                        <div>
                            <span class="text-gray-400 block">WhatsApp:</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <strong class="font-mono text-gray-800">{{ $u->no_wa ?? '-' }}</strong>
                                <button type="button"
                                        onclick="openEditWaModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->no_wa }}')"
                                        class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition cursor-pointer">
                                    <i class="fas fa-edit"></i> Ganti
                                </button>
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Status Kirim WA:</span>
                            @if($u->otp_delivery_status === 'berhasil')
                                <span class="text-blue-600 font-bold">✓ Terkirim</span>
                            @elseif($u->otp_delivery_status === 'gagal')
                                <span class="text-red-600 font-bold">✗ Gagal Kirim</span>
                            @else
                                <span class="text-amber-600 font-bold">Menunggu</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-400 block">Dibuat:</span>
                            <span>{{ $u->otp_created_at ? $u->otp_created_at->format('H:i:s') : '-' }} WIB</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Berlaku Sampai:</span>
                            <span class="font-bold {{ $isActive ? 'text-teal-700' : 'text-gray-400' }}">
                                {{ $u->otp_expired_at ? $u->otp_expired_at->format('H:i:s') : '-' }} WIB
                            </span>
                        </div>
                    </div>

                    @if($u->otp_delivery_error)
                        <div class="bg-red-50 border border-red-200 rounded-xl p-2.5 text-xs text-red-700 flex items-start gap-2">
                            <i class="fas fa-exclamation-circle mt-0.5 text-red-500"></i>
                            <div class="break-words">
                                <strong>Pesan Error Gateway:</strong> {{ $u->otp_delivery_error }}
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 flex-wrap">
                        @if(auth('admin')->check() && auth('admin')->user()->isSuperAdmin())
                            <form method="POST" action="{{ route('admin.impersonate', $u->id) }}" class="w-full sm:w-auto"
                                  onsubmit="return confirm('Buka portal sebagai wali ananda {{ addslashes($u->name) }} tanpa kirim OTP?')">
                                @csrf
                                <button type="submit"
                                        style="background-color: #d97706; color: #ffffff;"
                                        class="w-full sm:w-auto px-3 py-2 rounded-lg text-xs font-bold hover:opacity-90 transition border-0 cursor-pointer flex items-center justify-center gap-1.5 shadow-sm"
                                        title="Login langsung ke portal akun ini tanpa kirim OTP">
                                    <i class="fas fa-sign-in-alt"></i> Auto Login
                                </button>
                            </form>
                        @endif

                        <button type="button"
                                onclick="openEditWaModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->no_wa }}')"
                                style="background-color: #f59e0b; color: #ffffff;"
                                class="w-full sm:w-auto px-3 py-2 rounded-lg text-xs font-bold hover:opacity-90 transition border-0 cursor-pointer flex items-center justify-center gap-1.5 shadow-sm">
                            <i class="fas fa-phone-alt"></i> Ganti No. WA
                        </button>

                        @if($isActive)
                            <form method="POST" action="{{ route('admin.otp.resend', $u->id) }}" class="w-full sm:w-auto"
                                  onsubmit="return confirm('Kirim ulang kode OTP ke WhatsApp {{ $u->no_wa }}?')">
                                @csrf
                                <button type="submit"
                                        style="background-color: #2563eb; color: #ffffff;"
                                        class="w-full sm:w-auto px-3 py-2 rounded-lg text-xs font-bold hover:opacity-90 transition border-0 cursor-pointer flex items-center justify-center gap-1.5 shadow-sm">
                                    <i class="fab fa-whatsapp"></i> Kirim Ulang ke WhatsApp
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
                @empty
                <p class="py-8 text-center text-sm text-gray-500">Tidak ada data OTP.</p>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($otpUsers->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
                    {{ $otpUsers->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

{{-- Modal Detail Pendaftar & OTP --}}
<div id="detailOtpModal" class="fixed inset-0 z-50 hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all">
        <div class="bg-teal-600 px-6 py-4 text-white flex items-center justify-between">
            <h3 class="font-bold text-base flex items-center gap-2">
                <i class="ti ti-shield-lock text-xl"></i> Detail OTP & Gateway
            </h3>
            <button type="button" onclick="closeDetailModal()" class="text-white/80 hover:text-white text-xl font-bold bg-transparent border-0 cursor-pointer">
                &times;
            </button>
        </div>

        <div class="p-6 space-y-4">
            <div class="bg-teal-50 border border-teal-200 rounded-2xl p-4 text-center">
                <p class="text-xs font-bold text-teal-700 uppercase tracking-widest">Kode OTP Aktif</p>
                <div class="mt-1 flex items-center justify-center gap-2">
                    <span id="modal-otp-code" class="text-3xl font-black font-mono text-teal-800 tracking-widest"></span>
                    <button type="button" onclick="copyModalOtp()" title="Salin" class="p-2 rounded-lg bg-white border border-teal-300 text-teal-700 hover:bg-teal-100 transition cursor-pointer">
                        <i class="far fa-copy text-sm"></i>
                    </button>
                </div>
                <p id="modal-otp-status" class="text-xs font-semibold mt-2 text-teal-600"></p>
            </div>

            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Nama Pendaftar:</span>
                    <strong id="modal-name" class="text-gray-900"></strong>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Nomor WhatsApp:</span>
                    <strong id="modal-wa" class="font-mono text-gray-900"></strong>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Email:</span>
                    <span id="modal-email" class="text-gray-800"></span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Waktu Dibuat:</span>
                    <span id="modal-created" class="text-gray-700"></span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Batas Berlaku:</span>
                    <span id="modal-expired" class="font-semibold text-gray-800"></span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-500">Status Pengiriman WA:</span>
                    <span id="modal-delivery-status" class="font-bold"></span>
                </div>
            </div>

            <div id="modal-error-box" class="hidden bg-red-50 border border-red-200 rounded-xl p-3 text-xs text-red-800">
                <strong class="block mb-1 font-bold flex items-center gap-1.5 text-red-700">
                    <i class="fas fa-exclamation-triangle"></i> Detail Error Gateway:
                </strong>
                <p id="modal-error-text" class="break-words font-mono text-[11px]"></p>
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-3.5 border-t border-gray-100 flex justify-end">
            <button type="button" onclick="closeDetailModal()" class="px-5 py-2 rounded-xl text-sm font-bold bg-gray-200 text-gray-700 hover:bg-gray-300 transition border-0 cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- MODAL UBAH NOMOR WHATSAPP WALI --}}
<div id="editWaModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 p-4 backdrop-blur-xs">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl animate-fade-in border border-gray-100">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2 text-teal-800">
                <div class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                    <i class="fas fa-phone-alt text-base"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 leading-tight">Ubah No. WA Wali Santri</h3>
                    <p class="text-xs text-gray-500">Perbarui nomor penerima kode OTP & notifikasi</p>
                </div>
            </div>
            <button type="button" onclick="closeEditWaModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="editWaForm" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama Wali Santri</label>
                <div id="editWaModalName" class="px-3 py-2 rounded-lg bg-gray-50 text-gray-800 text-sm font-semibold border border-gray-200">
                    -
                </div>
            </div>

            <div>
                <label for="inputNewWa" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Nomor WhatsApp Baru <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fab fa-whatsapp text-green-600 text-base"></i>
                    </span>
                    <input type="tel" id="inputNewWa" name="no_wa" required
                           class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-gray-300 font-mono text-sm font-bold text-gray-800 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                           placeholder="Contoh: 081234567890 atau 6281234567890">
                </div>
                <p class="text-[11px] text-gray-500 mt-1.5 leading-relaxed">
                    <i class="fas fa-info-circle text-teal-600"></i> Nomor baru akan otomatis disinkronkan ke akun login wali dan seluruh data santri anak-anaknya.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeEditWaModal()"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 transition shadow-sm cursor-pointer flex items-center gap-1.5">
                    <i class="fas fa-save"></i> Simpan & Perbarui Nomor
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Toast Notification Salin --}}
<div id="toastNotification" class="fixed bottom-6 right-6 z-50 hidden transform transition-all duration-300 bg-gray-900 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 border border-gray-700">
    <div class="w-7 h-7 rounded-lg bg-teal-500 text-white flex items-center justify-center flex-shrink-0">
        <i class="fas fa-check text-xs"></i>
    </div>
    <div class="text-sm font-medium" id="toastMessage">Kode OTP berhasil disalin!</div>
</div>

<script>
    // Salin kode OTP ke clipboard
    function copyOtp(code, name) {
        if (!code) {
            showToast('Kode OTP tidak tersedia', true);
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(code).then(() => {
                showToast(`Kode OTP (${code}) untuk ${name || 'pendaftar'} berhasil disalin!`);
            }).catch(() => fallbackCopy(code));
        } else {
            fallbackCopy(code);
        }
    }

    function fallbackCopy(text) {
        const temp = document.createElement('textarea');
        temp.value = text;
        temp.style.position = 'fixed';
        temp.style.left = '-9999px';
        document.body.appendChild(temp);
        temp.select();
        try {
            document.execCommand('copy');
            showToast(`Kode OTP (${text}) berhasil disalin!`);
        } catch (err) {
            showToast('Gagal menyalin otomatis. Silakan salin manual.', true);
        }
        document.body.removeChild(temp);
    }

    function showToast(msg, isError = false) {
        const toast = document.getElementById('toastNotification');
        const text = document.getElementById('toastMessage');
        text.innerText = msg;

        if (isError) {
            toast.classList.remove('bg-gray-900');
            toast.classList.add('bg-red-900');
        } else {
            toast.classList.remove('bg-red-900');
            toast.classList.add('bg-gray-900');
        }

        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    // Modal Error
    function showErrorModal(name, errorText) {
        openDetailModal({
            name: name,
            email: '-',
            no_wa: '-',
            otp_code: '-',
            created_at: '-',
            expired_at: '-',
            is_active: false,
            status_pengiriman: 'gagal',
            error_pengiriman: errorText,
        });
    }

    // Modal Detail
    let currentModalOtp = '';
    function openDetailModal(data) {
        currentModalOtp = data.otp_code;
        document.getElementById('modal-name').innerText = data.name;
        document.getElementById('modal-email').innerText = data.email;
        document.getElementById('modal-wa').innerText = data.no_wa;
        document.getElementById('modal-otp-code').innerText = data.otp_code;
        document.getElementById('modal-created').innerText = data.created_at;
        document.getElementById('modal-expired').innerText = data.expired_at;

        const statusEl = document.getElementById('modal-otp-status');
        if (data.is_active) {
            statusEl.innerText = '● Kode OTP sedang aktif dan valid';
            statusEl.className = 'text-xs font-semibold mt-2 text-green-600';
        } else {
            statusEl.innerText = '● Kode OTP sudah kedaluwarsa';
            statusEl.className = 'text-xs font-semibold mt-2 text-gray-500';
        }

        const deliveryEl = document.getElementById('modal-delivery-status');
        if (data.status_pengiriman === 'berhasil') {
            deliveryEl.innerText = 'Terkirim Sukses via WhatsApp';
            deliveryEl.className = 'font-bold text-green-700';
        } else if (data.status_pengiriman === 'gagal') {
            deliveryEl.innerText = 'Gagal Terkirim ke WhatsApp';
            deliveryEl.className = 'font-bold text-red-700';
        } else {
            deliveryEl.innerText = 'Menunggu';
            deliveryEl.className = 'font-bold text-amber-600';
        }

        const errorBox = document.getElementById('modal-error-box');
        if (data.error_pengiriman) {
            errorBox.classList.remove('hidden');
            document.getElementById('modal-error-text').innerText = data.error_pengiriman;
        } else {
            errorBox.classList.add('hidden');
        }

        document.getElementById('detailOtpModal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detailOtpModal').classList.add('hidden');
    }

    function copyModalOtp() {
        if (currentModalOtp) {
            copyOtp(currentModalOtp, 'pendaftar');
        }
    }

    // Modal Ubah No WA
    function openEditWaModal(userId, name, currentWa) {
        document.getElementById('editWaModalName').innerText = name;
        document.getElementById('inputNewWa').value = currentWa && currentWa !== '-' ? currentWa : '';
        document.getElementById('editWaForm').action = `/admin/monitoring-otp/${userId}/update-phone`;
        document.getElementById('editWaModal').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('inputNewWa').focus();
        }, 100);
    }

    function closeEditWaModal() {
        document.getElementById('editWaModal').classList.add('hidden');
    }
</script>
@endsection
