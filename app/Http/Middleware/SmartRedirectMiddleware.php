<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class SmartRedirectMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Não processar se for uma rota de API, assets, ou rotas específicas
        if ($this->shouldSkipRedirection($request)) {
            return $next($request);
        }

        // Obter informações do dispositivo (deve ter passado pelo DeviceDetectionMiddleware)
        $deviceInfo = $request->get('device_info', []);

        // Obter usuário autenticado (JWT ou Session)
        $user = $this->getAuthenticatedUser($request);

        // Determinar para onde redirecionar
        $redirectUrl = $this->determineRedirectUrl($request, $deviceInfo, $user);

        // Se deve redirecionar e não está na URL correta
        if ($redirectUrl && $redirectUrl !== $request->url()) {
            // Preservar query parameters importantes
            $queryParams = $this->preserveQueryParams($request);

            if (!empty($queryParams)) {
                $redirectUrl .= '?' . http_build_query($queryParams);
            }

            return redirect($redirectUrl);
        }

        return $next($request);
    }

    /**
     * Verificar se deve pular o redirecionamento
     */
    private function shouldSkipRedirection(Request $request): bool
    {
        $path = $request->path();

        // Pular para rotas específicas
        $skipPaths = [
            'api/',
            'admin/',
            'login',
            'logout',
            'admin-bridge',
            'desktop/', // Permitir acesso direto às interfaces desktop
            'mobile/',  // Permitir acesso direto às interfaces mobile
            '_debugbar',
            'livewire',
            'assets/',
            'css/',
            'js/',
            'images/',
            'favicon.ico',
            'sw.js',
            'manifest.json',
            'ionic-build/'
        ];

        foreach ($skipPaths as $skipPath) {
            if (str_starts_with($path, $skipPath)) {
                return true;
            }
        }

        // Pular se veio de um redirecionamento para evitar loops
        if ($request->query('smart_redirect') === 'false') {
            return true;
        }

        // Pular se é uma requisição AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return true;
        }

        return false;
    }

    /**
     * Obter usuário autenticado (JWT ou Session)
     */
    private function getAuthenticatedUser(Request $request)
    {
        // Tentar JWT primeiro
        try {
            $token = $request->bearerToken()
                  ?? $request->cookie('jwt_token')
                  ?? $request->header('Authorization');

            if ($token) {
                $token = str_replace('Bearer ', '', $token);
                $user = JWTAuth::setToken($token)->authenticate();
                if ($user) {
                    return $user;
                }
            }
        } catch (\Exception $e) {
            // Falha no JWT, tentar session
        }

        // Tentar session Laravel
        if (Auth::check()) {
            return Auth::user();
        }

        return null;
    }

    /**
     * Determinar URL de redirecionamento baseado em device e usuário
     */
    private function determineRedirectUrl(Request $request, array $deviceInfo, $user): ?string
    {
        $currentPath = $request->path();

        // Se está na raiz '/', fazer redirecionamento inteligente
        if ($currentPath === '/' || $currentPath === '') {
            return $this->getSmartHomeUrl($deviceInfo, $user);
        }

        // Se não está autenticado, ir para login
        if (!$user) {
            // Se não está na página de login, redirecionar
            if ($currentPath !== 'login') {
                return url('/login');
            }
            return null;
        }

        // Verificar se o usuário está na interface adequada
        return $this->checkCurrentInterface($request, $deviceInfo, $user);
    }

    /**
     * Obter URL da home inteligente baseada em device e usuário
     */
    private function getSmartHomeUrl(array $deviceInfo, $user): string
    {
        // Se não está autenticado, ir para login
        if (!$user) {
            return url('/login');
        }

        // Obter interface recomendada baseada no tamanho da tela
        $recommendedInterface = $deviceInfo['recommended_interface'] ?? 'desktop';

        // Baseado no role do usuário
        switch ($user->role) {
            case 'field_user':
            case 'tecnico':
                // Técnicos sempre vão para mobile
                return url('/mobile');

            case 'admin':
                // Admins sempre vão para Filament
                return url('/admin');

            case 'gestor':
            case 'coordenador':
            case 'supervisor':
                // Gestores vão baseado no tamanho da tela
                // Mobile: tela <= 768px
                // Desktop: tela > 768px
                if ($recommendedInterface === 'mobile') {
                    return url('/mobile');
                } else {
                    return url('/desktop');
                }

            default:
                // Fallback baseado na interface recomendada (tamanho da tela)
                if ($recommendedInterface === 'mobile') {
                    return url('/mobile');
                } else {
                    return url('/desktop');
                }
        }
    }

    /**
     * Verificar se usuário está na interface adequada para seu role/device
     */
    private function checkCurrentInterface(Request $request, array $deviceInfo, $user): ?string
    {
        $currentPath = $request->path();
        $recommendedInterface = $deviceInfo['recommended_interface'] ?? 'desktop';
        $screenWidth = $deviceInfo['screen_width'] ?? 0;

        // Usuários field_user/tecnico devem estar no mobile
        if (in_array($user->role, ['field_user', 'tecnico'])) {
            if (!str_starts_with($currentPath, 'mobile')) {
                return url('/mobile');
            }
            return null;
        }

        // Admins podem estar em qualquer lugar (flexibilidade total)
        if ($user->role === 'admin') {
            return null;
        }

        // Para gestores, verificar compatibilidade tela/interface
        if (in_array($user->role, ['gestor', 'coordenador', 'supervisor'])) {
            // Verificar preferência do usuário (se existir)
            $userPreference = $user->interface_preference ?? 'auto';

            // Se preferência é automática, usar detecção de tela
            if ($userPreference === 'auto') {
                // Se está no mobile mas tela é desktop (> 768px), redirecionar para desktop
                if (str_starts_with($currentPath, 'mobile') && $recommendedInterface === 'desktop') {
                    return url('/desktop');
                }

                // Se está no desktop mas tela é mobile (<= 768px), redirecionar para mobile
                if (str_starts_with($currentPath, 'desktop') && $recommendedInterface === 'mobile') {
                    return url('/mobile');
                }
            }
            // Se usuário tem preferência manual, respeitar (não redirecionar)
        }

        return null;
    }

    /**
     * Preservar query parameters importantes
     */
    private function preserveQueryParams(Request $request): array
    {
        $importantParams = [
            'logout',
            'from',
            'retry',
            'redirect',
            'intended'
        ];

        $preserve = [];
        foreach ($importantParams as $param) {
            if ($request->has($param)) {
                $preserve[$param] = $request->get($param);
            }
        }

        // Adicionar flag para evitar loops
        $preserve['smart_redirect'] = 'true';

        return $preserve;
    }
}