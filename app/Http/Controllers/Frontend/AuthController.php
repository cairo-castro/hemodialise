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
        \Log::info('[AuthController::login] START', [
            'email' => $request->input('email'),
            'url' => $request->url(),
            'referer' => $request->header('referer'),
            'user_agent' => $request->header('User-Agent'),
            'expects_json' => $request->expectsJson(),
            'content_type' => $request->header('Content-Type')
        ]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        \Log::info('[AuthController::login] Attempting authentication', [
            'email' => $request->input('email'),
            'remember' => $request->boolean('remember')
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            \Log::info('[AuthController::login] Authentication SUCCESS', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'unit_id' => $user->unit_id,
                'session_id' => $request->session()->getId()
            ]);

            // Determine redirect: device detection (mobile vs desktop)
            // Note: Admin users can access /admin directly via URL bookmark
            $isMobile = \App\Services\DeviceDetector::isMobile($request);
            $redirectUrl = $isMobile ? '/mobile' : '/desktop';

            \Log::info('[AuthController::login] Determining redirect', [
                'user' => $user->email,
                'role' => $user->role,
                'device' => $isMobile ? 'mobile' : 'desktop',
                'redirect' => $redirectUrl,
                'expects_json' => $request->expectsJson()
            ]);

            // Return JSON for AJAX/Vue.js requests
            if ($request->expectsJson() || $request->header('Content-Type') === 'application/json') {
                \Log::info('[AuthController::login] Returning JSON response');
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
            \Log::info('[AuthController::login] Performing traditional redirect', [
                'redirect_url' => $redirectUrl
            ]);
            return redirect($redirectUrl);
        }

        // Login falhou
        \Log::warning('[AuthController::login] Authentication FAILED', [
            'email' => $request->input('email'),
            'expects_json' => $request->expectsJson()
        ]);

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
