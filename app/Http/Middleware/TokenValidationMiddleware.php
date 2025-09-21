<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class TokenValidationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Tentar obter o usuário do token
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('Token válido mas usuário não encontrado');
            }

        } catch (TokenExpiredException $e) {
            return $this->unauthorizedResponse('Token expirado');
        } catch (TokenInvalidException $e) {
            return $this->unauthorizedResponse('Token inválido');
        } catch (JWTException $e) {
            return $this->unauthorizedResponse('Token não fornecido');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Erro de autenticação');
        }

        return $next($request);
    }

    private function unauthorizedResponse($message)
    {
        return response()->json([
            'error' => $message,
            'code' => 401,
            'action' => 'redirect_to_login'
        ], 401);
    }
}