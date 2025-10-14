<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'jwt.auth' => \App\Http\Middleware\JwtAuthMiddleware::class,
            'device.detection' => \App\Http\Middleware\DeviceDetectionMiddleware::class,
            'smart.redirect' => \App\Http\Middleware\SmartRedirectMiddleware::class,
            'unit.scope' => \App\Http\Middleware\UnitScopeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
