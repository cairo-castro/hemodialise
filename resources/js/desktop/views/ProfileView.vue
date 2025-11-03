<template>
  <div class="profile-view space-y-6">
    <!-- Profile Header -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
      <div class="h-32 bg-gradient-to-r from-primary-500 to-primary-600"></div>
      <div class="px-8 pb-8">
        <div class="flex flex-col md:flex-row md:items-end md:space-x-6 -mt-16">
          <!-- Avatar -->
          <div class="relative">
            <div class="w-32 h-32 rounded-xl bg-white dark:bg-gray-900 p-2 shadow-lg">
              <div class="w-full h-full rounded-lg bg-primary-600 flex items-center justify-center text-white text-4xl font-bold">
                {{ userInitials }}
              </div>
            </div>
            <button
              class="absolute bottom-2 right-2 w-8 h-8 bg-white dark:bg-gray-800 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              title="Alterar foto"
            >
              <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </button>
          </div>

          <!-- User Info -->
          <div class="flex-1 mt-4 md:mt-0">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ userData.name }}</h1>
            <p class="text-gray-500 dark:text-gray-400">{{ userData.email }}</p>
            <div class="flex flex-wrap gap-2 mt-3">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/20 dark:text-primary-400">
                {{ getRoleName(userData.role) }}
              </span>
              <span v-if="userData.unit" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
                {{ userData.unit.name }}
              </span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3 mt-4 md:mt-0">
            <button
              v-if="!isEditing"
              @click="startEditing"
              class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium"
            >
              Editar Perfil
            </button>
            <template v-else>
              <button
                @click="cancelEditing"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium"
              >
                Cancelar
              </button>
              <button
                @click="saveProfile"
                :disabled="isSaving"
                class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ isSaving ? 'Salvando...' : 'Salvar' }}
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Profile Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information -->
        <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Informações Pessoais
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome Completo</label>
              <input
                v-model="formData.name"
                type="text"
                :disabled="!isEditing"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white disabled:bg-gray-50 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
              <input
                v-model="formData.email"
                type="email"
                :disabled="!isEditing"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white disabled:bg-gray-50 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Telefone</label>
              <input
                v-model="formData.phone"
                type="tel"
                :disabled="!isEditing"
                placeholder="(00) 00000-0000"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white disabled:bg-gray-50 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cargo</label>
              <input
                :value="getRoleName(userData.role)"
                type="text"
                disabled
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white cursor-not-allowed"
              />
            </div>
          </div>
        </div>

        <!-- Change Password -->
        <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Alterar Senha
          </h2>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Senha Atual</label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nova Senha</label>
                <input
                  v-model="passwordForm.new_password"
                  type="password"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirmar Senha</label>
                <input
                  v-model="passwordForm.confirm_password"
                  type="password"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
                />
              </div>
            </div>

            <button
              @click="changePassword"
              :disabled="!canChangePassword"
              class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Alterar Senha
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Activity Stats -->
        <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Estatísticas</h2>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Checklists Realizados</span>
              <span class="text-lg font-bold text-gray-900 dark:text-white">{{ stats.checklists }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Pacientes Atendidos</span>
              <span class="text-lg font-bold text-gray-900 dark:text-white">{{ stats.patients }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600 dark:text-gray-400">Dias Trabalhados</span>
              <span class="text-lg font-bold text-gray-900 dark:text-white">{{ stats.days }}</span>
            </div>
          </div>
        </div>

        <!-- Account Info -->
        <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informações da Conta</h2>
          <div class="space-y-3">
            <div>
              <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data de Cadastro</span>
              <p class="text-sm text-gray-900 dark:text-white mt-1">{{ formatDate(userData.created_at) }}</p>
            </div>
            <div>
              <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Último Acesso</span>
              <p class="text-sm text-gray-900 dark:text-white mt-1">{{ formatDate(userData.last_login) }}</p>
            </div>
            <div>
              <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status da Conta</span>
              <span class="inline-flex items-center mt-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                Ativa
              </span>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ações Rápidas</h2>
          <div class="space-y-2">
            <button
              @click="navigateTo('/desktop/settings')"
              class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
            >
              <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Configurações
            </button>
            <button
              @click="navigateTo('/desktop/help')"
              class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
            >
              <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Ajuda e Suporte
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const userData = ref({
  name: 'Carregando...',
  email: 'email@example.com',
  role: 'user',
  phone: '',
  unit: null,
  created_at: new Date(),
  last_login: new Date(),
});

const formData = ref({
  name: '',
  email: '',
  phone: '',
});

const passwordForm = ref({
  current_password: '',
  new_password: '',
  confirm_password: '',
});

const stats = ref({
  checklists: 0,
  patients: 0,
  days: 0,
});

const isEditing = ref(false);
const isSaving = ref(false);

const userInitials = computed(() => {
  if (!userData.value?.name || userData.value.name === 'Carregando...') return 'U';
  return userData.value.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
});

const canChangePassword = computed(() => {
  return passwordForm.value.current_password &&
         passwordForm.value.new_password &&
         passwordForm.value.confirm_password &&
         passwordForm.value.new_password === passwordForm.value.confirm_password;
});

onMounted(async () => {
  await loadUserData();
  await loadUserStats();
});

async function loadUserData() {
  try {
    const response = await fetch('/api/me', {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' }
    });

    if (response.ok) {
      const data = await response.json();
      userData.value = data.user;
      formData.value = {
        name: data.user.name,
        email: data.user.email,
        phone: data.user.phone || '',
      };
    }
  } catch (error) {
    console.error('Error loading user data:', error);
  }
}

async function loadUserStats() {
  // Mock data - replace with actual API call
  stats.value = {
    checklists: 142,
    patients: 87,
    days: 245,
  };
}

function startEditing() {
  isEditing.value = true;
}

function cancelEditing() {
  isEditing.value = false;
  formData.value = {
    name: userData.value.name,
    email: userData.value.email,
    phone: userData.value.phone || '',
  };
}

async function saveProfile() {
  isSaving.value = true;

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));

    userData.value = {
      ...userData.value,
      ...formData.value
    };

    isEditing.value = false;
    showSuccessToast('Perfil atualizado com sucesso!');
  } catch (error) {
    console.error('Error saving profile:', error);
    showErrorToast('Erro ao salvar perfil. Tente novamente.');
  } finally {
    isSaving.value = false;
  }
}

async function changePassword() {
  if (!canChangePassword.value) return;

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));

    passwordForm.value = {
      current_password: '',
      new_password: '',
      confirm_password: '',
    };

    showSuccessToast('Senha alterada com sucesso!');
  } catch (error) {
    console.error('Error changing password:', error);
    showErrorToast('Erro ao alterar senha. Tente novamente.');
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
  }, 4000);
}

function getRoleName(role) {
  const roles = {
    admin: 'Administrador',
    manager: 'Gerente',
    field_user: 'Usuário de Campo',
  };
  return roles[role] || 'Usuário';
}

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  });
}

function navigateTo(path) {
  router.push(path);
}
</script>

<style scoped>
.profile-view {
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
