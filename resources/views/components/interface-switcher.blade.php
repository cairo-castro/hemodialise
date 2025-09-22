{{-- Interface Switcher Component --}}
@props(['currentInterface' => 'auto', 'userRole' => null, 'position' => 'top-right'])

<div x-data="interfaceSwitcher('{{ $currentInterface }}', '{{ $userRole }}')"
     class="interface-switcher {{ $position === 'top-right' ? 'fixed top-4 right-4 z-50' : '' }}">

    {{-- Mobile/Compact View --}}
    <div class="md:hidden">
        <button @click="showMobileMenu = !showMobileMenu"
                class="flex items-center space-x-2 bg-white/90 backdrop-blur-sm border border-gray-200 rounded-lg px-3 py-2 shadow-sm hover:bg-white transition-all">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
            </svg>
            <span class="text-xs font-medium text-gray-700" x-text="getCurrentInterfaceName()"></span>
            <svg class="w-3 h-3 text-gray-400 transition-transform" :class="showMobileMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        {{-- Mobile Dropdown --}}
        <div x-show="showMobileMenu"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-1 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-1 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.away="showMobileMenu = false"
             class="absolute top-full right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">

            <div class="px-3 py-2 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Alternar Interface</h3>
                <p class="text-xs text-gray-500">Escolha como visualizar o sistema</p>
            </div>

            <template x-for="interface in availableInterfaces" :key="interface.key">
                <button @click="switchInterface(interface.key)"
                        class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 transition-colors"
                        :class="currentInterface === interface.key ? 'bg-blue-50 text-blue-700' : 'text-gray-700'">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                             :class="interface.bgColor">
                            <span x-html="interface.icon" class="text-white text-sm"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium" x-text="interface.name"></div>
                            <div class="text-xs text-gray-500 truncate" x-text="interface.description"></div>
                        </div>
                        <div x-show="currentInterface === interface.key" class="text-blue-500">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </button>
            </template>

            <div class="px-3 py-2 border-t border-gray-100 mt-2">
                <label class="flex items-center space-x-2">
                    <input type="checkbox"
                           x-model="savePreference"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-xs text-gray-600">Lembrar preferência</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Desktop View --}}
    <div class="hidden md:block">
        <div class="flex items-center space-x-2 bg-white/95 backdrop-blur-sm border border-gray-200 rounded-lg px-4 py-2 shadow-sm">
            {{-- Current Interface Indicator --}}
            <div class="flex items-center space-x-2">
                <div class="w-6 h-6 rounded-md flex items-center justify-center"
                     :class="getCurrentInterface()?.bgColor || 'bg-gray-500'">
                    <span x-html="getCurrentInterface()?.icon || '?'" class="text-white text-xs"></span>
                </div>
                <span class="text-sm font-medium text-gray-700" x-text="getCurrentInterfaceName()"></span>
            </div>

            {{-- Interface Buttons --}}
            <div class="flex items-center space-x-1 border-l border-gray-200 pl-3">
                <template x-for="interface in availableInterfaces" :key="interface.key">
                    <button @click="switchInterface(interface.key)"
                            :title="interface.name + ' - ' + interface.description"
                            class="w-8 h-8 rounded-md flex items-center justify-center transition-all"
                            :class="currentInterface === interface.key
                                ? interface.bgColor + ' shadow-sm'
                                : 'bg-gray-100 hover:bg-gray-200 text-gray-600'">
                        <span x-html="interface.icon"
                              :class="currentInterface === interface.key ? 'text-white' : 'text-gray-600'"
                              class="text-xs"></span>
                    </button>
                </template>
            </div>

            {{-- Settings --}}
            <div class="border-l border-gray-200 pl-3">
                <button @click="showSettings = !showSettings"
                        class="w-6 h-6 rounded-md flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>

                {{-- Settings Dropdown --}}
                <div x-show="showSettings"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-1 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-1 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.away="showSettings = false"
                     class="absolute top-full right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">

                    <div class="px-3 py-2 border-b border-gray-100">
                        <h4 class="text-sm font-medium text-gray-900">Preferências</h4>
                    </div>

                    <label class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 cursor-pointer">
                        <span class="text-sm text-gray-700">Lembrar interface</span>
                        <input type="checkbox"
                               x-model="savePreference"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </label>

                    <button @click="resetToAuto()"
                            class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span>Detecção automática</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Overlay --}}
    <div x-show="switching"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-[100]">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3 shadow-xl">
            <div class="animate-spin rounded-full h-8 w-8 border-2 border-blue-500 border-t-transparent"></div>
            <span class="text-gray-700 font-medium">Alterando interface...</span>
        </div>
    </div>
</div>

