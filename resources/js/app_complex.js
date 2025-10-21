import './bootstrap';
import { createApp } from 'vue';

// Import Preline components
import 'preline/preline';

// Desktop application imports
import DesktopApp from './desktop/presentation/components/DesktopApp.vue';
import { getContainer } from './desktop/core/di/DependencyContainer.js';

// Initialize Vue app for desktop if we're on desktop pages
function initializeDesktopApp() {
    const desktopAppElement = document.getElementById('desktop-app');
    if (desktopAppElement) {
        const container = getContainer();
        
        const app = createApp(DesktopApp, {
            authService: container.resolve('authService'),
            errorHandler: container.resolve('errorHandler')
        });

        app.mount('#desktop-app');
        
        console.log('Desktop Vue app initialized successfully');
    }
}


window.deviceDetection = function() {
    return {
        deviceInfo: null,
        performanceMetrics: {},

        init() {
            this.detectDevice();
            this.measurePerformance();
            this.setupDetectionListeners();
        },

        detectDevice() {
            const screenWidth = window.screen.width;
            const screenHeight = window.screen.height;
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const devicePixelRatio = window.devicePixelRatio || 1;
            const touchSupport = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

            // Enhanced connection detection
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            const connectionType = connection ? connection.effectiveType : 'unknown';
            const downlink = connection ? connection.downlink : null;

            // Memory and hardware detection
            const deviceMemory = navigator.deviceMemory || null;
            const hardwareConcurrency = navigator.hardwareConcurrency || null;

            // Platform and OS detection
            const platform = navigator.platform;
            const userAgent = navigator.userAgent;
            const isIOS = /iPad|iPhone|iPod/.test(userAgent);
            const isAndroid = /Android/.test(userAgent);
            const isMacOS = /Mac/.test(platform);
            const isWindows = /Win/.test(platform);

            // Advanced mobile detection
            const isMobile = screenWidth <= 768 ||
                           (touchSupport && screenWidth <= 1024) ||
                           /Mobi|Android/i.test(userAgent);

            const isTablet = (screenWidth > 768 && screenWidth <= 1024 && touchSupport) ||
                           /iPad/.test(userAgent) ||
                           (isAndroid && !/Mobile/.test(userAgent));

            const isDesktop = screenWidth > 1024 && !touchSupport;

            // Enhanced interface recommendation
            const recommendedInterface = this.getSmartRecommendation({
                screenWidth,
                viewportWidth,
                touchSupport,
                connectionType,
                downlink,
                deviceMemory,
                isMobile,
                isTablet,
                isDesktop,
                isIOS,
                isAndroid
            });

            this.deviceInfo = {
                screen: {
                    width: screenWidth,
                    height: screenHeight,
                    viewport_width: viewportWidth,
                    viewport_height: viewportHeight,
                    pixel_ratio: devicePixelRatio,
                    orientation: screen.orientation ? screen.orientation.type : 'unknown'
                },
                device: {
                    is_mobile: isMobile,
                    is_tablet: isTablet,
                    is_desktop: isDesktop,
                    touch_support: touchSupport,
                    platform: platform,
                    is_ios: isIOS,
                    is_android: isAndroid,
                    is_macos: isMacOS,
                    is_windows: isWindows
                },
                capabilities: {
                    device_memory: deviceMemory,
                    hardware_concurrency: hardwareConcurrency,
                    online: navigator.onLine,
                    local_storage: typeof(Storage) !== 'undefined',
                    service_worker: 'serviceWorker' in navigator,
                    web_assembly: typeof WebAssembly === 'object'
                },
                network: {
                    connection_type: connectionType,
                    downlink: downlink,
                    rtt: connection ? connection.rtt : null,
                    save_data: connection ? connection.saveData : false
                },
                recommended_interface: recommendedInterface,
                detection_timestamp: Date.now(),
                user_agent: userAgent
            };

            return this.deviceInfo;
        },

        getSmartRecommendation(info) {
            // Priority 1: Screen size and touch
            if (info.screenWidth < 768) {
                return 'ionic';
            }

            // Priority 2: Touch-enabled tablets
            if (info.isTablet && info.touchSupport) {
                return 'ionic';
            }

            // Priority 3: Connection quality
            if (info.connectionType && ['slow-2g', '2g'].includes(info.connectionType)) {
                return 'blade';
            }

            // Priority 4: Device performance
            if (info.deviceMemory && info.deviceMemory < 4) {
                return 'blade';
            }

            // Priority 5: Mobile browsers on desktop
            if (info.isMobile && info.screenWidth > 1024) {
                return 'ionic';
            }

            // Priority 6: Desktop without touch
            if (info.isDesktop && !info.touchSupport) {
                return 'preline';
            }

            // Fallback: Smart hybrid decision
            return info.touchSupport ? 'ionic' : 'preline';
        },

        measurePerformance() {
            const startTime = performance.now();

            setTimeout(() => {
                const jsExecutionTime = performance.now() - startTime;

                this.performanceMetrics = {
                    js_execution_time: jsExecutionTime,
                    dom_ready_time: performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart,
                    page_load_time: performance.timing.loadEventEnd - performance.timing.navigationStart,
                    dns_lookup_time: performance.timing.domainLookupEnd - performance.timing.domainLookupStart,
                    tcp_connect_time: performance.timing.connectEnd - performance.timing.connectStart,
                    first_byte_time: performance.timing.responseStart - performance.timing.requestStart,
                    memory_used: performance.memory ? performance.memory.usedJSHeapSize : null,
                    memory_limit: performance.memory ? performance.memory.jsHeapSizeLimit : null,
                    timestamp: Date.now()
                };
            }, 0);
        },

        setupDetectionListeners() {
            window.addEventListener('orientationchange', () => {
                setTimeout(() => this.detectDevice(), 100);
            });

            window.addEventListener('resize', this.debounce(() => {
                this.detectDevice();
            }, 250));

            if (navigator.connection) {
                navigator.connection.addEventListener('change', () => {
                    this.detectDevice();
                });
            }

            window.addEventListener('online', () => this.detectDevice());
            window.addEventListener('offline', () => this.detectDevice());
        },

        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

    
            if (!this.deviceInfo) return null;

            try {
                const token = localStorage.getItem('token');
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': token ? `Bearer ${token}` : ''
                    },
                    body: JSON.stringify({
                        device_info: this.deviceInfo,
                        performance_metrics: this.performanceMetrics
                    })
                });

                return response.ok ? await response.json() : null;
            } catch (error) {
                console.error('Device detection sync failed:', error);
                return null;
            }
        },

        getRecommendation() {
            return this.deviceInfo?.recommended_interface || 'preline';
        },

        shouldUseIonic() {
            return this.getRecommendation() === 'ionic';
        },

        shouldUsePreline() {
            return this.getRecommendation() === 'preline';
        },

        isLowPerformanceDevice() {
            return (this.deviceInfo?.capabilities.device_memory || 8) < 4 ||
                   (this.deviceInfo?.network.connection_type &&
                    ['slow-2g', '2g', '3g'].includes(this.deviceInfo.network.connection_type));
        }
    };
};

