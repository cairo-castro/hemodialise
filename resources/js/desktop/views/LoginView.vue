<template>
  <div class="login-view w-full max-w-md px-6">
    <div class="bg-white rounded-2xl shadow-2xl p-8">
      <!-- Logo -->
      <div class="text-center mb-8">
        <img
          src="/hemodialise_logo.png"
          alt="Logo Hemodiálise"
          class="w-24 h-24 mx-auto mb-4"
        />
        <h1 class="text-3xl font-bold text-gray-900">Sistema Hemodiálise</h1>
        <p class="text-gray-600 mt-2">Interface Desktop</p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Email
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            :class="{ 'border-red-500': errors.email }"
            placeholder="seu@email.com"
          />
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Senha
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            :class="{ 'border-red-500': errors.password }"
            placeholder="••••••••"
          />
          <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
          <input
            id="remember"
            v-model="form.remember"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label for="remember" class="ml-2 block text-sm text-gray-700">
            Lembrar-me
          </label>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="!loading">Entrar</span>
          <span v-else class="flex items-center justify-center">
            <svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Entrando...
          </span>
        </button>

        <!-- Error Message -->
        <div v-if="errors.general" class="p-4 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-600">{{ errors.general }}</p>
        </div>
      </form>

      <!-- Links -->
      <div class="mt-6 text-center text-sm text-gray-600">
        <p>Acesso móvel? <a href="/mobile" class="text-blue-600 hover:underline">Clique aqui</a></p>
        <p class="mt-2">Admin? <a href="/admin" class="text-blue-600 hover:underline">Painel Admin</a></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const emit = defineEmits(['login-success']);

const loading = ref(false);
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
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
