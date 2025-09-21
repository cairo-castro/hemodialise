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

        // Verificar se está logado
        if (!$user) {
            // Se não está logado, vai para nosso login centralizado
            return redirect('/login');
        }

        // Verificar se tem permissão para acessar o Filament
        if (!$user->canAccessAdmin()) {
            // Redireciona técnicos para mobile, outros para desktop
            if ($user->isTecnico()) {
                return redirect('/mobile');
            } else {
                return redirect('/desktop');
            }
        }

        return $next($request);
    }
}
