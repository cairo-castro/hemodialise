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

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <button
        @click="navigateTo('/desktop/checklists')"
        class="flex items-center p-4 bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-md transition-all group"
      >
        <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/20 rounded-lg flex items-center justify-center mr-4 group-hover:bg-primary-200 dark:group-hover:bg-primary-900/30 transition-colors">
          <ClipboardDocumentCheckIcon class="w-6 h-6 text-primary-600 dark:text-primary-400" />
        </div>
        <div class="flex-1 text-left">
          <p class="text-sm font-medium text-gray-900 dark:text-white">Novo Checklist</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Criar checklist de segurança</p>
        </div>
        <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <button
        @click="navigateTo('/desktop/patients')"
        class="flex items-center p-4 bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-green-300 dark:hover:border-green-700 hover:shadow-md transition-all group"
      >
        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center mr-4 group-hover:bg-green-200 dark:group-hover:bg-green-900/30 transition-colors">
          <UsersIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
        </div>
        <div class="flex-1 text-left">
          <p class="text-sm font-medium text-gray-900 dark:text-white">Novo Paciente</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Cadastrar novo paciente</p>
        </div>
        <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <button
        @click="navigateTo('/desktop/machines')"
        class="flex items-center p-4 bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-purple-300 dark:hover:border-purple-700 hover:shadow-md transition-all group"
      >
        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center mr-4 group-hover:bg-purple-200 dark:group-hover:bg-purple-900/30 transition-colors">
          <CpuChipIcon class="w-6 h-6 text-purple-600 dark:text-purple-400" />
        </div>
        <div class="flex-1 text-left">
          <p class="text-sm font-medium text-gray-900 dark:text-white">Ver Máquinas</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Gerenciar equipamentos</p>
        </div>
        <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Sessions by Shift Chart -->
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sessões por Turno</h3>
          <span class="text-xs text-gray-500 dark:text-gray-400">Últimos 7 dias</span>
        </div>
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
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Atividade Recente</h3>
          <button
            @click="navigateTo('/desktop/checklists')"
            class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
          >
            Ver todas →
          </button>
        </div>
        <div v-if="recentActivity.length > 0" class="space-y-2">
          <div
            v-for="(activity, index) in recentActivity"
            :key="index"
            class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors cursor-pointer group"
            @click="handleActivityClick(activity)"
          >
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
              :class="activity.bgColor"
            >
              <component :is="getActivityIcon(activity.type)" class="w-5 h-5 text-white" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ activity.title }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1">{{ activity.description }}</p>
              <div class="flex items-center mt-2 space-x-3">
                <span class="text-xs text-gray-400 dark:text-gray-500">{{ activity.time }}</span>
                <span
                  v-if="activity.status"
                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                  :class="getStatusColor(activity.status)"
                >
                  {{ activity.status }}
                </span>
              </div>
            </div>
            <svg class="w-5 h-5 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
        </div>
        <div v-else-if="loading" class="h-64 flex items-center justify-center">
          <div class="text-center">
            <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin mx-auto mb-3"></div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Carregando atividades...</p>
          </div>
        </div>
        <div v-else class="h-64 flex items-center justify-center">
          <div class="text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma atividade recente</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Transactions/Records Table -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mt-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Checklists Recentes</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Últimas verificações de segurança realizadas</p>
        </div>
        <button
          @click="navigateTo('/desktop/checklists')"
          class="px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors"
        >
          Ver todos
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-800/50">
            <tr>
              <th class="px-4 py-3 text-left font-semibold">ID</th>
              <th class="px-4 py-3 text-left font-semibold">Data</th>
              <th class="px-4 py-3 text-left font-semibold">Paciente</th>
              <th class="px-4 py-3 text-left font-semibold">Máquina</th>
              <th class="px-4 py-3 text-left font-semibold">Turno</th>
              <th class="px-4 py-3 text-left font-semibold">Status</th>
              <th class="px-4 py-3 text-right font-semibold">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="record in tableRecords"
              :key="record.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">#{{ record.id }}</td>
              <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ record.date }}</td>
              <td class="px-4 py-3 text-gray-900 dark:text-white">{{ record.patient }}</td>
              <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ record.machine }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="getShiftColor(record.shift)"
                >
                  {{ record.shift }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="getStatusColor(record.status)"
                >
                  {{ record.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <button
                  @click="viewRecord(record.id)"
                  class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition-colors"
                >
                  Ver detalhes
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import {
  ClipboardDocumentCheckIcon,
  CpuChipIcon,
  UsersIcon,
  CheckCircleIcon,
  BeakerIcon,
  SparklesIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';
import { usePolling } from '../composables/usePolling';

const router = useRouter();

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
const tableRecords = ref([
  {
    id: 1234,
    date: '02/11/2025 08:30',
    patient: 'Maria Silva Santos',
    machine: 'Máquina 05',
    shift: 'Matutino',
    status: 'Concluído',
  },
  {
    id: 1233,
    date: '02/11/2025 07:45',
    patient: 'João Carlos Oliveira',
    machine: 'Máquina 03',
    shift: 'Matutino',
    status: 'Concluído',
  },
  {
    id: 1232,
    date: '01/11/2025 19:20',
    patient: 'Ana Paula Costa',
    machine: 'Máquina 08',
    shift: 'Noturno',
    status: 'Concluído',
  },
  {
    id: 1231,
    date: '01/11/2025 15:15',
    patient: 'Pedro Henrique Lima',
    machine: 'Máquina 02',
    shift: 'Vespertino',
    status: 'Interrompido',
  },
  {
    id: 1230,
    date: '01/11/2025 14:30',
    patient: 'Carla Souza Mendes',
    machine: 'Máquina 01',
    shift: 'Vespertino',
    status: 'Concluído',
  },
]);

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

// Navigation helpers
function navigateTo(path) {
  router.push(path);
}

function viewRecord(id) {
  console.log('View record:', id);
  // Navigate to checklist detail view
  router.push(`/desktop/checklists/${id}`);
}

function handleActivityClick(activity) {
  console.log('Activity clicked:', activity);
  // Navigate based on activity type
}

// Activity icon helper
function getActivityIcon(type) {
  const icons = {
    checklist: ClipboardDocumentCheckIcon,
    cleaning: SparklesIcon,
    disinfection: BeakerIcon,
    warning: ExclamationTriangleIcon,
  };
  return icons[type] || ClipboardDocumentCheckIcon;
}

// Status color helper
function getStatusColor(status) {
  const colors = {
    'Concluído': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    'Interrompido': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    'Em Andamento': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    'Pendente': 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
  };
  return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
}

// Shift color helper
function getShiftColor(shift) {
  const colors = {
    'Matutino': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    'Vespertino': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    'Noturno': 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
  };
  return colors[shift] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
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

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
