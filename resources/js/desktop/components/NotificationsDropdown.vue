<template>
  <div class="relative" ref="dropdownRef">
    <!-- Notification Button -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
      :class="{ 'bg-gray-100 dark:bg-gray-800': isOpen }"
      title="Notificações"
    >
      <BellIcon class="w-5 h-5" />

      <!-- Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full ring-2 ring-white dark:ring-gray-950"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <Transition name="dropdown">
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-900 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50"
      >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notificações</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
              {{ unreadCount > 0 ? `${unreadCount} não ${unreadCount === 1 ? 'lida' : 'lidas'}` : 'Nenhuma notificação nova' }}
            </p>
          </div>
          <button
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
          >
            Marcar todas como lidas
          </button>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
          <!-- Empty State -->
          <div v-if="notifications.length === 0" class="px-4 py-12 text-center">
            <BellIcon class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" />
            <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma notificação</p>
          </div>

          <!-- Notification Items -->
          <div v-else>
            <div
              v-for="notification in notifications"
              :key="notification.id"
              class="notification-item flex items-start px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer border-l-4"
              :class="[
                notification.read
                  ? 'border-transparent'
                  : 'border-primary-500 bg-primary-50/50 dark:bg-primary-900/10'
              ]"
              @click="handleNotificationClick(notification)"
            >
              <!-- Icon -->
              <div
                class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-3"
                :class="getNotificationColor(notification.type)"
              >
                <component :is="getNotificationIcon(notification.type)" class="w-5 h-5 text-white" />
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ notification.title }}
                </p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                  {{ notification.message }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                  {{ formatTime(notification.created_at) }}
                </p>
              </div>

              <!-- Unread Indicator -->
              <div v-if="!notification.read" class="flex-shrink-0 ml-3">
                <div class="w-2 h-2 bg-primary-500 rounded-full"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
          <button
            class="w-full text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
            @click="viewAll"
          >
            Ver todas as notificações
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import {
  BellIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  InformationCircleIcon,
  XCircleIcon,
  ClipboardDocumentCheckIcon,
} from '@heroicons/vue/24/outline';

const isOpen = ref(false);
const dropdownRef = ref(null);

// Mock notifications (replace with real API data)
const notifications = ref([
  {
    id: 1,
    type: 'checklist',
    title: 'Novo checklist criado',
    message: 'Checklist de segurança para Máquina 5 foi criado por João Silva',
    created_at: new Date(Date.now() - 5 * 60 * 1000),
    read: false,
  },
  {
    id: 2,
    type: 'success',
    title: 'Limpeza concluída',
    message: 'Limpeza diária da Máquina 3 foi concluída com sucesso',
    created_at: new Date(Date.now() - 30 * 60 * 1000),
    read: false,
  },
  {
    id: 3,
    type: 'warning',
    title: 'Manutenção agendada',
    message: 'Máquina 2 tem manutenção preventiva agendada para amanhã',
    created_at: new Date(Date.now() - 2 * 60 * 60 * 1000),
    read: false,
  },
  {
    id: 4,
    type: 'info',
    title: 'Novo paciente cadastrado',
    message: 'Paciente Maria Santos foi cadastrado no sistema',
    created_at: new Date(Date.now() - 4 * 60 * 60 * 1000),
    read: true,
  },
  {
    id: 5,
    type: 'error',
    title: 'Checklist incompleto',
    message: 'Checklist da Máquina 1 foi interrompido e precisa ser revisado',
    created_at: new Date(Date.now() - 24 * 60 * 60 * 1000),
    read: true,
  },
]);

const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read).length;
});

function toggleDropdown() {
  isOpen.value = !isOpen.value;
}

function handleNotificationClick(notification) {
  // Mark as read
  notification.read = true;

  // Handle navigation based on notification type
  // You can implement specific actions here
  console.log('Notification clicked:', notification);
}

function markAllAsRead() {
  notifications.value.forEach(n => {
    n.read = true;
  });
}

function viewAll() {
  isOpen.value = false;
  // Navigate to notifications page
  console.log('View all notifications');
}

function getNotificationIcon(type) {
  const icons = {
    success: CheckCircleIcon,
    warning: ExclamationCircleIcon,
    error: XCircleIcon,
    info: InformationCircleIcon,
    checklist: ClipboardDocumentCheckIcon,
  };
  return icons[type] || InformationCircleIcon;
}

function getNotificationColor(type) {
  const colors = {
    success: 'bg-green-500',
    warning: 'bg-yellow-500',
    error: 'bg-red-500',
    info: 'bg-blue-500',
    checklist: 'bg-purple-500',
  };
  return colors[type] || 'bg-gray-500';
}

function formatTime(date) {
  const now = new Date();
  const diff = Math.floor((now - date) / 1000); // seconds

  if (diff < 60) return 'Agora';
  if (diff < 3600) return `${Math.floor(diff / 60)}m atrás`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h atrás`;
  if (diff < 604800) return `${Math.floor(diff / 86400)}d atrás`;

  return date.toLocaleDateString('pt-BR');
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

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
