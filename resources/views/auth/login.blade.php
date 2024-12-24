<!DOCTYPE html>
<html   lang="en" dir="ltr" >

<head>
	@include('layouts.admin.partial.head')
	<title>Halaman Login PPDB</title>
</head>

<body class="DEFAULT_THEME bg-white">
	<main>
				<!-- Main Content -->
                <div class="flex flex-col w-full  overflow-hidden relative min-h-screen radial-gradient items-center justify-center g-0 px-4">
                  
                    <div class="justify-center items-center w-full card lg:flex max-w-md ">
                        <div class=" w-full card-body">
                                <a href="../" class="py-4 block"><img src="../assets/images/logos/dark-logo.svg" alt="" class="mx-auto"/></a>
                                <p class="mb-4 text-gray-500 text-sm text-center">Selamat  Datang</p>
                            <!-- form -->
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <!-- username -->
                                <div class="mb-4">
                                    <label for="forEmail"
                                    class="block text-sm font-semibold mb-2 text-gray-600">Email</label>
                                <input type="email" id="forEmail" name="email"
                                    class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm focus:border-blue-600 focus:ring-0 " aria-describedby="hs-input-helper-text" required>
                                </div>
                                <!-- password -->
                                <div class="mb-6">
                                    <label for="forPassword"
                                    class="block text-sm font-semibold mb-2 text-gray-600">Password</label>
                                <input type="password" id="forPassword" name="password"
                                    class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm focus:border-blue-600 focus:ring-0 " aria-describedby="hs-input-helper-text" required>
                                </div>
                                <!-- checkbox -->
                                  <div class="flex justify-between">
                                    <div class="flex">
                                        <input type="checkbox" class="shrink-0 mt-0.5 border-gray-200 rounded-[4px] text-blue-600 focus:ring-blue-500 " id="hs-default-checkbox" checked>
                                        <label for="hs-default-checkbox" class="text-sm text-gray-600 ms-3">Ingat saya</label>
                                      </div>
                                        <a href="../" class="text-sm font-medium text-blue-600 hover:text-blue-700">Lupa Password ?</a>
                                  </div>
                                    <!-- button -->
                                      <div class="grid my-6">
                                        <button type="submit" class="btn py-[10px] text-base text-white font-medium hover:bg-blue-700">Sign In</button>
                                        {{-- <a href="../" class="btn py-[10px] text-base text-white font-medium hover:bg-blue-700">Sign In</a> --}}
                                      </div>
        
                                    <div class="flex justify-center gap-2 items-center">
                                        <p class="text-base font-medium text-gray-500">Belum punya akun?</p>
                                        <a href="{{ route('register') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Buat akun</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
				
			</div>
		<!--end of project-->
	</main>


	@include('layouts.admin.partial.scripts')
</body>

</html>