<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MustChangePasswordMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('web')->check()) {
            // Bypass jika sesi sedang dibuka oleh Super Admin via Auto-Login (Impersonate)
            if (session()->has('impersonated_by_admin_id')) {
                return $next($request);
            }

            $user = Auth::guard('web')->user();

            // Perbarui aktivitas online secara berkala (setiap 2 menit sekali agar ringan)
            if (!$user->last_activity_at || $user->last_activity_at->diffInMinutes(now()) >= 2) {
                $user->updateQuietly(['last_activity_at' => now()]);
            }

            if ($user->must_change_password) {
                // Jangan cegah route ubah password dan logout
                if (!$request->is('user/change-password*') &&
                    !$request->routeIs('user.password.change') &&
                    !$request->routeIs('user.password.update_forced') &&
                    !$request->is('logout') &&
                    !$request->routeIs('logout')) {

                    return redirect()->route('user.password.change')
                        ->with('warning', 'Demi keamanan akun Anda, silakan ubah password sementara Anda terlebih dahulu sebelum mengakses dashboard.');
                }
            }
        }

        return $next($request);
    }
}
