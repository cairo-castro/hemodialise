<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hemodiálise - Interface Executiva</title>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="{{ asset('hemodialise_logo.png') }}" as="image">
    
    <!-- Progressive Web App meta tags -->
    <meta name="theme-color" content="#1f2937">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Hemodiálise">
    
    <!-- Manifest for PWA -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Tailwind CSS and custom styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Initial loading styles to prevent FOUC -->
    <style>
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            color: white;
        }
        
        .loading-logo {
            width: 120px;
            height: 120px;
            margin-bottom: 2rem;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .loading-text {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #3b82f6;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .fade-out {
            opacity: 0;
            transition: opacity 0.5s ease-out;
        }
        
        [v-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="h-full bg-gray-50">
    <!-- Loading Screen -->
    <div id="loading-screen" class="loading-screen">
        <img src="{{ asset('hemodialise_logo.png') }}" alt="Logo Hemodiálise" class="loading-logo">
        <div class="loading-text">Sistema Hemodiálise</div>
        <div class="text-gray-300 mb-4">Carregando Interface Executiva...</div>
        <div class="loading-spinner"></div>
    </div>

    <!-- Vue.js App Container -->
    <div id="desktop-app" v-cloak>
        <!-- Content will be rendered by Vue.js -->
    </div>

    <!-- Fallback content for no-JavaScript users -->
    <noscript>
        <div class="min-h-screen flex items-center justify-center bg-gray-50">
            <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6 text-center">
                <img src="{{ asset('hemodialise_logo.png') }}" alt="Logo Hemodiálise" class="w-20 h-20 mx-auto mb-4">
                <h1 class="text-xl font-semibold text-gray-900 mb-2">JavaScript Necessário</h1>
                <p class="text-gray-600 mb-4">
                    Este sistema requer JavaScript para funcionar corretamente. 
                    Por favor, habilite o JavaScript em seu navegador.
                </p>
                <a href="/login" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Voltar ao Login
                </a>
            </div>
        </div>
    </noscript>

    <!-- Hidden loading management script -->
    <script>
        // Hide loading screen when Vue.js app is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit for Vue to initialize
            setTimeout(function() {
                const loadingScreen = document.getElementById('loading-screen');
                if (loadingScreen) {
                    loadingScreen.classList.add('fade-out');
                    setTimeout(function() {
                        loadingScreen.style.display = 'none';
                    }, 500);
                }
            }, 1000);
        });

        // Enhanced error handling
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
            // Show user-friendly error message
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen && loadingScreen.style.display !== 'none') {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded max-w-md text-sm';
                errorDiv.innerHTML = `
                    <strong>Erro ao carregar a aplicação:</strong><br>
                    Por favor, recarregue a página ou entre em contato com o suporte.
                    <br><br>
                    <button onclick="window.location.reload()" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">
                        Recarregar Página
                    </button>
                `;
                loadingScreen.appendChild(errorDiv);
            }
        });

        // Vue.js app loaded callback
        window.vueAppLoaded = function() {
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen) {
                loadingScreen.classList.add('fade-out');
                setTimeout(function() {
                    loadingScreen.style.display = 'none';
                }, 500);
            }
        };
    </script>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('SW registered: ', registration);
                    })
                    .catch(function(registrationError) {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    </script>
</body>
</html>