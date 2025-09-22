<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class SmartRouteController extends Controller
{
    /**
     * Rota principal inteligente que detecta device e redireciona automaticamente
     */
    public function home(Request $request)
    {
        // Obter informações do dispositivo
        $deviceInfo = $request->get('device_info', []);

        // Obter usuário autenticado
        $user = $this->getAuthenticatedUser($request);

        // Se não está autenticado, ir para login
        if (!$user) {
            return $this->redirectToLogin($deviceInfo);
        }

        // Determinar interface baseada em user role e device
        $targetInterface = $this->determineTargetInterface($user, $deviceInfo);

        // Redirecionar para a interface apropriada
        return $this->redirectToInterface($targetInterface, $request);
    }

    /**
     * Endpoint para detecção de dispositivo via AJAX
     */
    public function detectDevice(Request $request)
    {
        $screenWidth = $request->input('screen_width');
        $screenHeight = $request->input('screen_height');
        $devicePixelRatio = $request->input('device_pixel_ratio', 1);
        $touchSupport = $request->input('touch_support', false);
        $connectionType = $request->input('connection_type', 'unknown');

        // Atualizar detecção com informações do cliente
        $deviceInfo = $request->get('device_info', []);
        $deviceInfo['screen_width'] = $screenWidth;
        $deviceInfo['screen_height'] = $screenHeight;
        $deviceInfo['device_pixel_ratio'] = $devicePixelRatio;
        $deviceInfo['touch_support'] = $touchSupport;
        $deviceInfo['connection_type'] = $connectionType;

        // Re-avaliar recomendação baseada em mais dados
        $deviceInfo['recommended_interface'] = $this->getEnhancedRecommendation($deviceInfo);

        return response()->json([
            'device_info' => $deviceInfo,
            'recommended_url' => $this->getRecommendedUrl($deviceInfo, auth()->user()),
            'user' => auth()->user() ? [
                'role' => auth()->user()->role,
                'interface_preference' => auth()->user()->interface_preference ?? 'auto'
            ] : null
        ]);
    }

    /**
     * Endpoint para alternar interface (override manual)
     */
    public function switchInterface(Request $request)
    {
        $targetInterface = $request->input('interface'); // 'ionic', 'preline', 'admin'
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        // Mapear interfaces antigas para novas
        $interfaceMap = [
            'mobile' => 'ionic',
            'desktop' => 'preline',
            'admin' => 'admin'
        ];

        $targetInterface = $interfaceMap[$targetInterface] ?? $targetInterface;

        // Verificar se o usuário pode acessar a interface solicitada
        if (!$this->canAccessInterface($user, $targetInterface)) {
            return response()->json([
                'error' => 'Acesso negado para esta interface',
                'allowed_interfaces' => $this->getAllowedInterfaces($user),
                'user_role' => $user->role
            ], 403);
        }

        // Salvar preferência (opcional)
        if ($request->input('save_preference', false)) {
            $user->update([
                'interface_preference' => $targetInterface,
                'updated_at' => now()
            ]);
        }

        // Retornar URL da nova interface
        $redirectUrl = $this->getInterfaceUrl($targetInterface);

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'interface' => $targetInterface,
            'message' => "Interface alterada para: " . $this->getInterfaceName($targetInterface)
        ]);
    }

    /**
     * Endpoint para obter preferências do usuário
     */
    public function getUserPreferences(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'interface_preference' => $user->interface_preference ?? 'auto',
                'allowed_interfaces' => $this->getAllowedInterfaces($user),
                'current_interface' => $this->getCurrentInterfaceFromUrl($request)
            ]
        ]);
    }

    /**
     * Endpoint para resetar preferências para auto-detecção
     */
    public function resetPreferences(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        $user->update([
            'interface_preference' => null,
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Preferências resetadas para detecção automática',
            'redirect_url' => '/' // Vai para home com auto-detecção
        ]);
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
            // JWT falhou, tentar session
        }

        // Tentar session
        if (Auth::check()) {
            return Auth::user();
        }

        return null;
    }

    /**
     * Determinar interface alvo baseada em usuário e device
     */
    private function determineTargetInterface($user, array $deviceInfo): string
    {
        // Verificar preferência do usuário primeiro
        $userPreference = $user->interface_preference ?? 'auto';

        if ($userPreference !== 'auto') {
            // Se usuário tem preferência específica, verificar se pode acessar
            if ($this->canAccessInterface($user, $userPreference)) {
                return $userPreference;
            }
        }

        // Lógica automática baseada em role
        switch ($user->role) {
            case 'field_user':
            case 'tecnico':
                return 'mobile'; // Sempre mobile

            case 'admin':
                // Admin pode ir para qualquer lugar, preferir admin panel
                if ($deviceInfo['is_desktop']) {
                    return 'admin';
                }
                return $deviceInfo['is_mobile'] ? 'mobile' : 'desktop';

            case 'gestor':
            case 'coordenador':
            case 'supervisor':
                // Baseado no device
                if ($deviceInfo['is_mobile'] && !$deviceInfo['is_tablet']) {
                    return 'mobile';
                }
                return 'desktop';

            default:
                // Fallback baseado no device
                return $deviceInfo['is_mobile'] ? 'mobile' : 'desktop';
        }
    }

    /**
     * Verificar se usuário pode acessar interface
     */
    private function canAccessInterface($user, string $interface): bool
    {
        switch ($interface) {
            case 'ionic':
            case 'mobile':
                return true; // Todos podem acessar mobile

            case 'preline':
            case 'desktop':
                // Técnicos não podem acessar desktop (apenas mobile)
                return !in_array($user->role, ['field_user', 'tecnico']);

            case 'admin':
                // Apenas admins, gestores e coordenadores podem acessar admin panel
                return in_array($user->role, ['admin', 'gestor', 'coordenador']);

            default:
                return false;
        }
    }

    /**
     * Obter interfaces permitidas para o usuário
     */
    private function getAllowedInterfaces($user): array
    {
        $allowed = [];

        if ($this->canAccessInterface($user, 'ionic')) {
            $allowed[] = 'ionic';
        }

        if ($this->canAccessInterface($user, 'preline')) {
            $allowed[] = 'preline';
        }

        if ($this->canAccessInterface($user, 'admin')) {
            $allowed[] = 'admin';
        }

        return $allowed;
    }

    /**
     * Obter URL da interface
     */
    private function getInterfaceUrl(string $interface): string
    {
        switch ($interface) {
            case 'ionic':
            case 'mobile':
                return url('/mobile/ionic');
            case 'preline':
            case 'desktop':
                return url('/desktop/preline');
            case 'admin':
                return url('/admin');
            default:
                return url('/');
        }
    }

    /**
     * Redirecionar para login
     */
    private function redirectToLogin(array $deviceInfo)
    {
        // Login responsivo que detecta automaticamente
        return redirect('/login');
    }

    /**
     * Redirecionar para interface apropriada
     */
    private function redirectToInterface(string $interface, Request $request)
    {
        $url = $this->getInterfaceUrl($interface);

        // Preservar query parameters
        $queryParams = $request->query();
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return redirect($url);
    }

    /**
     * Recomendação aprimorada baseada em mais dados do dispositivo
     */
    private function getEnhancedRecommendation(array $deviceInfo): string
    {
        $screenWidth = $deviceInfo['screen_width'] ?? 0;
        $touchSupport = $deviceInfo['touch_support'] ?? false;
        $connectionType = $deviceInfo['connection_type'] ?? 'unknown';

        // Se é mobile pequeno, sempre Ionic
        if ($screenWidth > 0 && $screenWidth < 768) {
            return 'ionic';
        }

        // Se é tablet ou desktop pequeno com touch, preferir Ionic
        if ($screenWidth >= 768 && $screenWidth <= 1024 && $touchSupport) {
            return 'ionic';
        }

        // Se é desktop grande sem touch, preferir Blade
        if ($screenWidth > 1024 && !$touchSupport) {
            return 'blade';
        }

        // Considerar conexão lenta para recomendar interface mais leve
        if (in_array($connectionType, ['slow-2g', '2g', '3g'])) {
            return 'blade'; // Blade é mais leve que Ionic
        }

        // Fallback para recomendação original
        return $deviceInfo['recommended_interface'] ?? 'blade';
    }

    /**
     * Obter URL recomendada baseada em device info e usuário
     */
    private function getRecommendedUrl(array $deviceInfo, $user): ?string
    {
        if (!$user) {
            return url('/login');
        }

        $interface = $this->determineTargetInterface($user, $deviceInfo);
        return $this->getInterfaceUrl($interface);
    }

    /**
     * Obter nome da interface para exibição
     */
    private function getInterfaceName(string $interface): string
    {
        switch ($interface) {
            case 'ionic':
                return 'Interface Mobile';
            case 'preline':
                return 'Interface Desktop';
            case 'admin':
                return 'Painel Administrativo';
            default:
                return 'Interface Desconhecida';
        }
    }

    /**
     * Detectar interface atual baseada na URL
     */
    private function getCurrentInterfaceFromUrl($request): string
    {
        $path = $request->getPathInfo();

        if (str_contains($path, '/mobile') || str_contains($path, '/ionic')) {
            return 'ionic';
        } elseif (str_contains($path, '/desktop') || str_contains($path, '/preline')) {
            return 'preline';
        } elseif (str_contains($path, '/admin')) {
            return 'admin';
        }

        return 'auto';
    }
}