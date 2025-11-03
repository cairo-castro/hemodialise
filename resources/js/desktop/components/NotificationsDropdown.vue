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
const notifications = ref([]);
const unreadCount = ref(0);
const lastCheckTimestamp = ref(null);
const pollingInterval = ref(null);
const isLoading = ref(false);

const POLLING_INTERVAL_MS = 30000; // Poll every 30 seconds

// Load initial notifications
async function loadNotifications() {
  try {
    isLoading.value = true;
    const response = await fetch('/api/notifications?per_page=10', {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' }
    });

    if (response.ok) {
      const data = await response.json();
      notifications.value = data.notifications.map(n => ({
        ...n,
        created_at: new Date(n.created_at),
        read: !!n.read_at,
      }));
      unreadCount.value = data.unread_count;
      lastCheckTimestamp.value = new Date().toISOString();
    }
  } catch (error) {
    console.error('Error loading notifications:', error);
  } finally {
    isLoading.value = false;
  }
}

// Poll for new notifications
async function pollNotifications() {
  try {
    const params = new URLSearchParams();
    if (lastCheckTimestamp.value) {
      params.append('last_check', lastCheckTimestamp.value);
    }

    const response = await fetch(`/api/notifications/poll?${params}`, {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' }
    });

    if (response.ok) {
      const data = await response.json();

      // Add new notifications to the list
      if (data.notifications && data.notifications.length > 0) {
        const newNotifications = data.notifications.map(n => ({
          ...n,
          created_at: new Date(n.created_at),
          read: !!n.read_at,
        }));

        // Prepend new notifications
        notifications.value = [...newNotifications, ...notifications.value].slice(0, 20);
      }

      unreadCount.value = data.unread_count;
      lastCheckTimestamp.value = data.timestamp;
    }
  } catch (error) {
    console.error('Error polling notifications:', error);
  }
}

// Start polling
function startPolling() {
  // Poll immediately
  pollNotifications();

  // Then poll every POLLING_INTERVAL_MS
  pollingInterval.value = setInterval(() => {
    pollNotifications();
  }, POLLING_INTERVAL_MS);
}

// Stop polling
function stopPolling() {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value);
    pollingInterval.value = null;
  }
}

function toggleDropdown() {
  isOpen.value = !isOpen.value;

  // Reload notifications when opening dropdown
  if (isOpen.value) {
    loadNotifications();
  }
}

async function handleNotificationClick(notification) {
  // Mark as read
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    await fetch(`/api/notifications/${notification.id}/mark-read`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      }
    });

    // Update local state
    notification.read = true;
    unreadCount.value = Math.max(0, unreadCount.value - 1);

    // Navigate if action_url exists
    if (notification.action_url) {
      window.location.href = notification.action_url;
    }
  } catch (error) {
    console.error('Error marking notification as read:', error);
  }
}

async function markAllAsRead() {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const response = await fetch('/api/notifications/mark-all-read', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      }
    });

    if (response.ok) {
      // Update local state
      notifications.value.forEach(n => {
        n.read = true;
      });
      unreadCount.value = 0;
    }
  } catch (error) {
    console.error('Error marking all as read:', error);
  }
}

function viewAll() {
  isOpen.value = false;
  // Navigate to notifications page (can be implemented later)
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

  // Load initial notifications
  loadNotifications();

  // Start polling
  startPolling();
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);

  // Stop polling when component unmounts
  stopPolling();
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
