<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total de Pacientes</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
            <UsersIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pacientes Ativos</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ stats.active }}</p>
          </div>
          <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
            <CheckCircleIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Média de Idade</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.averageAge }}</p>
          </div>
          <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Distribuição por Status</h3>
        <div id="patient-status-chart">
          <apexchart type="pie" height="300" :options="statusOptions" :series="statusSeries"></apexchart>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Faixa Etária</h3>
        <div id="patient-age-chart">
          <apexchart type="bar" height="300" :options="ageOptions" :series="ageSeries"></apexchart>
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
import { ref, computed, onMounted } from 'vue';
import { UsersIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import VueApexCharts from 'vue3-apexcharts';
import { useExcelExport } from '../../composables/useExcelExport';

const apexchart = VueApexCharts;
const { exportWithCharts } = useExcelExport();
const props = defineProps({ dateRange: { type: Object, required: true } });

// Stats from API
const stats = ref({
  total: 0,
  active: 0,
  averageAge: 0
});

const isLoading = ref(false);

// Load data from API
async function loadReportData() {
  isLoading.value = true;
  try {
    const response = await fetch('/api/reports/patients', {
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

      // Update status distribution chart
      if (result.data.statusDistribution) {
        statusSeries.value = result.data.statusDistribution.series;
        statusLabels.value = result.data.statusDistribution.labels;
      }

      // Update age distribution chart
      if (result.data.ageDistribution) {
        ageSeries.value = [{
          name: 'Pacientes',
          data: result.data.ageDistribution.data
        }];
        ageCategories.value = result.data.ageDistribution.categories;
      }
    }
  } catch (error) {
    console.error('Erro ao carregar relatório:', error);
  } finally {
    isLoading.value = false;
  }
}

// Load on mount
onMounted(() => {
  loadReportData();
});

const statusSeries = ref([]);
const statusLabels = ref(['Ativo', 'Inativo', 'Alta', 'Óbito']);

const statusOptions = computed(() => ({
  labels: statusLabels.value,
  colors: ['#10B981', '#F59E0B', '#8B5CF6', '#EF4444'],
  legend: { position: 'bottom', labels: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } }
}));

const ageSeries = ref([{ name: 'Pacientes', data: [] }]);
const ageCategories = ref(['18-30', '31-45', '46-60', '61-75', '76+']);

const ageOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 4 } },
  colors: ['#3B82F6'],
  xaxis: {
    categories: ageCategories.value,
    labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } }
  },
  yaxis: { labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } } },
  grid: { borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB' }
}));

async function exportReport() {
  try {
    const sheets = [
      {
        sheetName: 'Resumo',
        data: [
          { 'Métrica': 'Total de Pacientes', 'Valor': stats.value.total },
          { 'Métrica': 'Pacientes Ativos', 'Valor': stats.value.active },
          { 'Métrica': 'Média de Idade', 'Valor': stats.value.averageAge }
        ]
      },
      {
        sheetName: 'Por Status',
        data: statusLabels.value.map((status, index) => ({
          'Status': status,
          'Quantidade': statusSeries.value[index] || 0,
          'Percentual': statusSeries.value.reduce((a, b) => a + b, 0) > 0
            ? `${((statusSeries.value[index] || 0) / statusSeries.value.reduce((a, b) => a + b, 0) * 100).toFixed(1)}%`
            : '0%'
        }))
      },
      {
        sheetName: 'Por Faixa Etária',
        data: ageCategories.value.map((age, index) => ({
          'Faixa Etária': age,
          'Quantidade': ageSeries.value[0].data[index] || 0
        }))
      }
    ];

    const chartSelectors = [
      {
        selector: '#patient-status-chart',
        sheetName: 'Gráfico Status',
        title: 'Distribuição de Pacientes por Status'
      },
      {
        selector: '#patient-age-chart',
        sheetName: 'Gráfico Idade',
        title: 'Distribuição de Pacientes por Faixa Etária'
      }
    ];

    const success = await exportWithCharts(sheets, 'relatorio_pacientes', chartSelectors);

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
