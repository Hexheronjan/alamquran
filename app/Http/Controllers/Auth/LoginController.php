<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Carbon;

use App\Services\WhatsappService;
use App\Services\NotificationService;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('user.dashboard');
        }
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login'); // View yang digunakan bersama
    }

    public function showLoginAdminForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login-admin'); // View khusus untuk admin
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'no_wa' => 'required|string',
        ], [
            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $noWa = WhatsappService::formatPhoneNumber($request->no_wa);
        if (!$noWa) {
            return back()->withInput()->withErrors(['no_wa' => 'Format nomor WhatsApp tidak valid. Gunakan 08xxxxxxxxxx atau 628xxxxxxxxxx.']);
        }

        // Variasi format nomor agar user bisa login baik dengan awalan 08, 628, +628, dsb
        $rawInput = trim($request->no_wa);
        $cleanRaw = preg_replace('/[\s\-\.]/', '', $rawInput);
        $cleanRawWithoutPlus = ltrim($cleanRaw, '+');
        $format08 = preg_replace('/^628/', '08', $noWa);
        $double8 = preg_replace('/^628/', '6288', $noWa);

        $user = User::where(function ($q) use ($noWa, $cleanRaw, $cleanRawWithoutPlus, $format08, $double8) {
            $q->where('no_wa', $noWa)
              ->orWhere('no_wa', $format08)
              ->orWhere('no_wa', $cleanRaw)
              ->orWhere('no_wa', $cleanRawWithoutPlus)
              ->orWhere('no_wa', $double8);
        })->first();

        if (!$user) {
            return back()->withInput()->withErrors(['no_wa' => 'Nomor WhatsApp belum terdaftar. Hubungi admin pondok jika akun wali belum dibuat.']);
        }

        // Auto-sinkronkan format no_wa ke standar 628xxx jika sebelumnya tersimpan beda format
        // Hanya update jika nomor hasil normalisasi belum dipakai user lain
        if ($user->no_wa !== $noWa) {
            $alreadyUsed = User::where('no_wa', $noWa)->where('id', '!=', $user->id)->exists();
            if (!$alreadyUsed) {
                $user->no_wa = $noWa;
                $user->save();
            }
        }
        $now = Carbon::now();

        // Jika OTP sebelumnya masih aktif, pertahankan kode yang sama
        if ($user->isOtpActive()) {
            $otp = $user->otp_code;
        } else {
            $otp = rand(100000, 999999);
            $user->otp_code = $otp;
            $user->otp_created_at = $now;
            $user->otp_expired_at = $now->copy()->addMinutes(5);
        }

        $user->otp_delivery_status = 'menunggu';
        $user->otp_delivery_error = null;
        $user->save();

        // Kirim OTP ke WhatsApp
        if (!$this->sendOtpToWhatsapp($user, $otp)) {
            return back()->withInput()->withErrors([
                'no_wa' => 'OTP belum dapat dikirim: ' . ($user->fresh()->otp_delivery_error ?? 'Gateway WhatsApp bermasalah. Silakan coba lagi.'),
            ]);
        }

        session(['no_wa' => $user->no_wa]);
        return redirect()->route('otp.verify.form');
    }

    public function loginPassword(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required'    => 'Nomor WhatsApp atau Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $input = trim($request->input('login'));
        $password = $request->input('password');
        $remember = $request->filled('remember');

        // Normalisasi jika input berupa nomor telepon
        $normalizedPhone = WhatsappService::formatPhoneNumber($input);

        // Cari user berdasarkan no_wa (ternormalisasi), no_wa asli, atau email
        $user = User::where(function ($q) use ($input, $normalizedPhone) {
            if ($normalizedPhone) {
                $q->where('no_wa', $normalizedPhone);
            }
            $q->orWhere('no_wa', $input)
              ->orWhere('email', $input);
        })->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            return back()->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => 'Nomor WhatsApp/Username atau Password tidak sesuai.']);
        }

        // Tandai email sebagai verified otomatis jika belum (user dibuat admin)
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }

        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();

        // Update tracking login & aktivitas online wali
        $user->updateQuietly([
            'last_login_at'    => now(),
            'last_login_ip'    => $request->ip(),
            'last_activity_at' => now(),
            'login_count'      => ($user->login_count ?? 0) + 1,
        ]);

        if ($user->must_change_password) {
            return redirect()->route('user.password.change')
                ->with('warning', 'Akun Anda menggunakan password sementara. Silakan ubah password Anda terlebih dahulu.');
        }

        return redirect()->intended('/user/dashboard');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'no_wa' => 'required',
            'otp_code' => 'required',
        ]);

        $noWa = WhatsappService::formatPhoneNumber($request->no_wa) ?? $request->no_wa;
        $format08 = preg_replace('/^628/', '08', $noWa);
        $double8 = preg_replace('/^628/', '6288', $noWa);

        $user = User::where(function ($q) use ($noWa, $request, $format08, $double8) {
            $q->where('no_wa', $noWa)
              ->orWhere('no_wa', $request->no_wa)
              ->orWhere('no_wa', $format08)
              ->orWhere('no_wa', $double8);
        })
        ->where('otp_code', $request->otp_code)
        ->where('otp_expired_at', '>', Carbon::now())
        ->first();

        if (!$user) {
            return back()->withErrors(['otp_code' => 'Kode OTP salah atau sudah kedaluwarsa.']);
        }

        // Reset kode OTP sebelum login
        $user->otp_code            = null;
        $user->otp_created_at      = null;
        $user->otp_expired_at      = null;
        $user->otp_delivery_status = null;
        $user->otp_delivery_error  = null;

        // Tandai email sebagai verified otomatis saat login via OTP berhasil
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
        }

        $user->save();

        // Hancurkan session lama sepenuhnya — mencegah konflik cookie browser
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Login menggunakan guard 'web' dengan Persistent Session (Remember Me = true)
        // Wali hanya minta OTP sekali di awal. Selama tidak klik Logout, wali akan login otomatis tanpa OTP.
        Auth::guard('web')->login($user, true);

        // Update tracking login & aktivitas online wali
        $user->updateQuietly([
            'last_login_at'    => now(),
            'last_login_ip'    => $request->ip(),
            'last_activity_at' => now(),
            'login_count'      => ($user->login_count ?? 0) + 1,
        ]);

        if ($user->must_change_password) {
            return redirect()->route('user.password.change');
        }

        return redirect('/user/dashboard');
    }

    private function sendOtpToWhatsapp(User $user, $otp): bool
    {
        $message = "*Kode OTP Login*\n\n"
                . "Halo, berikut adalah kode OTP untuk login ke akun PSB Anda:\n\n"
                . "*$otp*\n\n"
                . "Kode ini hanya berlaku selama 5 menit.\n\n"
                . "Jangan bagikan kode ini kepada siapa pun.";

        try {
            $result = NotificationService::sendSensitive([
                'recipient_number' => $user->no_wa,
                'recipient_name' => $user->name,
                'user_id' => $user->id,
                'modul' => 'OTP Login',
                'message' => $message,
                'audit_body' => '[OTP login dikirim; isi tidak disimpan]',
                'reference_id' => 'OTP-LOGIN-' . $user->id . '-' . $user->otp_created_at?->timestamp,
                'sync' => true,
            ]);

            if ($result && in_array($result->status, ['pending', 'processing', 'sent'], true)) {
                $user->otp_delivery_status = $result->status === 'sent' ? 'berhasil' : 'menunggu';
                $user->otp_delivery_error = null;
                $sent = true;
            } else {
                $user->otp_delivery_status = 'gagal';
                $user->otp_delivery_error = $result?->error_message ?? 'Gagal mengirim pesan WhatsApp';
                $sent = false;
            }
            $user->save();
            return $sent;
        } catch (\Exception $e) {
            \Log::error('Gagal mengirim OTP via Onesender: ' . $e->getMessage());
            $user->otp_delivery_status = 'gagal';
            $user->otp_delivery_error = $e->getMessage();
            $user->save();
            return false;
        }
    }

    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $request->input('email');
        $password   = $request->input('password');
        $remember   = $request->filled('remember');

        // Cari admin berdasarkan email atau username
        $admin = \App\Models\Admin::where('email', $loginInput)
            ->orWhere('username', $loginInput)
            ->first();

        if (!$admin || !\Illuminate\Support\Facades\Hash::check($password, $admin->password)) {
            return back()->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Email/Username atau password salah.']);
        }

        // Cek status aktif
        if (!$admin->is_active) {
            \App\Services\AdminActivityLogger::log(
                'login_failed',
                'Percobaan login gagal: Akun admin sedang dinonaktifkan',
                'Admin',
                $admin->id,
                $admin
            );
            return back()->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Akun admin ini telah dinonaktifkan. Silakan hubungi Admin Utama.']);
        }

        // Login ke guard admin
        Auth::guard('admin')->login($admin, $remember);

        // Update riwayat login
        $admin->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Catat log aktivitas
        \App\Services\AdminActivityLogger::log(
            'login',
            'Admin berhasil login ke sistem',
            'Admin',
            $admin->id,
            $admin
        );

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        // Logout berdasarkan guard
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            \App\Services\AdminActivityLogger::log(
                'logout',
                'Admin keluar dari sistem (logout)',
                'Admin',
                $admin->id,
                $admin
            );
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('form-login-admin');
        } else {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login');
        }
    }
}
