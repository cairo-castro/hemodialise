<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <title>Login - Sistema Hemodiálise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .gradient-bg {
            background: linear-gradient(-45deg, #2563eb, #1e40af, #3b82f6, #60a5fa);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
        }

        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes shine {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }

        .shine-effect {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            background-size: 200% 100%;
            animation: shine 3s infinite;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen overflow-x-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 left-0 w-96 h-96 bg-white opacity-5 rounded-full blur-3xl transform -translate-x-1/2 -translate-y-1/2 float"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-white opacity-5 rounded-full blur-3xl transform translate-x-1/2 translate-y-1/2 float" style="animation-delay: 3s;"></div>
        <div class="absolute top-1/2 left-1/2 w-96 h-96 bg-white opacity-3 rounded-full blur-3xl transform -translate-x-1/2 -translate-y-1/2 float" style="animation-delay: 1.5s;"></div>
    </div>

    <div class="min-h-screen flex items-center justify-center px-4 py-12 relative z-10">
        <div class="max-w-6xl w-full">
            <!-- Logo and Title -->
            <div class="text-center mb-16 fade-in-up">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-3xl shadow-2xl mb-8 pulse-slow relative overflow-hidden">
                    <div class="absolute inset-0 shine-effect"></div>
                    <svg class="w-14 h-14 text-blue-600 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-5xl md:text-6xl font-bold text-white mb-4 tracking-tight">
                    Sistema de Hemodiálise
                </h1>
                <p class="text-blue-100 text-xl">Controle de Qualidade e Segurança</p>
                <div class="w-24 h-1 bg-white opacity-30 mx-auto mt-6 rounded-full"></div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid md:grid-cols-2 gap-8 mb-12 fade-in-up delay-1">
                <!-- Desktop/Admin Card -->
                <div class="glass-effect rounded-3xl shadow-2xl p-10 card-hover relative overflow-hidden group">
                    <!-- Hover Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="relative z-10">
                        <div class="flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl mb-8 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>

                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Painel Desktop</h2>
                        <p class="text-gray-600 mb-8 leading-relaxed text-lg">
                            Acesse o sistema completo de gestão, relatórios analíticos, gráficos interativos e administração de usuários.
                        </p>

                        <div class="space-y-4 mb-10">
                            <div class="flex items-center text-base text-gray-700 group/item">
                                <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-lg mr-4 group-hover/item:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Relatórios completos e exportação</span>
                            </div>
                            <div class="flex items-center text-base text-gray-700 group/item">
                                <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-lg mr-4 group-hover/item:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Gráficos e análises avançadas</span>
                            </div>
                            <div class="flex items-center text-base text-gray-700 group/item">
                                <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-lg mr-4 group-hover/item:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Gestão de usuários e unidades</span>
                            </div>
                        </div>

                        <a href="/admin" class="block w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold px-8 py-5 rounded-2xl transition-all duration-300 text-center shadow-xl hover:shadow-2xl transform hover:-translate-y-1 relative overflow-hidden group/btn">
                            <div class="absolute inset-0 bg-white opacity-0 group-hover/btn:opacity-20 transition-opacity"></div>
                            <div class="flex items-center justify-center relative z-10">
                                <span class="text-lg">Acessar Painel Admin</span>
                                <svg class="w-6 h-6 ml-3 group-hover/btn:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </a>

                        <div class="mt-6 flex items-center justify-center space-x-2 text-sm text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span>Para administradores e gestores</span>
                        </div>
                    </div>
                </div>

                <!-- Mobile/Field Card -->
                <div class="glass-effect rounded-3xl shadow-2xl p-10 card-hover relative overflow-hidden group">
                    <!-- Hover Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="relative z-10">
                        <div class="flex items-center justify-center w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl mb-8 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>

                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Interface Mobile</h2>
                        <p class="text-gray-600 mb-8 leading-relaxed text-lg">
                            Interface otimizada para dispositivos móveis. Ideal para registro de checklists e controles em campo.
                        </p>

                        <div class="space-y-4 mb-10">
                            <div class="flex items-center text-base text-gray-700 group/item">
                                <div class="flex items-center justify-center w-8 h-8 bg-purple-100 rounded-lg mr-4 group-hover/item:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Registro rápido de checklists</span>
                            </div>
                            <div class="flex items-center text-base text-gray-700 group/item">
                                <div class="flex items-center justify-center w-8 h-8 bg-purple-100 rounded-lg mr-4 group-hover/item:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Controle de limpeza e desinfecção</span>
                            </div>
                            <div class="flex items-center text-base text-gray-700 group/item">
                                <div class="flex items-center justify-center w-8 h-8 bg-purple-100 rounded-lg mr-4 group-hover/item:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="font-medium">Otimizado para uso offline</span>
                            </div>
                        </div>

                        <a href="/mobile" class="block w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold px-8 py-5 rounded-2xl transition-all duration-300 text-center shadow-xl hover:shadow-2xl transform hover:-translate-y-1 relative overflow-hidden group/btn">
                            <div class="absolute inset-0 bg-white opacity-0 group-hover/btn:opacity-20 transition-opacity"></div>
                            <div class="flex items-center justify-center relative z-10">
                                <span class="text-lg">Acessar Interface Mobile</span>
                                <svg class="w-6 h-6 ml-3 group-hover/btn:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </a>

                        <div class="mt-6 flex items-center justify-center space-x-2 text-sm text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>Para técnicos e usuários de campo</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="text-center fade-in-up delay-3">
                <div class="glass-effect rounded-2xl p-8 inline-block shadow-xl">
                    <div class="flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <p class="text-white text-lg font-bold">
                            Sistema de Controle de Qualidade
                        </p>
                    </div>
                    <p class="text-blue-100 text-sm">
                        Secretaria de Estado da Saúde do Maranhão
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
