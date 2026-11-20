<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    @include('layouts.admin.partial.head2')
    <title>Halaman Verifikasi OTP PSB</title>
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
        .login-card h1, .login-card label { font-weight: 700; }
        .login-card p, .login-card span, .login-card .text-gray-500 { font-weight: 700; }
        .login-card .text-gray-500 { color: #ffffff; text-shadow: 0 1px 3px rgba(0,0,0,.85), 0 0 10px rgba(0,0,0,.45); }
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

                    <div class="flex items-center gap-3 mb-4">
                        <span class="flex-1 h-px bg-gray-200"></span>
                        <span class="text-xs text-gray-900 font-semibold whitespace-nowrap">Verifikasi OTP</span>
                        <span class="flex-1 h-px bg-gray-200"></span>
                    </div>

                    <p class="mb-2 text-gray-500 text-sm text-center">Masukkan OTP yang dikirim ke WhatsApp <span class="text-blue-600 font-semibold">Anda</span></p>

                    @if($errors->any())
                        <div class="mb-3 rounded-lg border-l-4 border-red-500 bg-red-50 p-3 text-xs text-red-700">{{ $errors->first() }}</div>
                    @endif

                    <form action="{{ route('otp.verify') }}" method="POST" class="max-w-lg mx-auto space-y-4 text-center" onsubmit="combineOtp()">
                        @csrf
                        <input type="hidden" name="no_wa" value="{{ $no_wa }}">
                        <input type="hidden" name="otp_code" id="combined-otp" required />

                        <div class="flex justify-center py-3 space-x-2">
                            @for ($i = 1; $i <= 6; $i++)
                                <div>
                                    <label for="otp-{{ $i }}" class="sr-only">OTP {{ $i }}</label>
                                    <input type="text" maxlength="1" id="otp-{{ $i }}"
                                        class="w-12 h-12 text-xl font-bold text-center text-gray-900
                                        border-b-2 border-[#5d87ff] border-t-0 border-l-0 border-r-0
                                        focus:border-[#C9A906] focus:ring-0 appearance-none outline-none transition duration-200"
                                        required oninput="toUpperAndNext(this, {{ $i }})" onkeydown="backOnDelete(event, {{ $i }})" />
                                </div>
                            @endfor
                        </div>

                        <div class="flex justify-center">
                            <button type="submit"
                                class="flex items-center mt-4 justify-center w-full bg-[#5d87ff] text-white text-base font-semibold py-2.5 px-4 rounded-xl hover:bg-[#C9A906] transition duration-200 shadow-md cursor-pointer">
                                Verifikasi
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('otp.send') }}" class="mt-5 text-center">
                        @csrf
                        <input type="hidden" name="no_wa" value="{{ $no_wa }}">
                        <button type="submit" class="text-xs font-bold text-teal-700 hover:underline">OTP belum diterima? Kirim ulang</button>
                    </form>

                </div>
            </div>
        </div>

        <script>
            function toUpperAndNext(el, index) {
                el.value = el.value.toUpperCase();
                if (el.value.length === 1 && index < 6) {
                    document.getElementById('otp-' + (index + 1)).focus();
                }
                combineOtp();
            }

            function backOnDelete(e, index) {
                if (e.key === "Backspace" && e.target.value === "" && index > 1) {
                    document.getElementById('otp-' + (index - 1)).focus();
                }
            }

            function combineOtp() {
                let otp = '';
                for (let i = 1; i <= 6; i++) {
                    otp += document.getElementById(`otp-${i}`).value;
                }
                document.getElementById('combined-otp').value = otp;
            }
        </script>
    </main>

    @include('layouts.admin.partial.scripts')
</body>

</html>
