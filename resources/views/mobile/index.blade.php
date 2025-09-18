<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hemodiálise Mobile</title>
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div id="app" x-data="mobileApp()" x-init="init()">
        <!-- Loading Screen -->
        <div x-show="loading" class="fixed inset-0 bg-blue-600 flex items-center justify-center z-50">
            <div class="text-center text-white">
                <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                <h1 class="text-2xl font-bold">Hemodiálise</h1>
                <p>Sistema Mobile</p>
            </div>
        </div>

        <!-- Login Screen -->
        <div x-show="!user && !loading" class="min-h-screen flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-6">
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Entrar</h1>
                    <p class="text-gray-600">Sistema de Hemodiálise</p>
                </div>

                <form @submit.prevent="login()" class="space-y-4">
                    <div>
                        <input type="email" x-model="loginForm.email" placeholder="Email"
                               class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <input type="password" x-model="loginForm.password" placeholder="Senha"
                               class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <button type="submit" :disabled="loggingIn"
                            class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50">
                        <span x-show="!loggingIn">Entrar</span>
                        <span x-show="loggingIn">Entrando...</span>
                    </button>
                </form>

                <div x-show="error" class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm" x-text="error"></div>
            </div>
        </div>

        <!-- Main App -->
        <div x-show="user && !loading" class="min-h-screen">
            <!-- Header -->
            <header class="bg-blue-600 text-white p-4 shadow-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-bold">Hemodiálise</h1>
                        <p class="text-blue-100 text-sm" x-text="user?.name"></p>
                    </div>
                    <button @click="logout()" class="bg-blue-700 px-3 py-1 rounded text-sm">Sair</button>
                </div>
            </header>

            <!-- Navigation -->
            <nav class="bg-white border-b border-gray-200 p-4">
                <div class="flex space-x-4 overflow-x-auto">
                    <button @click="currentView = 'dashboard'"
                            :class="currentView === 'dashboard' ? 'bg-blue-100 text-blue-700' : 'text-gray-600'"
                            class="px-4 py-2 rounded-lg whitespace-nowrap">Dashboard</button>
                    <button @click="currentView = 'checklist'"
                            :class="currentView === 'checklist' ? 'bg-blue-100 text-blue-700' : 'text-gray-600'"
                            class="px-4 py-2 rounded-lg whitespace-nowrap">Checklist</button>
                    <button @click="currentView = 'patients'"
                            :class="currentView === 'patients' ? 'bg-blue-100 text-blue-700' : 'text-gray-600'"
                            class="px-4 py-2 rounded-lg whitespace-nowrap">Pacientes</button>
                </div>
            </nav>

            <!-- Content -->
            <main class="p-4">
                <!-- Dashboard -->
                <div x-show="currentView === 'dashboard'" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="font-semibold text-gray-800">Checklists Hoje</h3>
                            <p class="text-2xl font-bold text-blue-600">12</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="font-semibold text-gray-800">Pendentes</h3>
                            <p class="text-2xl font-bold text-orange-600">3</p>
                        </div>
                    </div>
                </div>

                <!-- Checklist Form -->
                <div x-show="currentView === 'checklist'" class="space-y-4">
                    <div class="bg-white rounded-lg shadow p-4">
                        <h2 class="text-lg font-bold mb-4">Novo Checklist</h2>
                        <form @submit.prevent="submitChecklist()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Máquina</label>
                                <select x-model="checklistForm.machine_id" class="w-full p-3 border rounded-lg" required>
                                    <option value="">Selecione uma máquina</option>
                                    <option value="1">Máquina 01</option>
                                    <option value="2">Máquina 02</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Paciente</label>
                                <select x-model="checklistForm.patient_id" class="w-full p-3 border rounded-lg" required>
                                    <option value="">Selecione um paciente</option>
                                    <option value="1">João Silva</option>
                                    <option value="2">Maria Santos</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                                <select x-model="checklistForm.shift" class="w-full p-3 border rounded-lg" required>
                                    <option value="">Selecione o turno</option>
                                    <option value="matutino">Matutino</option>
                                    <option value="vespertino">Vespertino</option>
                                    <option value="noturno">Noturno</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold">
                                Iniciar Checklist
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Patients -->
                <div x-show="currentView === 'patients'" class="space-y-4">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-bold">Pacientes</h2>
                        </div>
                        <div class="divide-y">
                            <div class="p-4 flex justify-between items-center">
                                <div>
                                    <h3 class="font-medium">João Silva</h3>
                                    <p class="text-sm text-gray-600">Tipo A+</p>
                                </div>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Ativo</span>
                            </div>
                            <div class="p-4 flex justify-between items-center">
                                <div>
                                    <h3 class="font-medium">Maria Santos</h3>
                                    <p class="text-sm text-gray-600">Tipo O-</p>
                                </div>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Ativo</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Status Bar -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-2 text-center text-xs text-gray-500">
                <span x-show="online" class="text-green-600">● Online</span>
                <span x-show="!online" class="text-red-600">● Offline</span>
                | <span x-text="user?.unit?.name || 'Sem unidade'"></span>
            </div>
        </div>
    </div>

    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>