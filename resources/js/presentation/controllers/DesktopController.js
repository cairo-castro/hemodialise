/**
 * Desktop Application Controller - Controlador principal da interface desktop
 * Seguindo Clean Architecture: Presentation Layer
 * Seguindo SOLID: Dependency Inversion Principle, Single Responsibility
 */
import { AuthenticationService } from '../application/services/AuthenticationService.js';
import { HttpClient } from '../infrastructure/http/HttpClient.js';
import { TokenStorage } from '../infrastructure/storage/TokenStorage.js';
import { ErrorHandler } from '../infrastructure/error/ErrorHandler.js';
import { User } from '../domain/entities/User.js';

export class DesktopController {
    constructor() {
        // Dependency Injection
        this.httpClient = new HttpClient();
        this.tokenStorage = new TokenStorage();
        this.errorHandler = new ErrorHandler();
        this.authService = new AuthenticationService(this.httpClient, this.tokenStorage);
        
        // Estado da aplicação
        this.state = {
            loading: true,
            user: new User(),
            currentView: 'dashboard',
            canToggleInterfaces: false
        };

        // Bind methods to preserve 'this' context
        this.init = this.init.bind(this);
        this.checkAuth = this.checkAuth.bind(this);
        this.logout = this.logout.bind(this);
        this.switchInterface = this.switchInterface.bind(this);
    }

    /**
     * Inicializa a aplicação
     */
    async init() {
        console.log('Desktop app initializing...');
        
        try {
            // Verificar autenticação de forma não-bloqueante
            this.checkAuth().catch(error => {
                this.errorHandler.logError(error, 'Auth initialization');
                this.state.user = new User({ role: 'guest' });
            });

            // Ocultar loading após timeout reduzido
            setTimeout(() => {
                console.log('Hiding loading screen...');
                this.state.loading = false;
                this._initializeUIComponents();
            }, 1000);

        } catch (error) {
            this.errorHandler.handleNetworkError(error);
            this.state.loading = false;
        }
    }

    /**
     * Verifica autenticação do usuário
     */
    async checkAuth() {
        console.log('Starting auth check...');
        
        try {
            const authResult = await this.authService.checkAuthentication();
            
            if (authResult.success) {
                this.state.user = authResult.user;
                this.state.canToggleInterfaces = authResult.user.isManager();
                
                // Redirecionar usuários de campo para mobile
                if (authResult.user.isFieldUser()) {
                    console.log('Field user detected, redirecting to mobile...');
                    setTimeout(() => {
                        window.location.href = '/mobile/ionic';
                    }, 500);
                    return;
                }
                
                console.log('Auth successful, user:', authResult.user.name);
            } else {
                this.state.user = authResult.user; // User com role 'guest'
                this.state.canToggleInterfaces = false;
                
                if (authResult.error && !authResult.error.includes('timeout')) {
                    this.errorHandler.showInfo('Para acesso completo, faça login no sistema');
                }
            }
        } catch (error) {
            this.errorHandler.handleAuthError(error);
            this.state.user = new User({ role: 'guest' });
            this.state.canToggleInterfaces = false;
        }
    }

    /**
     * Realiza logout do usuário
     */
    async logout() {
        try {
            const result = await this.authService.logout();
            
            if (result.success) {
                this.errorHandler.showSuccess('Logout realizado com sucesso');
                setTimeout(() => {
                    window.location.href = '/login?logout=true';
                }, 1000);
            } else {
                this.errorHandler.showError('Erro no logout, redirecionando...');
                setTimeout(() => {
                    window.location.href = '/login?logout=true';
                }, 2000);
            }
        } catch (error) {
            this.errorHandler.handleNetworkError(error);
            // Forçar redirecionamento mesmo com erro
            setTimeout(() => {
                window.location.href = '/login?logout=true';
            }, 2000);
        }
    }

    /**
     * Alterna entre interfaces
     */
    async switchInterface(targetInterface) {
        try {
            const response = await this.httpClient.post('/api/smart-route/switch-interface', {
                body: { interface: targetInterface },
                headers: {
                    'Authorization': `Bearer ${this.tokenStorage.getToken()}`,
                    'X-CSRF-TOKEN': this._getCsrfToken()
                }
            });

            const data = await response.json();
            
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                this.errorHandler.showError(data.error || 'Erro ao alternar interface');
            }
        } catch (error) {
            this.errorHandler.handleNetworkError(error);
        }
    }

    /**
     * Obtém título da view atual
     */
    getViewTitle() {
        const titles = {
            'dashboard': 'Dashboard Executivo',
            'safety-control': 'Controle de Segurança',
            'machines': 'Gestão de Máquinas',
            'patients': 'Gestão de Pacientes',
            'reports-operational': 'Relatórios Operacionais',
            'reports-quality': 'Qualidade e Segurança',
            'analytics': 'Analytics Avançado'
        };
        return titles[this.state.currentView] || 'Dashboard';
    }

    /**
     * Obtém subtítulo da view atual
     */
    getViewSubtitle() {
        const subtitles = {
            'dashboard': 'Visão geral em tempo real do sistema de hemodiálise',
            'safety-control': 'Monitoramento e controle de protocolos de segurança',
            'machines': 'Status e manutenção de equipamentos',
            'patients': 'Cadastro e acompanhamento de pacientes',
            'reports-operational': 'Relatórios detalhados das operações',
            'reports-quality': 'Indicadores de qualidade e conformidade',
            'analytics': 'Insights e métricas avançadas para tomada de decisão'
        };
        return subtitles[this.state.currentView] || 'Sistema de gestão executiva';
    }

    /**
     * Inicializa componentes da UI (Preline)
     */
    _initializeUIComponents() {
        try {
            if (window.HSStaticMethods) {
                window.HSStaticMethods.autoInit();
                console.log('Preline components initialized');
            } else {
                console.warn('Preline HSStaticMethods not available');
            }
        } catch (error) {
            this.errorHandler.logError(error, 'UI Components initialization');
        }
    }

    /**
     * Obtém token CSRF
     */
    _getCsrfToken() {
        const metaElement = document.querySelector('meta[name="csrf-token"]');
        return metaElement ? metaElement.getAttribute('content') : '';
    }

    /**
     * Getter para compatibilidade com Alpine.js
     */
    get loading() {
        return this.state.loading;
    }

    get user() {
        return this.state.user;
    }

    get currentView() {
        return this.state.currentView;
    }

    set currentView(view) {
        this.state.currentView = view;
    }

    get canToggleInterfaces() {
        return this.state.canToggleInterfaces;
    }

    /**
     * Método para obter display do role do usuário (compatibilidade)
     */
    getRoleDisplay(role) {
        return new User({ role }).getRoleDisplay();
    }
}