<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;

class HandleExpiredSession
{
    /**
     * Handle an incoming request.
     *
     * Automatically logs out users when CSRF token expires for security
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (TokenMismatchException $e) {
            // Log the session expiration event for security audit
            \Log::info('[HandleExpiredSession] Session expired - forcing logout', [
                'user_id' => auth()->id() ?? 'guest',
                'email' => auth()->user()->email ?? 'N/A',
                'path' => $request->getPathInfo(),
                'method' => $request->getMethod(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'interface' => $this->detectInterface($request),
            ]);

            // Force logout to clear any stale session data
            if (auth()->check()) {
                auth()->logout();
            }

            // Invalidate the session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Handle API requests (JSON response expected)
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'session_expired' => true,
                    'message' => 'Sua sessão expirou por motivos de segurança. Por favor, faça login novamente.',
                    'redirect' => route('login'),
                ], 419);
            }

            // Handle web requests (HTML response expected)
            return redirect()
                ->route('login')
                ->with('session_expired', true)
                ->with('message', 'Sua sessão expirou por motivos de segurança. Por favor, faça login novamente.');
        }
    }

    /**
     * Detect which interface the request came from
     */
    private function detectInterface(Request $request): string
    {
        $path = $request->getPathInfo();

        if (str_starts_with($path, '/admin')) {
            return 'admin';
        }

        if (str_starts_with($path, '/desktop')) {
            return 'desktop';
        }

        if (str_starts_with($path, '/mobile') || str_contains($request->userAgent(), 'Ionic')) {
            return 'mobile';
        }

        if (str_starts_with($path, '/api/')) {
            return 'api';
        }

        return 'web';
    }
}
