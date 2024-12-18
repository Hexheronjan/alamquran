<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); // View yang digunakan bersama
    }

    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // Cek di tabel user
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            return redirect()->intended('/user/dashboard'); // Redirect user
        }

        // Cek di tabel admin
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            return redirect()->intended('/admin/dashboard'); // Redirect admin
        }

        // Jika gagal
        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    public function logout(Request $request)
    {
        // Logout berdasarkan guard
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        } else {
            Auth::guard('web')->logout();
        }

        return redirect()->route('login');
    }
}
