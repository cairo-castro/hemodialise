<template>
  <div class="login-view min-h-screen flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-950">
    <!-- Auth Card -->
    <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-8">
      <!-- Header -->
      <div class="flex flex-col items-center mb-8">
        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Login</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center mt-2">
          Digite suas credenciais para acessar sua conta
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="space-y-5">
        <!-- Email Field -->
        <div class="space-y-2">
          <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Email
          </label>
          <input
            id="email"
            v-model="formData.email"
            type="email"
            required
            autocomplete="email"
            placeholder="Digite seu email"
            class="block w-full px-3.5 py-2.5 text-sm bg-white dark:bg-gray-950 border rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600"
            :class="errors.email
              ? 'border-red-500 dark:border-red-500 focus:ring-red-500'
              : 'border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white'"
          />
          <p v-if="errors.email" class="text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ errors.email }}
          </p>
        </div>

        <!-- Password Field -->
        <div class="space-y-2">
          <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Senha
          </label>
          <div class="relative">
            <input
              id="password"
              v-model="formData.password"
              :type="showPassword ? 'text' : 'password'"
              required
              autocomplete="current-password"
              placeholder="Digite sua senha"
              class="block w-full px-3.5 py-2.5 pr-10 text-sm bg-white dark:bg-gray-950 border rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600"
              :class="errors.password
                ? 'border-red-500 dark:border-red-500 focus:ring-red-500'
                : 'border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white'"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
              tabindex="-1"
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
          <p v-if="errors.password" class="text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ errors.password }}
          </p>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center">
          <input
            id="remember"
            v-model="formData.remember"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded cursor-pointer"
          />
          <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
            Lembrar-me
          </label>
        </div>

        <!-- Error Alert -->
        <div v-if="errors.general" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <p class="ml-3 text-sm text-red-800 dark:text-red-200">{{ errors.general }}</p>
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
        >
          <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ loading ? 'Entrando...' : 'Entrar' }}</span>
        </button>
      </form>

      <!-- Footer -->
      <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
        <p class="text-xs text-center text-gray-500 dark:text-gray-400">
          <a href="/mobile" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Acesso Móvel</a>
          <span class="mx-2">•</span>
          <a href="/admin" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Painel Admin</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

// Emit events
const emit = defineEmits(['login-success']);

// State
const loading = ref(false);
const showPassword = ref(false);

// Form data
const formData = reactive({
  email: '',
  password: '',
  remember: false,
});

// Errors
const errors = reactive({
  email: '',
  password: '',
  general: '',
});

// Validation schema (similar to Nuxt UI pattern)
const validateEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!email) return 'Email é obrigatório';
  if (!emailRegex.test(email)) return 'Email inválido';
  return '';
};

const validatePassword = (password) => {
  if (!password) return 'Senha é obrigatória';
  if (password.length < 6) return 'Senha deve ter pelo menos 6 caracteres';
  return '';
};

// Handle form submission
async function handleSubmit() {
  // Clear previous errors
  errors.email = '';
  errors.password = '';
  errors.general = '';

  // Validate fields
  errors.email = validateEmail(formData.email);
  errors.password = validatePassword(formData.password);

  // Check if there are validation errors
  if (errors.email || errors.password) {
    return;
  }

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
        email: formData.email,
        password: formData.password,
        remember: formData.remember,
      }),
    });

    const data = await response.json();

    if (response.ok) {
      emit('login-success', data.user);
    } else {
      // Handle validation errors from server
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
