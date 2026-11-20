<!-- ---------------------------------- -->
<!-- Start Vertical Layout Sidebar -->
<!-- ---------------------------------- -->
<div class="p-5">
  @include("layouts.admin.partial.logo-sidebar")
</div>
<div class="scroll-sidebar" style="padding-bottom: 16px;">
  <div class="px-6 mt-4">
    @php
      $adminUser = auth('admin')->user();
    @endphp
    <nav class="w-full flex flex-col sidebar-nav">
      <ul id="sidebarnav" class="text-gray-600 text-sm">

        {{-- Section HOME --}}
        <li class="text-xs font-bold pb-4">
          <i class="ti ti-dots nav-small-cap-icon text-lg hidden text-center"></i>
          <span>HOME</span>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.dashboard')}}">
            <i class="ti ti-layout-dashboard text-xl"></i> <span>Dashboard</span>
          </a>
        </li>

        {{-- MANAJEMEN ADMIN --}}
        @if($adminUser && $adminUser->canAccess('admin_management'))
          @php
            $manajemenAdminActive = request()->routeIs('admin.admins.index', 'admin.admins.create', 'admin.admins.edit', 'admin.admins.logs', 'admin.backup.*');
          @endphp
          <li class="mt-4">
            <button type="button"
              onclick="toggleAccordion('accordion-manajemen-admin')"
              class="accordion-toggle w-full flex items-center justify-between py-2 px-3 rounded-md text-xs font-bold text-purple-700 hover:bg-purple-50 transition-colors duration-200"
              aria-expanded="{{ $manajemenAdminActive ? 'true' : 'false' }}">
              <span>MANAJEMEN ADMIN</span>
              <i class="ti ti-chevron-down text-sm transition-transform duration-300 {{ $manajemenAdminActive ? 'rotate-180' : '' }}" id="icon-accordion-manajemen-admin"></i>
            </button>
            <ul id="accordion-manajemen-admin"
              class="accordion-content overflow-hidden transition-all duration-300 ease-in-out {{ $manajemenAdminActive ? '' : 'hidden' }} mt-1 space-y-0.5">
              <li class="sidebar-item">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-purple-600 hover:bg-purple-100 {{ request()->routeIs('admin.admins.index', 'admin.admins.create', 'admin.admins.edit') ? 'bg-purple-50 text-purple-700 font-bold' : '' }}" href="{{ route('admin.admins.index')}}">
                  <i class="ti ti-users-group text-xl text-purple-600"></i> <span>Daftar Admin</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-purple-600 hover:bg-purple-100 {{ request()->routeIs('admin.admins.logs') ? 'bg-purple-50 text-purple-700 font-bold' : '' }}" href="{{ route('admin.admins.logs')}}">
                  <i class="ti ti-history text-xl text-purple-600"></i> <span>Riwayat Aktivitas</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-purple-600 hover:bg-purple-100 {{ request()->routeIs('admin.backup.*') ? 'bg-purple-50 text-purple-700 font-bold' : '' }}" href="{{ route('admin.backup.index')}}">
                  <i class="ti ti-shield-check text-xl text-purple-600"></i> <span>Backup & Restore</span>
                </a>
              </li>
            </ul>
          </li>
        @endif

        {{-- MASTER DATA --}}
        @if($adminUser && $adminUser->canAccess('master_data'))
          @php
            $masterDataActive = request()->routeIs('admin.schools.*', 'admin.angkatan.*');
          @endphp
          <li class="mt-4">
            <button type="button"
              onclick="toggleAccordion('accordion-master-data')"
              class="accordion-toggle w-full flex items-center justify-between py-2 px-3 rounded-md text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors duration-200"
              aria-expanded="{{ $masterDataActive ? 'true' : 'false' }}">
              <span>MASTER DATA</span>
              <i class="ti ti-chevron-down text-sm transition-transform duration-300 {{ $masterDataActive ? 'rotate-180' : '' }}" id="icon-accordion-master-data"></i>
            </button>
            <ul id="accordion-master-data"
              class="accordion-content overflow-hidden transition-all duration-300 ease-in-out {{ $masterDataActive ? '' : 'hidden' }} mt-1 space-y-0.5">
              <li class="sidebar-item">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.schools.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.schools.index')}}">
                  <i class="ti ti-school text-xl"></i> <span>Data Madrasah</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.angkatan.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.angkatan.index')}}">
                  <i class="ti ti-calendar-event text-xl"></i> <span>Data Angkatan</span>
                </a>
              </li>
            </ul>
          </li>
        @endif

        {{-- DATA SANTRI & PSB --}}
        @php
          $canAccessSantriMenu = $adminUser && (
            $adminUser->canAccess('ppdb') ||
            $adminUser->canAccess('ppdb_read') ||
            $adminUser->canAccess('kegiatan') ||
            $adminUser->canAccess('prestasi') ||
            $adminUser->canAccess('pelanggaran')
          );
        @endphp
        @if($canAccessSantriMenu)
          @php
            $kelolaSantriActive = request()->routeIs(
              'admin.santri.index', 'admin.santri.show', 'admin.santri.edit',
              'admin.santri.kenaikan_kelas*',
              'admin.kartu_santri.*',
              'admin.alumni.*',
              'admin.kegiatan.*',
              'admin.prestasi.*',
              'admin.pelanggaran.*'
            );
          @endphp
          <li class="mt-4">
            <button type="button"
              onclick="toggleAccordion('accordion-kelola-santri')"
              class="accordion-toggle w-full flex items-center justify-between py-2 px-3 rounded-md text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors duration-200"
              aria-expanded="{{ $kelolaSantriActive ? 'true' : 'false' }}">
              <span>KELOLA SANTRI</span>
              <i class="ti ti-chevron-down text-sm transition-transform duration-300 {{ $kelolaSantriActive ? 'rotate-180' : '' }}" id="icon-accordion-kelola-santri"></i>
            </button>
            <ul id="accordion-kelola-santri"
              class="accordion-content overflow-hidden transition-all duration-300 ease-in-out {{ $kelolaSantriActive ? '' : 'hidden' }} mt-1 space-y-0.5">
              @if($adminUser->canAccess('ppdb') || $adminUser->canAccess('ppdb_read'))
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.santri.index', 'admin.santri.show', 'admin.santri.edit') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.santri.index')}}">
                    <i class="ti ti-users text-xl"></i> <span>Data Santri Baru</span>
                  </a>
                </li>
              @endif
              @if($adminUser->canAccess('ppdb'))
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.kartu_santri.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.kartu_santri.index') }}">
                    <i class="ti ti-id-badge text-xl text-emerald-600"></i> <span>Kartu Santri</span>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.santri.kenaikan_kelas*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.santri.kenaikan_kelas')}}">
                    <i class="ti ti-stairs-up text-xl"></i> <span>Kenaikan Kelas</span>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.alumni.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.alumni.index')}}">
                    <i class="ti ti-school text-xl"></i> <span>Data Alumni</span>
                  </a>
                </li>
              @endif
              @if($adminUser->canAccess('kegiatan') || $adminUser->canAccess('ppdb'))
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.kegiatan.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.kegiatan.index')}}">
                    <i class="ti ti-calendar-event text-xl"></i> <span>Kegiatan Santri</span>
                  </a>
                </li>
              @endif
              @if($adminUser->canAccess('prestasi') || $adminUser->canAccess('ppdb'))
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-amber-600 hover:bg-amber-50 {{ request()->routeIs('admin.prestasi.*') ? 'bg-amber-50 text-amber-700 font-bold' : '' }}" href="{{ route('admin.prestasi.index')}}">
                    <i class="ti ti-trophy text-xl text-amber-500"></i> <span>Prestasi Santri</span>
                  </a>
                </li>
              @endif
              @if($adminUser->canAccess('pelanggaran') || $adminUser->canAccess('ppdb'))
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-red-600 hover:bg-red-50 {{ request()->routeIs('admin.pelanggaran.*') ? 'bg-red-50 text-red-700 font-bold' : '' }}" href="{{ route('admin.pelanggaran.index')}}">
                    <i class="ti ti-gavel text-xl text-red-500"></i> <span>Pelanggaran & Disiplin</span>
                  </a>
                </li>
              @endif
            </ul>
          </li>
        @endif

        {{-- KESEHATAN SANTRI (UKS) - DIPISAH DARI KELOLA SANTRI --}}
        @if($adminUser && ($adminUser->canAccess('kesehatan') || $adminUser->canAccess('ppdb')))
          <li class="sidebar-item mt-2">
            <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-rose-600 hover:bg-rose-50 {{ request()->routeIs('admin.kesehatan.*') ? 'bg-rose-50 text-rose-700 font-bold' : '' }}" href="{{ route('admin.kesehatan.index')}}">
              <i class="ti ti-first-aid-kit text-xl text-rose-500"></i> <span>Kesehatan Santri (UKS)</span>
            </a>
          </li>
        @endif

        {{-- PENGGUNA & OTP --}}
        @if($adminUser && $adminUser->canAccess('pengguna'))
          <li class="sidebar-item mt-2">
            <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.pengguna.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.pengguna.index')}}">
              <i class="ti ti-user-circle text-xl"></i> <span>Pengguna / Akun Wali</span>
            </a>
          </li>
        @endif

        @if($adminUser && $adminUser->canAccess('otp_monitoring'))
          <li class="sidebar-item">
            <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.otp.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.otp.index')}}">
              <i class="ti ti-shield-lock text-xl"></i> <span>Monitoring OTP</span>
            </a>
          </li>
        @endif

        {{-- MONITORING ONLINE WALI --}}
        @if($adminUser && ($adminUser->canAccess('monitoring_wali') || $adminUser->canAccess('pengguna') || $adminUser->canAccess('otp_monitoring') || $adminUser->isSuperAdmin()))
          <li class="sidebar-item">
            <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-emerald-600 hover:bg-emerald-50 {{ request()->routeIs('admin.monitoring.wali') ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}" href="{{ route('admin.monitoring.wali')}}">
              <i class="ti ti-activity-heartbeat text-xl text-emerald-600"></i> <span>Monitoring Online Wali</span>
            </a>
          </li>
        @endif

        {{-- KALENDER AKADEMIK --}}
        @if($adminUser && ($adminUser->canAccess('kalender') || $adminUser->canAccess('ppdb') || $adminUser->isSuperAdmin()))
          <li class="sidebar-item mt-2">
            <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-teal-600 hover:bg-teal-50 {{ request()->routeIs('admin.kalender.*') ? 'bg-teal-50 text-teal-700 font-bold' : '' }}" href="{{ route('admin.kalender.index')}}">
              <i class="ti ti-calendar text-xl text-teal-600"></i> <span>Kalender Akademik</span>
            </a>
          </li>
        @endif

        {{-- ABSENSI --}}
        @if($adminUser && ($adminUser->canAccess('absensi') || $adminUser->canAccess('ppdb')))
          @php
            $absensiActive = request()->routeIs('admin.absensi.index', 'admin.absensi.input', 'admin.absensi.rekap');
          @endphp
          <li class="mt-4">
            <button type="button"
              onclick="toggleAccordion('accordion-absensi')"
              class="accordion-toggle w-full flex items-center justify-between py-2 px-3 rounded-md text-xs font-bold text-indigo-700 hover:bg-indigo-50 transition-colors duration-200"
              aria-expanded="{{ $absensiActive ? 'true' : 'false' }}">
              <span>ABSENSI</span>
              <i class="ti ti-chevron-down text-sm transition-transform duration-300 {{ $absensiActive ? 'rotate-180' : '' }}" id="icon-accordion-absensi"></i>
            </button>
            <ul id="accordion-absensi"
              class="accordion-content overflow-hidden transition-all duration-300 ease-in-out {{ $absensiActive ? '' : 'hidden' }} mt-1 space-y-0.5">
              <li class="sidebar-item">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-indigo-600 hover:bg-indigo-50 {{ request()->routeIs('admin.absensi.index') ? 'bg-indigo-50 text-indigo-700 font-bold' : '' }}" href="{{ route('admin.absensi.index')}}">
                  <i class="ti ti-clipboard-check text-xl text-indigo-600"></i> <span>Absensi Harian</span>
                </a>
              </li>
              <li class="sidebar-item pl-4">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 text-xs {{ request()->routeIs('admin.absensi.input') ? 'text-indigo-700 font-bold' : '' }}" href="{{ route('admin.absensi.input')}}">
                  <i class="ti ti-pencil text-base"></i> <span>Input Absensi</span>
                </a>
              </li>
              <li class="sidebar-item pl-4">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 text-xs {{ request()->routeIs('admin.absensi.rekap') ? 'text-indigo-700 font-bold' : '' }}" href="{{ route('admin.absensi.rekap')}}">
                  <i class="ti ti-chart-bar text-base"></i> <span>Rekap Bulanan</span>
                </a>
              </li>
            </ul>
          </li>
        @endif

        {{-- TAHFIDZ AL-QUR'AN --}}
        @if($adminUser && $adminUser->canAccess('tahfidz'))
          @php
            $tahfidzActive = request()->routeIs('admin.tahfidz.index', 'admin.tahfidz.master_surah');
          @endphp
          <li class="mt-4">
            <button type="button"
              onclick="toggleAccordion('accordion-tahfidz')"
              class="accordion-toggle w-full flex items-center justify-between py-2 px-3 rounded-md text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition-colors duration-200"
              aria-expanded="{{ $tahfidzActive ? 'true' : 'false' }}">
              <span>TAHFIDZ AL-QUR'AN</span>
              <i class="ti ti-chevron-down text-sm transition-transform duration-300 {{ $tahfidzActive ? 'rotate-180' : '' }}" id="icon-accordion-tahfidz"></i>
            </button>
            <ul id="accordion-tahfidz"
              class="accordion-content overflow-hidden transition-all duration-300 ease-in-out {{ $tahfidzActive ? '' : 'hidden' }} mt-1 space-y-0.5">
              <li class="sidebar-item">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-emerald-600 hover:bg-emerald-50 {{ request()->routeIs('admin.tahfidz.index') ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}" href="{{ route('admin.tahfidz.index')}}">
                  <i class="ti ti-book text-xl text-emerald-600"></i> <span>Setoran Tahfidz</span>
                </a>
              </li>
              <li class="sidebar-item pl-4">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 text-xs {{ request()->routeIs('admin.tahfidz.master_surah') ? 'text-emerald-700 font-bold' : '' }}" href="{{ route('admin.tahfidz.master_surah')}}">
                  <i class="ti ti-database text-base"></i> <span>Master Data Surah</span>
                </a>
              </li>
            </ul>
          </li>
        @endif

        {{-- UANG SAKU SANTRI - DIPISAH DARI KEUANGAN/SPP --}}
        @if($adminUser && ($adminUser->canAccess('uang_saku') || $adminUser->canAccess('rekening_kelas')))
          @php
            $uangSakuActive = request()->routeIs(
              'admin.uang_saku.*',
              'admin.rekening_kelas.*',
              'admin.deposit_transfer.*'
            );
          @endphp
          <li class="mt-4">
            <button type="button"
              onclick="toggleAccordion('accordion-uang-saku')"
              class="accordion-toggle w-full flex items-center justify-between py-2 px-3 rounded-md text-xs font-bold text-amber-700 hover:bg-amber-50 transition-colors duration-200"
              aria-expanded="{{ $uangSakuActive ? 'true' : 'false' }}">
              <span>UANG SAKU SANTRI</span>
              <i class="ti ti-chevron-down text-sm transition-transform duration-300 {{ $uangSakuActive ? 'rotate-180' : '' }}" id="icon-accordion-uang-saku"></i>
            </button>
            <ul id="accordion-uang-saku"
              class="accordion-content overflow-hidden transition-all duration-300 ease-in-out {{ $uangSakuActive ? '' : 'hidden' }} mt-1 space-y-0.5">
              <li class="sidebar-item">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-amber-600 hover:bg-amber-50 {{ request()->routeIs('admin.uang_saku.index', 'admin.uang_saku.show') ? 'bg-amber-50 text-amber-700 font-bold' : '' }}" href="{{ route('admin.uang_saku.index')}}">
                  <i class="ti ti-wallet text-xl text-amber-600"></i> <span>Uang Saku Santri</span>
                </a>
              </li>
              <li class="sidebar-item pl-4">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center text-gray-500 hover:text-amber-600 hover:bg-amber-50 text-xs {{ request()->routeIs('admin.deposit_transfer.*') ? 'text-amber-600 font-bold' : '' }}" href="{{ route('admin.deposit_transfer.index')}}">
                  <i class="ti ti-checkup-list text-base"></i> <span>Verifikasi Deposit Transfer</span>
                </a>
              </li>
              <li class="sidebar-item pl-4">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center text-gray-500 hover:text-amber-600 hover:bg-amber-50 text-xs {{ request()->routeIs('admin.rekening_kelas.*') ? 'text-amber-600 font-bold' : '' }}" href="{{ route('admin.rekening_kelas.index')}}">
                  <i class="ti ti-building-bank text-base"></i> <span>Rekening Ustadz Kelas</span>
                </a>
              </li>
              <li class="sidebar-item pl-4">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center text-gray-500 hover:text-amber-600 hover:bg-amber-50 text-xs {{ request()->routeIs('admin.uang_saku.createManual') ? 'text-amber-600 font-bold' : '' }}" href="{{ route('admin.uang_saku.createManual')}}">
                  <i class="ti ti-plus text-base"></i> <span>Input Transaksi Manual</span>
                </a>
              </li>
            </ul>
          </li>
        @endif

        {{-- KEUANGAN (BENDAHARA) --}}
        @if($adminUser && ($adminUser->canAccess('daftar_ulang') || $adminUser->canAccess('spp') || $adminUser->canAccess('infaq')))
          @php
            $keuanganActive = request()->routeIs(
              'admin.daftar_ulang.*',
              'admin.spp.*',
              'admin.infaq.*',
              'admin.invoice_setting.*'
            );
          @endphp
          <li class="mt-4">
            <button type="button"
              onclick="toggleAccordion('accordion-keuangan')"
              class="accordion-toggle w-full flex items-center justify-between py-2 px-3 rounded-md text-xs font-bold text-blue-700 hover:bg-blue-50 transition-colors duration-200"
              aria-expanded="{{ $keuanganActive ? 'true' : 'false' }}">
              <span>KEUANGAN</span>
              <i class="ti ti-chevron-down text-sm transition-transform duration-300 {{ $keuanganActive ? 'rotate-180' : '' }}" id="icon-accordion-keuangan"></i>
            </button>
            <ul id="accordion-keuangan"
              class="accordion-content overflow-hidden transition-all duration-300 ease-in-out {{ $keuanganActive ? '' : 'hidden' }} mt-1 space-y-0.5">
              @if($adminUser->canAccess('daftar_ulang'))
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.daftar_ulang.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.daftar_ulang.index')}}">
                    <i class="ti ti-cash-banknote text-xl"></i> <span>Biaya Daftar Ulang</span>
                  </a>
                </li>
              @endif
              @if($adminUser->canAccess('spp'))
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.spp.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.spp.index')}}">
                    <i class="ti ti-calendar-month text-xl"></i> <span>Administrasi & SPP</span>
                  </a>
                </li>
              @endif
              @if($adminUser->canAccess('infaq'))
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-rose-600 hover:bg-rose-50 {{ request()->routeIs('admin.infaq.*') ? 'bg-rose-50 text-rose-700 font-bold' : '' }}" href="{{ route('admin.infaq.index')}}">
                    <i class="ti ti-heart-handshake text-xl text-rose-500"></i> <span>Infaq & Sedekah Online</span>
                  </a>
                </li>
              @endif
              @if($adminUser->canAccess('spp') || $adminUser->canAccess('daftar_ulang') || $adminUser->isSuperAdmin())
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-teal-600 hover:bg-teal-50 {{ request()->routeIs('admin.invoice_setting.*') ? 'bg-teal-50 text-teal-700 font-bold' : '' }}" href="{{ route('admin.invoice_setting.index')}}">
                    <i class="ti ti-file-invoice text-xl text-teal-600"></i> <span>Pengaturan Kop & Invoice</span>
                  </a>
                </li>
              @endif
            </ul>
          </li>
        @endif

        {{-- KANTIN & AQUMART --}}
        @if($adminUser && $adminUser->canAccess('kantin'))
          @php
            $kantinActive = request()->routeIs(
              'admin.kantin.kasir.*',
              'admin.kantin.produk.index',
              'admin.kantin.produk.create',
              'admin.kantin.laporan.*'
            );
          @endphp
          <li class="mt-4">
            <button type="button"
              onclick="toggleAccordion('accordion-kantin')"
              class="accordion-toggle w-full flex items-center justify-between py-2 px-3 rounded-md text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition-colors duration-200"
              aria-expanded="{{ $kantinActive ? 'true' : 'false' }}">
              <span>KANTIN & AQUMART</span>
              <i class="ti ti-chevron-down text-sm transition-transform duration-300 {{ $kantinActive ? 'rotate-180' : '' }}" id="icon-accordion-kantin"></i>
            </button>
            <ul id="accordion-kantin"
              class="accordion-content overflow-hidden transition-all duration-300 ease-in-out {{ $kantinActive ? '' : 'hidden' }} mt-1 space-y-0.5">
              <li class="sidebar-item">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-emerald-600 hover:bg-emerald-50 {{ request()->routeIs('admin.kantin.kasir.*') ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}" href="{{ route('admin.kantin.kasir.index')}}">
                  <i class="ti ti-cash-register text-xl text-emerald-600"></i> <span>Kasir POS Belanja</span>
                </a>
              </li>
              <li class="sidebar-item pl-4">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 text-xs {{ request()->routeIs('admin.kantin.produk.index') ? 'text-emerald-700 font-bold' : '' }}" href="{{ route('admin.kantin.produk.index')}}">
                  <i class="ti ti-packages text-base"></i> <span>Kelola Produk & Stok</span>
                </a>
              </li>
              <li class="sidebar-item pl-8">
                <a class="sidebar-link gap-3 py-1.5 px-3 rounded-md w-full flex items-center text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 text-xs {{ request()->routeIs('admin.kantin.produk.create') ? 'text-emerald-700 font-bold' : '' }}" href="{{ route('admin.kantin.produk.create')}}">
                  <i class="ti ti-plus text-xs"></i> <span>Tambah Produk Baru</span>
                </a>
              </li>
              <li class="sidebar-item pl-4">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 text-xs {{ request()->routeIs('admin.kantin.laporan.*') ? 'text-emerald-700 font-bold' : '' }}" href="{{ route('admin.kantin.laporan.index')}}">
                  <i class="ti ti-report-money text-base"></i> <span>Laporan Laba Rugi</span>
                </a>
              </li>
            </ul>
          </li>
        @endif

        {{-- SURAT MENYURAT --}}
        @if($adminUser && ($adminUser->canAccess('ppdb') || $adminUser->isSuperAdmin()))
          <li class="text-xs font-bold mb-3 mt-8 text-amber-700">
            <span>SURAT MENYURAT</span>
          </li>
          <li class="sidebar-item">
            <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-amber-600 hover:bg-amber-50 {{ request()->routeIs('admin.surat.keluar.*') ? 'bg-amber-50 text-amber-700 font-bold' : '' }}" href="{{ route('admin.surat.keluar.index')}}">
              <i class="ti ti-send text-xl text-amber-600"></i> <span>Surat Keluar</span>
            </a>
          </li>
          <li class="sidebar-item">
            <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-amber-600 hover:bg-amber-50 {{ request()->routeIs('admin.surat.masuk.*') ? 'bg-amber-50 text-amber-700 font-bold' : '' }}" href="{{ route('admin.surat.masuk.index')}}">
              <i class="ti ti-mail-opened text-xl text-amber-600"></i> <span>Arsip Surat Masuk</span>
            </a>
          </li>
        @endif

        {{-- PERIZINAN & KEHADIRAN --}}
        @if($adminUser && ($adminUser->canAccess('ppdb') || $adminUser->isSuperAdmin()))
          @php
            $perizinanActive = request()->routeIs('admin.perizinan.index', 'admin.perizinan.scanner');
          @endphp
          <li class="mt-4">
            <button type="button"
              onclick="toggleAccordion('accordion-perizinan')"
              class="accordion-toggle w-full flex items-center justify-between py-2 px-3 rounded-md text-xs font-bold text-cyan-700 hover:bg-cyan-50 transition-colors duration-200"
              aria-expanded="{{ $perizinanActive ? 'true' : 'false' }}">
              <span>PERIZINAN & KEHADIRAN</span>
              <i class="ti ti-chevron-down text-sm transition-transform duration-300 {{ $perizinanActive ? 'rotate-180' : '' }}" id="icon-accordion-perizinan"></i>
            </button>
            <ul id="accordion-perizinan"
              class="accordion-content overflow-hidden transition-all duration-300 ease-in-out {{ $perizinanActive ? '' : 'hidden' }} mt-1 space-y-0.5">
              <li class="sidebar-item">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-cyan-600 hover:bg-cyan-50 {{ request()->routeIs('admin.perizinan.index') ? 'bg-cyan-50 text-cyan-700 font-bold' : '' }}" href="{{ route('admin.perizinan.index')}}">
                  <i class="ti ti-door-exit text-xl text-cyan-600"></i> <span>Kehadiran & Perizinan</span>
                </a>
              </li>
              <li class="sidebar-item pl-4">
                <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center text-gray-500 hover:text-cyan-600 hover:bg-cyan-50 text-xs {{ request()->routeIs('admin.perizinan.scanner') ? 'text-cyan-700 font-bold' : '' }}" href="{{ route('admin.perizinan.scanner')}}">
                  <i class="ti ti-qrcode text-base"></i> <span>Scanner QR Code</span>
                </a>
              </li>
            </ul>
          </li>
        @endif

        {{-- BROADCAST / KOMUNIKASI --}}
        @php
          $canAccessBroadcast = $adminUser && ($adminUser->canAccess('ppdb') || $adminUser->canAccess('daftar_ulang') || $adminUser->canAccess('spp'));
          $canAccessWaLogs = $adminUser && ($adminUser->canAccess('admin_management') || $adminUser->isSuperAdmin());
          $canAccessSaran = $adminUser && ($adminUser->canAccess('saran') || $adminUser->canAccess('operator') || $adminUser->isSuperAdmin());
          $canAccessTelepon = $adminUser && ($adminUser->canAccess('telepon') || $adminUser->canAccess('ppdb') || $adminUser->canAccess('operator') || $adminUser->isSuperAdmin());
        @endphp
        @if($canAccessBroadcast || $canAccessWaLogs || $canAccessSaran || $canAccessTelepon)
          @php
            $komunikasiActive = request()->routeIs('admin.broadcast.*', 'admin.whatsapp.logs', 'admin.saran.*', 'admin.telepon.*');
          @endphp
          <li class="mt-4">
            <button type="button"
              onclick="toggleAccordion('accordion-komunikasi')"
              class="accordion-toggle w-full flex items-center justify-between py-2 px-3 rounded-md text-xs font-bold text-green-700 hover:bg-green-50 transition-colors duration-200"
              aria-expanded="{{ $komunikasiActive ? 'true' : 'false' }}">
              <span>KOMUNIKASI</span>
              <i class="ti ti-chevron-down text-sm transition-transform duration-300 {{ $komunikasiActive ? 'rotate-180' : '' }}" id="icon-accordion-komunikasi"></i>
            </button>
            <ul id="accordion-komunikasi"
              class="accordion-content overflow-hidden transition-all duration-300 ease-in-out {{ $komunikasiActive ? '' : 'hidden' }} mt-1 space-y-0.5">
              @if($canAccessTelepon)
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-emerald-600 hover:bg-emerald-50 {{ request()->routeIs('admin.telepon.*') ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}" href="{{ route('admin.telepon.index')}}">
                    <i class="ti ti-phone-call text-xl text-emerald-600"></i> <span>Jadwal Telepon Santri</span>
                  </a>
                </li>
              @endif
              @if($canAccessBroadcast)
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-green-600 hover:bg-green-50 {{ request()->routeIs('admin.broadcast.*') ? 'bg-green-50 text-green-700 font-bold' : '' }}" href="{{ route('admin.broadcast.index')}}">
                    <i class="ti ti-brand-whatsapp text-xl text-green-600"></i> <span>Broadcast WhatsApp</span>
                  </a>
                </li>
              @endif
              @if($canAccessWaLogs)
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-green-600 hover:bg-green-50 {{ request()->routeIs('admin.whatsapp.logs') ? 'bg-green-50 text-green-700 font-bold' : '' }}" href="{{ route('admin.whatsapp.logs')}}">
                    <i class="ti ti-activity text-xl text-teal-600"></i> <span>Log Aktivitas WhatsApp</span>
                  </a>
                </li>
              @endif
              @if($canAccessSaran)
                <li class="sidebar-item">
                  <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-emerald-600 hover:bg-emerald-50 {{ request()->routeIs('admin.saran.*') ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}" href="{{ route('admin.saran.index')}}">
                    <i class="ti ti-message-2-question text-xl text-emerald-600"></i> <span>Kotak Saran & Masukan</span>
                  </a>
                </li>
              @endif
            </ul>
          </li>
        @endif

        {{-- PROFIL & LOGOUT --}}
        <li class="sidebar-item mt-8">
          <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-100 {{ request()->routeIs('admin.profile.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}" href="{{ route('admin.profile.edit')}}">
            <i class="ti ti-file-description text-xl"></i> <span>Profil</span>
          </a>
        </li>

        {{-- TEMPAT SAMPAH (PALING BAWAH - KHUSUS SUPER ADMIN) --}}
        @if($adminUser && $adminUser->isSuperAdmin())
          <li class="sidebar-item mt-2">
            <a class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center hover:text-red-600 hover:bg-red-50 {{ request()->routeIs('admin.santri.trash*') ? 'bg-red-50 text-red-700 font-bold' : '' }}" href="{{ route('admin.santri.trash')}}">
              <i class="ti ti-trash text-xl text-red-500"></i> <span>Tempat Sampah</span>
            </a>
          </li>
        @endif

        <li class="text-xs font-bold mb-4 mt-6 text-gray-300">
          <span>─────────────────</span>
        </li>

        <li class="sidebar-item">
          <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="sidebar-link gap-3 py-2 px-3 rounded-md w-full flex items-center text-left hover:text-red-600 hover:bg-red-100 cursor-pointer">
                  <i class="ti ti-logout text-xl"></i>
                  <span>Logout</span>
              </button>
          </form>
        </li>

      </ul>
    </nav>
  </div>
</div>

{{-- Accordion JavaScript --}}
<script>
  function toggleAccordion(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);

    if (!content) return;

    const isHidden = content.classList.contains('hidden');

    if (isHidden) {
      // Open
      content.classList.remove('hidden');
      content.style.maxHeight = '0px';
      content.style.opacity = '0';
      requestAnimationFrame(() => {
        content.style.transition = 'max-height 0.3s ease-in-out, opacity 0.25s ease-in-out';
        content.style.maxHeight = content.scrollHeight + 'px';
        content.style.opacity = '1';
      });
      if (icon) icon.classList.add('rotate-180');
    } else {
      // Close
      content.style.transition = 'max-height 0.3s ease-in-out, opacity 0.25s ease-in-out';
      content.style.maxHeight = content.scrollHeight + 'px';
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          content.style.maxHeight = '0px';
          content.style.opacity = '0';
        });
      });
      setTimeout(() => {
        content.classList.add('hidden');
        content.style.maxHeight = '';
        content.style.opacity = '';
      }, 300);
      if (icon) icon.classList.remove('rotate-180');
    }
  }

  // On page load: ensure open accordions have max-height set properly
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.accordion-content:not(.hidden)').forEach(function (el) {
      el.style.maxHeight = el.scrollHeight + 'px';
      el.style.opacity = '1';
    });
  });
</script>
<!-- </aside> -->