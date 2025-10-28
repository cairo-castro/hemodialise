<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilamentAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('[FilamentAccessMiddleware] START', [
            'url' => $request->url(),
            'path' => $request->path(),
            'method' => $request->method(),
            'route_name' => $request->route() ? $request->route()->getName() : null,
            'is_authenticated' => auth()->check(),
            'session_id' => $request->session()->getId()
        ]);

        $user = $request->user();

        // This middleware only runs AFTER authentication by Filament
        // So $user should always be set at this point

        if (!$user) {
            // This shouldn't happen, but just in case
            \Log::error('[FilamentAccessMiddleware] NO USER FOUND - This should not happen in authMiddleware!', [
                'url' => $request->url(),
                'auth_check' => auth()->check(),
                'session_id' => $request->session()->getId(),
                'all_guards' => array_keys(config('auth.guards')),
                'default_guard' => config('auth.defaults.guard')
            ]);
            return redirect()->route('filament.admin.auth.login');
        }

        \Log::info('[FilamentAccessMiddleware] User found', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'unit_id' => $user->unit_id
        ]);

        // Verificar se tem permissão para acessar o Filament Admin
        // Apenas usuários GLOBAIS: super-admin, gestor-global ou coordenador global
        $canAccess = $user->canAccessAdmin();

        \Log::info('[FilamentAccessMiddleware] Checking canAccessAdmin()', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'can_access' => $canAccess
        ]);

        if (!$canAccess) {
            \Log::warning('[FilamentAccessMiddleware] ACCESS DENIED - Redirecting user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'unit_id' => $user->unit_id,
                'reason' => 'canAccessAdmin() returned false'
            ]);

            // Redirecionar baseado no dispositivo do usuário
            $userAgent = $request->header('User-Agent', '');
            $isMobile = stripos($userAgent, 'Mobile') !== false ||
                       stripos($userAgent, 'Android') !== false ||
                       stripos($userAgent, 'iPhone') !== false;

            $redirectUrl = $isMobile ? '/mobile' : '/desktop';

            \Log::info('[FilamentAccessMiddleware] Redirecting to', [
                'redirect_url' => $redirectUrl,
                'is_mobile' => $isMobile
            ]);

            return redirect($redirectUrl)->with('error', 'Acesso negado. Apenas usuários globais podem acessar o painel administrativo.');
        }

        \Log::info('[FilamentAccessMiddleware] ACCESS GRANTED - Proceeding to next middleware', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role
        ]);

        $response = $next($request);

        \Log::info('[FilamentAccessMiddleware] Response ready', [
            'status_code' => $response->getStatusCode(),
            'is_redirect' => $response->isRedirect(),
            'redirect_url' => $response->isRedirect() ? $response->headers->get('Location') : null
        ]);

        return $response;
    }
}
