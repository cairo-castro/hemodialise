<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        // Se já estiver autenticado, redirecionar para a interface apropriada
        if (Auth::check()) {
            $isMobileDevice = $this->isMobile($request);
            $redirectUrl = $isMobileDevice ? '/mobile' : '/desktop';
            
            \Log::info('User already authenticated, redirecting', [
                'user' => Auth::user()->email,
                'device' => $isMobileDevice ? 'mobile' : 'desktop',
                'redirect' => $redirectUrl
            ]);
            
            return redirect($redirectUrl);
        }
        
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

            // Detectar dispositivo e redirecionar apropriadamente
            $isMobileDevice = $this->isMobile($request);
            
            // Redirecionar baseado no DISPOSITIVO, não no tipo de usuário
            $redirectUrl = $isMobileDevice ? '/mobile' : '/desktop';
            
            \Log::info('Login redirect', [
                'user' => $user->email,
                'device' => $isMobileDevice ? 'mobile' : 'desktop',
                'redirect' => $redirectUrl,
                'user_agent' => $request->header('User-Agent')
            ]);

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
        $userAgent = $request->header('User-Agent', '');

        // Debug: Log para verificar
        \Log::info('User Agent Detection', ['user_agent' => $userAgent]);

        // Detecção mais precisa de dispositivos móveis
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 
            'BlackBerry', 'Windows Phone', 'webOS', 'Opera Mini',
            'IEMobile', 'Mobile Safari'
        ];

        $isMobile = false;
        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                $isMobile = true;
                break;
            }
        }

        // Excluir tablets grandes que podem preferir versão desktop
        // mas manter iPad como mobile para melhor experiência
        
        \Log::info('Is Mobile Result', ['is_mobile' => $isMobile]);

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
