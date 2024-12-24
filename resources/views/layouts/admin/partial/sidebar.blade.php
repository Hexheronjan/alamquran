<!-- ---------------------------------- -->
<!-- Start Vertical Layout Sidebar -->
<!-- ---------------------------------- -->
<div class="p-5" >
  @include("layouts.admin.partial.logo-sidebar")
</div>
<div class="scroll-sidebar" data-simplebar="">
 <div class="px-6 mt-8" >
    <nav class=" w-full flex flex-col sidebar-nav">
      <ul  id="sidebarnav" class="text-gray-600 text-sm">
        <li class="text-xs font-bold pb-4">
          <i class="ti ti-dots nav-small-cap-icon text-lg hidden text-center"></i>
          <span>HOME</span>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link gap-3 py-2 px-3  rounded-md  w-full flex items-center hover:text-blue-600 hover:bg-blue-500" href="{{ route('admin.dashboard')}}"
           >
            <i class="ti ti-layout-dashboard  text-xl"></i> <span>Dashboard</span>
          </a>
        </li>

        <li class="text-xs font-bold mb-4 mt-8">
          <i class="ti ti-dots nav-small-cap-icon text-lg hidden text-center"></i>
          <span>MASTER DATA</span>
        </li>         

        <li class="sidebar-item">
          <a class="sidebar-link gap-3 py-2 px-3  rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-500" href="{{ route('admin.form_groups.index')}}"
           >
            <i class="ti ti-file-description  text-xl"></i> <span>Data Formulir</span>
          </a>
        </li> 

        <li class="sidebar-item">
          <a class="sidebar-link gap-3 py-2 px-3  rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-500" href="{{ route('admin.schools.index')}}"
           >
            <i class="ti ti-file-description  text-xl"></i> <span>Data Sekolah</span>
          </a>
        </li> 

        <li class="text-xs font-bold mb-4 mt-8">
          <i class="ti ti-dots nav-small-cap-icon text-lg hidden text-center"></i>
          <span>KELOLA DATA</span>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link gap-3 py-2 px-3  rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-500" href="{{ route('admin.form_submission.index')}}"
           >
            <i class="ti ti-file-description  text-xl"></i> <span>Daftar Pendaftaran</span>
          </a>
        </li> 

        {{-- <li class="sidebar-item">
          <a class="sidebar-link gap-3 py-2 px-3  rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-500" href="@@webRoot/pages/authentication-register.html"
           >
            <i class="ti ti-user-plus  text-xl"></i> <span>Register</span>
          </a>
        </li>   --}}

        <li class="text-xs font-bold mb-4 mt-8">
          <i class="ti ti-dots nav-small-cap-icon text-lg hidden text-center"></i>
          <span>EXTRA</span>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link gap-3 py-2 px-3  rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-500" href="@@webRoot/pages/icons.html"
           >
            <i class="ti ti-mood-happy  text-xl"></i> <span>Icons</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link gap-3 py-2 px-3  rounded-md w-full flex items-center hover:text-blue-600 hover:bg-blue-500" href="@@webRoot/pages/sample-page.html"
           >
            <i class="ti ti-aperture  text-xl"></i> <span>Sample Page</span>
          </a>
        </li>        

      </ul>
    </nav>
  </div>
</div>

<!-- </aside> -->