<script>
function interfaceSwitcher(currentInterface, userRole) {
    return {
        currentInterface: currentInterface,
        userRole: userRole,
        showMobileMenu: false,
        showSettings: false,
        savePreference: false,
        switching: false,

        availableInterfaces: [
            {
                key: 'ionic',
                name: 'Mobile',
                description: 'Interface otimizada para dispositivos móveis',
                icon: '📱',
                bgColor: 'bg-green-500',
                url: '/mobile/ionic'
            },
            {
                key: 'preline',
                name: 'Desktop',
                description: 'Interface executiva para gestão',
                icon: '🖥️',
                bgColor: 'bg-blue-500',
                url: '/desktop/preline'
            },
            {
                key: 'admin',
                name: 'Admin',
                description: 'Painel administrativo Filament',
                icon: '⚙️',
                bgColor: 'bg-purple-500',
                url: '/admin'
            }
        ],

        init() {
            // Filter interfaces based on user role
            this.filterAvailableInterfaces();

            // Load saved preference
            const saved = localStorage.getItem('interface_preference_save');
            this.savePreference = saved === 'true';

            // Load user preferences from server
            this.loadUserPreferences();

            // Setup keyboard shortcuts
            this.setupKeyboardShortcuts();

            // Setup real-time updates
            this.setupRealTimeUpdates();
        },

        filterAvailableInterfaces() {
            // All roles can access mobile and desktop
            // Only admin, gestor, and coordenador can access admin panel
            if (!['admin', 'gestor', 'coordenador'].includes(this.userRole)) {
                this.availableInterfaces = this.availableInterfaces.filter(i => i.key !== 'admin');
            }
        },

        getCurrentInterface() {
            return this.availableInterfaces.find(i => i.key === this.currentInterface);
        },

        getCurrentInterfaceName() {
            const current = this.getCurrentInterface();
            return current ? current.name : 'Auto';
        },

        async switchInterface(interfaceKey) {
            if (interfaceKey === this.currentInterface) return;

            this.switching = true;
            this.showMobileMenu = false;
            this.showSettings = false;

            try {
                // Call API to switch interface
                const response = await fetch('/api/smart-route/switch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
                    },
                    body: JSON.stringify({
                        interface: interfaceKey,
                        save_preference: this.savePreference
                    })
                });

                const data = await response.json();

                if (response.ok && data.redirect_url) {
                    // Store preference
                    if (this.savePreference) {
                        localStorage.setItem('interface_preference', interfaceKey);
                        localStorage.setItem('interface_preference_save', 'true');
                    }

                    // Redirect to new interface
                    window.location.href = data.redirect_url;
                } else {
                    throw new Error(data.error || 'Falha ao alternar interface');
                }
            } catch (error) {
                console.error('Interface switch failed:', error);
                alert('Erro ao alternar interface: ' + error.message);
                this.switching = false;
            }
        },

        async resetToAuto() {
            this.switching = true;

            try {
                const response = await fetch('/api/smart-route/reset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    localStorage.removeItem('interface_preference');
                    localStorage.removeItem('interface_preference_save');
                    this.savePreference = false;
                    this.showSettings = false;

                    // Redirect to home for auto-detection
                    window.location.href = data.redirect_url || '/';
                } else {
                    throw new Error(data.error || 'Falha ao resetar preferências');
                }
            } catch (error) {
                console.error('Reset preferences failed:', error);
                alert('Erro ao resetar preferências: ' + error.message);
                this.switching = false;
            }
        },

        async loadUserPreferences() {
            try {
                const response = await fetch('/api/smart-route/preferences', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.user) {
                        this.userRole = data.user.role;
                        this.currentInterface = data.user.current_interface;

                        // Update available interfaces based on server response
                        this.availableInterfaces = this.availableInterfaces.filter(interface =>
                            data.user.allowed_interfaces.includes(interface.key)
                        );
                    }
                }
            } catch (error) {
                console.error('Failed to load user preferences:', error);
            }
        },

        setupKeyboardShortcuts() {
            document.addEventListener('keydown', (event) => {
                // Alt + 1,2,3 for quick interface switching
                if (event.altKey && !event.ctrlKey && !event.shiftKey) {
                    event.preventDefault();

                    switch (event.key) {
                        case '1':
                            if (this.availableInterfaces[0]) {
                                this.switchInterface(this.availableInterfaces[0].key);
                            }
                            break;
                        case '2':
                            if (this.availableInterfaces[1]) {
                                this.switchInterface(this.availableInterfaces[1].key);
                            }
                            break;
                        case '3':
                            if (this.availableInterfaces[2]) {
                                this.switchInterface(this.availableInterfaces[2].key);
                            }
                            break;
                    }
                }
            });
        },

        setupRealTimeUpdates() {
            // Listen for custom events from other parts of the app
            window.addEventListener('interface-switch-request', (event) => {
                this.switchInterface(event.detail.interface);
            });

            // Listen for preference changes
            window.addEventListener('storage', (event) => {
                if (event.key === 'interface_preference') {
                    this.loadUserPreferences();
                }
            });

            // Check for updates every 30 seconds
            setInterval(() => {
                this.loadUserPreferences();
            }, 30000);
        }
    };
}
</script>

<style>
.interface-switcher .bg-green-500 { background-color: #10b981; }
.interface-switcher .bg-blue-500 { background-color: #3b82f6; }
.interface-switcher .bg-purple-500 { background-color: #8b5cf6; }
.interface-switcher .bg-gray-500 { background-color: #6b7280; }
</style>