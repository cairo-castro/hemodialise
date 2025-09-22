<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center space-x-2">
                <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                <span>Alternar Interface</span>
            </div>
        </x-slot>

        <x-slot name="description">
            Escolha a interface mais adequada para sua atividade atual
        </x-slot>

        <div class="space-y-4">
            <!-- Current Interface Indicator -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center text-white text-lg">
                        ⚙️
                    </div>
                    <div>
                        <h3 class="font-medium text-blue-900">Interface Atual: Painel Administrativo</h3>
                        <p class="text-sm text-blue-600">Usuário: {{ $userName }} ({{ ucfirst($userRole) }})</p>
                    </div>
                </div>
            </div>

            <!-- Interface Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($availableInterfaces as $interface)
                    <div class="border rounded-lg p-4 hover:shadow-md transition-shadow
                                {{ $interface['key'] === 'admin' ? 'border-blue-200 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">

                        <div class="flex items-start space-x-3">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center text-xl
                                        @if($interface['color'] === 'success') bg-green-500 text-white
                                        @elseif($interface['color'] === 'primary') bg-blue-500 text-white
                                        @elseif($interface['color'] === 'warning') bg-orange-500 text-white
                                        @else bg-gray-500 text-white @endif">
                                {{ $interface['icon'] }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900 truncate">
                                    {{ $interface['name'] }}
                                    @if($interface['key'] === 'admin')
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Atual
                                        </span>
                                    @endif
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $interface['description'] }}</p>

                                @if($interface['key'] !== 'admin')
                                    <button
                                        onclick="switchInterface('{{ $interface['key'] }}', '{{ $interface['url'] }}')"
                                        class="mt-3 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white
                                               @if($interface['color'] === 'success') bg-green-600 hover:bg-green-700
                                               @elseif($interface['color'] === 'primary') bg-blue-600 hover:bg-blue-700
                                               @else bg-gray-600 hover:bg-gray-700 @endif
                                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <x-heroicon-o-arrow-top-right-on-square class="w-3 h-3 mr-1" />
                                        Alternar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Quick Switch Bar -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900">Alteração Rápida</h4>
                        <p class="text-sm text-gray-600">Acesse diretamente outras interfaces</p>
                    </div>

                    <div class="flex space-x-2">
                        <button
                            onclick="switchInterface('ionic', '/mobile/ionic')"
                            class="inline-flex items-center px-3 py-2 border border-green-300 text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            📱 Mobile
                        </button>

                        <button
                            onclick="switchInterface('preline', '/desktop/preline')"
                            class="inline-flex items-center px-3 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            🖥️ Desktop
                        </button>
                    </div>
                </div>
            </div>

            <!-- Auto-Detection Settings -->
            <div class="border-t pt-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            id="auto-detection"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            onchange="toggleAutoDetection(this.checked)">
                        <label for="auto-detection" class="text-sm font-medium text-gray-700">
                            Detecção Automática
                        </label>
                    </div>
                    <p class="text-xs text-gray-500">
                        Redirecionar automaticamente baseado no dispositivo
                    </p>
                </div>
            </div>
        </div>
    </x-filament::section>

    <script>
        function switchInterface(interfaceKey, url) {
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Alterando...';
            button.disabled = true;

            // Call API to switch interface
            fetch('/api/smart-route/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    interface: interfaceKey,
                    save_preference: document.getElementById('auto-detection')?.checked || false
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.redirect_url) {
                    // Store preference if needed
                    if (document.getElementById('auto-detection')?.checked) {
                        localStorage.setItem('interface_preference', interfaceKey);
                    }

                    // Redirect to new interface
                    window.location.href = data.redirect_url;
                } else {
                    throw new Error(data.error || 'Falha ao alternar interface');
                }
            })
            .catch(error => {
                console.error('Interface switch failed:', error);
                alert('Erro ao alternar interface: ' + error.message);

                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function toggleAutoDetection(enabled) {
            if (enabled) {
                localStorage.setItem('interface_auto_detection', 'true');
            } else {
                localStorage.removeItem('interface_auto_detection');
                localStorage.removeItem('interface_preference');
            }
        }

        // Load auto-detection setting
        document.addEventListener('DOMContentLoaded', function() {
            const autoDetection = localStorage.getItem('interface_auto_detection') === 'true';
            const checkbox = document.getElementById('auto-detection');
            if (checkbox) {
                checkbox.checked = autoDetection;
            }

            // Debug: verificar se CSRF está disponível
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('Interface Switcher Widget loaded', {
                csrf_available: !!csrfToken,
                csrf_token: csrfToken ? csrfToken.substring(0, 10) + '...' : 'not found'
            });
        });
    </script>
</x-filament-widgets::widget>