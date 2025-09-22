import './bootstrap';

// Import Preline components
import 'preline/preline';

// Enhanced Client-Side Device Detection
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

            // Battery status (if available)
            let batteryLevel = null;
            let isCharging = null;
            if ('getBattery' in navigator) {
                navigator.getBattery().then(battery => {
                    batteryLevel = battery.level;
                    isCharging = battery.charging;
                    this.updateBatteryInfo(batteryLevel, isCharging);
                });
            }

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
                battery: {
                    level: batteryLevel,
                    charging: isCharging
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
                return 'ionic'; // Always Ionic for small screens
            }

            // Priority 2: Touch-enabled tablets
            if (info.isTablet && info.touchSupport) {
                return 'ionic'; // Tablets get Ionic for better touch experience
            }

            // Priority 3: Connection quality
            if (info.connectionType && ['slow-2g', '2g'].includes(info.connectionType)) {
                return 'blade'; // Lighter interface for slow connections
            }

            // Priority 4: Device performance
            if (info.deviceMemory && info.deviceMemory < 4) {
                return 'blade'; // Less memory-intensive for low-end devices
            }

            // Priority 5: Mobile browsers on desktop
            if (info.isMobile && info.screenWidth > 1024) {
                return 'ionic'; // Mobile browser on large screen
            }

            // Priority 6: Desktop without touch
            if (info.isDesktop && !info.touchSupport) {
                return 'preline'; // Desktop interface for non-touch
            }

            // Fallback: Smart hybrid decision
            return info.touchSupport ? 'ionic' : 'preline';
        },

        measurePerformance() {
            const startTime = performance.now();

            // Measure JavaScript execution time
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
            // Listen for orientation changes
            window.addEventListener('orientationchange', () => {
                setTimeout(() => this.detectDevice(), 100);
            });

            // Listen for viewport changes
            window.addEventListener('resize', this.debounce(() => {
                this.detectDevice();
            }, 250));

            // Listen for connection changes
            if (navigator.connection) {
                navigator.connection.addEventListener('change', () => {
                    this.detectDevice();
                });
            }

            // Listen for online/offline changes
            window.addEventListener('online', () => this.detectDevice());
            window.addEventListener('offline', () => this.detectDevice());
        },

        updateBatteryInfo(level, charging) {
            if (this.deviceInfo) {
                this.deviceInfo.battery.level = level;
                this.deviceInfo.battery.charging = charging;
            }
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

        async sendToServer(endpoint = '/api/device-detection') {
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

// Login App Logic
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
            // Initialize device detection
            this.deviceDetector = window.deviceDetection();
            this.deviceDetector.init();

            // Verificar se já está logado
            this.checkAuth();
        },

        async checkAuth() {
            // Verificar parâmetros na URL
            const urlParams = new URLSearchParams(window.location.search);

            // Se é um logout, não verificar token
            if (urlParams.get('logout') === 'true') {
                localStorage.removeItem('token');
                document.cookie = 'jwt_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                return;
            }

            // Se veio de redirecionamento de uma view, não verificar auth automaticamente
            if (urlParams.get('from') === 'mobile' ||
                urlParams.get('from') === 'desktop' ||
                urlParams.get('from') === 'admin-bridge' ||
                urlParams.get('retry') === 'true') {
                localStorage.removeItem('token'); // Limpar token inválido se houver
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
                    // Definir cookie para facilitar acesso pelo backend
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
            // Get device recommendation from client-side detection
            const deviceRecommendation = this.deviceDetector?.getRecommendation() || 'preline';

            // Send device info to server for enhanced decision making
            if (this.deviceDetector) {
                this.deviceDetector.sendToServer('/api/smart-route/detect').then(serverResponse => {
                    console.log('Device detection synced with server:', serverResponse);
                });
            }

            // Enhanced redirection logic combining user role, preferences, and device detection
            if (user.role === 'tecnico' || user.role === 'field_user') {
                // Field users always go to mobile/ionic interface
                window.location.href = '/mobile/ionic';
            } else if (user.role === 'admin') {
                // Admins go to admin panel (Filament)
                window.location.href = '/admin-bridge';
            } else {
                // Gestores, coordenadores, supervisores: smart routing based on device
                const userPreference = user.default_view || 'auto';

                if (userPreference === 'auto') {
                    // Automatic detection based on device capabilities
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
                    // Default to desktop/preline
                    window.location.href = '/desktop/preline';
                }
            }
        }
    }
};

