<template>
  <div class="desktop-app h-screen bg-gray-50 dark:bg-gray-900 antialiased">
    <!-- Authentication Check -->
    <div v-if="!isAuthenticated" class="auth-container">
      <LoginView @login-success="handleLoginSuccess" />
    </div>

    <!-- Main App (Authenticated) -->
    <div v-else class="main-container h-full">
      <DesktopLayout>
        <router-view />
      </DesktopLayout>
    </div>

    <!-- Session Expired Modal -->
    <div v-if="showSessionExpiredModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/20 rounded-full mb-4">
          <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white text-center mb-2">
          Sessão Expirada
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6">
          {{ sessionExpiredMessage }}
        </p>
        <button
          @click="redirectToLogin"
          class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors"
        >
          Fazer Login
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import LoginView from './views/LoginView.vue';
import DesktopLayout from './components/DesktopLayout.vue';
import api from './utils/api';

const router = useRouter();
const isAuthenticated = ref(false);
const user = ref(null);
const showSessionExpiredModal = ref(false);
const sessionExpiredMessage = ref('Sua sessão expirou por motivos de segurança. Por favor, faça login novamente.');

// Check authentication status on mount
onMounted(async () => {
  await checkAuth();

  // Listen for session expired events
  window.addEventListener('session-expired', handleSessionExpiredEvent);
});

onUnmounted(() => {
  window.removeEventListener('session-expired', handleSessionExpiredEvent);
});

async function checkAuth() {
  try {
    // Check if user is authenticated via Laravel session with Sanctum
    const response = await api.get('/api/me');

    if (response.ok) {
      const data = await response.json();
      user.value = data.user;
      isAuthenticated.value = true;
    } else {
      isAuthenticated.value = false;
    }
  } catch (error) {
    console.error('Auth check failed:', error);
    isAuthenticated.value = false;
  }
}

function handleLoginSuccess(userData) {
  user.value = userData;
  isAuthenticated.value = true;
  router.push('/desktop');
}

function handleSessionExpiredEvent(event) {
  sessionExpiredMessage.value = event.detail?.message || sessionExpiredMessage.value;
  showSessionExpiredModal.value = true;
}

function redirectToLogin() {
  showSessionExpiredModal.value = false;
  window.location.href = '/login';
}
</script>

<style scoped>
.desktop-app {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.auth-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: rgb(249 250 251);
}

/* Dark mode support */
:global(.dark) .auth-container {
  background-color: rgb(3 7 18);
}

.main-container {
  display: flex;
  flex-direction: column;
}
</style>
