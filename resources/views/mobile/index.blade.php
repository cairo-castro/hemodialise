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

        <!-- Main App - Nova Home Screen -->
        <div x-show="user && !loading" class="min-h-screen">
            <script>
                // Redirecionar para nova home após autenticação
                window.location.href = '/mobile/home';
            </script>

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
                    <!-- Busca/Cadastro de Paciente -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <h2 class="text-lg font-bold mb-4">Buscar/Cadastrar Paciente</h2>

                        <!-- Busca por Nome e Data de Nascimento -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                                <input type="text" x-model="patientForm.full_name"
                                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Digite o nome completo do paciente" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
                                <input type="date" x-model="patientForm.birth_date"
                                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <button @click="searchPatient()"
                                    class="w-full bg-green-600 text-white p-3 rounded-lg font-semibold hover:bg-green-700">
                                Buscar Paciente
                            </button>
                        </div>

                        <!-- Paciente Encontrado -->
                        <div x-show="searchResult" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h3 class="font-semibold text-green-800 mb-2">✓ Paciente Encontrado</h3>
                            <div class="text-sm text-gray-700">
                                <p><strong>Nome:</strong> <span x-text="searchResult?.full_name"></span></p>
                                <p><strong>Data de Nascimento:</strong> <span x-text="searchResult?.birth_date"></span></p>
                                <p><strong>Idade:</strong> <span x-text="searchResult?.age"></span> anos</p>
                                <p><strong>Tipo Sanguíneo:</strong> <span x-text="searchResult?.blood_type || 'Não informado'"></span></p>
                            </div>
                            <button @click="startNewPatientSearch()"
                                    class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                                Buscar outro paciente
                            </button>
                        </div>

                        <!-- Paciente Não Encontrado - Formulário de Cadastro -->
                        <div x-show="patientSearched && !searchResult && !showPatientForm" class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h3 class="font-semibold text-yellow-800 mb-2">⚠ Paciente não encontrado</h3>
                            <p class="text-sm text-gray-700 mb-3">
                                Não foi encontrado um paciente com o nome "<span x-text="patientForm.full_name"></span>"
                                e data de nascimento "<span x-text="patientForm.birth_date"></span>".
                            </p>
                            <button @click="showPatientForm = true"
                                    class="w-full bg-blue-600 text-white p-2 rounded-lg font-semibold hover:bg-blue-700">
                                Cadastrar Novo Paciente
                            </button>
                        </div>

                        <!-- Formulário de Cadastro -->
                        <div x-show="showPatientForm" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h3 class="font-semibold text-blue-800 mb-3">Cadastrar Novo Paciente</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Prontuário Médico</label>
                                    <input type="text" x-model="patientForm.medical_record"
                                           class="w-full p-2 border rounded-lg text-sm"
                                           placeholder="Número do prontuário" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Sanguíneo</label>
                                    <select x-model="patientForm.blood_type" class="w-full p-2 border rounded-lg text-sm">
                                        <option value="">Selecione</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alergias</label>
                                    <textarea x-model="patientForm.allergies"
                                              class="w-full p-2 border rounded-lg text-sm h-20"
                                              placeholder="Descreva alergias conhecidas (opcional)"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                                    <textarea x-model="patientForm.observations"
                                              class="w-full p-2 border rounded-lg text-sm h-20"
                                              placeholder="Observações gerais (opcional)"></textarea>
                                </div>
                                <div class="flex space-x-2">
                                    <button @click="savePatient()"
                                            class="flex-1 bg-green-600 text-white p-2 rounded-lg font-semibold hover:bg-green-700">
                                        Cadastrar Paciente
                                    </button>
                                    <button @click="showPatientForm = false"
                                            class="flex-1 bg-gray-500 text-white p-2 rounded-lg font-semibold hover:bg-gray-600">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulário do Checklist -->
                    <div x-show="searchResult" class="bg-white rounded-lg shadow p-4">
                        <h2 class="text-lg font-bold mb-4">Checklist de Segurança</h2>
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