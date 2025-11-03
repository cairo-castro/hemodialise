<template>
  <div class="space-y-6">
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance Geral da Unidade</h3>
      <div id="performance-chart">
        <apexchart type="line" height="350" :options="performanceOptions" :series="performanceSeries"></apexchart>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Comparativo Mensal</h3>
        <div id="monthly-comparison-chart">
          <apexchart type="bar" height="300" :options="monthlyOptions" :series="monthlySeries"></apexchart>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Distribuição de Sessões por Status</h3>
        <div id="session-status-chart">
          <apexchart type="donut" height="300" :options="sessionStatusOptions" :series="sessionStatusSeries"></apexchart>
        </div>
      </div>
    </div>

    <!-- Export Button -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Exportar Relatório</h3>
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
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { useExcelExport } from '../../composables/useExcelExport';

const apexchart = VueApexCharts;
const { exportWithCharts } = useExcelExport();
const props = defineProps({ dateRange: { type: Object, required: true } });

const performanceSeries = ref([
  { name: 'Checklists', data: [145, 152, 148, 156, 162, 159, 165] },
  { name: 'Limpezas', data: [98, 102, 105, 108, 103, 110, 112] },
  { name: 'Taxa Conformidade', data: [92, 93, 94, 93, 95, 94, 96] }
]);

const performanceOptions = computed(() => ({
  chart: { type: 'line', toolbar: { show: false } },
  stroke: { curve: 'smooth', width: 3 },
  colors: ['#3B82F6', '#8B5CF6', '#10B981'],
  xaxis: {
    categories: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6', 'Sem 7'],
    labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } }
  },
  yaxis: { labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } } },
  grid: { borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB' },
  legend: { position: 'top', labels: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } }
}));

const monthlySeries = ref([
  { name: 'Mês Atual', data: [165, 112, 156] },
  { name: 'Mês Anterior', data: [152, 105, 148] }
]);

const monthlyOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
  colors: ['#3B82F6', '#9CA3AF'],
  xaxis: {
    categories: ['Checklists', 'Limpezas', 'Procedimentos'],
    labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } }
  },
  yaxis: { labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } } },
  grid: { borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB' },
  legend: { position: 'top', labels: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } }
}));

const sessionStatusSeries = ref([1175, 72]);
const sessionStatusOptions = computed(() => ({
  chart: { type: 'donut' },
  labels: ['Concluído', 'Interrompido'],
  colors: ['#10B981', '#EF4444'],
  legend: {
    position: 'bottom',
    labels: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' }
  },
  plotOptions: {
    pie: {
      donut: {
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total de Sessões',
            formatter: () => '1,247'
          }
        }
      }
    }
  }
}));

async function exportReport() {
  try {
    const sheets = [
      {
        sheetName: 'Performance Semanal',
        data: performanceOptions.value.xaxis.categories.map((week, index) => ({
          'Período': week,
          'Checklists': performanceSeries.value[0].data[index],
          'Limpezas': performanceSeries.value[1].data[index],
          'Taxa Conformidade (%)': performanceSeries.value[2].data[index]
        }))
      },
      {
        sheetName: 'Comparativo Mensal',
        data: monthlyOptions.value.xaxis.categories.map((category, index) => ({
          'Categoria': category,
          'Mês Atual': monthlySeries.value[0].data[index],
          'Mês Anterior': monthlySeries.value[1].data[index],
          'Variação': monthlySeries.value[0].data[index] - monthlySeries.value[1].data[index]
        }))
      },
      {
        sheetName: 'Status das Sessões',
        data: sessionStatusOptions.value.labels.map((label, index) => ({
          'Status': label,
          'Quantidade': sessionStatusSeries.value[index],
          'Percentual': `${((sessionStatusSeries.value[index] / sessionStatusSeries.value.reduce((a, b) => a + b, 0)) * 100).toFixed(1)}%`
        }))
      }
    ];

    const chartSelectors = [
      {
        selector: '#performance-chart',
        sheetName: 'Gráfico Performance',
        title: 'Performance Geral da Unidade ao Longo do Tempo'
      },
      {
        selector: '#monthly-comparison-chart',
        sheetName: 'Gráfico Comparativo',
        title: 'Comparativo Mensal de Procedimentos'
      },
      {
        selector: '#session-status-chart',
        sheetName: 'Gráfico Sessões',
        title: 'Distribuição de Sessões por Status'
      }
    ];

    const success = await exportWithCharts(sheets, 'relatorio_performance', chartSelectors);

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
