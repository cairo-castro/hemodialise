<template>
  <div class="dashboard-view space-y-6">
    <!-- Live Update Indicator -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-2">
        <div class="relative">
          <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
          <div class="absolute inset-0 w-2 h-2 bg-green-500 rounded-full animate-ping"></div>
        </div>
        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
          Atualizações em tempo real ativas
        </span>
      </div>
      <button
        @click="refreshAll"
        class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        :disabled="statsPolling.loading.value"
      >
        <svg class="w-4 h-4 inline-block mr-1" :class="{ 'animate-spin': statsPolling.loading.value }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Atualizar Agora
      </button>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div
        v-for="metric in metrics"
        :key="metric.label"
        class="metric-card bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6 hover:shadow-lg transition-all duration-200"
        :class="{ 'ring-2 ring-blue-500 ring-opacity-50': statsPolling.loading.value }"
      >
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ metric.label }}</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ metric.value }}</p>
            <div class="flex items-center mt-2">
              <span
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                :class="metric.change >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'"
              >
                {{ metric.change >= 0 ? '+' : '' }}{{ metric.change }}%
              </span>
            </div>
          </div>
          <div
            class="w-12 h-12 rounded-lg flex items-center justify-center"
            :class="metric.bgColor"
          >
            <component :is="metric.icon" class="w-6 h-6 text-white" />
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Sessions by Shift Chart -->
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sessões por Turno - Últimos 7 Dias</h3>
        <div v-if="sessionsByShiftData" class="h-64">
          <div class="h-full flex items-end justify-between space-x-2">
            <div
              v-for="(label, index) in sessionsByShiftData.labels"
              :key="index"
              class="flex-1 flex flex-col items-center space-y-1"
            >
              <div class="w-full flex flex-col items-center space-y-1 flex-1 justify-end">
                <!-- Matutino -->
                <div
                  v-if="sessionsByShiftData.datasets[0].data[index] > 0"
                  class="w-full bg-blue-500 rounded-t transition-all hover:bg-blue-600"
                  :style="{ height: `${getBarHeight(sessionsByShiftData.datasets[0].data[index])}%` }"
                  :title="`Matutino: ${sessionsByShiftData.datasets[0].data[index]}`"
                ></div>
                <!-- Vespertino -->
                <div
                  v-if="sessionsByShiftData.datasets[1].data[index] > 0"
                  class="w-full bg-green-500 transition-all hover:bg-green-600"
                  :style="{ height: `${getBarHeight(sessionsByShiftData.datasets[1].data[index])}%` }"
                  :title="`Vespertino: ${sessionsByShiftData.datasets[1].data[index]}`"
                ></div>
                <!-- Noturno -->
                <div
                  v-if="sessionsByShiftData.datasets[2].data[index] > 0"
                  class="w-full bg-purple-500 rounded-b transition-all hover:bg-purple-600"
                  :style="{ height: `${getBarHeight(sessionsByShiftData.datasets[2].data[index])}%` }"
                  :title="`Noturno: ${sessionsByShiftData.datasets[2].data[index]}`"
                ></div>
              </div>
              <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">{{ label }}</p>
            </div>
          </div>
        </div>
        <div v-else class="h-64 flex items-center justify-center">
          <p class="text-gray-500 dark:text-gray-400">Carregando...</p>
        </div>
        <!-- Legend -->
        <div v-if="sessionsByShiftData" class="flex items-center justify-center space-x-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
          <div class="flex items-center space-x-2">
            <div class="w-3 h-3 bg-blue-500 rounded"></div>
            <span class="text-sm text-gray-600 dark:text-gray-400">Matutino</span>
          </div>
          <div class="flex items-center space-x-2">
            <div class="w-3 h-3 bg-green-500 rounded"></div>
            <span class="text-sm text-gray-600 dark:text-gray-400">Vespertino</span>
          </div>
          <div class="flex items-center space-x-2">
            <div class="w-3 h-3 bg-purple-500 rounded"></div>
            <span class="text-sm text-gray-600 dark:text-gray-400">Noturno</span>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Atividade Recente</h3>
        <div v-if="recentActivity.length > 0" class="space-y-2">
          <div
            v-for="(activity, index) in recentActivity"
            :key="index"
            class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors cursor-pointer"
          >
            <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0" :class="activity.dotColor"></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ activity.title }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ activity.description }}</p>
            </div>
            <span class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0">{{ activity.time }}</span>
          </div>
        </div>
        <div v-else-if="loading" class="h-64 flex items-center justify-center">
          <p class="text-gray-500 dark:text-gray-400">Carregando...</p>
        </div>
        <div v-else class="h-64 flex items-center justify-center">
          <p class="text-gray-500 dark:text-gray-400">Nenhuma atividade recente</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import {
  ClipboardDocumentCheckIcon,
  CpuChipIcon,
  UsersIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline';
import { usePolling } from '../composables/usePolling';

const loading = ref(true);
const metrics = ref([
  {
    label: 'Checklists Hoje',
    value: '...',
    change: 0,
    icon: ClipboardDocumentCheckIcon,
    bgColor: 'bg-blue-500',
  },
  {
    label: 'Máquinas Ativas',
    value: '...',
    change: 0,
    icon: CpuChipIcon,
    bgColor: 'bg-green-500',
  },
  {
    label: 'Pacientes',
    value: '...',
    change: 0,
    icon: UsersIcon,
    bgColor: 'bg-purple-500',
  },
  {
    label: 'Conformidade',
    value: '...',
    change: 0,
    icon: CheckCircleIcon,
    bgColor: 'bg-orange-500',
  },
]);

const sessionsByShiftData = ref(null);
const recentActivity = ref([]);

// Setup polling for stats (every 10 seconds)
const statsPolling = usePolling(loadStats, {
  interval: 10000,
  immediate: true,
});

// Setup polling for sessions by shift (every 30 seconds)
const sessionsPolling = usePolling(loadSessionsByShift, {
  interval: 30000,
  immediate: true,
});

// Setup polling for recent activity (every 15 seconds)
const activityPolling = usePolling(loadRecentActivity, {
  interval: 15000,
  immediate: true,
});

// Watch for initial data load
watch([statsPolling.data, sessionsPolling.data, activityPolling.data], () => {
  if (statsPolling.data.value && sessionsPolling.data.value && activityPolling.data.value) {
    loading.value = false;
  }
});

// Manual refresh function
const refreshAll = () => {
  statsPolling.refresh();
  sessionsPolling.refresh();
  activityPolling.refresh();
};

async function loadStats() {
  const response = await fetch('/api/dashboard/stats', {
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' },
  });

  if (!response.ok) {
    throw new Error(`Stats API error: ${response.status}`);
  }

  const result = await response.json();
  const { data } = result;

  // Update metrics with new data
  metrics.value[0].value = data.checklists.value.toString();
  metrics.value[0].change = data.checklists.change;

  metrics.value[1].value = `${data.machines.active}/${data.machines.total}`;
  metrics.value[1].change = data.machines.change;

  metrics.value[2].value = data.patients.value.toString();
  metrics.value[2].change = data.patients.change;

  metrics.value[3].value = `${data.conformity.value}%`;
  metrics.value[3].change = data.conformity.change;

  return data;
}

async function loadSessionsByShift() {
  const response = await fetch('/api/dashboard/sessions-by-shift', {
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' },
  });

  if (!response.ok) {
    throw new Error(`Sessions API error: ${response.status}`);
  }

  const { data } = await response.json();
  sessionsByShiftData.value = data;
  return data;
}

async function loadRecentActivity() {
  const response = await fetch('/api/dashboard/recent-activity', {
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' },
  });

  if (!response.ok) {
    throw new Error(`Activity API error: ${response.status}`);
  }

  const { data } = await response.json();
  recentActivity.value = data;
  return data;
}

function getBarHeight(value) {
  if (!sessionsByShiftData.value) return 0;

  // Find max value across all datasets for proper scaling
  const allValues = sessionsByShiftData.value.datasets
    .flatMap(dataset => dataset.data)
    .filter(v => v > 0);

  const maxValue = Math.max(...allValues, 1);

  // Return height as percentage (minimum 10% for visibility)
  return Math.max((value / maxValue) * 90, 10);
}
</script>

<style scoped>
.metric-card {
  cursor: pointer;
}

.dashboard-view {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
