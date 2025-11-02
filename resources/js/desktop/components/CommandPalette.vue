<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="close"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal -->
        <div class="flex min-h-full items-start justify-center p-4 pt-20">
          <div
            class="relative w-full max-w-2xl bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
            @click.stop
          >
            <!-- Search Input -->
            <div class="flex items-center border-b border-gray-200 dark:border-gray-700 px-4 py-3">
              <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                ref="searchInput"
                v-model="searchQuery"
                type="text"
                class="flex-1 bg-transparent border-none outline-none text-gray-900 dark:text-white placeholder-gray-400 text-base"
                placeholder="Buscar ou executar comando..."
                @keydown.down.prevent="selectNext"
                @keydown.up.prevent="selectPrevious"
                @keydown.enter.prevent="executeSelected"
                @keydown.esc="close"
              />
              <kbd class="hidden sm:inline-block px-2 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
                ESC
              </kbd>
            </div>

            <!-- Results -->
            <div class="max-h-96 overflow-y-auto p-2">
              <!-- No results -->
              <div v-if="filteredCommands.length === 0" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm">Nenhum resultado encontrado</p>
              </div>

              <!-- Command Groups -->
              <div v-else>
                <div
                  v-for="group in groupedCommands"
                  :key="group.category"
                  class="mb-4"
                >
                  <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    {{ group.category }}
                  </div>
                  <div class="space-y-1">
                    <button
                      v-for="(command, index) in group.commands"
                      :key="command.id"
                      :ref="el => { if (el) commandRefs[command.id] = el }"
                      class="w-full flex items-center px-3 py-2.5 rounded-lg text-left transition-colors"
                      :class="[
                        selectedIndex === getGlobalIndex(group.category, index)
                          ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400'
                          : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
                      ]"
                      @click="execute(command)"
                      @mouseenter="selectedIndex = getGlobalIndex(group.category, index)"
                    >
                      <component
                        :is="command.icon"
                        class="w-5 h-5 mr-3 flex-shrink-0"
                        :class="selectedIndex === getGlobalIndex(group.category, index) ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400'"
                      />
                      <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium">{{ command.label }}</div>
                        <div v-if="command.description" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                          {{ command.description }}
                        </div>
                      </div>
                      <kbd
                        v-if="command.shortcut"
                        class="hidden sm:inline-block ml-3 px-2 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded"
                      >
                        {{ command.shortcut }}
                      </kbd>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-800/50">
              <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center space-x-4">
                  <span class="flex items-center">
                    <kbd class="px-2 py-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded mr-1">↑</kbd>
                    <kbd class="px-2 py-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded mr-2">↓</kbd>
                    navegar
                  </span>
                  <span class="flex items-center">
                    <kbd class="px-2 py-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded mr-2">↵</kbd>
                    selecionar
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import {
  HomeIcon,
  ClipboardDocumentCheckIcon,
  CpuChipIcon,
  UsersIcon,
  UserCircleIcon,
  Cog6ToothIcon,
  MagnifyingGlassIcon,
  BellIcon,
  SunIcon,
  MoonIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline';

const emit = defineEmits(['toggle-theme', 'logout']);

const router = useRouter();
const isOpen = ref(false);
const searchQuery = ref('');
const selectedIndex = ref(0);
const searchInput = ref(null);
const commandRefs = ref({});

// Define all commands
const commands = [
  // Navigation
  { id: 'nav-dashboard', category: 'Navegação', label: 'Dashboard', description: 'Ir para página inicial', icon: HomeIcon, action: () => router.push('/desktop') },
  { id: 'nav-checklists', category: 'Navegação', label: 'Checklists', description: 'Ver checklists de segurança', icon: ClipboardDocumentCheckIcon, action: () => router.push('/desktop/checklists') },
  { id: 'nav-machines', category: 'Navegação', label: 'Máquinas', description: 'Gerenciar máquinas', icon: CpuChipIcon, action: () => router.push('/desktop/machines') },
  { id: 'nav-patients', category: 'Navegação', label: 'Pacientes', description: 'Gerenciar pacientes', icon: UsersIcon, action: () => router.push('/desktop/patients') },
  { id: 'nav-profile', category: 'Navegação', label: 'Meu Perfil', description: 'Ver e editar meu perfil', icon: UserCircleIcon, action: () => router.push('/desktop/profile') },
  { id: 'nav-settings', category: 'Navegação', label: 'Configurações', description: 'Configurações do sistema', icon: Cog6ToothIcon, action: () => router.push('/desktop/settings') },
  { id: 'nav-help', category: 'Navegação', label: 'Ajuda e Suporte', description: 'Central de ajuda e FAQs', icon: MagnifyingGlassIcon, action: () => router.push('/desktop/help') },

  // Actions
  { id: 'action-search', category: 'Ações', label: 'Buscar', description: 'Buscar em todo sistema', icon: MagnifyingGlassIcon, shortcut: '⌘K', action: () => {} },
  { id: 'action-notifications', category: 'Ações', label: 'Notificações', description: 'Ver notificações', icon: BellIcon, action: () => {} },
  { id: 'action-theme-light', category: 'Ações', label: 'Tema Claro', description: 'Ativar tema claro', icon: SunIcon, action: () => emit('toggle-theme', 'light') },
  { id: 'action-theme-dark', category: 'Ações', label: 'Tema Escuro', description: 'Ativar tema escuro', icon: MoonIcon, action: () => emit('toggle-theme', 'dark') },
  { id: 'action-logout', category: 'Ações', label: 'Sair', description: 'Sair do sistema', icon: ArrowRightOnRectangleIcon, action: () => emit('logout') },
];

// Filter commands based on search
const filteredCommands = computed(() => {
  if (!searchQuery.value) return commands;

  const query = searchQuery.value.toLowerCase();
  return commands.filter(cmd =>
    cmd.label.toLowerCase().includes(query) ||
    cmd.description?.toLowerCase().includes(query) ||
    cmd.category.toLowerCase().includes(query)
  );
});

// Group commands by category
const groupedCommands = computed(() => {
  const groups = {};

  filteredCommands.value.forEach(cmd => {
    if (!groups[cmd.category]) {
      groups[cmd.category] = {
        category: cmd.category,
        commands: []
      };
    }
    groups[cmd.category].commands.push(cmd);
  });

  return Object.values(groups);
});

// Get global index for a command
function getGlobalIndex(category, localIndex) {
  let globalIndex = 0;

  for (const group of groupedCommands.value) {
    if (group.category === category) {
      return globalIndex + localIndex;
    }
    globalIndex += group.commands.length;
  }

  return 0;
}

// Get command by global index
function getCommandByIndex(index) {
  let currentIndex = 0;

  for (const group of groupedCommands.value) {
    if (index < currentIndex + group.commands.length) {
      return group.commands[index - currentIndex];
    }
    currentIndex += group.commands.length;
  }

  return null;
}

// Navigation
function selectNext() {
  selectedIndex.value = Math.min(selectedIndex.value + 1, filteredCommands.value.length - 1);
  scrollToSelected();
}

function selectPrevious() {
  selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
  scrollToSelected();
}

function scrollToSelected() {
  nextTick(() => {
    const command = getCommandByIndex(selectedIndex.value);
    if (command && commandRefs.value[command.id]) {
      commandRefs.value[command.id].scrollIntoView({
        block: 'nearest',
        behavior: 'smooth'
      });
    }
  });
}

function executeSelected() {
  const command = getCommandByIndex(selectedIndex.value);
  if (command) {
    execute(command);
  }
}

function execute(command) {
  command.action();
  close();
}

function open() {
  isOpen.value = true;
  selectedIndex.value = 0;
  searchQuery.value = '';
  nextTick(() => {
    searchInput.value?.focus();
  });
}

function close() {
  isOpen.value = false;
  searchQuery.value = '';
}

// Keyboard shortcut handler
function handleKeyboard(e) {
  // Cmd/Ctrl + K to open
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault();
    if (isOpen.value) {
      close();
    } else {
      open();
    }
  }
}

// Watch search query to reset selection
watch(searchQuery, () => {
  selectedIndex.value = 0;
});

onMounted(() => {
  window.addEventListener('keydown', handleKeyboard);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyboard);
});

// Expose open/close methods
defineExpose({
  open,
  close,
  isOpen,
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
  opacity: 0;
}
</style>
