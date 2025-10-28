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
            $isMobile = \App\Services\DeviceDetector::isMobile($request);
            $redirectUrl = $isMobile ? '/mobile' : '/desktop';

            \Log::info('User already authenticated, redirecting by device', [
                'user' => Auth::user()->email,
                'device' => $isMobile ? 'mobile' : 'desktop',
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

            // Determine redirect: device detection (mobile vs desktop)
            // Note: Admin users can access /admin directly via URL bookmark
            $isMobile = \App\Services\DeviceDetector::isMobile($request);
            $redirectUrl = $isMobile ? '/mobile' : '/desktop';

            \Log::info('User login successful', [
                'user' => $user->email,
                'role' => $user->role,
                'device' => $isMobile ? 'mobile' : 'desktop',
                'redirect' => $redirectUrl
            ]);

            // Return JSON for AJAX/Vue.js requests
            if ($request->expectsJson() || $request->header('Content-Type') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Login realizado com sucesso',
                    'redirect' => $redirectUrl,
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
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

}
