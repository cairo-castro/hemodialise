<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#dc2626">
    <title>Hemodiálise - Sistema Mobile</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 25%, #991b1b 50%, #7f1d1d  100%);
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        .blood-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .blood-cell {
            position: absolute;
            background: radial-gradient(circle at 30% 30%, #fee2e2, #fecaca, #fca5a5, #f87171);
            border-radius: 50%;
            animation: float 8s infinite linear;
            opacity: 0.7;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .blood-cell:nth-child(odd) {
            animation-duration: 10s;
            opacity: 0.5;
        }

        .blood-cell:nth-child(even) {
            animation-duration: 12s;
            opacity: 0.6;
        }

        .blood-cell.small {
            width: 8px;
            height: 8px;
        }

        .blood-cell.medium {
            width: 12px;
            height: 12px;
        }

        .blood-cell.large {
            width: 16px;
            height: 16px;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0px) rotate(0deg);
            }
            25% {
                transform: translateY(75vh) translateX(20px) rotate(90deg);
            }
            50% {
                transform: translateY(50vh) translateX(-10px) rotate(180deg);
            }
            75% {
                transform: translateY(25vh) translateX(15px) rotate(270deg);
            }
            100% {
                transform: translateY(-100px) translateX(0px) rotate(360deg);
            }
        }

        .app-container {
            position: relative;
            z-index: 10;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: rgba(0,0,0,0.1);
            backdrop-filter: blur(5px);
        }

        .status-bar {
            height: env(safe-area-inset-top, 44px);
            background: rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }

        .header {
            padding: 20px 24px;
            text-align: center;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo-container {
            margin-bottom: 16px;
        }

        .logo {
            width: 80px;
            height: 80px;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
        }

        .app-title {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.5);
        }

        .app-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 16px;
            font-weight: 500;
            text-shadow: 0 1px 4px rgba(0,0,0,0.5);
        }

        .main-content {
            flex: 1;
            padding: 32px 24px 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 24px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 32px;
        }

        .menu-item {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 24px 16px;
            text-align: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .menu-item:active {
            transform: scale(0.96);
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        }

        .menu-item:active::before {
            opacity: 1;
        }

        .menu-icon {
            font-size: 32px;
            margin-bottom: 12px;
            display: block;
        }

        .menu-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .menu-subtitle {
            font-size: 12px;
            opacity: 0.8;
            line-height: 1.3;
        }

        .quick-actions {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 20px;
            padding: 20px;
            margin-top: 16px;
        }

        .quick-actions-title {
            color: white;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            text-align: center;
        }

        .quick-buttons {
            display: flex;
            gap: 12px;
        }

        .quick-btn {
            flex: 1;
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 12px;
            padding: 12px 8px;
            color: white;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .quick-btn:active {
            transform: scale(0.95);
            background: rgba(255,255,255,0.3);
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 12px 24px;
            padding-bottom: calc(12px + env(safe-area-inset-bottom, 0px));
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .nav-item {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 10px;
            font-weight: 500;
        }

        .nav-item.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }

        .nav-item:active {
            transform: scale(0.95);
        }

        .nav-icon {
            font-size: 20px;
        }

        @media (max-width: 375px) {
            .menu-grid {
                gap: 16px;
            }

            .menu-item {
                padding: 20px 12px;
            }

            .main-content {
                padding: 24px 20px 80px;
            }
        }

        @media (min-height: 812px) {
            .main-content {
                padding-top: 48px;
            }
        }
    </style>
</head>
<body>
    <div class="blood-container" id="bloodContainer">
        <!-- Hemácias serão geradas dinamicamente -->
    </div>

    <!-- Loading Screen -->
    <div id="loadingScreen" class="fixed inset-0 bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center z-50">
        <div class="text-center text-white">
            <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <h1 class="text-2xl font-bold">Hemodiálise</h1>
            <p>Carregando...</p>
        </div>
    </div>

    <div class="app-container" id="mainApp" style="display: none;">
        <div class="status-bar"></div>

        <header class="header">
            <div class="user-info" style="position: absolute; top: 20px; right: 24px; display: flex; gap: 8px; align-items: center;">
                <!-- Interface Switcher Component -->
                <div id="interfaceSwitcher" style="display: none;">
                    @auth
                        <x-interface-switcher currentInterface="ionic" :userRole="auth()->user()->role" position="inline" />
                    @endauth
                </div>
                <button onclick="logout()" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); border-radius: 20px; padding: 8px 16px; color: white; font-size: 12px; font-weight: 500;">
                    Sair
                </button>
            </div>
            <div class="logo-container">
                <img src="/assets/images/hemodialise_logo.svg" alt="Logo Hemodiálise" class="logo">
            </div>
            <h1 class="app-title">Hemodiálise</h1>
            <p class="app-subtitle" id="userWelcome">Sistema de Gestão Clínica</p>
        </header>

        <main class="main-content">
            <div class="menu-grid">
                <a href="/mobile/checklists" class="menu-item">
                    <span class="menu-icon">✅</span>
                    <div class="menu-title">Check-list</div>
                    <div class="menu-subtitle">Segurança do Paciente</div>
                </a>

                <a href="/mobile/patients" class="menu-item">
                    <span class="menu-icon">👤</span>
                    <div class="menu-title">Pacientes</div>
                    <div class="menu-subtitle">Cadastro e Consulta</div>
                </a>

                <a href="/mobile/machines" class="menu-item">
                    <span class="menu-icon">🏥</span>
                    <div class="menu-title">Máquinas</div>
                    <div class="menu-subtitle">Controle Equipamentos</div>
                </a>

                <a href="/mobile/cleaning" class="menu-item">
                    <span class="menu-icon">🧽</span>
                    <div class="menu-title">Limpeza</div>
                    <div class="menu-subtitle">Desinfecção</div>
                </a>
            </div>

            <div class="quick-actions">
                <h3 class="quick-actions-title">Ações Rápidas</h3>
                <div class="quick-buttons">
                    <button class="quick-btn" onclick="newChecklist()">
                        Novo Check-list
                    </button>
                    <button class="quick-btn" onclick="emergencyProtocol()">
                        Protocolo Emergência
                    </button>
                    <button class="quick-btn" onclick="syncData()">
                        Sincronizar
                    </button>
                </div>
            </div>
        </main>

        <nav class="bottom-nav">
            <a href="/mobile" class="nav-item active">
                <span class="nav-icon">🏠</span>
                <span>Início</span>
            </a>
            <a href="/mobile/reports" class="nav-item">
                <span class="nav-icon">📊</span>
                <span>Relatórios</span>
            </a>
            <a href="/mobile/profile" class="nav-item">
                <span class="nav-icon">👤</span>
                <span>Perfil</span>
            </a>
            <a href="/mobile/settings" class="nav-item">
                <span class="nav-icon">⚙️</span>
                <span>Ajustes</span>
            </a>
        </nav>
    </div>

    <script>
        // Gerar hemácias animadas
        function createBloodCells() {
            const container = document.getElementById('bloodContainer');
            const cellCount = 25;

            for (let i = 0; i < cellCount; i++) {
                const cell = document.createElement('div');
                cell.className = 'blood-cell';

                // Tamanhos variados
                const sizes = ['small', 'medium', 'large'];
                const randomSize = sizes[Math.floor(Math.random() * sizes.length)];
                cell.classList.add(randomSize);

                // Posição inicial aleatória
                cell.style.left = Math.random() * 100 + '%';
                cell.style.animationDelay = Math.random() * 8 + 's';
                cell.style.animationDuration = (8 + Math.random() * 4) + 's';

                container.appendChild(cell);

                // Recriar a célula quando a animação terminar
                cell.addEventListener('animationiteration', () => {
                    cell.style.left = Math.random() * 100 + '%';
                });
            }
        }

        // Funções de ação rápida
        function newChecklist() {
            window.location.href = '/mobile/checklists/new';
        }

        function emergencyProtocol() {
            alert('Protocolo de Emergência ativado!\n\n1. Interromper procedimento\n2. Verificar sinais vitais\n3. Chamar equipe médica\n4. Registrar ocorrência');
        }

        function syncData() {
            const btn = event.target;
            btn.textContent = 'Sincronizando...';
            btn.disabled = true;

            setTimeout(() => {
                btn.textContent = 'Sincronizado ✓';
                setTimeout(() => {
                    btn.textContent = 'Sincronizar';
                    btn.disabled = false;
                }, 2000);
            }, 1500);
        }

        // Função para alternar entre views
        async function switchView(view) {
            if (view === 'mobile') return; // Já estamos na mobile

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
                } else if (data.error) {
                    alert('Erro: ' + data.error);
                }
            } catch (error) {
                console.error('Erro ao alternar view:', error);
                alert('Erro de conexão ao alternar visualização');
            }
        }

        // Função de logout
        function logout() {
            // Remover token do localStorage
            localStorage.removeItem('token');

            // Fazer logout via API
            fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            }).then(() => {
                // Redirecionar para login
                window.location.href = '/login';
            }).catch(() => {
                // Mesmo com erro, redirecionar para login
                window.location.href = '/login';
            });
        }

        // Verificar autenticação
        function checkAuth() {
            const token = localStorage.getItem('token');

            // Se não tem token, redirecionar para login
            if (!token) {
                redirectToLogin();
                return;
            }

            // Validar token via API com retry
            validateToken(token, 0);
        }

        function validateToken(token, retryCount = 0) {
            fetch('/api/me', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else if (response.status === 401) {
                    // Token expirado ou inválido
                    handleInvalidToken();
                    return null;
                } else if (retryCount < 2) {
                    // Retry em caso de erro de rede
                    setTimeout(() => validateToken(token, retryCount + 1), 1000);
                    return null;
                } else {
                    throw new Error('Falha de conexão');
                }
            })
            .then(data => {
                if (!data) return; // Retry ou erro

                if (data.user) {
                    handleAuthSuccess(data.user);
                } else {
                    handleInvalidToken();
                }
            })
            .catch(error => {
                console.error('Auth check failed:', error);
                if (retryCount < 2) {
                    setTimeout(() => validateToken(token, retryCount + 1), 2000);
                } else {
                    handleInvalidToken();
                }
            });
        }

        function handleAuthSuccess(user) {
            // Verificar se tem permissão para acessar mobile
            const allowedRoles = ['tecnico', 'gestor', 'coordenador', 'supervisor'];
            if (!allowedRoles.includes(user.role)) {
                window.location.href = '/admin-bridge';
                return;
            }

            // Atualizar interface com dados do usuário
            const welcomeElement = document.getElementById('userWelcome');
            if (welcomeElement) {
                welcomeElement.textContent = `Bem-vindo, ${user.name}`;
            }

            // Mostrar interface switcher se não for técnico
            if (user.role !== 'tecnico') {
                document.getElementById('interfaceSwitcher').style.display = 'block';
            }

            // Esconder loading e mostrar app
            setTimeout(() => {
                document.getElementById('loadingScreen').style.display = 'none';
                document.getElementById('mainApp').style.display = 'flex';
            }, 800);
        }

        function handleInvalidToken() {
            localStorage.removeItem('token');
            localStorage.removeItem('jwt_token');

            // Limpar cookies
            document.cookie = 'jwt_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';

            redirectToLogin();
        }

        function redirectToLogin() {
            // Verificar se já estamos vindo de um redirect para evitar loop infinito
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('from') !== 'mobile' && urlParams.get('retry') !== 'true') {
                window.location.href = '/login?from=mobile&retry=true';
            } else {
                // Se já tentou redirecionar, mostrar erro
                document.getElementById('loadingScreen').innerHTML = `
                    <div class="text-center text-white">
                        <div class="text-red-400 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h1 class="text-xl font-bold mb-2">Erro de Autenticação</h1>
                        <p class="mb-4">Sessão expirada ou inválida</p>
                        <button onclick="forceLogin()" class="bg-white text-red-600 px-4 py-2 rounded-lg font-semibold">
                            Fazer Login Novamente
                        </button>
                    </div>
                `;
            }
        }

        function forceLogin() {
            localStorage.clear();
            window.location.href = '/login';
        }

        // Função de debug (remover em produção)
        window.debugAuth = async function() {
            const token = localStorage.getItem('token');
            console.log('Token no localStorage:', token);

            if (token) {
                try {
                    const response = await fetch('/api/debug-token', {
                        headers: {
                            'Authorization': 'Bearer ' + token
                        }
                    });
                    const data = await response.json();
                    console.log('Debug do token:', data);
                } catch (error) {
                    console.error('Erro no debug:', error);
                }
            } else {
                console.log('Nenhum token encontrado');
            }
        };

        // Inicializar quando o DOM carregar
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar autenticação primeiro
            checkAuth();

            createBloodCells();

            // Adicionar efeito de haptic feedback para iOS
            if ('vibrate' in navigator) {
                document.querySelectorAll('.menu-item, .quick-btn, .nav-item').forEach(element => {
                    element.addEventListener('touchstart', () => {
                        navigator.vibrate(10);
                    });
                });
            }
        });

        // Prevenir zoom no iOS
        document.addEventListener('gesturestart', function (e) {
            e.preventDefault();
        });

        // Adicionar classe para detecção de dispositivo iOS
        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            document.body.classList.add('ios');
        }
    </script>
</body>
</html>