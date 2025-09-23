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

const handleInterfaceSwitch = (interfaceType) => {
  console.log('Switching to interface:', interfaceType);

  switch (interfaceType) {
    case 'mobile':
      window.location.href = '/mobile/app';
      break;
    case 'admin':
      window.location.href = '/admin';
      break;
    default:
      console.warn('Unknown interface type:', interfaceType);
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