// Mobile App Logic
window.mobileApp = function() {
    return {
        loading: true,
        user: null,
        currentView: 'dashboard',
        online: navigator.onLine,
        error: '',
        loggingIn: false,

        loginForm: {
            email: '',
            password: ''
        },

        checklistForm: {
            machine_id: '',
            patient_id: '',
            shift: '',
            checklist_data: {},
            observations: ''
        },

        patientForm: {
            full_name: '',
            birth_date: '',
            medical_record: '',
            blood_type: '',
            allergies: '',
            observations: ''
        },

        searchResult: null,
        showPatientForm: false,
        patientSearched: false,

        init() {
            // Initialize device detection for mobile app
            this.deviceDetector = window.deviceDetection();
            this.deviceDetector.init();

            this.checkAuth();
            this.setupOfflineHandlers();
            this.registerServiceWorker();
            this.setupDeviceOptimizations();

            setTimeout(() => {
                this.loading = false;
            }, 1500);
        },

        async checkAuth() {
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
                        this.user = data.user;
                    } else {
                        localStorage.removeItem('token');
                    }
                } catch (error) {
                    console.error('Auth check failed:', error);
                    localStorage.removeItem('token');
                }
            }
        },

        async login() {
            this.loggingIn = true;
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
                    this.user = data.user;
                    this.loginForm.email = '';
                    this.loginForm.password = '';
                } else {
                    this.error = data.error || 'Erro no login';
                }
            } catch (error) {
                this.error = 'Erro de conexão';
                console.error('Login failed:', error);
            }

            this.loggingIn = false;
        },

        async logout() {
            try {
                // Limpar localStorage
                localStorage.removeItem('token');

                // Limpar cookie
                document.cookie = 'jwt_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';

                // Logout via sessão web (limpa sessão Laravel)
                await fetch('/logout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Content-Type': 'application/json'
                    }
                });
            } catch (error) {
                console.error('Logout failed:', error);
            }

            // Forçar redirecionamento com parâmetro de logout
            window.location.href = '/login?logout=true';
        },

        async searchPatient() {
            if (!this.patientForm.full_name || !this.patientForm.birth_date) {
                alert('Por favor, preencha o nome completo e a data de nascimento.');
                return;
            }

            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/patients/search', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    },
                    body: JSON.stringify({
                        full_name: this.patientForm.full_name,
                        birth_date: this.patientForm.birth_date
                    })
                });

                const data = await response.json();
                this.patientSearched = true;

                if (data.found) {
                    this.searchResult = data.patient;
                    this.showPatientForm = false;
                    // Preencher o formulário com os dados encontrados
                    this.patientForm = {
                        full_name: data.patient.full_name,
                        birth_date: data.patient.birth_date,
                        medical_record: data.patient.medical_record,
                        blood_type: data.patient.blood_type || '',
                        allergies: '',
                        observations: ''
                    };
                    // Usar o paciente encontrado no checklist
                    this.checklistForm.patient_id = data.patient.id;
                } else {
                    this.searchResult = null;
                    this.showPatientForm = true;
                }
            } catch (error) {
                console.error('Patient search failed:', error);
                alert('Erro de conexão ao buscar paciente');
            }
        },

        async savePatient() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/patients', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    },
                    body: JSON.stringify(this.patientForm)
                });

                const data = await response.json();

                if (data.success) {
                    this.searchResult = data.patient;
                    this.showPatientForm = false;
                    this.checklistForm.patient_id = data.patient.id;
                    alert('Paciente cadastrado com sucesso!');
                } else {
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join('\n');
                        alert('Erro de validação:\n' + errorMessages);
                    } else {
                        alert('Erro: ' + (data.message || 'Falha ao cadastrar paciente'));
                    }
                }
            } catch (error) {
                console.error('Patient save failed:', error);
                alert('Erro de conexão ao cadastrar paciente');
            }
        },

        startNewPatientSearch() {
            this.patientForm = {
                full_name: '',
                birth_date: '',
                medical_record: '',
                blood_type: '',
                allergies: '',
                observations: ''
            };
            this.searchResult = null;
            this.showPatientForm = false;
            this.patientSearched = false;
            this.checklistForm.patient_id = '';
        },

        async submitChecklist() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/checklists', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    },
                    body: JSON.stringify(this.checklistForm)
                });

                if (response.ok) {
                    // Reset form and show success
                    this.checklistForm = {
                        machine_id: '',
                        patient_id: '',
                        shift: '',
                        checklist_data: {},
                        observations: ''
                    };
                    alert('Checklist salvo com sucesso!');
                } else {
                    const data = await response.json();
                    alert('Erro: ' + (data.message || 'Falha ao salvar'));
                }
            } catch (error) {
                console.error('Submit failed:', error);
                alert('Erro de conexão');
            }
        },

        setupOfflineHandlers() {
            window.addEventListener('online', () => {
                this.online = true;
                console.log('Back online');
            });

            window.addEventListener('offline', () => {
                this.online = false;
                console.log('Gone offline');
            });
        },

        async registerServiceWorker() {
            if ('serviceWorker' in navigator) {
                try {
                    await navigator.serviceWorker.register('/sw.js');
                    console.log('Service Worker registered');
                } catch (error) {
                    console.error('Service Worker registration failed:', error);
                }
            }
        },

        setupDeviceOptimizations() {
            if (!this.deviceDetector) return;

            // Optimize for low-performance devices
            if (this.deviceDetector.isLowPerformanceDevice()) {
                console.log('Low performance device detected, applying optimizations...');

                // Reduce animation duration
                document.documentElement.style.setProperty('--animation-duration', '0.1s');

                // Disable complex animations
                document.documentElement.classList.add('reduce-motion');

                // Implement lazy loading for images
                this.enableLazyLoading();
            }

            // Optimize for battery-powered devices
            const batteryInfo = this.deviceDetector.deviceInfo?.battery;
            if (batteryInfo && batteryInfo.level !== null && batteryInfo.level < 0.2 && !batteryInfo.charging) {
                console.log('Low battery detected, enabling power saving mode...');
                this.enablePowerSavingMode();
            }

            // Adapt interface for touch devices
            if (this.deviceDetector.deviceInfo?.device.touch_support) {
                document.documentElement.classList.add('touch-device');
            }

            // Send comprehensive device info to server
            this.syncDeviceInfoWithServer();
        },

        enableLazyLoading() {
            // Implement intersection observer for lazy loading
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            observer.unobserve(img);
                        }
                    });
                });

                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
        },

        enablePowerSavingMode() {
            // Reduce frequency of automatic updates
            this.powerSavingMode = true;

            // Disable non-essential animations
            document.documentElement.classList.add('power-saving');

            // Reduce background sync frequency
            if (this.backgroundSyncInterval) {
                clearInterval(this.backgroundSyncInterval);
                this.backgroundSyncInterval = setInterval(() => {
                    this.syncPendingData();
                }, 30000); // Sync every 30 seconds instead of 10
            }
        },

        async syncDeviceInfoWithServer() {
            try {
                const result = await this.deviceDetector.sendToServer('/api/smart-route/detect');
                if (result && result.recommended_url) {
                    console.log('Server recommended interface:', result.recommended_url);

                    // Check if we're on the wrong interface
                    const currentPath = window.location.pathname;
                    const recommendedPath = new URL(result.recommended_url).pathname;

                    if (currentPath !== recommendedPath && !currentPath.includes(recommendedPath)) {
                        console.log('Interface mismatch detected, consider redirecting...');
                        // Could show a non-intrusive suggestion to switch interfaces
                    }
                }
            } catch (error) {
                console.error('Failed to sync device info with server:', error);
            }
        }
    }
}
