/**
 * Device Detection Script
 * Captura as dimensões da tela do usuário e envia para o servidor via cookies
 * Isso permite que o Laravel faça redirecionamento inteligente baseado no tamanho real da tela
 */

(function() {
    'use strict';

    /**
     * Obter dimensões atuais da tela
     */
    function getScreenDimensions() {
        return {
            width: window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
            height: window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight,
            devicePixelRatio: window.devicePixelRatio || 1
        };
    }

    /**
     * Criar ou atualizar cookie
     */
    function setCookie(name, value, days = 30) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Lax";
    }

    /**
     * Obter valor de cookie
     */
    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    /**
     * Atualizar cookies com dimensões da tela
     */
    function updateScreenDimensionsCookies() {
        const dimensions = getScreenDimensions();

        // Verificar se os valores mudaram
        const currentWidth = getCookie('screen_width');
        const currentHeight = getCookie('screen_height');
        const currentDPR = getCookie('device_pixel_ratio');

        const hasChanged =
            currentWidth !== String(dimensions.width) ||
            currentHeight !== String(dimensions.height) ||
            currentDPR !== String(dimensions.devicePixelRatio);

        if (hasChanged) {
            // Atualizar cookies
            setCookie('screen_width', dimensions.width);
            setCookie('screen_height', dimensions.height);
            setCookie('device_pixel_ratio', dimensions.devicePixelRatio);

            console.log('[Device Detection] Screen dimensions updated:', dimensions);

            // Verificar se houve mudança significativa de largura
            const widthDiff = Math.abs(dimensions.width - parseInt(currentWidth || 0));
            if (widthDiff > 50 && currentWidth) {
                console.log('[Device Detection] Significant screen size change detected:', widthDiff + 'px');

                // Disparar evento customizado para a aplicação reagir
                window.dispatchEvent(new CustomEvent('screenSizeChanged', {
                    detail: dimensions
                }));

                // Verificar se precisa redirecionar automaticamente
                checkRedirectNeeded();
            }
        }

        return dimensions;
    }

    /**
     * Verificar se deve redirecionar baseado nas dimensões
     */
    function checkRedirectNeeded() {
        const dimensions = getScreenDimensions();
        const currentPath = window.location.pathname;

        // Breakpoint: 768px
        // Mobile: largura <= 768px
        // Desktop: largura > 768px
        const isMobileScreen = dimensions.width <= 768;
        const isDesktopScreen = dimensions.width > 768;

        // Verificar se está na interface errada
        const isInMobileInterface = currentPath.startsWith('/mobile');
        const isInDesktopInterface = currentPath.startsWith('/desktop');

        // Não redirecionar se estiver em páginas de admin ou login
        const skipPaths = ['/admin', '/login', '/logout', '/api'];
        const shouldSkip = skipPaths.some(path => currentPath.startsWith(path));

        if (shouldSkip) {
            console.log('[Device Detection] Skipping redirect for:', currentPath);
            return;
        }

        // Redirecionar se necessário
        if (isMobileScreen && isInDesktopInterface) {
            console.log('[Device Detection] Mobile screen (' + dimensions.width + 'px) detected in desktop interface. Redirecting to /mobile...');
            window.location.href = '/mobile';
        } else if (isDesktopScreen && isInMobileInterface) {
            console.log('[Device Detection] Desktop screen (' + dimensions.width + 'px) detected in mobile interface. Redirecting to /desktop...');
            window.location.href = '/desktop';
        } else {
            console.log('[Device Detection] Current interface is correct for screen size (' + dimensions.width + 'px)');
        }
    }

    /**
     * Inicializar detecção
     */
    function initialize() {
        // Atualizar dimensões ao carregar
        updateScreenDimensionsCookies();

        // Atualizar dimensões quando a janela for redimensionada
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                updateScreenDimensionsCookies();
            }, 250); // Debounce de 250ms
        });

        // Atualizar dimensões quando orientação mudar (mobile)
        window.addEventListener('orientationchange', function() {
            setTimeout(function() {
                updateScreenDimensionsCookies();
                // Verificar se precisa redirecionar após mudança de orientação
                checkRedirectNeeded();
            }, 300); // Aguardar orientação estabilizar
        });

        console.log('[Device Detection] Initialized successfully');
    }

    // Executar quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }

    // Expor função globalmente para uso em outras partes da aplicação
    window.DeviceDetection = {
        getDimensions: getScreenDimensions,
        updateCookies: updateScreenDimensionsCookies,
        checkRedirect: checkRedirectNeeded
    };

})();
