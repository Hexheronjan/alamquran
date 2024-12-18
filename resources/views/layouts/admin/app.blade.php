<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.admin.partial.head')
    <title>Aplikasi PPDB</title>
</head>
<body class="bg-grey">
    <main>
        <!-- Start the project -->
        <div id="main-wrapper" class="flex">
			<aside id="application-sidebar-brand"
				class="hs-overlay hs-overlay-open:translate-x-0 -translate-x-full  transform hidden xl:block xl:translate-x-0 xl:end-auto xl:bottom-0 fixed top-0 with-vertical h-screen z-[999] flex-shrink-0 border-r-[1px] w-[270px] border-gray-400  bg-white left-sidebar   transition-all duration-300"
				data-hs-overlay-backdrop-template='<div class="hs-overlay-backdrop bg-gray-900/50 fixed inset-0 z-[998]"></div>'>
				@include('layouts.admin.partial.sidebar')
			</aside>
		
            <div class="w-full page-wrapper overflow-hidden">
                <!-- Header Start -->
                <header class="container full-container w-full text-sm py-5 xl:px-9 px-5">
                    @include('layouts.admin.partial.header')
                </header>
                <!-- Header End -->

                <!-- Main Content -->
                <div class="content-container">
                    @yield('content')
                </div>
                <!-- Main Content End -->
				
            </div>
        </div>
        <!-- End of project -->
        {{-- @include('layouts.admin.partial.footer') --}}
    </main>
    @include('layouts.admin.partial.scripts')
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
	<!-- ApexCharts -->
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
	<!-- Custom JS -->
	<script src="{{ asset('assets/js/dashboard.js') }}"></script>
	
	
</body>
</html>