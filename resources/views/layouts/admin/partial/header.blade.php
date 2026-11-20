<!-- ========== HEADER ========== -->
<nav class="w-full flex items-center justify-between gap-2" aria-label="Global">
    <div class="flex items-center gap-3">
        <button type="button" class="text-xl p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 focus:outline-none lg:hidden border-0 cursor-pointer flex items-center justify-center flex-shrink-0"
            id="headerCollapse"
            data-hs-overlay="#application-sidebar-brand"
            aria-controls="application-sidebar-brand"
            aria-label="Toggle navigation">
            <i class="ti ti-menu-2 text-2xl"></i>
        </button>
        <div class="flex items-center gap-2 min-w-0">
            <img src="{{ asset('assets/images/logos/icon-p.png')}}" alt="Logo" class="lg:hidden flex-shrink-0" style="width: 28px !important; height: 28px !important; max-width: 28px !important; max-height: 28px !important; object-fit: contain;">
            <span class="font-bold text-gray-800 text-base lg:text-lg truncate">PSB Admin</span>
        </div>
    </div>
    <div class="flex items-center gap-3 flex-shrink-0">
        @include('layouts.admin.partial.header-components.dd-profile')
    </div>
</nav>
<!-- ========== END HEADER ========== -->