<template>
  <div class="dashboard-view space-y-6">
    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div
        v-for="metric in metrics"
        :key="metric.label"
        class="metric-card bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6 hover:shadow-lg transition-shadow duration-200"
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
import { ref, onMounted } from 'vue';
import {
  ClipboardDocumentCheckIcon,
  CpuChipIcon,
  UsersIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline';

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

onMounted(async () => {
  await Promise.all([
    loadStats(),
    loadSessionsByShift(),
    loadRecentActivity(),
  ]);
  loading.value = false;
});

async function loadStats() {
  try {
    const response = await fetch('/api/dashboard/stats', {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' },
    });

    if (response.ok) {
      const { data } = await response.json();

      metrics.value[0].value = data.checklists.value.toString();
      metrics.value[0].change = data.checklists.change;

      metrics.value[1].value = `${data.machines.active}/${data.machines.total}`;
      metrics.value[1].change = data.machines.change;

      metrics.value[2].value = data.patients.value.toString();
      metrics.value[2].change = data.patients.change;

      metrics.value[3].value = `${data.conformity.value}%`;
      metrics.value[3].change = data.conformity.change;
    }
  } catch (error) {
    console.error('Error loading stats:', error);
  }
}

async function loadSessionsByShift() {
  try {
    const response = await fetch('/api/dashboard/sessions-by-shift', {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' },
    });

    if (response.ok) {
      const { data } = await response.json();
      sessionsByShiftData.value = data;
    }
  } catch (error) {
    console.error('Error loading sessions by shift:', error);
  }
}

async function loadRecentActivity() {
  try {
    const response = await fetch('/api/dashboard/recent-activity', {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' },
    });

    if (response.ok) {
      const { data } = await response.json();
      recentActivity.value = data;
    }
  } catch (error) {
    console.error('Error loading recent activity:', error);
  }
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
