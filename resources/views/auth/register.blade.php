 <!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    @include('layouts.admin.partial.head2')
    <title>Halaman Daftar Akun PSB</title>
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

        /* Overlay transparan tipis (~18%) agar card kontras namun foto gedung tetap natural & jelas */
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
        <!-- Full-screen Background Container -->
        <div class="login-bg-container flex items-center justify-center w-full px-4 py-8 relative">
            <!-- Overlay Transparan Tipis (18%) -->
            <div class="login-overlay"></div>

            <!-- Card Register di Tengah Layar -->
            <div class="login-card relative z-10 w-full max-w-md rounded-2xl p-6 sm:p-8 my-6">
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

                    <div class="flex items-center gap-3 mb-4">
                        <span class="flex-1 h-px bg-gray-200"></span>
                        <span class="text-xs text-gray-900 font-semibold whitespace-nowrap">Daftar</span>
                        <span class="flex-1 h-px bg-gray-200"></span>
                    </div>

                    <!-- form -->
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <!-- Nama -->
                        <div class="mb-4">
                            <label for="forName" class="block text-sm font-semibold mb-2 text-gray-700">Nama</label>
                            <input type="text" id="forName" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap"
                                class="py-3 px-4 block w-full border border-gray-300 rounded-xl text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>


                        <!-- Nomor WhatsApp -->
                        <div class="mb-4">
                            <label for="forWA" class="block text-sm font-semibold mb-1 text-gray-700">Nomor WhatsApp</label>
                            <p class="text-xs text-gray-400 mb-2">Login menggunakan nomor WhatsApp ini</p>
                            <input type="text" id="forWA" name="no_wa" value="{{ old('no_wa') }}" placeholder="08XXXXXXXXXX"
                                class="py-3 px-4 block w-full border border-gray-300 rounded-xl text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition @error('no_wa') border-red-500 @enderror">
                            @error('no_wa')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- button -->
                        <div class="flex justify-center">
                            <button type="submit"
                                class="flex items-center mt-4 justify-center w-full bg-[#5d87ff] text-white text-base font-semibold py-2.5 px-4 rounded-xl hover:bg-[#C9A906] transition duration-200 shadow-md cursor-pointer">
                                Daftar
                            </button>
                        </div>

                        <div class="flex justify-center gap-2 items-center mt-6 pt-4 border-t border-gray-100">
                            <p class="text-sm font-medium text-gray-500">Sudah punya akun?</p>
                            <a href="{{ route('login') }}" class="text-sm font-bold text-blue-600 hover:text-blue-700">Masuk</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    @include('layouts.admin.partial.scripts')
</body>

</html>

