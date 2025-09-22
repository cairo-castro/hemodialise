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
        $screenWidth = $request->input('screen_width', 0);

        // Cache key baseado em user agent e screen width
        $cacheKey = 'device_detection_' . md5($userAgent . $screenWidth);

        return Cache::remember($cacheKey, 3600, function () use ($userAgent, $acceptHeader, $screenWidth) {
            // Detectar mobile devices
            $mobileKeywords = [
                'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry',
                'Windows Phone', 'Opera Mini', 'IEMobile', 'webOS'
            ];

            $isMobile = false;
            foreach ($mobileKeywords as $keyword) {
                if (stripos($userAgent, $keyword) !== false) {
                    $isMobile = true;
                    break;
                }
            }

            // Detectar tablet vs phone
            $isTablet = false;
            if ($isMobile) {
                $tabletKeywords = ['iPad', 'Tablet', 'PlayBook', 'Nexus 7', 'Nexus 10', 'Galaxy Tab'];
                foreach ($tabletKeywords as $keyword) {
                    if (stripos($userAgent, $keyword) !== false) {
                        $isTablet = true;
                        break;
                    }
                }

                // Detectar tablet por screen width se disponível
                if ($screenWidth > 768 && $screenWidth < 1024) {
                    $isTablet = true;
                }
            }

            // Detectar desktop
            $isDesktop = !$isMobile;

            // Detectar tipo específico
            $deviceType = 'desktop';
            if ($isMobile) {
                $deviceType = $isTablet ? 'tablet' : 'mobile';
            }

            // Detectar browser
            $browser = $this->detectBrowser($userAgent);

            // Detectar OS
            $os = $this->detectOS($userAgent);

            // Verificar se suporta features modernas
            $supportsModernFeatures = $this->supportsModernFeatures($userAgent);

            return [
                'is_mobile' => $isMobile,
                'is_tablet' => $isTablet,
                'is_desktop' => $isDesktop,
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
                'user_agent' => $userAgent,
                'screen_width' => $screenWidth,
                'supports_modern_features' => $supportsModernFeatures,
                'recommended_interface' => $this->getRecommendedInterface($deviceType, $supportsModernFeatures),
                'can_force_desktop' => !$isMobile || $isTablet, // Tablets podem forçar desktop
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
     * Recomendar interface baseado no dispositivo
     */
    private function getRecommendedInterface(string $deviceType, bool $supportsModernFeatures): string
    {
        // Mobile sempre usa Ionic
        if ($deviceType === 'mobile') {
            return 'ionic';
        }

        // Tablets podem usar ambos, preferir Ionic para melhor UX touch
        if ($deviceType === 'tablet') {
            return $supportsModernFeatures ? 'ionic' : 'blade';
        }

        // Desktop usa Blade com Preline
        return 'blade';
    }
}