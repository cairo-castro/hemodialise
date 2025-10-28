<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (Traefik, load balancers, etc.)
        $middleware->trustProxies(at: '*');

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
