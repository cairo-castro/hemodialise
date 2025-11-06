<template>
  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total de Limpezas</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total }}</p>
          </div>
          <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
            <SparklesIcon class="w-6 h-6 text-purple-600 dark:text-purple-400" />
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
            <p class="text-sm text-gray-500 dark:text-gray-400">Limpezas Diárias</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.daily }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Desinfecções Químicas</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.chemical }}</p>
          </div>
          <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
            <BeakerIcon class="w-6 h-6 text-orange-600 dark:text-orange-400" />
          </div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Limpezas por Tipo</h3>
        <div id="cleaning-type-chart">
          <apexchart
            type="bar"
            height="300"
            :options="cleaningTypeOptions"
            :series="cleaningTypeSeries"
          ></apexchart>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Conformidade por Item</h3>
        <div id="item-conformity-chart">
          <apexchart
            type="radar"
            height="300"
            :options="itemConformityOptions"
            :series="itemConformitySeries"
          ></apexchart>
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
import { SparklesIcon, CheckCircleIcon, BeakerIcon } from '@heroicons/vue/24/outline';
import VueApexCharts from 'vue3-apexcharts';
import { useExcelExport } from '../../composables/useExcelExport';

const apexchart = VueApexCharts;
const { exportWithCharts } = useExcelExport();

const props = defineProps({
  dateRange: { type: Object, required: true }
});

// Stats from API
const stats = ref({
  total: 0,
  conformityRate: 0,
  daily: 0,
  chemical: 0
});

const isLoading = ref(false);

// Load data from API
async function loadReportData() {
  if (!props.dateRange.start || !props.dateRange.end) return;

  isLoading.value = true;
  try {
    const response = await fetch(`/api/reports/cleaning?start_date=${props.dateRange.start}&end_date=${props.dateRange.end}`, {
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

      // Update cleaning type chart
      if (result.data.cleaningByType) {
        cleaningTypeSeries.value = [{
          name: 'Quantidade',
          data: result.data.cleaningByType.data
        }];
        cleaningTypeCategories.value = result.data.cleaningByType.categories;
      }

      // Update item conformity chart
      if (result.data.itemConformity) {
        itemConformitySeries.value = [{
          name: 'Conformidade',
          data: result.data.itemConformity.data
        }];
        itemConformityCategories.value = result.data.itemConformity.categories;
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

const cleaningTypeSeries = ref([{
  name: 'Quantidade',
  data: []
}]);

const cleaningTypeCategories = ref(['Diária', 'Semanal', 'Mensal', 'Especial']);

const cleaningTypeOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false } },
  plotOptions: {
    bar: { horizontal: true, borderRadius: 4 }
  },
  colors: ['#8B5CF6'],
  xaxis: {
    categories: cleaningTypeCategories.value,
    labels: {
      style: {
        colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280'
      }
    }
  },
  yaxis: {
    labels: {
      style: {
        colors: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280'
      }
    }
  },
  grid: {
    borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
  }
}));

const itemConformitySeries = ref([{
  name: 'Conformidade',
  data: []
}]);

const itemConformityCategories = ref(['Máquina HD', 'Osmose', 'Suporte Soro', 'Desinfecção']);

const itemConformityOptions = computed(() => ({
  chart: { type: 'radar', toolbar: { show: false } },
  xaxis: {
    categories: itemConformityCategories.value
  },
  colors: ['#10B981'],
  yaxis: { min: 0, max: 100 }
}));

async function exportReport() {
  try {
    const sheets = [
      {
        sheetName: 'Resumo',
        data: [
          { 'Métrica': 'Total de Limpezas', 'Valor': stats.value.total },
          { 'Métrica': 'Taxa de Conformidade', 'Valor': `${stats.value.conformityRate}%` },
          { 'Métrica': 'Limpezas Diárias', 'Valor': stats.value.daily },
          { 'Métrica': 'Desinfecções Químicas', 'Valor': stats.value.chemical }
        ]
      },
      {
        sheetName: 'Por Tipo',
        data: cleaningTypeCategories.value.map((type, index) => ({
          'Tipo de Limpeza': type,
          'Quantidade': cleaningTypeSeries.value[0].data[index] || 0
        }))
      },
      {
        sheetName: 'Conformidade por Item',
        data: itemConformityCategories.value.map((item, index) => ({
          'Item': item,
          'Taxa de Conformidade (%)': itemConformitySeries.value[0].data[index] || 0
        }))
      }
    ];

    const chartSelectors = [
      {
        selector: '#cleaning-type-chart',
        sheetName: 'Gráfico Tipos',
        title: 'Distribuição de Limpezas por Tipo'
      },
      {
        selector: '#item-conformity-chart',
        sheetName: 'Gráfico Conformidade',
        title: 'Conformidade por Item de Limpeza'
      }
    ];

    const success = await exportWithCharts(sheets, 'relatorio_limpeza', chartSelectors);

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
