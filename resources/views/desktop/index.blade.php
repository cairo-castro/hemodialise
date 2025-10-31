<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hemodiálise - Interface Desktop</title>

    <!-- Preload critical resources -->
    <link rel="preload" href="{{ asset('hemodialise_logo.png') }}" as="image">

    <!-- Progressive Web App meta tags -->
    <meta name="theme-color" content="#1f2937">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Hemodiálise Desktop">

    <!-- Manifest for PWA -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Desktop assets -->
    @vite(['resources/js/desktop.js'], 'desktop-assets')

    <!-- Theme initialization script (prevent flash) -->
    <script>
        // Initialize theme before page renders to prevent flash
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <!-- Loading screen styles -->
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
            margin-bottom: 0.5rem;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-top: 1rem;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(0.95); }
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
        <div class="text-gray-300 mb-4">Carregando Interface Desktop...</div>
        <div class="loading-spinner"></div>
    </div>

    <!-- Vue.js App Container -->
    <div id="desktop-app" v-cloak class="h-full">
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
                <a href="/admin" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Painel Admin
                </a>
            </div>
        </div>
    </noscript>
</body>
</html>
