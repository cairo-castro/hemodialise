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
import { ref, computed, watch, onMounted } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { useExcelExport } from '../../composables/useExcelExport';

const apexchart = VueApexCharts;
const { exportWithCharts } = useExcelExport();
const props = defineProps({ dateRange: { type: Object, required: true } });

const isLoading = ref(false);

// Load data from API
async function loadReportData() {
  if (!props.dateRange.start || !props.dateRange.end) return;

  isLoading.value = true;
  try {
    const response = await fetch(`/api/reports/performance?start_date=${props.dateRange.start}&end_date=${props.dateRange.end}`, {
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
      // Update weekly performance chart
      if (result.data.weeklyPerformance) {
        performanceSeries.value = [
          { name: 'Checklists', data: result.data.weeklyPerformance.checklists },
          { name: 'Limpezas', data: result.data.weeklyPerformance.cleanings },
          { name: 'Taxa Conformidade', data: result.data.weeklyPerformance.conformityRate }
        ];
        performanceCategories.value = result.data.weeklyPerformance.categories;
      }

      // Update monthly comparison chart
      if (result.data.monthlyComparison) {
        monthlySeries.value = [
          { name: 'Período Atual', data: result.data.monthlyComparison.current },
          { name: 'Período Anterior', data: result.data.monthlyComparison.previous }
        ];
        monthlyCategories.value = result.data.monthlyComparison.categories;
      }

      // Update session status chart
      if (result.data.sessionStatus) {
        sessionStatusSeries.value = result.data.sessionStatus.series;
        sessionStatusLabels.value = result.data.sessionStatus.labels;
        sessionStatusTotal.value = result.data.sessionStatus.total;
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

const performanceSeries = ref([
  { name: 'Checklists', data: [] },
  { name: 'Limpezas', data: [] },
  { name: 'Taxa Conformidade', data: [] }
]);

const performanceCategories = ref([]);

const performanceOptions = computed(() => ({
  chart: { type: 'line', toolbar: { show: false } },
  stroke: { curve: 'smooth', width: 3 },
  colors: ['#3B82F6', '#8B5CF6', '#10B981'],
  xaxis: {
    categories: performanceCategories.value,
    labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } }
  },
  yaxis: { labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } } },
  grid: { borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB' },
  legend: { position: 'top', labels: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } }
}));

const monthlySeries = ref([
  { name: 'Período Atual', data: [] },
  { name: 'Período Anterior', data: [] }
]);

const monthlyCategories = ref(['Checklists', 'Limpezas', 'Procedimentos']);

const monthlyOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
  colors: ['#3B82F6', '#9CA3AF'],
  xaxis: {
    categories: monthlyCategories.value,
    labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } }
  },
  yaxis: { labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } } },
  grid: { borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB' },
  legend: { position: 'top', labels: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } }
}));

const sessionStatusSeries = ref([]);
const sessionStatusLabels = ref(['Concluído', 'Interrompido']);
const sessionStatusTotal = ref(0);

const sessionStatusOptions = computed(() => ({
  chart: { type: 'donut' },
  labels: sessionStatusLabels.value,
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
            formatter: () => sessionStatusTotal.value.toLocaleString('pt-BR')
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
        data: performanceCategories.value.map((week, index) => ({
          'Período': week,
          'Checklists': performanceSeries.value[0].data[index] || 0,
          'Limpezas': performanceSeries.value[1].data[index] || 0,
          'Taxa Conformidade (%)': performanceSeries.value[2].data[index] || 0
        }))
      },
      {
        sheetName: 'Comparativo Mensal',
        data: monthlyCategories.value.map((category, index) => ({
          'Categoria': category,
          'Período Atual': monthlySeries.value[0].data[index] || 0,
          'Período Anterior': monthlySeries.value[1].data[index] || 0,
          'Variação': (monthlySeries.value[0].data[index] || 0) - (monthlySeries.value[1].data[index] || 0)
        }))
      },
      {
        sheetName: 'Status das Sessões',
        data: sessionStatusLabels.value.map((label, index) => ({
          'Status': label,
          'Quantidade': sessionStatusSeries.value[index] || 0,
          'Percentual': sessionStatusTotal.value > 0
            ? `${(((sessionStatusSeries.value[index] || 0) / sessionStatusTotal.value) * 100).toFixed(1)}%`
            : '0%'
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
