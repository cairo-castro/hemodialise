<template>
  <div class="desktop-layout flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside
      class="sidebar w-64 bg-gray-900 text-white flex flex-col transition-all duration-300"
      :class="{ 'w-20': !sidebarExpanded }"
    >
      <!-- Logo -->
      <div class="p-6 flex items-center justify-between border-b border-gray-800">
        <div v-if="sidebarExpanded" class="flex items-center space-x-3">
          <img src="/hemodialise_logo.png" alt="Logo" class="w-10 h-10" />
          <span class="font-bold text-lg">Hemodiálise</span>
        </div>
        <img v-else src="/hemodialise_logo.png" alt="Logo" class="w-10 h-10 mx-auto" />
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <router-link
          v-for="item in navigationItems"
          :key="item.name"
          :to="item.path"
          class="nav-item flex items-center px-4 py-3 rounded-lg transition-colors duration-200"
          :class="{
            'bg-blue-600 text-white': isActive(item.path),
            'text-gray-300 hover:bg-gray-800': !isActive(item.path)
          }"
        >
          <component
            :is="item.icon"
            class="w-6 h-6"
            :class="{ 'mr-3': sidebarExpanded }"
          />
          <span v-if="sidebarExpanded" class="font-medium">{{ item.label }}</span>
        </router-link>
      </nav>

      <!-- User Section -->
      <div class="p-4 border-t border-gray-800">
        <button
          @click="handleLogout"
          class="w-full flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-800 transition-colors duration-200"
        >
          <svg class="w-6 h-6" :class="{ 'mr-3': sidebarExpanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span v-if="sidebarExpanded" class="font-medium">Sair</span>
        </button>
      </div>

      <!-- Toggle Button -->
      <button
        @click="toggleSidebar"
        class="p-4 border-t border-gray-800 hover:bg-gray-800 transition-colors duration-200"
      >
        <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            :d="sidebarExpanded ? 'M11 19l-7-7 7-7m8 14l-7-7 7-7' : 'M13 5l7 7-7 7M5 5l7 7-7 7'"
          />
        </svg>
      </button>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Bar -->
      <header class="bg-white shadow-sm z-10">
        <div class="px-6 py-4 flex items-center justify-between">
          <h1 class="text-2xl font-semibold text-gray-900">{{ pageTitle }}</h1>

          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ user?.name || 'Usuário' }}</p>
              <p class="text-xs text-gray-500">{{ user?.email }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold">
              {{ userInitials }}
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  HomeIcon,
  ClipboardDocumentCheckIcon,
  CpuChipIcon,
  UsersIcon,
  UserCircleIcon,
} from '@heroicons/vue/24/outline';

const route = useRoute();
const router = useRouter();

const sidebarExpanded = ref(true);
const user = ref(null); // Will be populated from parent

const navigationItems = [
  { name: 'dashboard', path: '/desktop', label: 'Dashboard', icon: HomeIcon },
  { name: 'checklists', path: '/desktop/checklists', label: 'Checklists', icon: ClipboardDocumentCheckIcon },
  { name: 'machines', path: '/desktop/machines', label: 'Máquinas', icon: CpuChipIcon },
  { name: 'patients', path: '/desktop/patients', label: 'Pacientes', icon: UsersIcon },
  { name: 'profile', path: '/desktop/profile', label: 'Perfil', icon: UserCircleIcon },
];

const pageTitle = computed(() => {
  const item = navigationItems.find(item => item.path === route.path);
  return item?.label || 'Sistema Hemodiálise';
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

function toggleSidebar() {
  sidebarExpanded.value = !sidebarExpanded.value;
}

function isActive(path) {
  return route.path === path || route.path.startsWith(path + '/');
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

    // Redirect to login
    window.location.href = '/desktop';
  } catch (error) {
    console.error('Logout error:', error);
    // Force redirect anyway
    window.location.href = '/desktop';
  }
}
</script>

<style scoped>
.sidebar {
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.nav-item {
  font-size: 0.95rem;
}

.nav-item.router-link-active {
  background-color: #2563eb;
  color: white;
}
</style>
