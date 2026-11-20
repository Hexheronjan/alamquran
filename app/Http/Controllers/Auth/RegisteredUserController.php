<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

use App\Services\NotificationService;
use App\Services\WhatsappService;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Normalisasi nomor WA sebelum validasi & simpan
        // Agar "08xxx" dan "628xxx" dianggap sama oleh sistem
        if ($request->no_wa) {
            $normalized = WhatsappService::formatPhoneNumber($request->no_wa);
            $request->merge(['no_wa' => $normalized ?? $request->no_wa]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'no_wa' => ['required', 'string', 'max:255', 'unique:'.User::class],
        ], [
            'no_wa.unique' => 'Akun sudah terdaftar. Silakan masuk, tidak perlu buat akun kembali.',
        ]);

        // Email & password bersifat opsional (diisi dari menu Profil).
        // Kolom database masih wajib terisi, jadi dibuat otomatis:
        // email placeholder unik berbasis no WA, password acak (login memakai OTP WhatsApp).
        $user = User::create([
            'name' => $request->name,
            'email' => $request->no_wa . User::PLACEHOLDER_EMAIL_DOMAIN,
            'no_wa' => $request->no_wa, // sudah ternormalisasi ke 628xxx di atas
            'password' => Hash::make(\Illuminate\Support\Str::random(32)),
        ]);

        // Generate OTP
        $now = Carbon::now();
        $otp = rand(100000, 999999);
        $user->otp_code = $otp;
        $user->otp_created_at = $now;
        $user->otp_expired_at = $now->copy()->addMinutes(5);
        $user->otp_delivery_status = 'menunggu';
        $user->otp_delivery_error = null;
        $user->save();

        // Kirim OTP via WhatsApp
        $this->sendOtpToWhatsapp($user, $otp);

        session(['no_wa' => $user->no_wa]);
        return redirect()->route('otp.verify.form');
    }

    private function sendOtpToWhatsapp(User $user, $otp)
    {
        $message = "*Kode OTP Registrasi*\n\n"
            . "Halo, berikut adalah kode OTP untuk verifikasi akun Anda:\n\n"
            . "*$otp*\n\n"
            . "Kode ini hanya berlaku selama 5 menit.\n\n"
            . "Jangan bagikan kode ini kepada siapa pun.";

        try {
            $result = NotificationService::sendSensitive([
                'recipient_number' => $user->no_wa,
                'recipient_name' => $user->name,
                'user_id' => $user->id,
                'modul' => 'OTP Registrasi',
                'message' => $message,
                'audit_body' => '[OTP registrasi dikirim; isi tidak disimpan]',
                'reference_id' => 'OTP-REGISTRASI-' . $user->id . '-' . $user->otp_created_at?->timestamp,
                'sync' => true,
            ]);

            if ($result && in_array($result->status, ['pending', 'processing', 'sent'], true)) {
                $user->otp_delivery_status = $result->status === 'sent' ? 'berhasil' : 'menunggu';
                $user->otp_delivery_error = null;
            } else {
                $user->otp_delivery_status = 'gagal';
                $user->otp_delivery_error = $result?->error_message ?? 'Gagal mengirim pesan WhatsApp';
            }
            $user->save();
        } catch (\Exception $e) {
            \Log::error('Gagal kirim OTP via WhatsappService: ' . $e->getMessage());
            $user->otp_delivery_status = 'gagal';
            $user->otp_delivery_error = $e->getMessage();
            $user->save();
        }
    }
}
