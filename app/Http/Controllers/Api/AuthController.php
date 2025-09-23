<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        $user = auth('api')->user();
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'unit_id' => $user->unit_id,
                'unit' => $user->unit,
            ]
        ]);
    }

    public function me()
    {
        $user = auth('api')->user();
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'unit_id' => $user->unit_id,
                'unit' => $user->unit,
            ]
        ]);
    }

    public function refresh()
    {
        try {
            $newToken = auth('api')->refresh();
            $user = auth('api')->user();

            return response()->json([
                'token' => $newToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'unit_id' => $user->unit_id,
                    'unit' => $user->unit,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token inválido ou expirado'], 401);
        }
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
}
