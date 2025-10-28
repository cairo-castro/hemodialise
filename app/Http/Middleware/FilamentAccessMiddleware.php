<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilamentAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // This middleware only runs AFTER authentication by Filament
        // So $user should always be set at this point

        if (!$user) {
            // This shouldn't happen, but just in case
            \Log::error('FilamentAccessMiddleware: No authenticated user found');
            return redirect()->route('filament.admin.auth.login');
        }

        // Verificar se tem permissão para acessar o Filament Admin
        // Apenas usuários GLOBAIS: super-admin, gestor-global ou coordenador global
        if (!$user->canAccessAdmin()) {
            \Log::warning('Tentativa de acesso ao admin negada', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'unit_id' => $user->unit_id
            ]);

            // Redirecionar baseado no dispositivo do usuário
            $userAgent = $request->header('User-Agent', '');
            $isMobile = stripos($userAgent, 'Mobile') !== false ||
                       stripos($userAgent, 'Android') !== false ||
                       stripos($userAgent, 'iPhone') !== false;

            $redirectUrl = $isMobile ? '/mobile' : '/desktop';

            return redirect($redirectUrl)->with('error', 'Acesso negado. Apenas usuários globais podem acessar o painel administrativo.');
        }

        \Log::info('Acesso ao admin concedido', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role
        ]);

        return $next($request);
    }
}
