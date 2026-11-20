<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    public function showOtpForm()
    {
        if (\Illuminate\Support\Facades\Auth::guard('web')->check()) {
            return redirect()->route('user.dashboard');
        }
        $no_wa = session('no_wa');
        return view('auth.verify-otp', compact('no_wa'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'no_wa' => 'required',
            'otp_code' => 'required',
        ]);

        $noWa = \App\Services\WhatsappService::formatPhoneNumber($request->no_wa) ?? $request->no_wa;
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

        Auth::login($user, true);

        $user->otp_code = null;
        $user->otp_created_at = null;
        $user->otp_expired_at = null;
        $user->otp_delivery_status = null;
        $user->otp_delivery_error = null;
        $user->save();

        return redirect()->route('user.dashboard');
    }
}
