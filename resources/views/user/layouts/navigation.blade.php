<nav x-data="{ open: false, menuOpen: false }" class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color:#16a34a;">
                            <i class="ti ti-book text-white text-lg"></i>
                        </div>
                        <span class="font-bold text-gray-800 text-sm hidden sm:block">Alam Qur'an</span>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden lg:flex items-center gap-1 ms-6">
                    <a href="{{ route('user.dashboard') }}"
                       class="px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('user.dashboard') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ti ti-home me-1"></i>Dashboard
                    </a>
                    <a href="{{ route('user.uang_saku') }}"
                       class="px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('user.uang_saku') || request()->routeIs('user.deposit_transfer.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ti ti-wallet me-1"></i>Uang Saku
                    </a>
                    <a href="{{ route('user.spp.index') }}"
                       class="px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('user.spp.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ti ti-receipt me-1"></i>SPP
                    </a>
                    <a href="{{ route('user.tahfidz') }}"
                       class="px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('user.tahfidz') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ti ti-book me-1"></i>Tahfidz
                    </a>
                    <a href="{{ route('user.absensi') }}"
                       class="px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('user.absensi') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ti ti-calendar-check me-1"></i>Absensi
                    </a>
                    <a href="{{ route('user.perizinan') }}"
                       class="px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('user.perizinan') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ti ti-qrcode me-1"></i>Perizinan
                    </a>

                    <!-- More Dropdown -->
                    <div class="relative" x-data="{ dropOpen: false }">
                        <button @click="dropOpen = !dropOpen" @click.away="dropOpen = false"
                                class="px-3 py-2 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 flex items-center gap-1">
                            <i class="ti ti-dots me-1"></i>Lainnya
                            <i class="ti ti-chevron-down text-[10px]"></i>
                        </button>
                        <div x-show="dropOpen" x-cloak
                             class="absolute left-0 mt-1 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <a href="{{ route('user.kegiatan') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                <i class="ti ti-calendar-event text-emerald-600"></i>Kegiatan Santri
                            </a>
                            <a href="{{ route('user.kondisi') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                <i class="ti ti-heart-rate-monitor text-red-500"></i>Kondisi Santri
                            </a>
                            <a href="{{ route('user.infaq') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                <i class="ti ti-heart text-pink-500"></i>Infaq & Sedekah
                            </a>
                            <a href="{{ route('user.kartu_santri') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                <i class="ti ti-id-badge text-blue-500"></i>Kartu Santri
                            </a>
                            <a href="{{ route('user.surat') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                <i class="ti ti-mail text-indigo-500"></i>Surat & Dokumen
                            </a>
                            <a href="{{ route('user.notifikasi') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                <i class="ti ti-bell text-orange-500"></i>Notifikasi
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Profile + Notif -->
            <div class="flex items-center gap-2">
                <!-- Notifikasi bell icon -->
                <a href="{{ route('user.notifikasi') }}" class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-50 hidden sm:flex">
                    <i class="ti ti-bell text-lg"></i>
                </a>

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ profileOpen: false }">
                    <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold"
                             style="background-color:#16a34a;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden sm:block text-xs font-medium max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                        <i class="ti ti-chevron-down text-[10px] hidden sm:block"></i>
                    </button>
                    <div x-show="profileOpen" x-cloak
                         class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-xs font-bold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-gray-400 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('user.profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
                            <i class="ti ti-user-edit text-gray-500"></i>Edit Profil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-red-600 hover:bg-red-50">
                                <i class="ti ti-logout"></i>Keluar
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Hamburger (mobile) -->
                <button @click="open = ! open" class="lg:hidden p-2 rounded-lg text-gray-400 hover:bg-gray-100">
                    <i class="ti ti-menu-2 text-lg" x-show="!open"></i>
                    <i class="ti ti-x text-lg" x-show="open" x-cloak></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div x-show="open" x-cloak class="lg:hidden border-t border-gray-100 bg-white">
        <div class="px-4 py-3 space-y-1 max-h-screen overflow-y-auto pb-6">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-3 py-1">Menu Utama</p>
            <a href="{{ route('user.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.dashboard') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="ti ti-home text-base"></i>Dashboard
            </a>
            <a href="{{ route('user.uang_saku') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.uang_saku') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="ti ti-wallet text-base"></i>Uang Saku
            </a>
            <a href="{{ route('user.deposit_transfer.create') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50 ps-9">
                <i class="ti ti-upload text-base"></i>Deposit Uang Saku
            </a>
            <a href="{{ route('user.spp.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.spp.*') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="ti ti-receipt text-base"></i>SPP & Administrasi
            </a>

            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-3 py-1 mt-2">Pemantauan Santri</p>
            <a href="{{ route('user.tahfidz') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.tahfidz') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="ti ti-book text-base"></i>Tahfidz Al-Qur'an
            </a>
            <a href="{{ route('user.absensi') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.absensi') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="ti ti-calendar-check text-base"></i>Absensi Sekolah
            </a>
            <a href="{{ route('user.kegiatan') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.kegiatan') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="ti ti-calendar-event text-base"></i>Kegiatan Santri
            </a>
            <a href="{{ route('user.kondisi') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.kondisi') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="ti ti-heart-rate-monitor text-base"></i>Kondisi Santri
            </a>

            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-3 py-1 mt-2">Perizinan & Sosial</p>
            <a href="{{ route('user.perizinan') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.perizinan') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="ti ti-qrcode text-base"></i>Perizinan & QR Wali
            </a>
            <a href="{{ route('user.infaq') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.infaq') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="ti ti-heart text-base"></i>Infaq & Sedekah
            </a>
            <a href="{{ route('user.kartu_santri') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.kartu_santri') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="ti ti-id-badge text-base"></i>Kartu Santri Digital
            </a>
            <a href="{{ route('user.surat') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.surat') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="ti ti-mail text-base"></i>Surat & Dokumen
            </a>
            <a href="{{ route('user.notifikasi') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('user.notifikasi') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="ti ti-bell text-base"></i>Notifikasi
            </a>

            <div class="border-t border-gray-100 mt-3 pt-3">
                <a href="{{ route('user.profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                    <i class="ti ti-user-edit text-base"></i>Edit Profil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-red-600 hover:bg-red-50">
                        <i class="ti ti-logout text-base"></i>Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
