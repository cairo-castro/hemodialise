<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Console\Scheduling\Schedule;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withSchedule(function (Schedule $schedule) {
        // Clean expired sessions every hour
        $schedule->command('sessions:clean')->hourly();
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // API routes with web middleware for Sanctum SPA authentication
            Route::middleware('web')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (Traefik, load balancers, etc.)
        $middleware->trustProxies(at: '*');

        // Note: CORS middleware removed for Sanctum SPA authentication
        // Sanctum SPAs should be on the same domain and don't need CORS
        // If you need CORS, configure it properly with specific origins when credentials are used

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'unit.scope' => \App\Http\Middleware\UnitScopeMiddleware::class,
        ]);

        // Use custom CSRF token verification middleware
        // Note: Sanctum SPA authentication REQUIRES CSRF protection on API routes
        $middleware->validateCsrfTokens(except: [
            '/logout', // Permitir logout GET sem CSRF
            'admin-bridge', // Bridge para conversão JWT->Session
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle CSRF Token Mismatch (419 error) with automatic logout for security
        $exceptions->render(function (TokenMismatchException $e, $request) {
            // Log the session expiration event for security audit
            \Log::info('[Session Expired] CSRF token mismatch - forcing logout', [
                'user_id' => auth()->id() ?? 'guest',
                'email' => auth()->user()->email ?? 'N/A',
                'path' => $request->getPathInfo(),
                'method' => $request->getMethod(),
                'ip' => $request->ip(),
                'referer' => $request->headers->get('referer'),
            ]);

            // Force logout to clear any stale session data for security
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
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            // Handle 405 Method Not Allowed specifically for admin routes
            if (str_starts_with($request->getPathInfo(), '/admin')) {
                // Log the specific issue for debugging
                \Log::warning('405 Method Not Allowed for admin route', [
                    'path' => $request->getPathInfo(),
                    'method' => $request->getMethod(),
                    'user' => auth()->id() ? auth()->user()->email : 'not authenticated',
                ]);

                // If user is authenticated and trying to access admin, redirect to admin-bridge
                if (auth()->check() && auth()->user()->canAccessAdmin()) {
                    return redirect('/admin-bridge')->with('error', 'Ocorreu um erro ao acessar o painel administrativo. Redirecionando...');
                }

                // Otherwise, redirect to login
                return redirect('/login')->with('error', 'Acesso não autorizado. Por favor, faça login.');
            }
        });
    })->create();
