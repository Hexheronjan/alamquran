<!DOCTYPE html>
<html   lang="en" dir="ltr" >

<head>
    @include('layouts.admin.partial.head')
	<title>Modernize TailwindCSS HTML Admin Template</title>
</head>

<body class="DEFAULT_THEME bg-white">
	<main>
				<!-- Main Content -->
                <div class="flex flex-col w-full  overflow-hidden relative min-h-screen radial-gradient items-center justify-center g-0 px-4">
                  
                    <div class="justify-center items-center w-full card lg:flex max-w-md ">
                        <div class=" w-full card-body">
                                <a href="../" class="py-4 block"><img src="../assets/images/logos/dark-logo.svg" alt="" class="mx-auto"/></a>
                                <p class="mb-4 text-gray-500 text-sm text-center">Buat akun sekarang!</p>
                            <!-- form -->
                            <form method="POST" action="{{ route('register') }}">
                                @csrf
                                <!-- username -->
                                <div class="mb-4">
                                    <label for="forName"
                                    class="block text-sm font-semibold mb-2 text-gray-600">Name</label>
                                <input type="text" id="forName" name="name"
                                    class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm focus:border-blue-600 focus:ring-0 " aria-describedby="hs-input-helper-text">
                                </div>
                                <!-- Email -->
                                <div class="mb-4">
                                    <label for="forEmail"
                                    class="block text-sm font-semibold mb-2 text-gray-600">Alamat Email</label>
                                <input type="email" id="forEmail" name="email"
                                    class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm focus:border-blue-600 focus:ring-0 " aria-describedby="hs-input-helper-text">
                                </div>
                                <!-- password -->
                                <div class="mb-4">
                                    <label for="forPassword"
                                    class="block text-sm font-semibold mb-2 text-gray-600">Password</label>
                                <input type="password" id="forPassword" name="password"
                                    class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm focus:border-blue-600 focus:ring-0 " aria-describedby="hs-input-helper-text">
                                </div>

                                <div class="mb-4">
                                    <label for="forPasswordConfirmation"
                                    class="block text-sm font-semibold mb-2 text-gray-600">Konfirmasi Password</label>
                                <input type="password" id="forPasswordConfirmation" name="password_confirmation"
                                    class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm focus:border-blue-600 focus:ring-0 " aria-describedby="hs-input-helper-text">
                                </div>
                        
                                    <!-- button -->
                                      <div class="grid my-6">
                                        <button type="submit" class="btn py-[10px] text-base text-white font-medium hover:bg-blue-700">Sign Up</button>
                                        {{-- <a href="../" class="btn py-[10px] text-base text-white font-medium hover:bg-blue-700">Sign Up</a> --}}
                                      </div>
        
                                    <div class="flex justify-center gap-2 items-center">
                                        <p class="text-base font-medium text-gray-500">Sudah punya akun?</p>
                                        <a href="{{ route('login') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Masuk</a>
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