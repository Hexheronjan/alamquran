<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.admin.partial.head2')
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
            background-color: transparent;
            background-image: url('{{ asset('assets/images/logos/ponpes.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
        .login-bg-container::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,.22); }
        .login-card { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,.35); box-shadow: 0 4px 16px rgba(0,0,0,.15); }
        .login-card h1, .login-card label, .login-card span, .login-card a, .login-card p:not(.text-red-600) { color: #ffffff; text-shadow: 0 1px 3px rgba(0,0,0,.85), 0 0 10px rgba(0,0,0,.45); }
        .login-card .text-blue-600 { color: #fde68a; font-weight: 700; }
        .login-card h1, .login-card label { font-weight: 700; }
        .login-card p, .login-card span, .login-card .text-gray-500 { font-weight: 700; }
        .login-card .text-gray-500 { color: #ffffff; text-shadow: 0 1px 3px rgba(0,0,0,.85), 0 0 10px rgba(0,0,0,.45); }
        .login-card h1 { color: #0a1a44 !important; font-weight: 800; text-shadow: 0 0 8px rgba(255,255,255,.9), 0 0 3px rgba(255,255,255,.9); }
    </style>
</head>
<body class="m-0 font-sans antialiased">
    <main class="login-bg-container flex items-center justify-center px-4 py-8">
        <div class="login-card relative z-10 w-full max-w-md rounded-2xl p-8 sm:p-9">
            <div class="mb-7 text-center">
                <p class="text-sm font-bold uppercase tracking-[.15em] text-blue-600">Penerimaan Santri Baru (PSB)</p>
                <h1 class="mt-2 text-xl font-extrabold text-gray-800 sm:text-2xl">Pesantren Tahfizh Alam Qur'an</h1>
                <p class="mt-1 text-sm font-semibold uppercase tracking-[.35em] text-gray-800">Ponorogo</p>
                <div class="mt-5 flex items-center gap-3 text-xs font-semibold text-gray-700">
                    <span class="h-px flex-1 bg-gray-200"></span>
                    <span>Selamat Datang</span>
                    <span class="h-px flex-1 bg-gray-200"></span>
                </div>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-lg border-l-4 border-red-500 bg-red-50 p-3 text-xs text-red-700">{{ $errors->first() }}</div>
            @endif
            @if(session('status'))
                <div class="mb-4 rounded-lg border-l-4 border-emerald-500 bg-emerald-50 p-3 text-xs text-emerald-700">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('otp.send') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="no_wa" class="mb-2 block text-sm font-medium text-gray-700">Login dengan Nomor WhatsApp</label>
                    <input type="text" id="no_wa" name="no_wa" value="{{ old('no_wa') }}" inputmode="tel" autocomplete="tel" placeholder="08XXXXXXXXXX" required class="block w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="pt-3 text-center">
                    <button type="submit" style="background-color:#5b82f6;color:#ffffff" class="inline-flex min-w-40 items-center justify-center rounded-xl px-7 py-3 text-sm font-bold shadow-lg transition hover:opacity-90">Minta OTP</button>
                </div>
            </form>

            <div class="mt-7 border-t pt-5 text-center text-sm text-gray-500">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:underline">Buat akun</a>
            </div>
        </div>
    </main>
</body>
</html>
