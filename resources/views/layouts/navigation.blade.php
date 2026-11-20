@php
    $navSetting = \App\Models\InvoiceSetting::getSetting();
    $navLogoUrl = null;
    if (!empty($navSetting->logo) && file_exists(storage_path('app/public/' . $navSetting->logo))) {
        $navLogoUrl = asset('storage/' . $navSetting->logo);
    } elseif (file_exists(public_path('assets/images/logos/icon-logo-ppdb.png'))) {
        $navLogoUrl = asset('assets/images/logos/icon-logo-ppdb.png');
    } elseif (file_exists(public_path('assets/images/logos/icon-p.png'))) {
        $navLogoUrl = asset('assets/images/logos/icon-p.png');
    } elseif (file_exists(public_path('assets/images/logos/ponpes.png'))) {
        $navLogoUrl = asset('assets/images/logos/ponpes.png');
    }
@endphp
<nav x-data="{ open: false, menuOpen: false, dropOpen: false, profileOpen: false }" class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2.5">
                        @if($navLogoUrl)
                            <img src="{{ $navLogoUrl }}" alt="Logo Alam Qur'an" class="h-8 sm:h-9 w-auto max-h-9 object-contain">
                        @else
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-green-600 text-white font-bold">
                                <i class="fas fa-book-quran text-base"></i>
                            </div>
                        @endif
                        <span class="font-bold text-gray-800 text-sm sm:text-base hidden sm:block">Alam Qur'an</span>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden xl:flex items-center gap-1 ms-6">
                    <a href="{{ route('user.dashboard') }}"
                       class="px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('user.dashboard') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-home me-1"></i>Dashboard
                    </a>
                    <a href="{{ route('user.uang_saku') }}"
                       class="px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('user.uang_saku') || request()->routeIs('user.deposit_transfer.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-wallet me-1"></i>Uang Saku
                    </a>
                    <a href="{{ route('user.spp.index') }}"
                       class="px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('user.spp.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-receipt me-1"></i>SPP
                    </a>
                    <a href="{{ route('user.daftar_ulang.index') }}"
                       class="px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('user.daftar_ulang.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-money-check-alt me-1"></i>Daftar Ulang
                    </a>
                    <a href="{{ route('user.tahfidz') }}"
                       class="px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('user.tahfidz') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-quran me-1"></i>Tahfidz
                    </a>
                    <a href="{{ route('user.absensi') }}"
                       class="px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('user.absensi') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-calendar-check me-1"></i>Absensi
                    </a>
                    <a href="{{ route('user.perizinan') }}"
                       class="px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('user.perizinan') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-qrcode me-1"></i>Perizinan
                    </a>
                    <a href="{{ route('user.infaq') }}"
                       class="px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('user.infaq*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-heart me-1 text-rose-500"></i>Infaq
                    </a>

                    <!-- More Dropdown -->
                    <div class="relative" x-data="{ dropOpen: false }">
                        <button @click="dropOpen = !dropOpen" @click.away="dropOpen = false"
                                class="px-3 py-2 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-50 flex items-center gap-1">
                            <i class="fas fa-ellipsis-h me-1"></i>Lainnya
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </button>
                        <div x-show="dropOpen" x-cloak
                             class="absolute left-0 mt-1 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <a href="{{ route('user.kegiatan') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                <i class="fas fa-calendar-alt text-emerald-600 w-4"></i>Kegiatan Santri
                            </a>
                            <a href="{{ route('user.kondisi') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                <i class="fas fa-heartbeat text-rose-500 w-4"></i>Kondisi Santri
                            </a>
                            <a href="{{ route('user.prestasi.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                <i class="fas fa-trophy text-amber-500 w-4"></i>Prestasi Santri
                            </a>
                            <a href="{{ route('user.pelanggaran.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                <i class="fas fa-gavel text-red-500 w-4"></i>Disiplin & Pelanggaran
                            </a>
                            <a href="{{ route('user.kalender.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                <i class="fas fa-calendar-day text-teal-600 w-4"></i>Kalender Akademik
                            </a>
                            <a href="{{ route('user.telepon.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                <i class="fas fa-phone-alt text-emerald-600 w-4"></i>Jadwal Telepon Santri
                            </a>
                            <a href="{{ route('user.saran.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                <i class="fas fa-comment-dots text-emerald-600 w-4"></i>Saran & Masukan
                            </a>
                            <a href="{{ route('user.kartu_santri') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                <i class="fas fa-id-card text-blue-500 w-4"></i>Kartu Santri Digital
                            </a>
                            <a href="{{ route('user.surat') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                <i class="fas fa-envelope text-indigo-500 w-4"></i>Surat & Dokumen
                            </a>
                            <a href="{{ route('user.form.history') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                <i class="fas fa-file-alt text-amber-500 w-4"></i>Riwayat Pendaftaran
                            </a>
                            <a href="{{ route('user.notifikasi') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                <i class="fas fa-bell text-orange-500 w-4"></i>Notifikasi
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Desktop (lg to xl) Nav Links -->
                <div class="hidden lg:flex xl:hidden items-center gap-1 ms-4">
                    <a href="{{ route('user.dashboard') }}" class="px-2 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50">Dashboard</a>
                    <a href="{{ route('user.uang_saku') }}" class="px-2 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50">Uang Saku</a>
                    <a href="{{ route('user.spp.index') }}" class="px-2 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50">SPP</a>
                    <a href="{{ route('user.daftar_ulang.index') }}" class="px-2 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50">Daftar Ulang</a>
                    <a href="{{ route('user.tahfidz') }}" class="px-2 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50">Tahfidz</a>
                    <a href="{{ route('user.absensi') }}" class="px-2 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50">Absensi</a>
                    <a href="{{ route('user.perizinan') }}" class="px-2 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50">Izin</a>
                    <a href="{{ route('user.infaq') }}" class="px-2 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50">Infaq</a>

                    <div class="relative" x-data="{ dropOpen: false }">
                        <button @click="dropOpen = !dropOpen" @click.away="dropOpen = false"
                                class="px-2 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 flex items-center gap-1">
                            Lainnya <i class="fas fa-chevron-down text-[9px]"></i>
                        </button>
                        <div x-show="dropOpen" x-cloak
                             class="absolute left-0 mt-1 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <a href="{{ route('user.kegiatan') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">Kegiatan Santri</a>
                            <a href="{{ route('user.kondisi') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">Kondisi Santri</a>
                            <a href="{{ route('user.prestasi.index') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">Prestasi Santri</a>
                            <a href="{{ route('user.pelanggaran.index') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">Disiplin & Pelanggaran</a>
                            <a href="{{ route('user.kalender.index') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">Kalender Akademik</a>
                            <a href="{{ route('user.saran.index') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">Saran & Masukan</a>
                            <a href="{{ route('user.kartu_santri') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">Kartu Santri Digital</a>
                            <a href="{{ route('user.surat') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">Surat & Dokumen</a>
                            <a href="{{ route('user.form.history') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">Riwayat Pendaftaran</a>
                            <a href="{{ route('user.notifikasi') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">Notifikasi</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Profile + Notif -->
            <div class="flex items-center gap-2">
                <!-- Notifikasi bell icon -->
                <a href="{{ route('user.notifikasi') }}" class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-50 flex items-center justify-center">
                    <i class="fas fa-bell text-lg"></i>
                </a>

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ profileOpen: false }">
                    <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                        <div class="w-7 h-7 rounded-full bg-green-600 text-white flex items-center justify-center text-xs font-bold">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <span class="hidden sm:block text-xs font-medium max-w-[120px] truncate">{{ Auth::user()->name ?? 'User' }}</span>
                        <i class="fas fa-chevron-down text-[10px] hidden sm:block"></i>
                    </button>
                    <div x-show="profileOpen" x-cloak
                         class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-xs font-bold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-gray-400 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('user.profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-user-edit text-gray-500"></i>Edit Profil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50">
                                <i class="fas fa-sign-out-alt"></i>Keluar
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Hamburger (mobile) -->
                <button @click="open = ! open" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none">
                    <i class="fas fa-bars text-lg" x-show="!open"></i>
                    <i class="fas fa-times text-lg" x-show="open" x-cloak></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu (Accordion / Full List) -->
    <div x-show="open" x-cloak class="lg:hidden border-t border-gray-100 bg-white">
        <div class="px-4 py-3 space-y-1 max-h-[80vh] overflow-y-auto pb-6">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-3 py-1">Menu Utama</p>
            <a href="{{ route('user.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.dashboard') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-home text-base w-5 text-center"></i>Dashboard
            </a>
            <a href="{{ route('user.uang_saku') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.uang_saku') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-wallet text-base w-5 text-center"></i>Uang Saku
            </a>
            <a href="{{ route('user.deposit_transfer.create') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs text-gray-600 hover:bg-gray-50 ps-10">
                <i class="fas fa-upload text-xs w-4 text-center"></i>Deposit Uang Saku
            </a>
            <a href="{{ route('user.spp.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.spp.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-receipt text-base w-5 text-center"></i>SPP & Administrasi
            </a>
            <a href="{{ route('user.daftar_ulang.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.daftar_ulang.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-money-check-alt text-base w-5 text-center"></i>Daftar Ulang & Biaya Masuk
            </a>

            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-3 py-1 mt-3">Pemantauan Santri</p>
            <a href="{{ route('user.tahfidz') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.tahfidz') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-quran text-base w-5 text-center"></i>Tahfidz Al-Qur'an
            </a>
            <a href="{{ route('user.absensi') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.absensi') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-calendar-check text-base w-5 text-center"></i>Absensi Kehadiran
            </a>
            <a href="{{ route('user.kegiatan') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.kegiatan') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-calendar-alt text-base w-5 text-center"></i>Kegiatan Santri
            </a>
            <a href="{{ route('user.kondisi') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.kondisi') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-heartbeat text-base w-5 text-center"></i>Kondisi Santri
            </a>
            <a href="{{ route('user.prestasi.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.prestasi.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-trophy text-base w-5 text-center text-amber-500"></i>Prestasi Santri
            </a>
            <a href="{{ route('user.pelanggaran.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.pelanggaran.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-gavel text-base w-5 text-center text-red-500"></i>Disiplin & Pelanggaran
            </a>
            <a href="{{ route('user.kalender.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.kalender.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-calendar-day text-base w-5 text-center text-teal-600"></i>Kalender Akademik
            </a>
            <a href="{{ route('user.saran.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.saran.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-comment-dots text-base w-5 text-center text-emerald-600"></i>Saran & Masukan
            </a>

            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-3 py-1 mt-3">Perizinan, Dokumen & Sosial</p>
            <a href="{{ route('user.perizinan') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.perizinan') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-qrcode text-base w-5 text-center"></i>Perizinan & QR Wali
            </a>
            <a href="{{ route('user.telepon.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.telepon.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-phone-alt text-base w-5 text-center text-emerald-600"></i>Jadwal Telepon Santri
            </a>
            <a href="{{ route('user.infaq') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.infaq*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-heart text-base w-5 text-center text-rose-500"></i>Infaq & Sedekah
            </a>
            <a href="{{ route('user.kartu_santri') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.kartu_santri') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-id-card text-base w-5 text-center"></i>Kartu Santri Digital
            </a>
            <a href="{{ route('user.surat') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.surat') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-envelope text-base w-5 text-center"></i>Surat & Dokumen
            </a>
            <a href="{{ route('user.form.history') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.form.history') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-file-alt text-base w-5 text-center"></i>Riwayat Pendaftaran
            </a>
            <a href="{{ route('user.notifikasi') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.notifikasi') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-bell text-base w-5 text-center"></i>Notifikasi
            </a>

            <div class="border-t border-gray-100 mt-3 pt-3">
                <a href="{{ route('user.profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-user-edit text-base w-5 text-center"></i>Edit Profil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-rose-600 hover:bg-rose-50">
                        <i class="fas fa-sign-out-alt text-base w-5 text-center"></i>Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
