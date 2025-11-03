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
          :disabled="isSavingPreferences"
          class="mt-6 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isSavingPreferences ? 'Salvando...' : 'Salvar Preferências' }}
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import {
  Cog6ToothIcon,
  BellIcon,
} from '@heroicons/vue/24/outline';

const activeTab = ref('general');
const currentTheme = ref('light');
const isLoadingPreferences = ref(false);
const isSavingPreferences = ref(false);

const tabs = [
  { id: 'general', label: 'Geral', icon: Cog6ToothIcon },
  { id: 'notifications', label: 'Notificações', icon: BellIcon },
];

const notifications = ref({
  newChecklists: true,
  maintenance: true,
  weeklyReports: false,
  systemUpdates: true,
});

onMounted(async () => {
  currentTheme.value = localStorage.getItem('theme') || 'light';
  await fetchNotificationPreferences();
});

async function fetchNotificationPreferences() {
  try {
    isLoadingPreferences.value = true;
    const response = await axios.get('/api/notification-preferences');

    if (response.data.success && response.data.preferences) {
      const prefs = response.data.preferences;
      notifications.value = {
        newChecklists: prefs.email_new_checklists,
        maintenance: prefs.email_maintenance,
        weeklyReports: prefs.email_weekly_reports,
        systemUpdates: prefs.email_system_updates,
      };
    }
  } catch (error) {
    console.error('Erro ao carregar preferências:', error);
    showErrorToast('Erro ao carregar preferências de notificação');
  } finally {
    isLoadingPreferences.value = false;
  }
}

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

async function saveNotificationSettings() {
  if (isSavingPreferences.value) return;

  try {
    isSavingPreferences.value = true;

    const response = await axios.put('/api/notification-preferences', {
      email_new_checklists: notifications.value.newChecklists,
      email_maintenance: notifications.value.maintenance,
      email_weekly_reports: notifications.value.weeklyReports,
      email_system_updates: notifications.value.systemUpdates,
    });

    if (response.data.success) {
      showSuccessToast('Preferências de notificação salvas com sucesso!');
    }
  } catch (error) {
    console.error('Erro ao salvar preferências:', error);
    showErrorToast(error.response?.data?.message || 'Erro ao salvar preferências');
  } finally {
    isSavingPreferences.value = false;
  }
}

function showSuccessToast(message) {
  const toast = document.createElement('div');
  toast.className = 'fixed top-4 right-4 z-[100] px-6 py-4 bg-green-600 text-white rounded-lg shadow-lg flex items-center gap-3 animate-slide-in-right';
  toast.innerHTML = `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>
    <span class="font-medium">${message}</span>
  `;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'slide-out-right 0.3s ease-out forwards';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

function showErrorToast(message) {
  const toast = document.createElement('div');
  toast.className = 'fixed top-4 right-4 z-[100] px-6 py-4 bg-red-600 text-white rounded-lg shadow-lg flex items-center gap-3 animate-slide-in-right';
  toast.innerHTML = `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
    </svg>
    <span class="font-medium">${message}</span>
  `;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'slide-out-right 0.3s ease-out forwards';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
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
