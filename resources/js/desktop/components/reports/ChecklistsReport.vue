<template>
  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total de Checklists</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
            <ClipboardDocumentCheckIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Taxa de Conformidade</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ stats.conformityRate }}%</p>
          </div>
          <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
            <CheckCircleIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Completados</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.completed }}</p>
          </div>
          <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
            <CheckIcon class="w-6 h-6 text-purple-600 dark:text-purple-400" />
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Interrompidos</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ stats.interrupted }}</p>
          </div>
          <div class="w-12 h-12 bg-red-100 dark:bg-red-900/20 rounded-lg flex items-center justify-center">
            <XCircleIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Conformity Trend -->
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tendência de Conformidade</h3>
        <div id="conformity-trend-chart">
          <apexchart
            type="line"
            height="300"
            :options="conformityTrendOptions"
            :series="conformityTrendSeries"
          ></apexchart>
        </div>
      </div>

      <!-- Phase Distribution -->
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Distribuição por Fase</h3>
        <div id="phase-distribution-chart">
          <apexchart
            type="donut"
            height="300"
            :options="phaseDistributionOptions"
            :series="phaseDistributionSeries"
          ></apexchart>
        </div>
      </div>
    </div>

    <!-- Detailed Table -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detalhamento por Turno</h3>
        <button
          @click="exportReport"
          class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Exportar Excel
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Turno</th>
              <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Total</th>
              <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Conformes</th>
              <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Taxa</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="shift in shiftStats" :key="shift.name">
              <td class="px-4 py-3 text-gray-900 dark:text-white">{{ shift.name }}</td>
              <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ shift.total }}</td>
              <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">{{ shift.conforming }}</td>
              <td class="px-4 py-3 text-right">
                <span class="px-2 py-1 bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400 rounded-full text-xs font-medium">
                  {{ shift.rate }}%
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import {
  ClipboardDocumentCheckIcon,
  CheckCircleIcon,
  CheckIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline';
import VueApexCharts from 'vue3-apexcharts';
import { useExcelExport } from '../../composables/useExcelExport';

const apexchart = VueApexCharts;
const { exportWithCharts } = useExcelExport();

const props = defineProps({
  dateRange: {
    type: Object,
    required: true
  }
});

// Stats from API
const stats = ref({
  total: 0,
  conformityRate: 0,
  completed: 0,
  interrupted: 0,
  inProgress: 0
});

const isLoading = ref(false);

// Load data from API
async function loadReportData() {
  if (!props.dateRange.start || !props.dateRange.end) return;

  isLoading.value = true;
  try {
    const response = await fetch(`/api/reports/checklists?start_date=${props.dateRange.start}&end_date=${props.dateRange.end}`, {
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    if (!response.ok) {
      throw new Error('Erro ao carregar relatório');
    }

    const result = await response.json();

    if (result.success) {
      stats.value = result.data.stats;
      shiftStats.value = result.data.shiftStats || [];

      // Update conformity trend chart
      if (result.data.conformityTrend) {
        conformityTrendSeries.value = [{
          name: 'Taxa de Conformidade',
          data: result.data.conformityTrend.data
        }];
        conformityTrendCategories.value = result.data.conformityTrend.categories;
      }

      // Update phase distribution chart
      if (result.data.phaseDistribution) {
        phaseDistributionSeries.value = result.data.phaseDistribution.series;
        phaseDistributionLabels.value = result.data.phaseDistribution.labels;
      }
    }
  } catch (error) {
    console.error('Erro ao carregar relatório:', error);
  } finally {
    isLoading.value = false;
  }
}

// Watch for date range changes
watch(() => props.dateRange, () => {
  loadReportData();
}, { deep: true });

// Load on mount
onMounted(() => {
  loadReportData();
});

const conformityTrendSeries = ref([{
  name: 'Taxa de Conformidade',
  data: []
}]);

const conformityTrendCategories = ref([]);

const conformityTrendOptions = computed(() => ({
  chart: {
    type: 'line',
    toolbar: { show: false }
  },
  stroke: {
    curve: 'smooth',
    width: 3
  },
  colors: ['#10B981'],
  xaxis: {
    categories: conformityTrendCategories.value,
    labels: {
      style: {
        colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280'
      }
    }
  },
  yaxis: {
    min: 0,
    max: 100,
    labels: {
      formatter: (val) => val.toFixed(1) + '%',
      style: {
        colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280'
      }
    }
  },
  grid: {
    borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
  }
}));

const phaseDistributionSeries = ref([]);
const phaseDistributionLabels = ref(['Pré-Diálise', 'Durante Sessão', 'Pós-Diálise', 'Interrompido']);

const phaseDistributionOptions = computed(() => ({
  chart: {
    type: 'donut'
  },
  labels: phaseDistributionLabels.value,
  colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444'],
  legend: {
    position: 'bottom',
    labels: {
      colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280'
    }
  }
}));

const shiftStats = ref([]);

async function exportReport() {
  try {
    // Preparar dados para exportação
    const sheets = [
      {
        sheetName: 'Resumo',
        data: [
          {
            'Métrica': 'Total de Checklists',
            'Valor': stats.value.total
          },
          {
            'Métrica': 'Taxa de Conformidade',
            'Valor': `${stats.value.conformityRate}%`
          },
          {
            'Métrica': 'Completados',
            'Valor': stats.value.completed
          },
          {
            'Métrica': 'Interrompidos',
            'Valor': stats.value.interrupted
          }
        ]
      },
      {
        sheetName: 'Por Turno',
        data: shiftStats.value.map(shift => ({
          'Turno': shift.name,
          'Total': shift.total,
          'Conformes': shift.conforming,
          'Taxa de Conformidade': `${shift.rate}%`
        }))
      },
      {
        sheetName: 'Tendência Semanal',
        data: conformityTrendCategories.value.map((week, index) => ({
          'Período': week,
          'Taxa de Conformidade (%)': conformityTrendSeries.value[0].data[index]
        }))
      },
      {
        sheetName: 'Por Fase',
        data: phaseDistributionLabels.value.map((label, index) => ({
          'Fase': label,
          'Quantidade': phaseDistributionSeries.value[index] || 0,
          'Percentual': phaseDistributionSeries.value.reduce((a, b) => a + b, 0) > 0
            ? `${((phaseDistributionSeries.value[index] || 0) / phaseDistributionSeries.value.reduce((a, b) => a + b, 0) * 100).toFixed(1)}%`
            : '0%'
        }))
      }
    ];

    // Define chart selectors for export
    const chartSelectors = [
      {
        selector: '#conformity-trend-chart',
        sheetName: 'Gráfico Tendência',
        title: 'Tendência de Conformidade ao Longo do Tempo'
      },
      {
        selector: '#phase-distribution-chart',
        sheetName: 'Gráfico Fases',
        title: 'Distribuição de Checklists por Fase'
      }
    ];

    const success = await exportWithCharts(sheets, 'relatorio_checklists', chartSelectors);

    if (success) {
      showSuccessToast('Relatório exportado com sucesso! Os gráficos estão incluídos no arquivo Excel.');
    } else {
      showErrorToast('Erro ao exportar relatório');
    }
  } catch (error) {
    console.error('Erro ao exportar:', error);
    showErrorToast('Erro ao exportar relatório');
  }
}

function showSuccessToast(message) {
  const toast = document.createElement('div');
  toast.className = 'fixed top-4 right-4 z-[100] px-6 py-4 bg-green-600 text-white rounded-lg shadow-lg flex items-center gap-3 animate-slide-in-right';
  toast.innerHTML = `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>
    <span class="font-medium">${message}</span>
  `;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'slide-out-right 0.3s ease-out forwards';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

function showErrorToast(message) {
  const toast = document.createElement('div');
  toast.className = 'fixed top-4 right-4 z-[100] px-6 py-4 bg-red-600 text-white rounded-lg shadow-lg flex items-center gap-3 animate-slide-in-right';
  toast.innerHTML = `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
    </svg>
    <span class="font-medium">${message}</span>
  `;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'slide-out-right 0.3s ease-out forwards';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}
</script>
