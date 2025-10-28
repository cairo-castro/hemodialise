<?php

namespace App\Services;

use Illuminate\Http\Request;

class DeviceDetector
{
    /**
     * Detecta se é dispositivo móvel pelo User-Agent
     *
     * @param Request $request
     * @return bool
     */
    public static function isMobile(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');

        return stripos($userAgent, 'Mobile') !== false ||
               stripos($userAgent, 'Android') !== false ||
               stripos($userAgent, 'iPhone') !== false ||
               stripos($userAgent, 'iPad') !== false ||
               stripos($userAgent, 'iPod') !== false;
    }

    /**
     * Detecta se é tablet (opcional - para uso futuro)
     *
     * @param Request $request
     * @return bool
     */
    public static function isTablet(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');

        return (stripos($userAgent, 'iPad') !== false) ||
               (stripos($userAgent, 'Android') !== false &&
                stripos($userAgent, 'Mobile') === false);
    }

    /**
     * Detecta se é desktop
     *
     * @param Request $request
     * @return bool
     */
    public static function isDesktop(Request $request): bool
    {
        return !self::isMobile($request);
    }
}
