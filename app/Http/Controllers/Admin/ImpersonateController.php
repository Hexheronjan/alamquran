<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    /**
     * Auto-login sebagai akun wali santri tanpa pengiriman OTP.
     * Khusus Super Admin.
     */
    public function impersonate(Request $request, User $user)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403, 'Fitur auto-login akun wali ini hanya dapat digunakan oleh Super Admin.');
        }

        // Simpan data admin penanggung jawab di session
        session()->put('impersonated_by_admin_id', $admin->id);
        session()->put('impersonated_by_admin_name', $admin->name);
        session()->put('impersonate_source_url', url()->previous() ?: route('admin.pengguna.index'));

        // Login ke guard web sebagai wali tanpa OTP
        Auth::guard('web')->login($user);

        // Catat aktivitas di log admin
        AdminActivityLogger::log(
            'impersonate_wali',
            "Super Admin {$admin->name} melakukan auto-login ke akun wali '{$user->name}' (No. WA: {$user->no_wa}) tanpa pengiriman OTP untuk penanganan komplain",
            'User',
            $user->id
        );

        return redirect()->route('user.dashboard')
            ->with('success', "Mode Auto-Login Aktif: Anda berhasil masuk ke portal akun wali ananda {$user->name} tanpa pengiriman OTP.");
    }

    /**
     * Keluar dari mode impersonate dan kembali ke dashboard Super Admin.
     */
    public function leave(Request $request)
    {
        $adminName = session()->get('impersonated_by_admin_name', 'Super Admin');
        $sourceUrl = session()->get('impersonate_source_url');

        // Logout dari guard web
        Auth::guard('web')->logout();

        // Hapus session impersonasi
        session()->forget(['impersonated_by_admin_id', 'impersonated_by_admin_name', 'impersonate_source_url']);

        $target = ($sourceUrl && str_contains($sourceUrl, '/admin')) ? $sourceUrl : route('admin.pengguna.index');

        return redirect($target)->with('info', "Anda telah kembali ke panel Super Admin ({$adminName}).");
    }
}
