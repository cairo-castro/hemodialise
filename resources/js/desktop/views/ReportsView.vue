<template>
  <div class="reports-view space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Relatórios</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Análises detalhadas e comparações de desempenho
        </p>
      </div>
      <button
        @click="refreshData"
        class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors"
      >
        <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Atualizar Dados
      </button>
    </div>

    <!-- Report Type Selection -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Selecione o Tipo de Relatório</h3>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <button
          v-for="type in reportTypes"
          :key="type.id"
          @click="selectedReportType = type.id"
          :class="[
            'p-4 rounded-lg border-2 transition-all text-left hover:shadow-md',
            selectedReportType === type.id
              ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20'
              : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
          ]"
        >
          <component :is="type.icon" class="w-8 h-8 mb-2" :class="selectedReportType === type.id ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400'" />
          <h4 class="font-semibold text-gray-900 dark:text-white mb-1">{{ type.title }}</h4>
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ type.description }}</p>
        </button>
      </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Período</h3>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Data Inicial</label>
          <input
            v-model="dateRange.start"
            type="date"
            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-500"
          />
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Data Final</label>
          <input
            v-model="dateRange.end"
            type="date"
            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-500"
          />
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Período Rápido</label>
          <select
            @change="applyQuickPeriod"
            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
            <option value="">Personalizado</option>
            <option value="today">Hoje</option>
            <option value="week">Última Semana</option>
            <option value="month">Último Mês</option>
            <option value="quarter">Último Trimestre</option>
            <option value="year">Último Ano</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Report Content -->
    <div v-if="selectedReportType === 'checklists'" class="space-y-6">
      <!-- Checklists Report -->
      <ChecklistsReport :key="reportKey" :dateRange="dateRange" />
    </div>

    <div v-else-if="selectedReportType === 'cleaning'" class="space-y-6">
      <!-- Cleaning Report -->
      <CleaningReport :key="reportKey" :dateRange="dateRange" />
    </div>

    <div v-else-if="selectedReportType === 'patients'" class="space-y-6">
      <!-- Patients Report -->
      <PatientsReport :key="reportKey" :dateRange="dateRange" />
    </div>

    <div v-else-if="selectedReportType === 'performance'" class="space-y-6">
      <!-- Performance Report -->
      <PerformanceReport :key="reportKey" :dateRange="dateRange" />
    </div>

    <div v-else class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
      <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Selecione um tipo de relatório</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400">Escolha um tipo de relatório acima para visualizar os dados</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import {
  ClipboardDocumentCheckIcon,
  SparklesIcon,
  UsersIcon,
  ChartBarIcon,
} from '@heroicons/vue/24/outline';
import ChecklistsReport from '../components/reports/ChecklistsReport.vue';
import CleaningReport from '../components/reports/CleaningReport.vue';
import PatientsReport from '../components/reports/PatientsReport.vue';
import PerformanceReport from '../components/reports/PerformanceReport.vue';

const selectedReportType = ref('');
const reportKey = ref(0); // Key to force re-render

const reportTypes = [
  {
    id: 'checklists',
    title: 'Checklists de Segurança',
    description: 'Análise de conformidade e tendências',
    icon: ClipboardDocumentCheckIcon
  },
  {
    id: 'cleaning',
    title: 'Limpeza e Desinfecção',
    description: 'Controle de limpeza e higienização',
    icon: SparklesIcon
  },
  {
    id: 'patients',
    title: 'Pacientes',
    description: 'Estatísticas e distribuição',
    icon: UsersIcon
  },
  {
    id: 'performance',
    title: 'Desempenho da Unidade',
    description: 'Comparativo e performance geral',
    icon: ChartBarIcon
  }
];

const dateRange = reactive({
  start: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  end: new Date().toISOString().split('T')[0]
});

function applyQuickPeriod(event) {
  const period = event.target.value;
  const today = new Date();

  switch (period) {
    case 'today':
      dateRange.start = today.toISOString().split('T')[0];
      dateRange.end = today.toISOString().split('T')[0];
      break;
    case 'week':
      dateRange.start = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
      dateRange.end = today.toISOString().split('T')[0];
      break;
    case 'month':
      dateRange.start = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
      dateRange.end = today.toISOString().split('T')[0];
      break;
    case 'quarter':
      dateRange.start = new Date(today.getTime() - 90 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
      dateRange.end = today.toISOString().split('T')[0];
      break;
    case 'year':
      dateRange.start = new Date(today.getTime() - 365 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
      dateRange.end = today.toISOString().split('T')[0];
      break;
  }
}

function refreshData() {
  // Force re-render of components
  reportKey.value++;
}

// Watch for date range changes and trigger refresh
watch(() => [dateRange.start, dateRange.end], () => {
  refreshData();
}, { deep: true });
</script>

<style scoped>
.reports-view {
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
