<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DeviceDetectionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Detectar tipo de dispositivo
        $deviceInfo = $this->detectDevice($request);

        // Armazenar informações do dispositivo na request
        $request->merge(['device_info' => $deviceInfo]);

        // Adicionar informações ao view data
        view()->share('deviceInfo', $deviceInfo);

        return $next($request);
    }

    /**
     * Detectar tipo de dispositivo baseado em User-Agent e outras informações
     */
    private function detectDevice(Request $request): array
    {
        $userAgent = $request->userAgent();
        $acceptHeader = $request->header('Accept', '');

        // Capturar dimensões reais da tela do cookie ou request
        $screenWidth = $request->cookie('screen_width', $request->input('screen_width', 0));
        $screenHeight = $request->cookie('screen_height', $request->input('screen_height', 0));
        $devicePixelRatio = $request->cookie('device_pixel_ratio', $request->input('device_pixel_ratio', 1));

        // Cache key baseado em user agent e dimensões da tela
        $cacheKey = 'device_detection_' . md5($userAgent . $screenWidth . $screenHeight);

        return Cache::remember($cacheKey, 3600, function () use ($userAgent, $acceptHeader, $screenWidth, $screenHeight, $devicePixelRatio) {
            // Detectar mobile devices via User Agent
            $mobileKeywords = [
                'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry',
                'Windows Phone', 'Opera Mini', 'IEMobile', 'webOS'
            ];

            $isMobileUA = false;
            foreach ($mobileKeywords as $keyword) {
                if (stripos($userAgent, $keyword) !== false) {
                    $isMobileUA = true;
                    break;
                }
            }

            // Detectar tablet vs phone via User Agent
            $isTabletUA = false;
            if ($isMobileUA) {
                $tabletKeywords = ['iPad', 'Tablet', 'PlayBook', 'Nexus 7', 'Nexus 10', 'Galaxy Tab'];
                foreach ($tabletKeywords as $keyword) {
                    if (stripos($userAgent, $keyword) !== false) {
                        $isTabletUA = true;
                        break;
                    }
                }
            }

            // DETECÇÃO INTELIGENTE BASEADA NO TAMANHO DA TELA
            // Breakpoints mais precisos baseados em padrões modernos
            $isMobileScreen = false;
            $isTabletScreen = false;
            $isDesktopScreen = false;

            if ($screenWidth > 0) {
                // Mobile: até 768px (smartphones em portrait e landscape)
                if ($screenWidth <= 768) {
                    $isMobileScreen = true;
                }
                // Tablet: 769px até 1024px
                elseif ($screenWidth > 768 && $screenWidth <= 1024) {
                    $isTabletScreen = true;
                }
                // Desktop: acima de 1024px
                else {
                    $isDesktopScreen = true;
                }
            }

            // Decisão final: priorizar detecção por tela se disponível, senão usar UA
            $isMobile = $screenWidth > 0 ? $isMobileScreen : $isMobileUA;
            $isTablet = $screenWidth > 0 ? $isTabletScreen : $isTabletUA;
            $isDesktop = $screenWidth > 0 ? $isDesktopScreen : !$isMobileUA;

            // Ajuste: se UA indica mobile mas tela indica desktop, considerar o contexto
            // (ex: modo desktop no celular ou tablet com tela grande)
            if ($isMobileUA && $isDesktopScreen) {
                // Se UA é mobile mas tela é grande, verificar se é tablet ou modo desktop
                if ($screenWidth > 1024) {
                    // Tela muito grande, provavelmente modo desktop forçado ou cast
                    $isDesktop = true;
                    $isMobile = false;
                } else {
                    // Pode ser um tablet grande
                    $isTablet = true;
                    $isMobile = false;
                }
            }

            // Detectar tipo específico (usado para routing)
            $deviceType = 'desktop';
            if ($isMobile && !$isTablet) {
                $deviceType = 'mobile';
            } elseif ($isTablet) {
                $deviceType = 'tablet';
            }

            // Detectar browser
            $browser = $this->detectBrowser($userAgent);

            // Detectar OS
            $os = $this->detectOS($userAgent);

            // Verificar se suporta features modernas
            $supportsModernFeatures = $this->supportsModernFeatures($userAgent);

            // Determinar interface recomendada
            $recommendedInterface = $this->getRecommendedInterface($deviceType, $supportsModernFeatures, $screenWidth);

            return [
                'is_mobile' => $isMobile && !$isTablet,
                'is_tablet' => $isTablet,
                'is_desktop' => $isDesktop,
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
                'user_agent' => $userAgent,
                'screen_width' => (int)$screenWidth,
                'screen_height' => (int)$screenHeight,
                'device_pixel_ratio' => (float)$devicePixelRatio,
                'supports_modern_features' => $supportsModernFeatures,
                'recommended_interface' => $recommendedInterface,
                'can_force_desktop' => $isTablet || $isDesktop, // Tablets e desktop podem alternar
                'detection_method' => $screenWidth > 0 ? 'screen_size' : 'user_agent',
                'detection_timestamp' => now()->timestamp
            ];
        });
    }

    /**
     * Detectar browser
     */
    private function detectBrowser(string $userAgent): string
    {
        if (stripos($userAgent, 'Chrome') !== false) return 'chrome';
        if (stripos($userAgent, 'Firefox') !== false) return 'firefox';
        if (stripos($userAgent, 'Safari') !== false) return 'safari';
        if (stripos($userAgent, 'Edge') !== false) return 'edge';
        if (stripos($userAgent, 'Opera') !== false) return 'opera';
        if (stripos($userAgent, 'Internet Explorer') !== false) return 'ie';

        return 'unknown';
    }

    /**
     * Detectar sistema operacional
     */
    private function detectOS(string $userAgent): string
    {
        if (stripos($userAgent, 'Windows') !== false) return 'windows';
        if (stripos($userAgent, 'Mac') !== false) return 'macos';
        if (stripos($userAgent, 'Linux') !== false) return 'linux';
        if (stripos($userAgent, 'Android') !== false) return 'android';
        if (stripos($userAgent, 'iOS') !== false || stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) return 'ios';

        return 'unknown';
    }

    /**
     * Verificar se suporta features modernas
     */
    private function supportsModernFeatures(string $userAgent): bool
    {
        // Browsers muito antigos que não suportam features modernas
        $oldBrowsers = [
            'MSIE 6', 'MSIE 7', 'MSIE 8', 'MSIE 9', 'MSIE 10',
            'Chrome/4', 'Chrome/5', 'Chrome/6', 'Chrome/7', 'Chrome/8',
            'Firefox/3', 'Firefox/4', 'Firefox/5', 'Firefox/6'
        ];

        foreach ($oldBrowsers as $oldBrowser) {
            if (stripos($userAgent, $oldBrowser) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Recomendar interface baseado no dispositivo e tamanho da tela
     */
    private function getRecommendedInterface(string $deviceType, bool $supportsModernFeatures, int $screenWidth): string
    {
        // Mobile (até 768px) sempre usa interface Mobile
        if ($deviceType === 'mobile' || $screenWidth <= 768) {
            return 'mobile';
        }

        // Desktop (acima de 1024px) usa interface Desktop
        if ($deviceType === 'desktop' || $screenWidth > 1024) {
            return 'desktop';
        }

        // Tablets (769-1024px) preferem mobile para melhor UX touch, mas podem usar desktop
        if ($deviceType === 'tablet') {
            return $supportsModernFeatures ? 'mobile' : 'desktop';
        }

        // Fallback: Desktop
        return 'desktop';
    }
}