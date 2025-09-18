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
            \JWTAuth::setToken($token);
            $user = \JWTAuth::authenticate();

            if (!$user || $user->isFieldUser()) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }

            // Fazer login via sessão para o Filament
            auth()->login($user);

            return response()->json(['success' => true, 'redirect' => '/admin']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        }
    }
}