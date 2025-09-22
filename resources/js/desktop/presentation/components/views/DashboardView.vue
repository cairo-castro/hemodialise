<template>
  <div class="space-y-6">
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Sessions Today -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Sessões Hoje</p>
            <p class="text-3xl font-bold text-gray-900">24</p>
            <p class="text-sm text-green-600 font-medium">↗ +12% vs ontem</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Active Patients -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Pacientes Ativos</p>
            <p class="text-3xl font-bold text-gray-900">156</p>
            <p class="text-sm text-green-600 font-medium">+3 novos esta semana</p>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM5 8a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2V8z"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Machines Status -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Máquinas Online</p>
            <p class="text-3xl font-bold text-gray-900">8<span class="text-lg text-gray-500">/10</span></p>
            <p class="text-sm text-yellow-600 font-medium">2 em manutenção</p>
          </div>
          <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Alerts -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Alertas Ativos</p>
            <p class="text-3xl font-bold text-gray-900">3</p>
            <p class="text-sm text-red-600 font-medium">2 críticos</p>
          </div>
          <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Chart -->
      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sessões por Período</h3>
        <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
          <div class="text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
            </svg>
            <p class="text-sm">Gráfico de sessões em tempo real</p>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Atividades Recentes</h3>
        <div class="space-y-4">
          <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start space-x-3">
            <div :class="getActivityColor(activity.type)" class="activity-indicator rounded-full mt-2"></div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ activity.message }}</p>
              <p class="text-xs text-gray-500">{{ activity.timestamp }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Mock data - em um projeto real, viria de uma API
const recentActivities = ref([
  {
    id: 1,
    type: 'success',
    message: 'Sessão HD-03 finalizada com sucesso',
    timestamp: 'há 5 minutos'
  },
  {
    id: 2,
    type: 'warning',
    message: 'Máquina HD-05 entrou em manutenção programada',
    timestamp: 'há 15 minutos'
  },
  {
    id: 3,
    type: 'info',
    message: 'Novo paciente cadastrado no sistema',
    timestamp: 'há 32 minutos'
  },
  {
    id: 4,
    type: 'error',
    message: 'Alerta de pressão detectado - HD-07',
    timestamp: 'há 1 hora'
  }
]);

const getActivityColor = (type) => {
  const colors = {
    success: 'bg-green-500',
    warning: 'bg-yellow-500',
    info: 'bg-blue-500',
    error: 'bg-red-500'
  };
  return colors[type] || 'bg-gray-500';
};
</script>

<style scoped>
.card-hover {
  transform: scale(1);
  transition: transform 0.2s ease-in-out;
}

.card-hover:hover {
  transform: scale(1.05);
}

.activity-indicator {
  width: 0.5rem;
  height: 0.5rem;
}
</style>