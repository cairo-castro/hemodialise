<template>
  <div class="settings-view space-y-6">
    <!-- Settings Navigation Tabs -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800">
      <div class="border-b border-gray-200 dark:border-gray-800">
        <nav class="flex space-x-8 px-6" aria-label="Tabs">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors"
            :class="[
              activeTab === tab.id
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'
            ]"
          >
            <component :is="tab.icon" class="w-5 h-5 inline-block mr-2" />
            {{ tab.label }}
          </button>
        </nav>
      </div>
    </div>

    <!-- General Settings -->
    <div v-if="activeTab === 'general'" class="space-y-6">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aparência</h3>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Tema</label>
            <div class="flex items-center space-x-4">
              <button
                @click="changeTheme('light')"
                class="flex items-center px-4 py-3 border-2 rounded-lg transition-all"
                :class="[
                  currentTheme === 'light'
                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                ]"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Claro
              </button>

              <button
                @click="changeTheme('dark')"
                class="flex items-center px-4 py-3 border-2 rounded-lg transition-all"
                :class="[
                  currentTheme === 'dark'
                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                ]"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                Escuro
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Idioma</label>
            <select class="w-full md:w-auto px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
              <option value="pt-BR">Português (Brasil)</option>
              <option value="en">English</option>
              <option value="es">Español</option>
            </select>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Unidade Padrão</h3>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Selecione sua unidade de trabalho padrão
          </label>
          <select class="w-full md:w-auto px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
            <option>Hospital Regional de São Luís</option>
            <option>UPA Centro</option>
            <option>Clínica Municipal Norte</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Notifications Settings -->
    <div v-if="activeTab === 'notifications'" class="space-y-6">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Notificações por Email</h3>

        <div class="space-y-4">
          <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">Novos Checklists</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Receber email quando um novo checklist for criado</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="notifications.newChecklists" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>

          <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">Manutenção de Máquinas</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Alertas de manutenção preventiva agendada</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="notifications.maintenance" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>

          <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">Relatórios Semanais</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Resumo semanal de atividades da unidade</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="notifications.weeklyReports" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>

          <div class="flex items-center justify-between py-3">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">Atualizações do Sistema</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Novidades e melhorias do sistema</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="notifications.systemUpdates" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
        </div>

        <button
          @click="saveNotificationSettings"
          class="mt-6 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium"
        >
          Salvar Preferências
        </button>
      </div>
    </div>

    <!-- Data & Privacy Settings -->
    <div v-if="activeTab === 'privacy'" class="space-y-6">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Privacidade de Dados</h3>

        <div class="space-y-4">
          <div class="flex items-start space-x-3">
            <input type="checkbox" id="data-collection" class="mt-1 w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
            <div>
              <label for="data-collection" class="font-medium text-gray-900 dark:text-white">Coleta de Dados de Uso</label>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Permitir coleta anônima de dados para melhorar a experiência do sistema
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Exportar Dados</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          Baixe uma cópia de todos os seus dados armazenados no sistema
        </p>
        <button class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium">
          Exportar Meus Dados
        </button>
      </div>

      <div class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-6">
        <h3 class="text-lg font-semibold text-red-900 dark:text-red-400 mb-4">Zona de Perigo</h3>
        <p class="text-sm text-red-700 dark:text-red-300 mb-4">
          Ações irreversíveis. Tenha cuidado ao prosseguir.
        </p>
        <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
          Deletar Conta
        </button>
      </div>
    </div>

    <!-- System Settings (Admin Only) -->
    <div v-if="activeTab === 'system'" class="space-y-6">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Backup e Manutenção</h3>

        <div class="space-y-4">
          <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">Backup Automático</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Backup diário do banco de dados às 03:00</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
              Ativo
            </span>
          </div>

          <div class="flex items-center justify-between py-3">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">Último Backup</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">02 de novembro de 2025, 03:00</p>
            </div>
            <button class="px-3 py-1 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded transition-colors">
              Fazer Backup Agora
            </button>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Cache e Performance</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <button class="flex flex-col items-center p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg hover:border-primary-500 dark:hover:border-primary-500 transition-colors">
            <svg class="w-8 h-8 text-gray-600 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="font-medium text-gray-900 dark:text-white">Limpar Cache</span>
          </button>

          <button class="flex flex-col items-center p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg hover:border-primary-500 dark:hover:border-primary-500 transition-colors">
            <svg class="w-8 h-8 text-gray-600 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span class="font-medium text-gray-900 dark:text-white">Otimizar BD</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import {
  Cog6ToothIcon,
  BellIcon,
  ShieldCheckIcon,
  ServerIcon,
} from '@heroicons/vue/24/outline';

const activeTab = ref('general');
const currentTheme = ref('light');

const tabs = [
  { id: 'general', label: 'Geral', icon: Cog6ToothIcon },
  { id: 'notifications', label: 'Notificações', icon: BellIcon },
  { id: 'privacy', label: 'Privacidade', icon: ShieldCheckIcon },
  { id: 'system', label: 'Sistema', icon: ServerIcon },
];

const notifications = ref({
  newChecklists: true,
  maintenance: true,
  weeklyReports: false,
  systemUpdates: true,
});

onMounted(() => {
  currentTheme.value = localStorage.getItem('theme') || 'light';
});

function changeTheme(theme) {
  currentTheme.value = theme;
  localStorage.setItem('theme', theme);

  const html = document.documentElement;
  if (theme === 'dark') {
    html.classList.add('dark');
  } else {
    html.classList.remove('dark');
  }
}

function saveNotificationSettings() {
  alert('Preferências de notificação salvas com sucesso!');
}
</script>

<style scoped>
.settings-view {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
