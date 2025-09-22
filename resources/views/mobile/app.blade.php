<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#2563eb">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Mobile</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Apple PWA -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">

    <!-- Icons -->
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/icon-192x192.png') }}">

    <!-- Ionic CSS -->
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            background: #f8f9fa;
        }

        /* Desktop warning */
        .desktop-warning {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 2rem;
            box-sizing: border-box;
        }

        @media (min-width: 1024px) {
            .desktop-warning {
                display: flex !important;
            }
        }

        .desktop-warning h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 300;
        }

        .desktop-warning p {
            font-size: 1.2rem;
            line-height: 1.6;
            max-width: 600px;
            margin-bottom: 2rem;
        }

        .desktop-warning .desktop-link {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .desktop-warning .desktop-link:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
    </style>

    @vite(['resources/js/mobile/main.ts', 'resources/css/app.css'], 'mobile')
</head>
<body>
    <!-- Desktop Warning -->
    <div class="desktop-warning">
        <h1>📱 Interface Mobile</h1>
        <p>
            Esta interface foi otimizada para dispositivos móveis.
            Para uma melhor experiência em desktop, acesse a versão web completa.
        </p>
        <a href="{{ route('dashboard') }}" class="desktop-link">
            🖥️ Ir para versão Desktop
        </a>
    </div>

    <!-- Mobile App Container -->
    <div id="app"></div>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('SW registered: ', registration);
                    })
                    .catch((registrationError) => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    </script>
</body>
</html>