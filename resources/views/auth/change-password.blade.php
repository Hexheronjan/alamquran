<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.admin.partial.head2')
    <title>Ubah Password Pertama Kali - Pesantren Tahfizh Alam Qur'an</title>
    <style>
        .bg-container {
            min-height: 100vh;
            min-height: 100dvh;
            width: 100%;
            background-image: url('{{ asset('assets/images/logos/ponpes.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
        .bg-overlay {
            position: absolute;
            inset: 0;
            background-color: rgba(15, 23, 42, 0.65);
        }
    </style>
</head>
<body class="bg-gray-100 antialiased font-sans">
    <div class="bg-container flex items-center justify-center min-h-screen px-4 py-12">
        <div class="bg-overlay"></div>

        <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 sm:p-8 border border-gray-100">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <i class="ti ti-shield-lock text-3xl"></i>
                </div>
                <h1 class="text-xl font-extrabold text-gray-800">Wajib Ganti Password</h1>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                    Demi keamanan akun Anda, silakan ubah password sementara Anda menjadi password baru pribadi sebelum mengakses sistem.
                </p>
            </div>

            @if(session('warning'))
                <div class="mb-4 p-3 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg text-xs text-amber-800 flex items-start gap-2">
                    <i class="ti ti-alert-triangle text-base mt-0.5 flex-shrink-0"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 rounded-r-lg text-xs text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('user.password.update_forced') }}" class="space-y-4">
                @csrf

                <!-- Akun Info -->
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                    <div class="text-gray-500 font-medium">Akun Masuk:</div>
                    <div class="font-bold text-gray-900 mt-0.5">{{ Auth::user()->name }} ({{ Auth::user()->no_wa }})</div>
                </div>

                <!-- Password Baru -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Password Baru</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required minlength="8"
                               placeholder="Minimal 8 karakter"
                               class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <button type="button" onclick="togglePass('password', 'eye1')" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                            <i id="eye1" class="ti ti-eye text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Konfirmasi Password Baru -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Ulangi Password Baru</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                               placeholder="Ulangi password baru"
                               class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <button type="button" onclick="togglePass('password_confirmation', 'eye2')" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                            <i id="eye2" class="ti ti-eye text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            style="background-color: #0f766e; color: #ffffff;"
                            class="w-full py-3 rounded-xl font-bold text-sm shadow-md hover:opacity-95 transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="ti ti-check text-base"></i> Simpan Password & Buka Dashboard
                    </button>
                </div>
            </form>

            <div class="text-center mt-6 pt-4 border-t border-gray-100">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs text-gray-500 hover:text-red-600 font-semibold cursor-pointer inline-flex items-center gap-1">
                        <i class="ti ti-logout"></i> Keluar dari Akun
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'ti ti-eye-off text-lg';
            } else {
                input.type = 'password';
                icon.className = 'ti ti-eye text-lg';
            }
        }
    </script>
</body>
</html>
