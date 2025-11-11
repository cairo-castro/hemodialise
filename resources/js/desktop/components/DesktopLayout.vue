<template>
  <div class="desktop-layout flex h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar -->
    <aside
      class="sidebar bg-white dark:bg-gray-950 border-r border-gray-200 dark:border-gray-800 transition-all duration-300 flex flex-col"
      :class="sidebarCollapsed ? 'w-16' : 'w-64'"
    >
      <!-- Logo -->
      <div class="p-4 border-b border-gray-200 dark:border-gray-800 space-y-3">
        <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'space-x-3'">
          <img src="/hemodialise_logo.png" alt="Logo" class="w-8 h-8" />
          <span v-if="!sidebarCollapsed" class="font-semibold text-gray-900 dark:text-white">Hemodiálise</span>
        </div>

        <!-- Unit Selector -->
        <div v-if="!sidebarCollapsed && availableUnits.length > 0" class="px-1">
          <!-- Single Unit Display -->
          <div v-if="availableUnits.length <= 1" class="flex items-center px-3 py-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-xs text-gray-600 dark:text-gray-300 truncate font-medium">{{ currentUnit?.name || 'Carregando...' }}</span>
          </div>

          <!-- Multiple Units Selector -->
          <div v-else class="relative">
            <button
              @click="showUnitSelector = !showUnitSelector"
              class="w-full flex items-center justify-between px-3 py-2 text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-primary-300 dark:hover:border-primary-700 transition-colors"
            >
              <div class="flex items-center min-w-0 flex-1">
                <svg class="w-4 h-4 text-primary-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-gray-700 dark:text-gray-300 font-medium truncate">{{ currentUnit?.name }}</span>
              </div>
              <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 ml-2 flex-shrink-0 transition-transform" :class="{ 'rotate-180': showUnitSelector }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <div
              v-if="showUnitSelector"
              class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto"
            >
              <button
                v-for="unit in availableUnits"
                :key="unit.id"
                @click="selectUnit(unit.id)"
                class="w-full px-3 py-2 text-left text-xs hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors flex items-center"
                :class="{ 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-medium': unit.id === selectedUnitId }"
              >
                <svg v-if="unit.id === selectedUnitId" class="w-3 h-3 text-primary-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span class="flex-1 truncate" :class="{ 'ml-5': unit.id !== selectedUnitId }">{{ unit.name }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Collapsed Unit Indicator -->
        <div v-else-if="sidebarCollapsed && currentUnit" class="flex justify-center" :title="currentUnit.name">
          <div class="w-2 h-2 rounded-full bg-primary-500"></div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <router-link
          v-for="item in navigationItems"
          :key="item.name"
          :to="item.path"
          class="nav-item flex items-center text-sm font-medium rounded-lg transition-all duration-200 relative group"
          :class="[
            sidebarCollapsed ? 'justify-center p-3' : 'px-3 py-2.5',
            {
              'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400': isActive(item.path),
              'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800': !isActive(item.path)
            }
          ]"
          :title="sidebarCollapsed ? item.label : ''"
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

          <!-- Tooltip for collapsed state -->
          <div
            v-if="sidebarCollapsed"
            class="absolute left-full ml-2 px-3 py-2 bg-gray-900 dark:bg-gray-800 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 whitespace-nowrap z-50 shadow-lg"
          >
            {{ item.label }}
            <span
              v-if="item.badge"
              class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-500 text-white"
            >
              {{ item.badge }}
            </span>
          </div>
        </router-link>
      </nav>

      <!-- Sidebar Footer - Compact mode when collapsed -->
      <div class="border-t border-gray-200 dark:border-gray-800 p-3">
        <button
          v-if="sidebarCollapsed"
          @click="openCommandPalette"
          class="w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          title="Buscar (⌘K)"
        >
          <svg class="w-5 h-5 mx-auto text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Bar -->
      <header class="bg-white dark:bg-gray-950 border-b border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center justify-between">
          <!-- Left: Sidebar Toggle + Title -->
          <div class="flex items-center space-x-4">
            <!-- Sidebar Toggle Button -->
            <button
              @click="toggleSidebar"
              class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
              title="Toggle Sidebar"
            >
              <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>

            <!-- Page Title -->
            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ pageTitle }}</h1>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ pageSubtitle }}</p>
            </div>
          </div>

          <div class="flex items-center space-x-2">
            <!-- Command Palette Button -->
            <button
              @click="openCommandPalette"
              class="hidden lg:flex items-center space-x-2 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors border border-gray-200 dark:border-gray-700"
              title="Buscar (⌘K)"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <span>Buscar...</span>
              <kbd class="px-2 py-0.5 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded">
                ⌘K
              </kbd>
            </button>

            <!-- Mobile Search Button -->
            <button
              @click="openCommandPalette"
              class="lg:hidden p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
              title="Buscar"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </button>

            <!-- Notifications -->
            <NotificationsDropdown />

            <!-- User Menu -->
            <UserMenuDropdown
              :user="user"
              :theme="theme"
              @logout="handleLogout"
              @theme-change="handleThemeChange"
            />
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-6">
        <slot></slot>
      </main>
    </div>

    <!-- Command Palette -->
    <CommandPalette
      ref="commandPaletteRef"
      @toggle-theme="handleThemeChange"
      @logout="handleLogout"
    />
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
  ChartBarIcon,
} from '@heroicons/vue/24/outline';
import CommandPalette from './CommandPalette.vue';
import NotificationsDropdown from './NotificationsDropdown.vue';
import UserMenuDropdown from './UserMenuDropdown.vue';
import api from '../utils/api';

const route = useRoute();

const sidebarCollapsed = ref(false);
const theme = ref('light');
const user = ref({
  name: 'Usuário',
  email: 'usuario@hemodialise.ma.gov.br',
  role: 'user'
});

