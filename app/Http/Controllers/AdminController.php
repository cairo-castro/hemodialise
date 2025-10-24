<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function bridge(Request $request)
    {
        // Pegar token do localStorage via JavaScript
        return view('admin.bridge');
    }

    public function login(Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            return response()->json(['error' => 'Token não encontrado'], 400);
        }

        try {
            // Validar formato do token
            if (!$token || !is_string($token) || strlen($token) < 10) {
                return response()->json(['error' => 'Formato de token inválido'], 400);
            }

            \JWTAuth::setToken($token);
            $user = \JWTAuth::authenticate();

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 401);
            }

            \Log::info('Admin bridge attempt', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'can_access_admin' => $user->canAccessAdmin()
            ]);

            // Verificar se tem permissão para acessar admin
            if (!$user->canAccessAdmin()) {
                return response()->json([
                    'error' => 'Acesso negado - apenas usuários globais (Admin, Gestor Global, Coordenador Global) podem acessar',
                    'user_role' => $user->role,
                    'unit_id' => $user->unit_id,
                    'required' => 'Usuário global (sem unidade vinculada ou role super-admin/gestor-global)'
                ], 403);
            }

            // Fazer login via sessão para o Filament
            auth()->login($user, true); // remember = true

            \Log::info('Admin bridge success', [
                'user_id' => $user->id,
                'session_id' => session()->getId()
            ]);

            return response()->json(['success' => true, 'redirect' => '/admin']);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            \Log::warning('Admin bridge: Token expired', ['token' => substr($token, 0, 20) . '...']);
            return response()->json(['error' => 'Token expirado'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            \Log::warning('Admin bridge: Token invalid', ['token' => substr($token, 0, 20) . '...']);
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            \Log::error('Admin bridge: JWT Exception', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Falha na autenticação JWT'], 401);
        } catch (\Exception $e) {
            \Log::error('Admin bridge: General error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }
}