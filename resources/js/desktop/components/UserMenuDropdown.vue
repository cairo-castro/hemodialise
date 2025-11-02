<template>
  <div class="relative" ref="dropdownRef">
    <!-- User Button -->
    <button
      @click="toggleDropdown"
      class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
      :class="{ 'bg-gray-100 dark:bg-gray-800': isOpen }"
    >
      <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-sm font-semibold ring-2 ring-primary-100 dark:ring-primary-900">
        {{ userInitials }}
      </div>
      <div class="hidden md:block text-left">
        <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-32">
          {{ user?.name || 'Usuário' }}
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-32">
          {{ getRoleName(user?.role) }}
        </p>
      </div>
      <ChevronDownIcon
        class="hidden md:block w-4 h-4 text-gray-400 transition-transform"
        :class="{ 'rotate-180': isOpen }"
      />
    </button>

    <!-- Dropdown -->
    <Transition name="dropdown">
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-900 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50"
      >
        <!-- User Info -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white text-base font-semibold">
              {{ userInitials }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                {{ user?.name || 'Usuário' }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                {{ user?.email }}
              </p>
            </div>
          </div>
          <div v-if="user?.unit" class="mt-2 flex items-center text-xs text-gray-600 dark:text-gray-400">
            <MapPinIcon class="w-3 h-3 mr-1" />
            {{ user.unit.name }}
          </div>
        </div>

        <!-- Menu Items -->
        <div class="py-2">
          <button
            v-for="item in menuItems"
            :key="item.id"
            @click="handleMenuClick(item)"
            class="w-full flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          >
            <component :is="item.icon" class="w-5 h-5 mr-3 text-gray-400" />
            <span class="flex-1 text-left">{{ item.label }}</span>
            <kbd
              v-if="item.shortcut"
              class="hidden lg:inline-block px-2 py-0.5 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded"
            >
              {{ item.shortcut }}
            </kbd>
          </button>
        </div>

        <!-- Theme Toggle -->
        <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-700 dark:text-gray-300">Tema</span>
            <div class="flex items-center space-x-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
              <button
                @click="setTheme('light')"
                class="p-1.5 rounded transition-colors"
                :class="theme === 'light' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400'"
                title="Tema claro"
              >
                <SunIcon class="w-4 h-4" />
              </button>
              <button
                @click="setTheme('dark')"
                class="p-1.5 rounded transition-colors"
                :class="theme === 'dark' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400'"
                title="Tema escuro"
              >
                <MoonIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Logout -->
        <div class="p-2 border-t border-gray-200 dark:border-gray-700">
          <button
            @click="handleLogout"
            class="w-full flex items-center px-4 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
          >
            <ArrowRightOnRectangleIcon class="w-5 h-5 mr-3" />
            Sair do Sistema
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  UserCircleIcon,
  Cog6ToothIcon,
  BellIcon,
  QuestionMarkCircleIcon,
  ArrowRightOnRectangleIcon,
  ChevronDownIcon,
  SunIcon,
  MoonIcon,
  MapPinIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  user: {
    type: Object,
    default: () => ({ name: 'Usuário', email: 'usuario@example.com', role: 'user' })
  },
  theme: {
    type: String,
    default: 'light'
  }
});

const emit = defineEmits(['logout', 'theme-change']);

const router = useRouter();
const isOpen = ref(false);
const dropdownRef = ref(null);

const menuItems = [
  {
    id: 'profile',
    label: 'Meu Perfil',
    icon: UserCircleIcon,
    action: () => router.push('/desktop/profile'),
  },
  {
    id: 'settings',
    label: 'Configurações',
    icon: Cog6ToothIcon,
    action: () => router.push('/desktop/settings'),
    shortcut: '⌘,',
  },
  {
    id: 'help',
    label: 'Ajuda & Suporte',
    icon: QuestionMarkCircleIcon,
    action: () => router.push('/desktop/help'),
  },
];

const userInitials = computed(() => {
  if (!props.user?.name) return 'U';
  return props.user.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
});

function getRoleName(role) {
  const roles = {
    admin: 'Administrador',
    manager: 'Gerente',
    field_user: 'Usuário de Campo',
  };
  return roles[role] || 'Usuário';
}

function toggleDropdown() {
  isOpen.value = !isOpen.value;
}

function handleMenuClick(item) {
  isOpen.value = false;
  item.action();
}

function setTheme(newTheme) {
  emit('theme-change', newTheme);
}

function handleLogout() {
  isOpen.value = false;
  emit('logout');
}

// Close dropdown when clicking outside
function handleClickOutside(event) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
