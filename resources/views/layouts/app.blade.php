<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

       <title>@yield('title', 'Aplikasi PSB')</title> {{-- Default title jika kosong --}}
        <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/icon-p.png')}}" />
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 pb-20 lg:pb-0">
            @if(session()->has('impersonated_by_admin_id'))
                <div class="bg-amber-600 text-white px-4 py-2.5 shadow-md flex flex-wrap items-center justify-between gap-2 z-50 text-xs sm:text-sm font-semibold sticky top-0" style="background-color: #d97706 !important; color: #ffffff !important;">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user-shield text-base"></i>
                        <span>Mode Super Admin: Anda sedang masuk sebagai wali <strong>{{ Auth::user()->name }}</strong> (Tanpa OTP).</span>
                    </div>
                    <form action="{{ route('admin.impersonate.leave') }}" method="POST" class="inline m-0">
                        @csrf
                        <button type="submit" class="bg-white text-amber-900 hover:bg-amber-100 px-3 py-1.5 rounded-lg font-bold shadow-xs transition cursor-pointer text-xs flex items-center gap-1.5 border-0" style="color: #78350f !important; background-color: #ffffff !important;">
                            <i class="fas fa-arrow-left"></i> Kembali ke Super Admin
                        </button>
                    </form>
                </div>
            @endif

            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
            <div class="block lg:hidden fixed bottom-0 left-0 right-0 bg-white shadow-[0_-4px_10px_rgba(0,0,0,0.08)] z-50 border-t border-gray-200 no-print">
                <nav class="flex justify-around items-center py-2 text-[11px]">
                    <!-- Home -->
                    <a class="flex flex-col items-center {{ request()->routeIs('user.dashboard') ? 'text-green-600 font-bold' : 'text-gray-500 hover:text-green-600' }} transition"
                       href="{{ route('user.dashboard') }}">
                        <i class="fas fa-home text-lg mb-0.5"></i>
                        <span>Home</span>
                    </a>

                    <!-- Uang Saku -->
                    <a class="flex flex-col items-center {{ request()->routeIs('user.uang_saku') ? 'text-green-600 font-bold' : 'text-gray-500 hover:text-green-600' }} transition"
                       href="{{ route('user.uang_saku') }}">
                        <i class="fas fa-wallet text-lg mb-0.5"></i>
                        <span>Uang Saku</span>
                    </a>

                    <!-- SPP -->
                    <a class="flex flex-col items-center {{ request()->routeIs('user.spp.*') ? 'text-green-600 font-bold' : 'text-gray-500 hover:text-green-600' }} transition"
                       href="{{ route('user.spp.index') }}">
                        <i class="fas fa-receipt text-lg mb-0.5"></i>
                        <span>SPP</span>
                    </a>

                    <!-- Tahfidz -->
                    <a class="flex flex-col items-center {{ request()->routeIs('user.tahfidz') ? 'text-green-600 font-bold' : 'text-gray-500 hover:text-green-600' }} transition"
                       href="{{ route('user.tahfidz') }}">
                        <i class="fas fa-quran text-lg mb-0.5"></i>
                        <span>Tahfidz</span>
                    </a>

                    <!-- Kesehatan -->
                    <a class="flex flex-col items-center {{ request()->routeIs('user.kondisi*') ? 'text-green-600 font-bold' : 'text-gray-500 hover:text-green-600' }} transition"
                       href="{{ route('user.kondisi') }}">
                        <i class="fas fa-heartbeat text-lg mb-0.5"></i>
                        <span>Kesehatan</span>
                    </a>
                </nav>
            </div>
            
        </div>
    </body>
</html>
