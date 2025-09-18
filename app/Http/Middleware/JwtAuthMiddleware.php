<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Para requisições AJAX/API, verificar token no cabeçalho
        if ($request->expectsJson() || $request->is('api/*')) {
            try {
                $user = JWTAuth::parseToken()->authenticate();
                if (!$user) {
                    return response()->json(['error' => 'Unauthenticated'], 401);
                }
                auth()->setUser($user);
                return $next($request);
            } catch (JWTException $e) {
                return response()->json(['error' => 'Token inválido'], 401);
            }
        }

        // Para requisições web, verificar token no cookie ou cabeçalho
        $token = $request->header('Authorization') ?: $request->cookie('jwt_token');

        if (!$token) {
            // Se não há token, redirecionar para login
            return redirect('/login');
        }

        try {
            // Limpar prefixo Bearer se existir
            $token = str_replace('Bearer ', '', $token);
            JWTAuth::setToken($token);
            $user = JWTAuth::authenticate();

            if (!$user) {
                return redirect('/login');
            }

            auth()->setUser($user);
        } catch (JWTException $e) {
            // Log do erro para debug
            \Log::warning('JWT Auth failed: ' . $e->getMessage(), [
                'token_present' => !empty($token),
                'token_length' => $token ? strlen($token) : 0,
                'url' => $request->url()
            ]);
            return redirect('/login');
        }

        return $next($request);
    }
}
