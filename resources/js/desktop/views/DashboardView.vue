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
      <!-- Revenue Chart -->
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sessões por Turno</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 dark:bg-gray-900 rounded-lg">
          <p class="text-gray-500 dark:text-gray-400">Gráfico de sessões</p>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Atividade Recente</h3>
        <div class="space-y-4">
          <div
            v-for="(activity, index) in recentActivity"
            :key="index"
            class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors"
          >
            <div class="w-2 h-2 rounded-full mt-2" :class="activity.dotColor"></div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ activity.title }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ activity.description }}</p>
            </div>
            <span class="text-xs text-gray-400 dark:text-gray-500">{{ activity.time }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import {
  ClipboardDocumentCheckIcon,
  CpuChipIcon,
  UsersIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline';

const metrics = ref([
  {
    label: 'Checklists Hoje',
    value: '24',
    change: 12,
    icon: ClipboardDocumentCheckIcon,
    bgColor: 'bg-blue-500',
  },
  {
    label: 'Máquinas Ativas',
    value: '8/10',
    change: -5,
    icon: CpuChipIcon,
    bgColor: 'bg-green-500',
  },
  {
    label: 'Pacientes',
    value: '156',
    change: 3,
    icon: UsersIcon,
    bgColor: 'bg-purple-500',
  },
  {
    label: 'Conformidade',
    value: '95%',
    change: 5,
    icon: CheckCircleIcon,
    bgColor: 'bg-orange-500',
  },
]);

const recentActivity = ref([
  {
    title: 'Checklist Concluído',
    description: 'Máquina HD-001 - Turno Matutino',
    time: '5 min',
    dotColor: 'bg-green-500',
  },
  {
    title: 'Manutenção Agendada',
    description: 'Máquina HD-003 - Próxima semana',
    time: '1 hora',
    dotColor: 'bg-yellow-500',
  },
  {
    title: 'Novo Paciente',
    description: 'Maria Silva - Unidade Central',
    time: '2 horas',
    dotColor: 'bg-blue-500',
  },
]);
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
