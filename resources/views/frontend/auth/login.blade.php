<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Sistema Hemodiálise</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .mobile-detect { display: none; }
        @media (max-width: 768px) {
            .mobile-detect { display: block; }
            .desktop-detect { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div id="app" x-data="loginApp()" x-init="init()">
        <!-- Mobile Login -->
        <div class="mobile-detect">
            <div class="min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-6">
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-800">Sistema Hemodiálise</h1>
                        <p class="text-gray-600">Acesso Mobile</p>
                    </div>

                    <div x-show="error" class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm" x-text="error"></div>

                    <form @submit.prevent="login()" class="space-y-4">
                        <div>
                            <input type="email" x-model="loginForm.email" placeholder="Email"
                                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <input type="password" x-model="loginForm.password" placeholder="Senha"
                                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" x-model="loginForm.remember" id="remember" class="mr-2">
                            <label for="remember" class="text-sm text-gray-600">Lembrar-me</label>
                        </div>
                        <button type="submit" :disabled="loading" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!loading">Entrar</span>
                            <span x-show="loading">Entrando...</span>
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-500">Credenciais de teste:</p>
                        <p class="text-xs text-gray-400">tecnico.joao@hemodialise.com / tecnico123</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Login -->
        <div class="desktop-detect">
            <div class="min-h-screen flex">
                <!-- Left side - Image/Brand -->
                <div class="hidden lg:flex lg:w-1/2 bg-blue-600 items-center justify-center">
                    <div class="text-center text-white">
                        <h1 class="text-4xl font-bold mb-4">Sistema Hemodiálise</h1>
                        <p class="text-xl text-blue-100">Gestão Completa e Segura</p>
                        <div class="mt-8">
                            <div class="w-32 h-32 bg-white/20 rounded-full mx-auto flex items-center justify-center">
                                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side - Login Form -->
                <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
                    <div class="w-full max-w-md">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl font-bold text-gray-800">Bem-vindo</h2>
                            <p class="text-gray-600 mt-2">Faça login para acessar o sistema</p>
                        </div>

                        <div x-show="error" class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg" x-text="error"></div>

                        <form @submit.prevent="login()" class="space-y-6">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" x-model="loginForm.email" id="email"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                                <input type="password" x-model="loginForm.password" id="password"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input type="checkbox" x-model="loginForm.remember" id="remember_desktop" class="mr-2">
                                    <label for="remember_desktop" class="text-sm text-gray-600">Lembrar-me</label>
                                </div>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 disabled:opacity-50">
                                <span x-show="!loading">Entrar</span>
                                <span x-show="loading">Entrando...</span>
                            </button>
                        </form>

                        <div class="mt-8 text-center border-t pt-6">
                            <p class="text-sm text-gray-500 mb-2">Credenciais de teste:</p>
                            <div class="text-xs text-gray-400 space-y-1">
                                <p><strong>Admin:</strong> admin@hemodialise.com / admin123</p>
                                <p><strong>Gerente:</strong> gerente.sl@hemodialise.com / gerente123</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>