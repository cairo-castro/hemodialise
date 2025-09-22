<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema Hemodiálise - Desktop (Teste)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Sistema Hemodiálise - Interface Desktop</h1>

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-2">Status da Autenticação</h2>
                <div id="auth-status" class="p-4 bg-yellow-100 border border-yellow-400 rounded">
                    <p class="text-yellow-800">Verificando autenticação...</p>
                </div>
            </div>

            @auth
                <div class="mb-6 p-4 bg-green-100 border border-green-400 rounded">
                    <h3 class="text-green-800 font-semibold">Autenticado via Sessão Laravel</h3>
                    <p class="text-green-700">Usuário: {{ auth()->user()->name }}</p>
                    <p class="text-green-700">Role: {{ auth()->user()->role }}</p>
                </div>

                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">Troca de Interface</h2>
                    <x-interface-switcher currentInterface="preline" :userRole="auth()->user()->role" position="inline" />
                </div>
            @else
                <div class="mb-6 p-4 bg-red-100 border border-red-400 rounded">
                    <h3 class="text-red-800 font-semibold">Não Autenticado via Sessão Laravel</h3>
                    <p class="text-red-700">Você precisa fazer login para acessar todas as funcionalidades.</p>
                    <a href="/login" class="mt-2 inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Fazer Login
                    </a>
                </div>
            @endauth

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">Dashboard</h3>
                    <p class="text-blue-600">Visão geral do sistema</p>
                </div>

                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <h3 class="text-lg font-semibold text-green-800 mb-2">Relatórios</h3>
                    <p class="text-green-600">Análises e métricas</p>
                </div>

                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                    <h3 class="text-lg font-semibold text-purple-800 mb-2">Configurações</h3>
                    <p class="text-purple-600">Gerenciar sistema</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple auth check for testing
        async function checkAuth() {
            const authStatus = document.getElementById('auth-status');

            try {
                const response = await fetch('/api/me', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    authStatus.innerHTML = `
                        <div class="text-green-800">
                            <p class="font-semibold">✅ Autenticado via API</p>
                            <p>Usuário: ${data.user.name}</p>
                            <p>Role: ${data.user.role}</p>
                        </div>
                    `;
                    authStatus.className = 'p-4 bg-green-100 border border-green-400 rounded';
                } else {
                    authStatus.innerHTML = `
                        <div class="text-red-800">
                            <p class="font-semibold">❌ Não autenticado via API</p>
                            <p>Status: ${response.status}</p>
                        </div>
                    `;
                    authStatus.className = 'p-4 bg-red-100 border border-red-400 rounded';
                }
            } catch (error) {
                authStatus.innerHTML = `
                    <div class="text-red-800">
                        <p class="font-semibold">❌ Erro na verificação</p>
                        <p>Erro: ${error.message}</p>
                    </div>
                `;
                authStatus.className = 'p-4 bg-red-100 border border-red-400 rounded';
            }
        }

        // Check auth when page loads
        document.addEventListener('DOMContentLoaded', checkAuth);
    </script>
</body>
</html>