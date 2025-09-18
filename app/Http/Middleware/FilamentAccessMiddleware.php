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

        // Apenas admin e manager podem acessar o Filament
        if ($user->isFieldUser()) {
            // Redireciona field_users para mobile
            return redirect('/mobile');
        }

        return $next($request);
    }
}
