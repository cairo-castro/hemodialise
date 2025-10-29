<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // Apply web middleware to API routes for session-based authentication
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
            'jwt.auth' => \App\Http\Middleware\JwtAuthMiddleware::class,
            'unit.scope' => \App\Http\Middleware\UnitScopeMiddleware::class,
        ]);

        // Use custom CSRF token verification middleware
        $middleware->validateCsrfTokens(except: [
            '/logout', // Permitir logout GET sem CSRF
            'admin/*', // Excluir rotas do admin Filament do CSRF (pode causar 405)
            'admin-login',
            'admin-login/*',
            'admin-bridge',
            'filament/*', // Excluir todas as rotas do Filament do CSRF
            'api/*', // Excluir APIs do CSRF para evitar conflitos
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
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
