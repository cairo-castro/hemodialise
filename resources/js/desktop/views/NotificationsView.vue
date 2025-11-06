<template>
  <div class="notifications-view space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Notificações</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Central de avisos e atualizações do sistema</p>
      </div>
      <button
        v-if="unreadCount > 0"
        @click="markAllAsRead"
        class="px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors"
      >
        Marcar todas como lidas
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total de Notificações</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ notifications.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Não Lidas</p>
        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ unreadCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Lidas</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ readCount }}</p>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800">
      <div class="border-b border-gray-200 dark:border-gray-800">
        <nav class="flex -mb-px">
          <button
            @click="activeFilter = 'all'"
            :class="[
              'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
              activeFilter === 'all'
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'
            ]"
          >
            Todas ({{ notifications.length }})
          </button>
          <button
            @click="activeFilter = 'unread'"
            :class="[
              'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
              activeFilter === 'unread'
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'
            ]"
          >
            Não Lidas ({{ unreadCount }})
          </button>
          <button
            @click="activeFilter = 'read'"
            :class="[
              'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
              activeFilter === 'read'
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'
            ]"
          >
            Lidas ({{ readCount }})
          </button>
        </nav>
      </div>

      <!-- Notifications List -->
      <div class="divide-y divide-gray-200 dark:divide-gray-800">
        <div v-if="loading" class="flex items-center justify-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        </div>

        <div v-else-if="filteredNotifications.length === 0" class="text-center py-12">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            {{ activeFilter === 'unread' ? 'Nenhuma notificação não lida' : 'Nenhuma notificação' }}
          </h3>
          <p class="text-gray-500 dark:text-gray-400">
            {{ activeFilter === 'unread' ? 'Você está em dia!' : 'Aguarde novas atualizações do sistema' }}
          </p>
        </div>

        <div
          v-for="notification in filteredNotifications"
          :key="notification.id"
          @click="handleNotificationClick(notification)"
          :class="[
            'p-4 transition-colors cursor-pointer',
            notification.read_at
              ? 'hover:bg-gray-50 dark:hover:bg-gray-800/50'
              : 'bg-primary-50 dark:bg-primary-900/10 hover:bg-primary-100 dark:hover:bg-primary-900/20'
          ]"
        >
          <div class="flex items-start gap-4">
            <!-- Icon -->
            <div :class="[
              'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center',
              getNotificationIconClass(notification.type)
            ]">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIconPath(notification.type)" />
              </svg>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ notification.title }}
                  </p>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ notification.message }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                    {{ formatDate(notification.created_at) }}
                  </p>
                </div>

                <!-- Unread indicator -->
                <div v-if="!notification.read_at" class="flex-shrink-0">
                  <span class="inline-block w-2 h-2 bg-primary-600 rounded-full"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const notifications = ref([]);
const loading = ref(false);
const activeFilter = ref('all');

const emit = defineEmits(['open-checklist', 'open-machine']);

onMounted(() => {
  loadNotifications();
});

const filteredNotifications = computed(() => {
  if (activeFilter.value === 'unread') {
    return notifications.value.filter(n => !n.read_at);
  } else if (activeFilter.value === 'read') {
    return notifications.value.filter(n => n.read_at);
  }
  return notifications.value;
});

const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read_at).length;
});

const readCount = computed(() => {
  return notifications.value.filter(n => n.read_at).length;
});

async function loadNotifications() {
  try {
    loading.value = true;
    const response = await axios.get('/api/notifications');

    // Handle response structure - API returns { success, data, unread_count, pagination }
    if (response.data.success && Array.isArray(response.data.data)) {
      notifications.value = response.data.data;
    } else {
      console.error('Invalid response format:', response.data);
      notifications.value = [];
    }
  } catch (error) {
    console.error('Error loading notifications:', error);
    notifications.value = [];
  } finally {
    loading.value = false;
  }
}

async function handleNotificationClick(notification) {
  // Mark as read
  if (!notification.read_at) {
    await markAsRead(notification.id);
  }

  // Navigate based on action_url or data
  if (notification.action_url) {
    window.location.href = notification.action_url;
  } else if (notification.data) {
    const data = notification.data;

    if (data.checklist_id) {
      // Emit event to open checklist modal
      emit('open-checklist', data.checklist_id);
    } else if (data.machine_id) {
      // Emit event to open machine modal
      emit('open-machine', data.machine_id);
    } else if (data.url) {
      // Navigate to URL if provided
      window.location.href = data.url;
    }
  }
}

async function markAsRead(notificationId) {
  try {
    await axios.post(`/api/notifications/${notificationId}/read`);
    const notification = notifications.value.find(n => n.id === notificationId);
    if (notification) {
      notification.read_at = new Date().toISOString();
    }
  } catch (error) {
    console.error('Error marking notification as read:', error);
  }
}

async function markAllAsRead() {
  try {
    await axios.post('/api/notifications/mark-all-read');
    notifications.value.forEach(n => {
      if (!n.read_at) {
        n.read_at = new Date().toISOString();
      }
    });
  } catch (error) {
    console.error('Error marking all as read:', error);
  }
}

function getNotificationIconClass(type) {
  const classes = {
    'checklist_created': 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
    'checklist_completed': 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400',
    'checklist_interrupted': 'bg-red-100 text-red-600 dark:bg-red-900/20 dark:text-red-400',
    'patient_created': 'bg-purple-100 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
    'patient_status_changed': 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400',
    'cleaning_completed': 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-400',
    'machine_status': 'bg-orange-100 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400',
  };
  return classes[type] || 'bg-gray-100 text-gray-600 dark:bg-gray-900/20 dark:text-gray-400';
}

function getNotificationIconPath(type) {
  const icons = {
    'checklist_created': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    'checklist_completed': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    'checklist_interrupted': 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    'patient_created': 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    'patient_status_changed': 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
    'cleaning_completed': 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
    'machine_status': 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
  };
  return icons[type] || 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
}

function formatDate(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'Agora mesmo';
  if (diffMins < 60) return `${diffMins} minuto${diffMins > 1 ? 's' : ''} atrás`;
  if (diffHours < 24) return `${diffHours} hora${diffHours > 1 ? 's' : ''} atrás`;
  if (diffDays < 7) return `${diffDays} dia${diffDays > 1 ? 's' : ''} atrás`;

  return date.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}
</script>

<style scoped>
.notifications-view {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