// Legacy login app function (mantido para compatibilidade com outras páginas)
window.loginApp = function() {
    return {
        loading: false,
        error: '',

        loginForm: {
            email: '',
            password: '',
            remember: false
        },

        init() {
            this.deviceDetector = window.deviceDetection();
            this.deviceDetector.init();
            this.checkAuth();
        },

        async checkAuth() {
            const urlParams = new URLSearchParams(window.location.search);

            if (urlParams.get('logout') === 'true') {
                localStorage.removeItem('token');
                document.cookie = 'jwt_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                return;
            }

            if (urlParams.get('from') === 'mobile' ||
                urlParams.get('from') === 'desktop' ||
                urlParams.get('from') === 'admin-bridge' ||
                urlParams.get('retry') === 'true') {
                localStorage.removeItem('token');
                return;
            }

            const token = localStorage.getItem('token');
            if (token) {
                try {
                    const response = await fetch('/api/me', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.redirectUser(data.user);
                    } else {
                        localStorage.removeItem('token');
                    }
                } catch (error) {
                    localStorage.removeItem('token');
                }
            }
        },

        async login() {
            this.loading = true;
            this.error = '';

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.loginForm)
                });

                const data = await response.json();

                if (response.ok) {
                    localStorage.setItem('token', data.token);
                    document.cookie = `jwt_token=${data.token}; path=/; max-age=86400`;
                    this.redirectUser(data.user);
                } else {
                    this.error = data.error || 'Erro no login';
                }
            } catch (error) {
                this.error = 'Erro de conexão';
                console.error('Login failed:', error);
            }

            this.loading = false;
        },

        redirectUser(user) {
            const deviceRecommendation = this.deviceDetector?.getRecommendation() || 'preline';



            if (user.role === 'tecnico' || user.role === 'field_user') {
                window.location.href = '/mobile/ionic';
            } else if (user.role === 'admin') {
                window.location.href = '/admin-bridge';
            } else {
                const userPreference = user.default_view || 'auto';

                if (userPreference === 'auto') {
                    if (deviceRecommendation === 'ionic' ||
                        this.deviceDetector?.shouldUseIonic()) {
                        window.location.href = '/mobile/ionic';
                    } else {
                        window.location.href = '/desktop/preline';
                    }
                } else if (userPreference === 'mobile') {
                    window.location.href = '/mobile/ionic';
                } else if (userPreference === 'admin') {
                    window.location.href = '/admin-bridge';
                } else {
                    window.location.href = '/desktop/preline';
                }
            }
        }
    }
};

// Mobile app legacy function (reduzido para compatibilidade)
window.mobileApp = function() {
    console.warn('mobileApp function is deprecated. Use Vue.js components instead.');
    return {
        loading: true,
        user: null,
        
        init() {
            console.log('Legacy mobile app function called');
            setTimeout(() => {
                this.loading = false;
            }, 1000);
        }
    };
};

// Initialize desktop app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initializeDesktopApp();
    
    // Initialize Preline components
    if (window.HSStaticMethods) {
        window.HSStaticMethods.autoInit();
    }
});

console.log('App.js loaded with Vue.js support');