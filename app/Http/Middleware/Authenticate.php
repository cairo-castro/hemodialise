<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Don't redirect JSON requests
        if ($request->expectsJson()) {
            return null;
        }

        // For Filament admin panel requests, redirect to Filament's login
        if ($request->is('admin') || $request->is('admin/*')) {
            return route('filament.admin.auth.login');
        }

        // For all other requests, redirect to application login
        return route('login');
    }
}
