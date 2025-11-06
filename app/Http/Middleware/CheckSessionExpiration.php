<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('web')->check()) {
            $lastActivity = session('last_activity_time');
            $sessionLifetime = config('session.lifetime') * 60; // Convert to seconds

            // If last activity exists and session has expired
            if ($lastActivity && (time() - $lastActivity) > $sessionLifetime) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Sua sessão expirou. Por favor, faça login novamente.',
                        'expired' => true
                    ], 401);
                }

                return redirect()->route('filament.admin.auth.login')
                    ->with('status', 'Sua sessão expirou. Por favor, faça login novamente.');
            }

            // Update last activity time
            session(['last_activity_time' => time()]);
        }

        return $next($request);
    }
}
