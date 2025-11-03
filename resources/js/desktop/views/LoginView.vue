<template>
  <div class="login-view min-h-screen flex items-center justify-center px-4 bg-white dark:bg-gray-950">
    <!-- Login Container -->
    <div class="w-full max-w-sm">
      <!-- Logo/Brand -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 rounded-xl mb-6">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Entrar</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Sistema de Gestão de Hemodiálise</p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Email Field -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Email
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            autocomplete="email"
            class="block w-full px-3 py-2 text-sm bg-white dark:bg-gray-900 border rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus:ring-blue-500"
            :class="errors.email
              ? 'border-red-500 dark:border-red-500'
              : 'border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white'"
            placeholder="nome@exemplo.com"
          />
          <p v-if="errors.email" class="text-xs text-red-600 dark:text-red-400 mt-1.5">
            {{ errors.email }}
          </p>
        </div>

        <!-- Password Field -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Senha
          </label>
          <div class="relative">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              autocomplete="current-password"
              class="block w-full px-3 py-2 pr-10 text-sm bg-white dark:bg-gray-900 border rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus:ring-blue-500"
              :class="errors.password
                ? 'border-red-500 dark:border-red-500'
                : 'border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white'"
              placeholder="••••••••"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <svg v-if="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
              </svg>
            </button>
          </div>
          <p v-if="errors.password" class="text-xs text-red-600 dark:text-red-400 mt-1.5">
            {{ errors.password }}
          </p>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
          <input
            id="remember"
            v-model="form.remember"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-600 border-gray-300 dark:border-gray-600 rounded"
          />
          <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
            Lembrar-me
          </label>
        </div>

        <!-- Error Alert -->
        <div v-if="errors.general" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
          <p class="text-sm text-red-800 dark:text-red-200">{{ errors.general }}</p>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ loading ? 'Entrando...' : 'Entrar' }}</span>
        </button>
      </form>

      <!-- Footer Links -->
      <div class="mt-8 text-center space-y-3">
        <div class="flex items-center justify-center gap-3 text-xs text-gray-600 dark:text-gray-400">
          <a href="/mobile" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
            Acesso Móvel
          </a>
          <span>•</span>
          <a href="/admin" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
            Painel Admin
          </a>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-500">
          © 2025 Sistema de Hemodiálise
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const emit = defineEmits(['login-success']);

const loading = ref(false);
const showPassword = ref(false);

const form = reactive({
  email: '',
  password: '',
  remember: false,
});

const errors = reactive({
  email: '',
  password: '',
  general: '',
});

async function handleSubmit() {
  // Clear previous errors
  errors.email = '';
  errors.password = '';
  errors.general = '';

  loading.value = true;

  try {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const response = await fetch('/login', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        email: form.email,
        password: form.password,
        remember: form.remember,
      }),
    });

    const data = await response.json();

    if (response.ok) {
      emit('login-success', data.user);
    } else {
      // Handle validation errors
      if (data.errors) {
        errors.email = data.errors.email?.[0] || '';
        errors.password = data.errors.password?.[0] || '';
      } else {
        errors.general = data.message || 'Erro ao fazer login. Tente novamente.';
      }
    }
  } catch (error) {
    console.error('Login error:', error);
    errors.general = 'Erro de conexão. Verifique sua internet e tente novamente.';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.login-view {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
