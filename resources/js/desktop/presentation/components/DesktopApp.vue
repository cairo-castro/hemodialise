<template>
  <div class="h-full">
    <!-- Loading Screen -->
    <div v-if="loading" class="fixed inset-0 z-50 flex items-center justify-center medical-gradient">
      <div class="text-center text-white">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-white border-t-transparent mb-4"></div>
        <h1 class="text-2xl font-bold mb-2">Sistema Hemodiálise</h1>
        <p class="text-lg">Carregando Interface Executiva...</p>
      </div>
    </div>

    <!-- Main Application -->
    <div v-show="!loading" class="h-full">
      <!-- Login notification for guests -->
      <div v-if="isGuest" class="fixed top-4 right-4 z-50 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-lg shadow-lg">
        <div class="flex items-center space-x-2">
          <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
            <path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z"/>
          </svg>
          <span class="text-sm font-medium">
            Faça <a href="/login" class="underline hover:no-underline">login</a> para acesso completo
          </span>
        </div>
      </div>

      <!-- Sidebar -->
      <DesktopSidebar 
        :user="user"
        :current-view="currentView"
        :can-toggle-interfaces="canToggleInterfaces"
        @view-change="handleViewChange"
        @interface-switch="handleInterfaceSwitch"
        @logout="handleLogout"
      />

      <!-- Main Content Area -->
      <div class="w-full lg:ps-64">
        <!-- Top Header -->
        <DesktopHeader 
          :user="user"
          :view-title="getViewTitle()"
          :view-subtitle="getViewSubtitle()"
          @logout="handleLogout"
          @interface-switch="handleInterfaceSwitch"
        />

        <!-- Main Content -->
        <main class="p-4 sm:p-6 lg:p-8">
          <!-- Dashboard View -->
          <DashboardView v-if="currentView === 'dashboard'" />
          
          <!-- Other Views -->
          <PlaceholderView 
            v-else
            :title="getViewTitle()"
            :subtitle="getViewSubtitle()"
          />
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/useAuthStore.js';
import { useAppStore } from '../stores/useAppStore.js';
import DesktopSidebar from './DesktopSidebar.vue';
import DesktopHeader from './DesktopHeader.vue';
import DashboardView from './views/DashboardView.vue';
import PlaceholderView from './views/PlaceholderView.vue';

// Stores
const authStore = useAuthStore();
const appStore = useAppStore();

// Props de dependencies injection
const props = defineProps({
  authService: {
    type: Object,
    required: true
  },
  errorHandler: {
    type: Object,
    required: true
  }
});

// Estado local
const loading = ref(true);

// Computed properties
const { 
  user, 
  isAuthenticated, 
  isGuest, 
  canToggleInterfaces 
} = authStore;

const { 
  currentView, 
  getViewTitle, 
  getViewSubtitle 
} = appStore;

// Métodos
const init = async () => {
  console.log('Desktop app initializing...');
  
  try {
    await checkAuth();
  } catch (error) {
    props.errorHandler.handleGenericError(error);
    authStore.setGuestUser();
  } finally {
    // Sempre esconder loading após timeout
    setTimeout(() => {
      console.log('Hiding loading screen...');
      loading.value = false;
    }, 1500);
  }
};

const checkAuth = async () => {
  console.log('Starting auth check...');
  authStore.setLoading(true);
  
  try {
    const user = await props.authService.getCurrentUser();
    
    if (user) {
      authStore.setUser(user);
      
      // Verificar se pode acessar desktop
      if (user.isFieldUser()) {
        console.log('Field user detected, redirecting to mobile...');
        setTimeout(() => {
          window.location.href = '/mobile/ionic';
        }, 500);
        return;
      }
      
      console.log('Auth successful, user:', user.name);
    } else {
      console.log('Not authenticated, showing guest mode');
      authStore.setGuestUser();
    }
  } catch (error) {
    console.warn('Auth check failed:', error);
    authStore.setGuestUser();
  } finally {
    authStore.setLoading(false);
  }
};

const handleViewChange = (view) => {
  appStore.setCurrentView(view);
};

const handleInterfaceSwitch = async (data) => {
  try {
    console.log('Switching to interface:', data.interface, 'URL:', data.url);
    // O InterfaceSwitcher já faz o redirecionamento
    // Aqui podemos adicionar lógica adicional se necessário
  } catch (error) {
    console.error('Error switching interface:', error);
    appStore.addNotification({
      type: 'error',
      message: 'Erro ao alternar interface'
    });
  }
};

const handleLogout = async () => {
  try {
    await props.authService.logout();
  } catch (error) {
    props.errorHandler.handleGenericError(error);
    window.location.href = '/login?logout=true';
  }
};

// Lifecycle
onMounted(() => {
  init();
});
</script>