<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            // Perbarui aktivitas online wali (dibatasi 1x per 60 detik agar tidak membebani database)
            if ($user && (!$user->last_activity_at || $user->last_activity_at->diffInSeconds(now()) >= 60)) {
                $user->updateQuietly([
                    'last_activity_at' => now(),
                ]);
            }

            return $next($request);
        }

        return redirect()->route('login');
    }
}
