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

        // Add CORS middleware globally
        $middleware->append(\App\Http\Middleware\CorsMiddleware::class);

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
        // Handle CSRF Token Mismatch (419 error that causes "Page Expired")
        $exceptions->render(function (TokenMismatchException $e, $request) {
            // Log the CSRF token mismatch
            \Log::warning('CSRF Token Mismatch detected', [
                'path' => $request->getPathInfo(),
                'method' => $request->getMethod(),
                'user' => auth()->id() ? auth()->user()->email : 'not authenticated',
                'referer' => $request->headers->get('referer'),
            ]);

            // If it's an AJAX request, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sua sessão expirou. Por favor, recarregue a página.',
                    'redirect' => route('login'),
                ], 419);
            }

            // For Filament admin panel requests, redirect back with error message
            if (str_starts_with($request->getPathInfo(), '/admin')) {
                return redirect()->route('login')
                    ->with('error', 'Sua sessão expirou. Por favor, faça login novamente.');
            }

            // For other web requests, redirect to login
            return redirect()->route('login')
                ->with('error', 'Sua sessão expirou. Por favor, faça login novamente.');
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
