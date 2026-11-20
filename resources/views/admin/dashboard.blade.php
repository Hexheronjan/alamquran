@extends('layouts.admin.app')
@section('content')

@php
    $adminUser = auth('admin')->user();
@endphp

<div class="w-full px-4 lg:px-8 py-6">

    {{-- Flash Error Message (e.g. Akses Ditolak) --}}
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-rose-50 border border-rose-200 px-5 py-4 text-sm font-medium text-rose-800 shadow-sm animate-in fade-in">
            <i class="ti ti-alert-octagon text-2xl text-rose-600 flex-shrink-0"></i>
            <div>
                <p class="font-bold text-rose-900">Pemberitahuan Hak Akses</p>
                <p class="text-xs text-rose-700 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-4 text-sm font-medium text-emerald-800 shadow-sm">
            <i class="ti ti-circle-check text-2xl text-emerald-600 flex-shrink-0"></i>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1 flex items-center gap-2 flex-wrap">
                Selamat datang, <strong class="text-gray-800">{{ $adminUser->name }}</strong>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                    {{ $adminUser->role_label }}
                </span>
            </p>
        </div>
        @if($adminUser->canAccess('admin_management'))
            <a href="{{ route('admin.admins.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold rounded-xl text-xs transition border border-purple-200">
                <i class="ti ti-shield-lock text-base"></i> Kelola Hak Akses Admin
            </a>
        @endif
    </div>

    {{-- Shortcut Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-5">

        {{-- Manajemen Admin (Khusus Super Admin) --}}
        @if($adminUser->canAccess('admin_management'))
            <a href="{{ route('admin.admins.index') }}"
               class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-purple-200 transition-all duration-200 p-6 flex items-start gap-4">
                <div class="w-12 h-12 bg-purple-50 group-hover:bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0 transition">
                    <i class="ti ti-users-group text-2xl text-purple-600"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-800 group-hover:text-purple-700 transition">Manajemen Admin</h3>
                    <p class="text-sm text-gray-500 mt-0.5 leading-snug">Kelola akun admin, tentukan hak akses & pantau riwayat log.</p>
                    <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-purple-600 group-hover:gap-2 transition-all">
                        Kelola Akun <i class="ti ti-arrow-right text-sm"></i>
                    </span>
                </div>
            </a>
        @endif

        {{-- Data Santri Baru --}}
        @if($adminUser->canAccess('ppdb') || $adminUser->canAccess('ppdb_read') || $adminUser->canAccess('tahfidz'))
            <a href="{{ route('admin.santri.index') }}"
               class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 p-6 flex items-start gap-4">
                <div class="w-12 h-12 bg-blue-50 group-hover:bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 transition">
                    <i class="ti ti-users text-2xl text-blue-600"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-800 group-hover:text-blue-700 transition">Data Santri Baru</h3>
                    <p class="text-sm text-gray-500 mt-0.5 leading-snug">Verifikasi, setujui, atau tolak pendaftaran calon santri.</p>
                    <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-blue-600 group-hover:gap-2 transition-all">
                        Lihat Data <i class="ti ti-arrow-right text-sm"></i>
                    </span>
                </div>
            </a>
        @endif

        {{-- Kesehatan Santri (UKS) - DIPISAH --}}
        @if($adminUser->canAccess('kesehatan') || $adminUser->canAccess('ppdb'))
            <a href="{{ route('admin.kesehatan.index') }}"
               class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-rose-200 transition-all duration-200 p-6 flex items-start gap-4">
                <div class="w-12 h-12 bg-rose-50 group-hover:bg-rose-100 rounded-xl flex items-center justify-center flex-shrink-0 transition">
                    <i class="ti ti-first-aid-kit text-2xl text-rose-500"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-800 group-hover:text-rose-700 transition">Kesehatan Santri (UKS)</h3>
                    <p class="text-sm text-gray-500 mt-0.5 leading-snug">Catatan rekam medis, santri rawat inap & kepulangan.</p>
                    <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-rose-600 group-hover:gap-2 transition-all">
                        Lihat Data <i class="ti ti-arrow-right text-sm"></i>
                    </span>
                </div>
            </a>
        @endif

        {{-- Monitoring OTP --}}
        @if($adminUser->canAccess('otp_monitoring'))
            <a href="{{ route('admin.otp.index') }}"
               class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-teal-200 transition-all duration-200 p-6 flex items-start gap-4">
                <div class="w-12 h-12 bg-teal-50 group-hover:bg-teal-100 rounded-xl flex items-center justify-center flex-shrink-0 transition">
                    <i class="ti ti-shield-lock text-2xl text-teal-600"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-800 group-hover:text-teal-700 transition">Monitoring OTP</h3>
                    <p class="text-sm text-gray-500 mt-0.5 leading-snug">Pantau OTP aktif & salin manual jika WhatsApp gateway bermasalah.</p>
                    <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-teal-600 group-hover:gap-2 transition-all">
                        Lihat Data <i class="ti ti-arrow-right text-sm"></i>
                    </span>
                </div>
            </a>
        @endif

        {{-- Uang Saku Santri --}}
        @if($adminUser->canAccess('uang_saku') || $adminUser->canAccess('keuangan'))
            <a href="{{ route('admin.uang_saku.index') }}"
               class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-amber-200 transition-all duration-200 p-6 flex items-start gap-4">
                <div class="w-12 h-12 bg-amber-50 group-hover:bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0 transition">
                    <i class="ti ti-wallet text-2xl text-amber-600"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-800 group-hover:text-amber-700 transition">Uang Saku Santri</h3>
                    <p class="text-sm text-gray-500 mt-0.5 leading-snug">Catat titipan dan pengeluaran uang saku per santri.</p>
                    <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-amber-600 group-hover:gap-2 transition-all">
                        Lihat Data <i class="ti ti-arrow-right text-sm"></i>
                    </span>
                </div>
            </a>
        @endif

        {{-- Data Sekolah --}}
        @if($adminUser->canAccess('master_data'))
            <a href="{{ route('admin.schools.index') }}"
               class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-teal-200 transition-all duration-200 p-6 flex items-start gap-4">
                <div class="w-12 h-12 bg-teal-50 group-hover:bg-teal-100 rounded-xl flex items-center justify-center flex-shrink-0 transition">
                    <i class="ti ti-school text-2xl text-teal-600"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-800 group-hover:text-teal-700 transition">Data Madrasah</h3>
                    <p class="text-sm text-gray-500 mt-0.5 leading-snug">Atur informasi dan profil madrasah pesantren.</p>
                    <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-teal-600 group-hover:gap-2 transition-all">
                        Lihat Data <i class="ti ti-arrow-right text-sm"></i>
                    </span>
                </div>
            </a>

            {{-- Data Angkatan --}}
            <a href="{{ route('admin.angkatan.index') }}"
               class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-200 p-6 flex items-start gap-4">
                <div class="w-12 h-12 bg-indigo-50 group-hover:bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0 transition">
                    <i class="ti ti-calendar-event text-2xl text-indigo-600"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-800 group-hover:text-indigo-700 transition">Data Angkatan</h3>
                    <p class="text-sm text-gray-500 mt-0.5 leading-snug">Kelola tahun ajaran & jadwal buka pendaftaran santri baru.</p>
                    <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 group-hover:gap-2 transition-all">
                        Lihat Data <i class="ti ti-arrow-right text-sm"></i>
                    </span>
                </div>
            </a>
        @endif

        {{-- Profil --}}
        <a href="{{ route('admin.profile.edit') }}"
           class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-orange-200 transition-all duration-200 p-6 flex items-start gap-4">
            <div class="w-12 h-12 bg-orange-50 group-hover:bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0 transition">
                <i class="ti ti-user-circle text-2xl text-orange-500"></i>
            </div>
            <div class="min-w-0">
                <h3 class="font-bold text-gray-800 group-hover:text-orange-700 transition">Profil Admin</h3>
                <p class="text-sm text-gray-500 mt-0.5 leading-snug">Ubah nama, email, dan kata sandi akun admin.</p>
                <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-orange-500 group-hover:gap-2 transition-all">
                    Lihat Data <i class="ti ti-arrow-right text-sm"></i>
                </span>
            </div>
        </a>

    </div>

    {{-- Info Pendaftaran --}}
    @if($adminUser->canAccess('ppdb') || $adminUser->canAccess('master_data'))
        @php
            $angkatanAktif = \App\Models\Angkatan::open()->first();
        @endphp
        <div class="mt-8 {{ $angkatanAktif ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' }} border rounded-xl p-4 flex items-start gap-3 text-sm">
            <i class="ti ti-info-circle text-lg mt-0.5 {{ $angkatanAktif ? 'text-green-500' : 'text-yellow-500' }}"></i>
            <div>
                @if($angkatanAktif)
                    <span class="{{ $angkatanAktif ? 'text-green-800' : 'text-yellow-800' }}">
                        <strong>Pendaftaran Santri Sedang Buka</strong> — Angkatan <strong>{{ $angkatanAktif->nama_angkatan }}</strong>
                        ({{ $angkatanAktif->tahun_ajaran }}) aktif hingga
                        <strong>{{ \Carbon\Carbon::parse($angkatanAktif->tanggal_tutup_daftar)->translatedFormat('d M Y') }}</strong>.
                    </span>
                @else
                    <span class="text-yellow-800">
                        <strong>Pendaftaran Santri Saat Ini Ditutup.</strong>
                        Buka menu <a href="{{ route('admin.angkatan.index') }}" class="underline font-semibold">Data Angkatan</a>
                        untuk mengatur jadwal pendaftaran santri baru.
                    </span>
                @endif
            </div>
        </div>
    @endif

</div>
@endsection