<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hemodiálise - Desktop</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .sidebar-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glassmorphism {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .blood-bg {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 25%, #991b1b 50%, #7f1d1d 100%);
        }

        .card-hover {
            transition: all 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body class="bg-gray-50" x-data="desktopApp()" x-init="init()">
    <!-- Loading Screen -->
    <div x-show="loading" class="fixed inset-0 blood-bg flex items-center justify-center z-50">
        <div class="text-center text-white">
            <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <h1 class="text-2xl font-bold">Sistema Hemodiálise</h1>
            <p>Carregando Dashboard...</p>
        </div>
    </div>

    <!-- Main Container -->
    <div x-show="!loading" class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <div :class="sidebarOpen ? 'w-64' : 'w-16'" class="blood-bg text-white sidebar-transition flex-shrink-0">
            <div class="p-4">
                <!-- Logo e Toggle -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center" x-show="sidebarOpen">
                        <img src="/assets/images/hemodialise_logo.svg" alt="Logo" class="w-8 h-8 mr-3 filter brightness-0 invert">
                        <h1 class="text-lg font-bold">Hemodiálise</h1>
                    </div>
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>

                <!-- User Info -->
                <div class="mb-6 p-3 bg-white/10 rounded-lg" x-show="sidebarOpen && user">
                    <div class="text-sm font-medium" x-text="user?.name"></div>
                    <div class="text-xs text-white/70" x-text="getRoleDisplay(user?.role)"></div>
                    <div class="text-xs text-white/60" x-text="user?.unit?.name || 'Acesso Global'"></div>
                </div>

                <!-- View Toggle -->
                <div class="mb-6" x-show="canToggleViews">
                    <div x-show="sidebarOpen" class="text-xs text-white/70 mb-2">Alternar Visualização</div>
                    <div class="space-y-1">
                        <button @click="switchView('mobile')"
                                class="w-full flex items-center px-3 py-2 text-sm rounded-lg hover:bg-white/10 transition-colors"
                                :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 011 1v9a1 1 0 01-1 1H5a1 1 0 01-1-1V7zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"></path>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3">Mobile</span>
                        </button>
                        <button @click="switchView('desktop')"
                                class="w-full flex items-center px-3 py-2 text-sm rounded-lg bg-white/20"
                                :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3">Desktop</span>
                        </button>
                        <button @click="switchView('admin')"
                                class="w-full flex items-center px-3 py-2 text-sm rounded-lg hover:bg-white/10 transition-colors"
                                :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 8a2 2 0 110 4 2 2 0 010-4zM10 14a2 2 0 110 4 2 2 0 010-4z"></path>
                            </svg>
                            <span x-show="sidebarOpen" class="ml-3">Administração</span>
                        </button>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="space-y-1">
                    <a href="#" @click.prevent="currentView = 'dashboard'"
                       :class="currentView === 'dashboard' ? 'bg-white/20' : 'hover:bg-white/10'"
                       class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-9 9a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l2.293 2.293a1 1 0 001.414-1.414l-9-9z"></path>
                        </svg>
                        <span x-show="sidebarOpen" class="ml-3">Dashboard</span>
                    </a>

                    <a href="#" @click.prevent="currentView = 'operations'"
                       :class="currentView === 'operations' ? 'bg-white/20' : 'hover:bg-white/10'"
                       class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span x-show="sidebarOpen" class="ml-3">Operações</span>
                    </a>

                    <a href="#" @click.prevent="currentView = 'reports'"
                       :class="currentView === 'reports' ? 'bg-white/20' : 'hover:bg-white/10'"
                       class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414L15 8.414V14a1 1 0 11-2 0V9.414l-1.293 1.293a1 1 0 01-1.414-1.414L12.586 7H8z"></path>
                        </svg>
                        <span x-show="sidebarOpen" class="ml-3">Relatórios</span>
                    </a>

                    <a href="#" @click.prevent="currentView = 'analytics'"
                       :class="currentView === 'analytics' ? 'bg-white/20' : 'hover:bg-white/10'"
                       class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                        </svg>
                        <span x-show="sidebarOpen" class="ml-3">Analytics</span>
                    </a>
                </nav>

                <!-- Logout -->
                <div class="absolute bottom-4 left-4 right-4">
                    <button @click="logout()"
                            class="w-full flex items-center px-3 py-2 text-sm rounded-lg hover:bg-white/10 transition-colors"
                            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z"></path>
                        </svg>
                        <span x-show="sidebarOpen" class="ml-3">Sair</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="glassmorphism border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800" x-text="getViewTitle()"></h1>
                        <p class="text-gray-600" x-text="getViewSubtitle()"></p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                            </svg>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        <!-- User Profile -->
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-700" x-text="user?.name"></div>
                                <div class="text-xs text-gray-500" x-text="getRoleDisplay(user?.role)"></div>
                            </div>
                            <div class="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center text-white text-sm font-bold"
                                 x-text="user?.name?.charAt(0)"></div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Dashboard View -->
                <div x-show="currentView === 'dashboard'" class="space-y-6">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="glassmorphism rounded-xl p-6 card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Sessões Hoje</p>
                                    <p class="text-2xl font-bold text-gray-800">24</p>
                                    <p class="text-xs text-green-600">+12% vs ontem</p>
                                </div>
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="glassmorphism rounded-xl p-6 card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Pacientes Ativos</p>
                                    <p class="text-2xl font-bold text-gray-800">156</p>
                                    <p class="text-xs text-green-600">+3 novos</p>
                                </div>
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM5 8a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2V8z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="glassmorphism rounded-xl p-6 card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Máquinas Online</p>
                                    <p class="text-2xl font-bold text-gray-800">8/10</p>
                                    <p class="text-xs text-yellow-600">2 manutenção</p>
                                </div>
                                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="glassmorphism rounded-xl p-6 card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Alertas</p>
                                    <p class="text-2xl font-bold text-gray-800">3</p>
                                    <p class="text-xs text-red-600">2 críticos</p>
                                </div>
                                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts and Tables -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="glassmorphism rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Sessões por Turno</h3>
                            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                                <p class="text-gray-500">Gráfico de sessões por turno</p>
                            </div>
                        </div>

                        <div class="glassmorphism rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Últimas Atividades</h3>
                            <div class="space-y-3">
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium">Sessão HD-03 finalizada</p>
                                        <p class="text-xs text-gray-500">há 5 minutos</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium">Máquina HD-05 em manutenção</p>
                                        <p class="text-xs text-gray-500">há 15 minutos</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium">Novo paciente cadastrado</p>
                                        <p class="text-xs text-gray-500">há 32 minutos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operations View -->
                <div x-show="currentView === 'operations'" class="space-y-6">
                    <div class="glassmorphism rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Controle Operacional</h3>
                        <p class="text-gray-600">Módulo de operações em desenvolvimento...</p>
                    </div>
                </div>

                <!-- Reports View -->
                <div x-show="currentView === 'reports'" class="space-y-6">
                    <div class="glassmorphism rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Relatórios Gerenciais</h3>
                        <p class="text-gray-600">Módulo de relatórios em desenvolvimento...</p>
                    </div>
                </div>

                <!-- Analytics View -->
                <div x-show="currentView === 'analytics'" class="space-y-6">
                    <div class="glassmorphism rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Analytics e Métricas</h3>
                        <p class="text-gray-600">Módulo de analytics em desenvolvimento...</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        window.desktopApp = function() {
            return {
                loading: true,
                user: null,
                sidebarOpen: true,
                currentView: 'dashboard',
                canToggleViews: false,

                init() {
                    this.checkAuth();
                    setTimeout(() => {
                        this.loading = false;
                    }, 1000);
                },

                async checkAuth() {
                    const token = localStorage.getItem('token');

                    if (!token) {
                        this.redirectToLogin();
                        return;
                    }

                    try {
                        const response = await fetch('/api/view-toggle/user-views', {
                            headers: {
                                'Authorization': 'Bearer ' + token,
                                'Accept': 'application/json',
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.user = data.user;
                            this.canToggleViews = data.can_toggle;

                            // Verificar se tem permissão para desktop
                            if (!this.user.canAccessDesktop && !['gestor', 'coordenador', 'supervisor', 'admin'].includes(this.user.role)) {
                                window.location.href = '/mobile';
                                return;
                            }
                        } else if (response.status === 401) {
                            this.handleInvalidToken();
                        } else {
                            throw new Error('Erro de conexão');
                        }
                    } catch (error) {
                        console.error('Auth check failed:', error);
                        this.handleInvalidToken();
                    }
                },

                handleInvalidToken() {
                    localStorage.removeItem('token');
                    localStorage.removeItem('jwt_token');
                    document.cookie = 'jwt_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                    this.redirectToLogin();
                },

                redirectToLogin() {
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.get('from') !== 'desktop' && urlParams.get('retry') !== 'true') {
                        window.location.href = '/login?from=desktop&retry=true';
                    } else {
                        // Mostrar erro em vez de loop
                        this.loading = false;
                        document.body.innerHTML = `
                            <div class="min-h-screen bg-red-600 flex items-center justify-center">
                                <div class="text-center text-white">
                                    <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                                    <h1 class="text-2xl font-bold mb-2">Erro de Autenticação</h1>
                                    <p class="mb-4">Sessão expirada ou inválida</p>
                                    <button onclick="window.location.href='/login'" class="bg-white text-red-600 px-4 py-2 rounded-lg font-semibold">
                                        Fazer Login Novamente
                                    </button>
                                </div>
                            </div>
                        `;
                    }
                },

                async switchView(view) {
                    try {
                        const response = await fetch(`/api/view-toggle/switch-to-${view}`, {
                            method: 'POST',
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const data = await response.json();
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    } catch (error) {
                        console.error('Erro ao alternar view:', error);
                    }
                },

                async logout() {
                    try {
                        localStorage.removeItem('token');
                        await fetch('/logout', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        window.location.href = '/login?logout=true';
                    } catch (error) {
                        window.location.href = '/login?logout=true';
                    }
                },

                getRoleDisplay(role) {
                    const roles = {
                        'admin': 'Administrador',
                        'gestor': 'Gestor Regional',
                        'coordenador': 'Coordenador',
                        'supervisor': 'Supervisor',
                        'tecnico': 'Técnico'
                    };
                    return roles[role] || role;
                },

                getViewTitle() {
                    const titles = {
                        'dashboard': 'Dashboard Executivo',
                        'operations': 'Controle Operacional',
                        'reports': 'Relatórios Gerenciais',
                        'analytics': 'Analytics & Métricas'
                    };
                    return titles[this.currentView] || 'Dashboard';
                },

                getViewSubtitle() {
                    const subtitles = {
                        'dashboard': 'Visão geral do sistema de hemodiálise',
                        'operations': 'Gestão em tempo real das operações',
                        'reports': 'Relatórios detalhados e análises',
                        'analytics': 'Insights e métricas avançadas'
                    };
                    return subtitles[this.currentView] || 'Sistema de gestão';
                }
            }
        };
    </script>
</body>
</html>