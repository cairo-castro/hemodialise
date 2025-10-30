<template>
  <div class="dashboard-view">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Dashboard</h2>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="bg-white rounded-lg shadow-sm p-6 border border-gray-200"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">{{ stat.label }}</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stat.value }}</p>
          </div>
          <div
            class="w-12 h-12 rounded-full flex items-center justify-center"
            :class="stat.bgColor"
          >
            <component :is="stat.icon" class="w-6 h-6 text-white" />
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
      <h3 class="text-xl font-semibold text-gray-900 mb-4">Atividade Recente</h3>

      <div v-if="loading" class="flex justify-center py-8">
        <svg class="animate-spin h-8 w-8 text-blue-600" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <div v-else-if="recentActivity.length === 0" class="text-center py-8 text-gray-500">
        Nenhuma atividade recente
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="(activity, index) in recentActivity"
          :key="index"
          class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0"
        >
          <div class="flex items-center space-x-4">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
              <component :is="getActivityIcon(activity.type)" class="w-5 h-5 text-blue-600" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
              <p class="text-xs text-gray-500">{{ activity.description }}</p>
            </div>
          </div>
          <span class="text-xs text-gray-400">{{ activity.time }}</span>
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

const stats = ref([
  {
    label: 'Checklists Hoje',
    value: '0',
    icon: ClipboardDocumentCheckIcon,
    bgColor: 'bg-blue-500',
  },
  {
    label: 'Máquinas Ativas',
    value: '0',
    icon: CpuChipIcon,
    bgColor: 'bg-green-500',
  },
  {
    label: 'Pacientes',
    value: '0',
    icon: UsersIcon,
    bgColor: 'bg-purple-500',
  },
  {
    label: 'Conformidade',
    value: '0%',
    icon: CheckCircleIcon,
    bgColor: 'bg-orange-500',
  },
]);

const recentActivity = ref([]);

onMounted(async () => {
  await loadDashboardData();
});

async function loadDashboardData() {
  try {
    // TODO: Replace with actual API calls
    // const response = await fetch('/api/dashboard');
    // const data = await response.json();

    // Simulating API delay
    await new Promise(resolve => setTimeout(resolve, 1000));

    // Mock data
    stats.value[0].value = '12';
    stats.value[1].value = '8';
    stats.value[2].value = '45';
    stats.value[3].value = '95%';

    recentActivity.value = [
      {
        type: 'checklist',
        title: 'Checklist Concluído',
        description: 'Máquina HD-001 - Turno Matutino',
        time: 'Há 5 minutos',
      },
      {
        type: 'machine',
        title: 'Manutenção Agendada',
        description: 'Máquina HD-003 - Próxima semana',
        time: 'Há 1 hora',
      },
      {
        type: 'patient',
        title: 'Novo Paciente Cadastrado',
        description: 'Maria Silva - Unidade Central',
        time: 'Há 2 horas',
      },
    ];
  } catch (error) {
    console.error('Failed to load dashboard data:', error);
  } finally {
    loading.value = false;
  }
}

function getActivityIcon(type) {
  const icons = {
    checklist: ClipboardDocumentCheckIcon,
    machine: CpuChipIcon,
    patient: UsersIcon,
  };
  return icons[type] || CheckCircleIcon;
}
</script>

<style scoped>
.dashboard-view {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
