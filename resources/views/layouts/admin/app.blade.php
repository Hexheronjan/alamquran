<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.admin.partial.head')
    <title>Aplikasi PSB</title>
    <style>
        /* === PREVENT ALPINE.JS FLICKER ON PAGE REFRESH === */
        [x-cloak] { display: none !important; }

        /* === SIDEBAR RESPONSIVE & BACKDROP OVERLAY FIX === */

        /* Di Laptop (>= 1024px): Sidebar permanen di kiri, backdrop DILARANG MUNCUL */
        @media (min-width: 1024px) {
            #application-sidebar-brand {
                display: flex !important;
                transform: translateX(0) !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                bottom: 0 !important;
                z-index: 40 !important;
            }
            /* Geser konten ke kanan sejauh lebar sidebar */
            .page-wrapper {
                margin-left: 270px !important;
                width: calc(100% - 270px) !important;
            }
            /* Sembunyikan tombol hamburger di laptop */
            #headerCollapse {
                display: none !important;
            }
            /* Mati total elemen backdrop di laptop */
            .hs-overlay-backdrop {
                display: none !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }
        }

        /* Di HP & Tablet (< 1024px): Sidebar overlay di atas konten, full width page */
        @media (max-width: 1023px) {
            .page-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }
            /* Tampilkan tombol hamburger */
            #headerCollapse {
                display: flex !important;
            }
            /* Saat sidebar ditutup, hapus total elemen backdrop & un-lock klik */
            body:not(.hs-overlay-open) .hs-overlay-backdrop {
                display: none !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }
        }

        /* Styling umum & pengunci overflow mendatar */
        html, body {
            overflow-x: hidden;
            max-width: 100vw;
        }

        /* Backdrop overlay styling agar berada di bawah sidebar */
        .hs-overlay-backdrop {
            z-index: 45 !important;
        }

        /* Menu sidebar memiliki area scroll sendiri — aktif di semua ukuran layar */
        #application-sidebar-brand {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #application-sidebar-brand .scroll-sidebar {
            flex: 1 1 auto;
            min-height: 0;
            height: 100% !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            scrollbar-width: thin;
            scrollbar-color: #9ca3af transparent;
            -webkit-overflow-scrolling: touch; /* scroll smooth di iOS */
        }

        #application-sidebar-brand .scroll-sidebar::-webkit-scrollbar {
            width: 4px;
        }

        #application-sidebar-brand .scroll-sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        /* Di laptop: sidebar pasti flex + visible */
        @media (min-width: 1024px) {
            #application-sidebar-brand {
                display: flex !important;
                overflow: hidden !important;
            }
        }

        /* Force layout admin penuhi 100% lebar layar tanpa area kosong di samping */
        .container,
        .full-container,
        .content-container {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .container.full-container {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
        }

        /* Tabel admin penyesuaian responsif */
        .admin-data-table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed;
        }

        .admin-data-table th,
        .admin-data-table td {
            padding: 12px !important;
            white-space: normal !important;
            overflow-wrap: anywhere;
        }

        /* Responsivitas Layar HP (< 640px) & Desktop (>= 640px) */
        @media (max-width: 639px) {
            #main-wrapper,
            .page-wrapper,
            .content-container {
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
            }

            .container.full-container,
            .content-container .container,
            .content-container .full-container {
                width: 100% !important;
                max-width: 100% !important;
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .desktop-only-table {
                display: none !important;
            }

            .overflow-x-auto .admin-data-table {
                min-width: 850px !important;
                table-layout: auto !important;
            }
        }

        @media (min-width: 640px) {
            /* Di desktop, mobile-data-cards selalu disembunyikan */
            .mobile-data-cards {
                display: none !important;
            }
            /* Di desktop, desktop-only-table ikut kontrol Alpine */
        }
    </style>
    @stack('styles')
</head>
<body class="bg-grey">
    <main>
        <!-- Start project -->
        <div id="main-wrapper" class="flex min-h-screen">
            <aside id="application-sidebar-brand"
                class="hs-overlay hs-overlay-open:translate-x-0 -translate-x-full fixed top-0 left-0 bottom-0 z-[50] h-screen w-[270px] border-r border-gray-200 bg-white left-sidebar transition-all duration-300 transform hidden lg:block lg:translate-x-0"
                data-hs-overlay-backdrop-template='<div class="hs-overlay-backdrop bg-gray-900/50 fixed inset-0 z-[45]"></div>'>
                @include('layouts.admin.partial.sidebar')
            </aside>
        
            <div class="w-full page-wrapper min-w-0">
                <!-- Header Start -->
                <header class="w-full text-sm py-4 lg:py-5 px-4 lg:px-8 bg-white border-b border-gray-100 sticky top-0 z-30">
                    @include('layouts.admin.partial.header')
                </header>
                <!-- Header End -->

                <!-- Main Content -->
                <div class="content-container w-full min-w-0">
                    @yield('content')
                    @include('layouts.admin.partial.footer')
                </div>
                <!-- Main Content End -->
            </div>
        </div>
        <!-- End project -->
    </main>
    @include('layouts.admin.partial.scripts')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">

    <!-- jQuery (dibutuhkan Lightbox2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Lightbox2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
	<!-- ApexCharts -->
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
	<!-- Custom JS -->
	<script src="{{ asset('assets/js/dashboard.js') }}"></script>
    @stack('scripts')
</body>
</html>
