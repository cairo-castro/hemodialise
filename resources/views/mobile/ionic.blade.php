<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <title>{{ $title }}</title>

    <base href="/" />

    <meta name="color-scheme" content="light dark" />
    <meta name="viewport" content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="msapplication-tap-highlight" content="no" />

    <link rel="icon" type="image/png" href="/ionic-build/favicon.png" />

    <!-- add to homescreen for ios -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />

    <!-- CSRF Token para API calls -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- User data for interface switcher -->
    @auth
    <script>
        window.USER_DATA = {
            id: {{ auth()->user()->id }},
            name: "{{ auth()->user()->name }}",
            role: "{{ auth()->user()->role }}",
            can_switch_interfaces: {{ in_array(auth()->user()->role, ['admin', 'gestor', 'coordenador', 'supervisor']) ? 'true' : 'false' }}
        };
    </script>
    @else
    <script>
        window.USER_DATA = null;
    </script>
    @endauth

    <!-- Ionic CSS -->
    <link rel="stylesheet" href="/ionic-build/assets/index.css">
</head>

<body>
    <div id="app"></div>

    <!-- Ionic JS -->
    <script type="module" src="/ionic-build/assets/index.js"></script>
</body>
</html>