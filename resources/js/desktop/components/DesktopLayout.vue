<template>
  <div class="desktop-layout flex h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar -->
    <aside
      class="sidebar bg-white dark:bg-gray-950 border-r border-gray-200 dark:border-gray-800 transition-all duration-300 flex flex-col"
      :class="sidebarCollapsed ? 'w-16' : 'w-64'"
    >
      <!-- Logo & Toggle -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800">
        <div v-if="!sidebarCollapsed" class="flex items-center space-x-3">
          <img src="/hemodialise_logo.png" alt="Logo" class="w-8 h-8" />
          <span class="font-semibold text-gray-900 dark:text-white">Hemodiálise</span>
        </div>
        <img v-else src="/hemodialise_logo.png" alt="Logo" class="w-8 h-8 mx-auto" />

        <button
          @click="toggleSidebar"
          class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          :class="{ 'ml-auto': sidebarCollapsed }"
        >
          <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <router-link
          v-for="item in navigationItems"
          :key="item.name"
          :to="item.path"
          class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200"
          :class="{
            'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400': isActive(item.path),
            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800': !isActive(item.path)
          }"
        >
          <component
            :is="item.icon"
            class="w-5 h-5 flex-shrink-0"
            :class="sidebarCollapsed ? '' : 'mr-3'"
          />
          <span v-if="!sidebarCollapsed" class="truncate">{{ item.label }}</span>

          <!-- Badge (inbox counter example) -->
          <span
            v-if="item.badge && !sidebarCollapsed"
            class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-300"
          >
            {{ item.badge }}
          </span>
        </router-link>
      </nav>

      <!-- User Section -->
      <div class="border-t border-gray-200 dark:border-gray-800 p-3">
        <div
          v-if="!sidebarCollapsed"
          class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer transition-colors"
        >
          <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-sm font-semibold">
            {{ userInitials }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
              {{ user?.name || 'Usuário' }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
              {{ user?.email }}
            </p>
          </div>
        </div>

        <button
          v-else
          @click="handleLogout"
          class="w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        >
          <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-sm font-semibold mx-auto">
            {{ userInitials }}
          </div>
        </button>

        <!-- Logout Button (expanded) -->
        <button
          v-if="!sidebarCollapsed"
          @click="handleLogout"
          class="mt-2 w-full flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        >
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Sair
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Bar -->
      <header class="bg-white dark:bg-gray-950 border-b border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ pageTitle }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ pageSubtitle }}</p>
          </div>

          <div class="flex items-center space-x-4">
            <!-- Search Button -->
            <button class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </button>

            <!-- Notifications -->
            <button class="relative p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- Theme Toggle -->
            <button
              @click="toggleTheme"
              class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
            >
              <svg v-if="theme === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
              </svg>
            </button>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-6">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import {
  HomeIcon,
  ClipboardDocumentCheckIcon,
  CpuChipIcon,
  UsersIcon,
  UserCircleIcon,
  InboxIcon,
} from '@heroicons/vue/24/outline';

const route = useRoute();

const sidebarCollapsed = ref(false);
const theme = ref('light');
const user = ref({
  name: 'Usuário',
  email: 'usuario@hemodialise.ma.gov.br'
});

const navigationItems = [
  { name: 'dashboard', path: '/desktop', label: 'Dashboard', icon: HomeIcon },
  { name: 'checklists', path: '/desktop/checklists', label: 'Checklists', icon: ClipboardDocumentCheckIcon, badge: 4 },
  { name: 'machines', path: '/desktop/machines', label: 'Máquinas', icon: CpuChipIcon },
  { name: 'patients', path: '/desktop/patients', label: 'Pacientes', icon: UsersIcon },
  { name: 'profile', path: '/desktop/profile', label: 'Perfil', icon: UserCircleIcon },
];

const pageTitle = computed(() => {
  const item = navigationItems.find(item => item.path === route.path);
  return item?.label || 'Dashboard';
});

const pageSubtitle = computed(() => {
  const subtitles = {
    '/desktop': 'Visão geral do sistema',
    '/desktop/checklists': 'Checklists de segurança',
    '/desktop/machines': 'Gerenciamento de máquinas',
    '/desktop/patients': 'Cadastro de pacientes',
    '/desktop/profile': 'Configurações do perfil',
  };
  return subtitles[route.path] || '';
});

const userInitials = computed(() => {
  if (!user.value?.name) return 'U';
  return user.value.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
});

onMounted(() => {
  // Load theme from localStorage
  const savedTheme = localStorage.getItem('theme') || 'light';
  theme.value = savedTheme;
  applyTheme(savedTheme);
});

function toggleSidebar() {
  sidebarCollapsed.value = !sidebarCollapsed.value;
}

function toggleTheme() {
  theme.value = theme.value === 'dark' ? 'light' : 'dark';
  localStorage.setItem('theme', theme.value);
  applyTheme(theme.value);
}

function applyTheme(newTheme) {
  if (newTheme === 'dark') {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
}

function isActive(path) {
  return route.path === path || (path !== '/desktop' && route.path.startsWith(path));
}

async function handleLogout() {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    await fetch('/logout', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      credentials: 'same-origin',
    });

    window.location.href = '/desktop';
  } catch (error) {
    console.error('Logout error:', error);
    window.location.href = '/desktop';
  }
}
</script>

<style scoped>
.sidebar {
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
}

.nav-item {
  position: relative;
}

.nav-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 0;
  background: currentColor;
  border-radius: 0 2px 2px 0;
  transition: height 0.2s ease;
}

.nav-item.router-link-active::before {
  height: 70%;
}

:root {
  --color-primary-50: #f0fdf4;
  --color-primary-100: #dcfce7;
  --color-primary-600: #16a34a;
  --color-primary-900: #14532d;
}
</style>
