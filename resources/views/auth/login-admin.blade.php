<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    @include('layouts.admin.partial.head2')
    <title>Halaman Login Admin PSB</title>
    <link rel="preload" as="image" href="{{ asset('assets/images/logos/ponpes.png') }}" fetchpriority="high">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            min-height: 100dvh;
            background-color: #555b5f;
            background-image: url('{{ asset('assets/images/logos/ponpes.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .login-bg-container {
            min-height: 100vh;
            min-height: 100dvh;
            width: 100%;
            background-color: transparent;
            background-image: url('{{ asset('assets/images/logos/ponpes.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
        .login-overlay {
            position: absolute;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.18);
            pointer-events: none;
        }
        .login-card { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,.35); box-shadow: 0 4px 16px rgba(0,0,0,.15); }
        .login-card h1, .login-card label, .login-card span, .login-card a, .login-card p:not(.text-red-600) { color: #ffffff; text-shadow: 0 1px 3px rgba(0,0,0,.85), 0 0 10px rgba(0,0,0,.45); }
        .login-card .text-blue-600 { color: #fde68a; font-weight: 700; }
        .login-card h1 { color: #0a1a44 !important; font-weight: 800; text-shadow: 0 0 8px rgba(255,255,255,.9), 0 0 3px rgba(255,255,255,.9); }
    </style>
</head>

<body class="m-0 p-0 antialiased font-sans">
    <main>
        <div class="login-bg-container flex items-center justify-center w-full px-4 py-8 relative">
            <div class="login-overlay"></div>

            <div class="login-card relative z-10 w-full max-w-md rounded-2xl p-6 sm:p-8">
                <div class="w-full">

                    <!-- Header: Identitas PSB -->
                    <div class="text-center pt-3 pb-3">
                        <p class="text-sm font-bold tracking-[0.15em] text-blue-600 uppercase leading-relaxed">
                            Penerimaan Santri Baru (PSB)
                        </p>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-gray-800 leading-snug mt-1">
                            Pesantren Tahfizh Alam Qur'an
                        </h1>
                        <p class="text-sm font-semibold tracking-[0.3em] text-gray-900 uppercase mt-1">Ponorogo</p>
                    </div>

                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex-1 h-px bg-gray-200"></span>
                        <span class="text-xs text-gray-900 font-semibold whitespace-nowrap">Login Admin</span>
                        <span class="flex-1 h-px bg-gray-200"></span>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="{{ route('login-admin') }}">
                        @csrf

                        <!-- Email / Username -->
                        <div class="mb-4">
                            <label for="forEmail" class="block text-sm font-semibold mb-2 text-gray-700">Email atau Username</label>
                            <input type="text" id="forEmail" name="email" value="{{ old('email') }}" placeholder="Masukkan email atau username"
                                class="py-3 px-4 block w-full border border-gray-300 rounded-xl text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition @error('email') border-red-500 @enderror"
                                required autofocus>
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-5">
                            <label for="forPassword" class="block text-sm font-semibold mb-2 text-gray-700">Password</label>
                            <div class="relative">
                                <input type="password" id="forPassword" name="password"
                                    class="py-3 px-4 pr-12 block w-full border border-gray-300 rounded-xl text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition"
                                    required>
                                <button type="button" id="toggleAdminPassword" aria-label="Tampilkan password"
                                    class="absolute flex items-center text-gray-500 hover:text-blue-600"
                                    style="top: 50%; right: 0.75rem; transform: translateY(-50%);">
                                    <svg id="adminPasswordEyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z" />
                                        <circle cx="12" cy="12" r="2.5" />
                                    </svg>
                                    <svg id="adminPasswordEyeClosed" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.4A10.7 10.7 0 0 1 12 5.25c6.75 0 9.75 6.75 9.75 6.75a18.5 18.5 0 0 1-3.2 4.15M6.1 6.1A18.5 18.5 0 0 0 2.25 12S5.25 18.75 12 18.75c.72 0 1.4-.08 2.04-.23" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Ingat Saya -->
                        <div class="flex mb-2">
                            <input type="checkbox" class="shrink-0 mt-0.5 border-gray-200 rounded text-blue-600 focus:ring-blue-500" id="hs-default-checkbox" checked>
                            <label for="hs-default-checkbox" class="text-sm text-gray-600 ms-3">Ingat saya</label>
                        </div>

                        <!-- Button Login -->
                        <div class="flex justify-center mt-4">
                            <button type="submit"
                                class="flex items-center justify-center w-full bg-[#5d87ff] text-white text-base font-semibold py-2.5 px-4 rounded-xl hover:bg-[#C9A906] transition duration-200 shadow-md cursor-pointer">
                                Login
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <script>
            document.getElementById('toggleAdminPassword')?.addEventListener('click', function () {
                const passwordInput = document.getElementById('forPassword');
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                this.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
                document.getElementById('adminPasswordEyeOpen').classList.toggle('hidden', isHidden);
                document.getElementById('adminPasswordEyeClosed').classList.toggle('hidden', !isHidden);
            });
        </script>
    </main>

    @include('layouts.admin.partial.scripts')
</body>

</html>