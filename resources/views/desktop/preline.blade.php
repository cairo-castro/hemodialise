<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema Hemodiálise - Desktop Executive</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 h-full" x-data="desktopApp()" x-init="init()">
    <!-- Loading Screen -->
    <div x-show="loading" class="fixed inset-0 z-50 flex items-center justify-center medical-gradient">
        <div class="text-center text-white">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-white border-t-transparent mb-4"></div>
            <h1 class="text-2xl font-bold mb-2">Sistema Hemodiálise</h1>
            <p class="text-lg">Carregando Interface Executiva...</p>
        </div>
    </div>

    <!-- Main Application -->
    <div x-show="!loading" class="h-full">
        <!-- Sidebar -->
        <div class="hs-overlay hs-overlay-open:translate-x-0 -translate-x-full transition-all duration-300 transform fixed top-0 start-0 bottom-0 z-[60] w-64 bg-white border-e border-gray-200 pt-7 pb-10 overflow-y-auto lg:block lg:translate-x-0 lg:end-auto lg:bottom-0 [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300">

            <!-- Logo and Brand -->
            <div class="px-6">
                <div class="flex items-center mb-8">
                    <div class="w-10 h-10 medical-gradient rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Hemodiálise</h1>
                        <p class="text-sm text-gray-600">Sistema Executivo</p>
                    </div>
                </div>

                <!-- User Profile Card -->
                <div x-show="user" class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl mb-6 border border-blue-100">
                    <div class="flex items-center">
                        <div class="w-12 h-12 medical-gradient rounded-full flex items-center justify-center text-white font-bold text-lg mr-3" x-text="user?.name?.charAt(0)"></div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800" x-text="user?.name"></h3>
                            <p class="text-sm text-gray-600" x-text="getRoleDisplay(user?.role)"></p>
                            <p class="text-xs text-gray-500" x-text="user?.unit?.name || 'Acesso Global'"></p>
                        </div>
                    </div>
                </div>

                <!-- Interface Toggle -->
                <div x-show="canToggleInterfaces" class="mb-6">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Alternar Interface</p>
                    <div class="space-y-1">
                        <button @click="switchInterface('mobile')" class="w-full flex items-center px-3 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 011 1v9a1 1 0 01-1 1H5a1 1 0 01-1-1V7z"/>
                            </svg>
                            Interface Mobile
                        </button>
                        <button @click="switchInterface('admin')" class="w-full flex items-center px-3 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 8a2 2 0 110 4 2 2 0 010-4zM10 14a2 2 0 110 4 2 2 0 010-4z"/>
                            </svg>
                            Painel Admin
                        </button>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="hs-accordion-group p-6 w-full flex flex-col flex-wrap" data-hs-accordion-always-open>
                <ul class="space-y-1.5">
                    <!-- Dashboard -->
                    <li>
                        <button @click="currentView = 'dashboard'"
                                :class="currentView === 'dashboard' ? 'bg-blue-100 text-blue-800' : 'text-gray-700 hover:bg-gray-100'"
                                class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm font-medium rounded-lg w-full text-left transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-9 9a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l2.293 2.293a1 1 0 001.414-1.414l-9-9z"/>
                            </svg>
                            Dashboard Executivo
                        </button>
                    </li>

                    <!-- Operations -->
                    <li class="hs-accordion" id="operations-accordion">
                        <button type="button" class="hs-accordion-toggle hs-accordion-active:text-blue-600 hs-accordion-active:hover:bg-transparent w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Operações

                            <svg class="hs-accordion-active:block ms-auto hidden w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m18 15-6-6-6 6"/>
                            </svg>

                            <svg class="hs-accordion-active:hidden ms-auto block w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>

                        <div id="operations-accordion-sub" class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300 hidden">
                            <ul class="pt-2 ps-2">
                                <li>
                                    <button @click="currentView = 'safety-control'" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100 w-full text-left">
                                        Controle de Segurança
                                    </button>
                                </li>
                                <li>
                                    <button @click="currentView = 'machines'" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100 w-full text-left">
                                        Gestão de Máquinas
                                    </button>
                                </li>
                                <li>
                                    <button @click="currentView = 'patients'" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100 w-full text-left">
                                        Gestão de Pacientes
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Reports -->
                    <li class="hs-accordion" id="reports-accordion">
                        <button type="button" class="hs-accordion-toggle hs-accordion-active:text-blue-600 hs-accordion-active:hover:bg-transparent w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414L15 8.414V14a1 1 0 11-2 0V9.414l-1.293 1.293a1 1 0 01-1.414-1.414L12.586 7H8z"/>
                            </svg>
                            Relatórios

                            <svg class="hs-accordion-active:block ms-auto hidden w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m18 15-6-6-6 6"/>
                            </svg>

                            <svg class="hs-accordion-active:hidden ms-auto block w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>

                        <div id="reports-accordion-sub" class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300 hidden">
                            <ul class="pt-2 ps-2">
                                <li>
                                    <button @click="currentView = 'reports-operational'" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100 w-full text-left">
                                        Relatórios Operacionais
                                    </button>
                                </li>
                                <li>
                                    <button @click="currentView = 'reports-quality'" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100 w-full text-left">
                                        Qualidade e Segurança
                                    </button>
                                </li>
                                <li>
                                    <button @click="currentView = 'analytics'" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100 w-full text-left">
                                        Analytics Avançado
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- Logout -->
            <div class="px-6 mt-auto">
                <button @click="logout()" class="w-full flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z"/>
                    </svg>
                    Sair do Sistema
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="w-full lg:ps-64">
            <!-- Top Header -->
            <div class="sticky top-0 inset-x-0 z-20 bg-white border-y px-4 sm:px-6 md:px-8 lg:hidden">
                <div class="flex items-center py-4">
                    <button type="button" class="text-gray-500 hover:text-gray-600" data-hs-overlay="#application-sidebar" aria-controls="application-sidebar" aria-label="Toggle navigation">
                        <span class="sr-only">Toggle Navigation</span>
                        <svg class="w-5 h-5" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Page Header -->
            <header class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900" x-text="getViewTitle()"></h1>
                        <p class="mt-1 text-sm text-gray-600" x-text="getViewSubtitle()"></p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Interface Switcher Component -->
                        @auth
                            <x-interface-switcher currentInterface="preline" :userRole="auth()->user()->role" position="inline" />
                        @else
                            <div class="flex items-center space-x-2 bg-gray-100 rounded-lg px-3 py-2">
                                <span class="text-sm text-gray-600">Faça login para trocar interfaces</span>
                            </div>
                        @endauth

                        <!-- Real-time Status -->
                        <div class="flex items-center space-x-2">
                            <span class="status-indicator status-online"></span>
                            <span class="text-sm text-gray-600">Sistema Online</span>
                        </div>

                        <!-- Notifications -->
                        <div class="hs-dropdown relative inline-flex">
                            <button id="hs-dropdown-notifications" type="button" class="hs-dropdown-toggle relative p-2 text-gray-500 hover:text-gray-600 hover:bg-gray-100 rounded-lg">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                                </svg>
                                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>

                            <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg p-2 mt-2">
                                <div class="py-2 first:pt-0 last:pb-0">
                                    <span class="block py-2 px-3 text-xs font-medium uppercase text-gray-400">
                                        Notificações
                                    </span>
                                    <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100" href="#">
                                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                        Máquina HD-05 em alerta
                                    </a>
                                    <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100" href="#">
                                        <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                        3 checklists pendentes
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="hs-dropdown relative inline-flex">
                            <button id="hs-dropdown-user" type="button" class="hs-dropdown-toggle flex items-center space-x-3 text-left">
                                <div class="w-8 h-8 medical-gradient rounded-full flex items-center justify-center text-white text-sm font-bold" x-text="user?.name?.charAt(0)"></div>
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                </svg>
                            </button>

                            <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg p-2 mt-2">
                                <div class="py-2 first:pt-0 last:pb-0">
                                    <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100" href="#">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zM8 6a2 2 0 114 0v1H8V6z"/>
                                        </svg>
                                        Configurações
                                    </a>
                                    <a @click="logout()" class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-red-600 hover:bg-red-50" href="#">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z"/>
                                        </svg>
                                        Sair
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="p-4 sm:p-6 lg:p-8">
                <!-- Dashboard View -->
                <div x-show="currentView === 'dashboard'" class="space-y-6">
                    <!-- KPI Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Sessions Today -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Sessões Hoje</p>
                                    <p class="text-3xl font-bold text-gray-900">24</p>
                                    <p class="text-sm text-green-600 font-medium">↗ +12% vs ontem</p>
                                </div>
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Active Patients -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Pacientes Ativos</p>
                                    <p class="text-3xl font-bold text-gray-900">156</p>
                                    <p class="text-sm text-green-600 font-medium">+3 novos esta semana</p>
                                </div>
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM5 8a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2V8z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Machines Status -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Máquinas Online</p>
                                    <p class="text-3xl font-bold text-gray-900">8<span class="text-lg text-gray-500">/10</span></p>
                                    <p class="text-sm text-yellow-600 font-medium">2 em manutenção</p>
                                </div>
                                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Alerts -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Alertas Ativos</p>
                                    <p class="text-3xl font-bold text-gray-900">3</p>
                                    <p class="text-sm text-red-600 font-medium">2 críticos</p>
                                </div>
                                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts and Recent Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Chart -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sessões por Período</h3>
                            <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
                                <div class="text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                    </svg>
                                    <p class="text-sm">Gráfico de sessões em tempo real</p>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Atividades Recentes</h3>
                            <div class="space-y-4">
                                <div class="flex items-start space-x-3">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Sessão HD-03 finalizada com sucesso</p>
                                        <p class="text-xs text-gray-500">há 5 minutos</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Máquina HD-05 entrou em manutenção programada</p>
                                        <p class="text-xs text-gray-500">há 15 minutos</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Novo paciente cadastrado no sistema</p>
                                        <p class="text-xs text-gray-500">há 32 minutos</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="w-2 h-2 bg-red-500 rounded-full mt-2"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Alerta de pressão detectado - HD-07</p>
                                        <p class="text-xs text-gray-500">há 1 hora</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Other Views Placeholder -->
                <div x-show="currentView !== 'dashboard'" class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <div class="max-w-md mx-auto">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2" x-text="getViewTitle()"></h3>
                        <p class="text-gray-600 mb-4" x-text="getViewSubtitle()"></p>
                        <p class="text-sm text-gray-500">Esta seção está em desenvolvimento.</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        window.desktopApp = function() {
            return {
                loading: true,
                user: null,
                currentView: 'dashboard',
                canToggleInterfaces: false,

                init() {
                    console.log('Desktop app initializing...');
                    this.checkAuth();

                    // Always hide loading after timeout, even if auth fails
                    setTimeout(() => {
                        console.log('Hiding loading screen...');
                        this.loading = false;
                        // Initialize Preline after app loads
                        if (window.HSStaticMethods) {
                            window.HSStaticMethods.autoInit();
                        }
                    }, 2000);
                },

                async checkAuth() {
                    console.log('Starting auth check...');
                    // First try to get user from server (works with both JWT and session)
                    try {
                        const token = localStorage.getItem('token');
                        const headers = {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        };

                        // Add JWT token if available
                        if (token) {
                            headers['Authorization'] = `Bearer ${token}`;
                        }

                        const response = await fetch('/api/me', {
                            headers: headers
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.user = data.user;
                            this.canToggleInterfaces = ['admin', 'gestor', 'coordenador'].includes(this.user.role);

                            // Verificar se pode acessar desktop
                            if (['field_user', 'tecnico'].includes(this.user.role)) {
                                window.location.href = '/mobile/ionic';
                                return;
                            }
                        } else if (response.status === 401) {
                            // Not authenticated, redirect to login
                            localStorage.removeItem('token');
                            window.location.href = '/login?from=desktop&retry=true';
                        } else {
                            throw new Error(`Auth failed with status: ${response.status}`);
                        }
                    } catch (error) {
                        console.error('Auth check failed:', error);

                        // For 500 errors or network issues, try to show the interface anyway
                        if (error.message.includes('500') || error.message.includes('fetch')) {
                            console.warn('Server error, showing interface without full auth');
                            this.user = { name: 'Usuário', role: 'guest' };
                            return;
                        }

                        localStorage.removeItem('token');
                        window.location.href = '/login?from=desktop&error=auth_failed';
                    }
                },

                async switchInterface(targetInterface) {
                    try {
                        const response = await fetch('/api/smart-route/switch-interface', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ interface: targetInterface })
                        });

                        const data = await response.json();
                        if (data.success) {
                            window.location.href = data.redirect_url;
                        } else {
                            alert(data.error || 'Erro ao alternar interface');
                        }
                    } catch (error) {
                        console.error('Erro ao alternar interface:', error);
                    }
                },

                async logout() {
                    try {
                        localStorage.removeItem('token');
                        await fetch('/logout', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        window.location.href = '/login?logout=true';
                    } catch (error) {
                        window.location.href = '/login?logout=true';
                    }
                },

                getRoleDisplay(role) {
                    const roles = {
                        'admin': 'Administrador',
                        'gestor': 'Gestor Regional',
                        'coordenador': 'Coordenador',
                        'supervisor': 'Supervisor',
                        'tecnico': 'Técnico'
                    };
                    return roles[role] || role;
                },

                getViewTitle() {
                    const titles = {
                        'dashboard': 'Dashboard Executivo',
                        'safety-control': 'Controle de Segurança',
                        'machines': 'Gestão de Máquinas',
                        'patients': 'Gestão de Pacientes',
                        'reports-operational': 'Relatórios Operacionais',
                        'reports-quality': 'Qualidade e Segurança',
                        'analytics': 'Analytics Avançado'
                    };
                    return titles[this.currentView] || 'Dashboard';
                },

                getViewSubtitle() {
                    const subtitles = {
                        'dashboard': 'Visão geral em tempo real do sistema de hemodiálise',
                        'safety-control': 'Monitoramento e controle de protocolos de segurança',
                        'machines': 'Status e manutenção de equipamentos',
                        'patients': 'Cadastro e acompanhamento de pacientes',
                        'reports-operational': 'Relatórios detalhados das operações',
                        'reports-quality': 'Indicadores de qualidade e conformidade',
                        'analytics': 'Insights e métricas avançadas para tomada de decisão'
                    };
                    return subtitles[this.currentView] || 'Sistema de gestão executiva';
                }
            }
        };
    </script>

    <!-- Fallback script in case Alpine.js fails -->
    <script>
        // Emergency fallback to show content if Alpine.js fails
        setTimeout(() => {
            console.log('Emergency fallback check...');
            const loadingScreen = document.querySelector('[x-show="loading"]');
            const mainContent = document.querySelector('[x-show="!loading"]');

            if (loadingScreen && getComputedStyle(loadingScreen).display !== 'none') {
                console.log('Alpine.js may have failed, forcing content to show...');
                loadingScreen.style.display = 'none';
                if (mainContent) {
                    mainContent.style.display = 'block';
                }
            }
        }, 3000);

        // Check if Alpine.js loaded
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if (typeof Alpine === 'undefined') {
                    console.error('Alpine.js not loaded!');
                    const fallbackDiv = document.createElement('div');
                    fallbackDiv.innerHTML = `
                        <div class="fixed top-4 left-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
                            ⚠️ Alpine.js não carregou. Verifique o console para erros.
                        </div>
                    `;
                    document.body.appendChild(fallbackDiv);
                } else {
                    console.log('Alpine.js loaded successfully');
                }
            }, 1000);
        });
    </script>
</body>
</html>