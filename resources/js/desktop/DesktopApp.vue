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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import LoginView from './views/LoginView.vue';
import DesktopLayout from './components/DesktopLayout.vue';

const router = useRouter();
const isAuthenticated = ref(false);
const user = ref(null);

// Check authentication status on mount
onMounted(async () => {
  await checkAuth();
});

async function checkAuth() {
  try {
    // Check if user is authenticated via Laravel session
    const response = await fetch('/api/me', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin'
    });

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
