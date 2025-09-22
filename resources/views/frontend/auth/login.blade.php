<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Sistema Hemodiálise</title>
    @vite(['resources/css/app.css', 'resources/js/auth/login.js'])
    <style>
        .mobile-detect { display: none; }
        @media (max-width: 768px) {
            .mobile-detect { display: block; }
            .desktop-detect { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div id="login-app">
        <!-- Vue.js Login Component will be mounted here -->
        <div class="min-h-screen flex items-center justify-center">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-600">Carregando...</p>
            </div>
        </div>
    </div>
</body>
</html>