// Command Palette ref
const commandPaletteRef = ref(null);

// Unit management
const availableUnits = ref([]);
const currentUnit = ref(null);
const selectedUnitId = ref(null);
const showUnitSelector = ref(false);

const navigationItems = [
  { name: 'dashboard', path: '/desktop', label: 'Dashboard', icon: HomeIcon },
  { name: 'checklists', path: '/desktop/checklists', label: 'Checklists de Segurança', icon: ClipboardDocumentCheckIcon, badge: 4 },
  { name: 'cleaning-checklists', path: '/desktop/cleaning-checklists', label: 'Checklist de Limpeza', icon: ClipboardDocumentCheckIcon },
  { name: 'machines', path: '/desktop/machines', label: 'Máquinas', icon: CpuChipIcon },
  { name: 'patients', path: '/desktop/patients', label: 'Pacientes', icon: UsersIcon },
  { name: 'reports', path: '/desktop/reports', label: 'Relatórios', icon: ChartBarIcon },
];

const pageTitle = computed(() => {
  const item = navigationItems.find(item => item.path === route.path);
  return item?.label || 'Dashboard';
});

const pageSubtitle = computed(() => {
  const subtitles = {
    '/desktop': 'Visão geral do sistema',
    '/desktop/checklists': 'Checklists de segurança',
    '/desktop/cleaning-checklists': 'Controle de limpeza e desinfecção',
    '/desktop/machines': 'Gerenciamento de máquinas',
    '/desktop/patients': 'Cadastro de pacientes',
    '/desktop/reports': 'Análises detalhadas e comparações de desempenho',
    '/desktop/notifications': 'Central de avisos e atualizações',
    '/desktop/profile': 'Gerenciar informações pessoais',
    '/desktop/settings': 'Configurações do sistema',
    '/desktop/help': 'Central de ajuda e suporte',
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

onMounted(async () => {
  // Load theme from localStorage
  const savedTheme = localStorage.getItem('theme') || 'light';
  theme.value = savedTheme;
  applyTheme(savedTheme);

  // Load available units
  await loadAvailableUnits();

  // Load user data
  await loadUserData();
});

// Load user data from API
async function loadUserData() {
  try {
    const response = await api.get('/api/me');

    if (response.ok) {
      const data = await response.json();
      user.value = data.user || user.value;
    }
  } catch (error) {
    console.error('Error loading user data:', error);
  }
}

// Load available units
async function loadAvailableUnits() {
  try {
    const response = await api.get('/api/user-units');

    if (!response.ok) {
      console.warn('Failed to load units:', response.status);
      return;
    }

    const data = await response.json();

    if (data.success) {
      availableUnits.value = data.units || [];

      // Find current unit
      const currentUnitId = data.current_unit_id;

      if (currentUnitId) {
        currentUnit.value = data.units.find(u => u.id === currentUnitId);
        selectedUnitId.value = currentUnitId;
      } else if (data.units.length === 1) {
        // Only one unit available - select automatically
        await selectUnit(data.units[0].id);
        currentUnit.value = data.units[0];
        selectedUnitId.value = data.units[0].id;
      } else if (data.units.length > 0) {
        // Multiple units and none selected - use first as default
        currentUnit.value = data.units[0];
        selectedUnitId.value = data.units[0].id;
      }

      console.log('Units loaded (desktop):', {
        total: availableUnits.value.length,
        current: currentUnit.value?.name,
        currentId: selectedUnitId.value
      });
    }
  } catch (error) {
    console.error('Error loading available units:', error);
  }
}

// Select a unit
async function selectUnit(unitId) {
  showUnitSelector.value = false;

  if (unitId === selectedUnitId.value) {
    return; // Already selected
  }

  try {
    console.log('[DesktopLayout] Switching unit:', unitId);

    const response = await api.post('/api/user-units/switch', { unit_id: unitId });

    if (!response.ok) {
      const errorText = await response.text();
      console.error('[DesktopLayout] Switch unit failed:', response.status, errorText);
      throw new Error(`Failed to switch unit: ${response.status}`);
    }

    const data = await response.json();
    console.log('[DesktopLayout] Switch unit response:', data);

    if (data.success) {
      currentUnit.value = data.current_unit;
      selectedUnitId.value = unitId;

      // Reload page to apply new unit filter
      window.location.reload();
    }
  } catch (error) {
    console.error('[DesktopLayout] Error switching unit:', error);
    // Revert selection
    selectedUnitId.value = currentUnit.value?.id;
  }
}

function toggleSidebar() {
  sidebarCollapsed.value = !sidebarCollapsed.value;
}

function toggleTheme() {
  const newTheme = theme.value === 'dark' ? 'light' : 'dark';
  handleThemeChange(newTheme);
}

function handleThemeChange(newTheme) {
  theme.value = newTheme;
  localStorage.setItem('theme', newTheme);
  applyTheme(newTheme);
  console.log('Theme changed to:', newTheme);
}

function openCommandPalette() {
  commandPaletteRef.value?.open();
}

function applyTheme(newTheme) {
  const html = document.documentElement;
  if (newTheme === 'dark') {
    html.classList.add('dark');
  } else {
    html.classList.remove('dark');
  }
}

function isActive(path) {
  return route.path === path || (path !== '/desktop' && route.path.startsWith(path));
}

async function handleLogout() {
  try {
    console.log('[DesktopLayout] Logging out...');

    // Use api wrapper for proper CSRF handling
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    await fetch('/logout', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      credentials: 'include',
    });

    console.log('[DesktopLayout] Logout successful, redirecting...');
    window.location.href = '/desktop';
  } catch (error) {
    console.error('[DesktopLayout] Logout error:', error);
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
