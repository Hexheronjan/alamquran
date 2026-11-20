<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminPermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('form-login-admin');
        }

        // Cek status aktif akun admin
        if (!$admin->is_active) {
            Auth::guard('admin')->logout();
            return redirect()->route('form-login-admin')
                ->withErrors(['email' => 'Akun admin Anda telah dinonaktifkan. Silakan hubungi Admin Utama.']);
        }

        // Super Admin memiliki akses penuh ke seluruh rute admin
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Jika rute tidak memerlukan permission khusus
        if (empty($permissions)) {
            return $next($request);
        }

        // Cek apakah admin memiliki setidaknya salah satu permission yang dibutuhkan
        foreach ($permissions as $permission) {
            if ($admin->canAccess($permission)) {
                return $next($request);
            }
        }

        // Jika tidak memiliki izin akses
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk tindakan ini.',
            ], 403);
        }

        return redirect()->route('admin.dashboard')
            ->with('error', 'Akses Ditolak: Anda tidak memiliki hak akses untuk halaman tersebut.');
    }
}