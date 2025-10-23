<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('frontend.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Smart redirect simplificado: apenas /mobile ou /desktop
            $redirectUrl = '/desktop'; // fallback padrão
            
            if ($user->isTecnico()) {
                // Técnicos SEMPRE mobile
                $redirectUrl = '/mobile';
            } else if ($user->default_view === 'mobile') {
                // Usuários com preferência mobile
                $redirectUrl = '/mobile';
            } else {
                // Todos os outros vão para desktop
                $redirectUrl = '/desktop';
            }

            // Retornar JSON para Vue.js
            if ($request->expectsJson() || $request->header('Content-Type') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Login realizado com sucesso',
                    'redirect' => $redirectUrl,
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'default_view' => $user->default_view
                    ]
                ]);
            }

            // Fallback para redirect tradicional
            return redirect($redirectUrl);
        }

        // Login falhou
        if ($request->expectsJson() || $request->header('Content-Type') === 'application/json') {
            return response()->json([
                'success' => false,
                'message' => 'As credenciais fornecidas não conferem com nossos registros.',
                'errors' => [
                    'email' => ['As credenciais fornecidas não conferem com nossos registros.']
                ]
            ], 422);
        }

        throw ValidationException::withMessages([
            'email' => 'As credenciais fornecidas não conferem com nossos registros.',
        ]);
    }

    private function isMobile(Request $request)
    {
        $userAgent = $request->header('User-Agent');

        // Debug: Log para verificar
        \Log::info('User Agent: ' . $userAgent);

        $isMobile = preg_match('/(android|iphone|ipad|mobile|tablet)/i', $userAgent) &&
                   !preg_match('/(windows|mac|linux|desktop)/i', $userAgent);

        \Log::info('Is Mobile: ' . ($isMobile ? 'true' : 'false'));

        return $isMobile;
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
