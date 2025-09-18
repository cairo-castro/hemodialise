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

            // Lógica simples baseada no role
            if ($user->isFieldUser()) {
                // Técnicos SEMPRE mobile
                return redirect('/mobile');
            }

            // Admin e Manager SEMPRE Filament
            return redirect('/admin');
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
