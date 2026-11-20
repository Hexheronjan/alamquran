<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('user.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        // Email opsional: bila dikosongkan, pakai email placeholder (kolom DB wajib terisi)
        $user->email = $validated['email'] ?: ($user->no_wa . \App\Models\User::PLACEHOLDER_EMAIL_DOMAIN);

        if ($validated['password']) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('user.profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Tampilkan halaman wajib ubah password untuk wali pertama kali login.
     */
    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    /**
     * Simpan password baru dari form wajib ganti password.
     */
    public function updateForcedPassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required'  => 'Password baru wajib diisi.',
            'password.min'       => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('user.dashboard')
            ->with('success', 'Alhamdulillah! Password baru berhasil disimpan. Selamat datang di Dashboard Wali Santri.');
    }
}

