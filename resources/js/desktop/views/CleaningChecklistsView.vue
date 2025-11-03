<template>
  <div class="cleaning-checklists-view space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Checklists de Limpeza</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Controle de limpeza e desinfecção das máquinas</p>
      </div>
      <button
        @click="openWizard"
        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nova Limpeza
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Hoje</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
          {{ stats.total_today }}
        </p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Limpeza Diária</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
          {{ stats.daily }}
        </p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Limpeza Semanal</p>
        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">
          {{ stats.weekly }}
        </p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Limpeza Mensal</p>
        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
          {{ stats.monthly }}
        </p>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
      <div v-if="loadingCleanings" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <table v-else class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              ID
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              <div class="flex items-center gap-2">
                Máquina
                <div class="relative">
                  <button
                    @click.stop="toggleFilterDropdown('machine')"
                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                    :class="{ 'text-primary-600 dark:text-primary-400': machineFilter }"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                  </button>
                  <div
                    v-if="activeFilterDropdown === 'machine'"
                    @click.stop
                    class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg p-3 z-10 min-w-[200px]"
                  >
                    <input
                      v-model="machineFilter"
                      type="text"
                      placeholder="Filtrar máquina..."
                      class="w-full px-2 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                      @click.stop
                    />
                  </div>
                </div>
              </div>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              <div class="flex items-center gap-2">
                Data
                <div class="relative">
                  <button
                    @click.stop="toggleFilterDropdown('date')"
                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                    :class="{ 'text-primary-600 dark:text-primary-400': dateFilter }"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                  </button>
                  <div
                    v-if="activeFilterDropdown === 'date'"
                    @click.stop
                    class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg p-3 z-10 min-w-[150px]"
                  >
                    <input
                      v-model="dateFilter"
                      type="date"
                      class="w-full px-2 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                      @click.stop
                    />
                  </div>
                </div>
              </div>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              <div class="flex items-center gap-2">
                Tipo de Limpeza
                <div class="relative">
                  <button
                    @click.stop="toggleFilterDropdown('type')"
                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                    :class="{ 'text-primary-600 dark:text-primary-400': typeFilter }"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                  </button>
                  <div
                    v-if="activeFilterDropdown === 'type'"
                    @click.stop
                    class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg p-3 z-10 min-w-[180px]"
                  >
                    <select
                      v-model="typeFilter"
                      class="w-full px-2 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                      @click.stop
                    >
                      <option value="">Todos</option>
                      <option value="daily">Diária</option>
                      <option value="weekly">Semanal</option>
                      <option value="monthly">Mensal</option>
                      <option value="special">Especial</option>
                    </select>
                  </div>
                </div>
              </div>
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              Desinfecção Química
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              Máquina HD
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              Osmose
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              Responsável
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              <button
                v-if="hasActiveFilters"
                @click="clearAllFilters"
                class="inline-flex items-center gap-1 px-2 py-1 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                title="Limpar todos os filtros"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="filteredCleanings.length === 0">
            <td colspan="9" class="px-6 py-12 text-center">
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                {{ hasActiveFilters ? 'Nenhuma limpeza encontrada com os filtros aplicados' : 'Nenhuma limpeza registrada hoje' }}
              </p>
              <button
                v-if="hasActiveFilters"
                @click="clearAllFilters"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Limpar Filtros
              </button>
            </td>
          </tr>
          <tr
            v-for="cleaning in filteredCleanings"
            :key="cleaning.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-800/50"
          >
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
              #{{ cleaning.id }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              {{ cleaning.machine?.name || 'N/A' }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
              {{ formatDate(cleaning.cleaning_date) }}
            </td>
            <td class="px-6 py-4">
              <span :class="getCleaningTypeBadgeClass(cleaning)">
                {{ getCleaningTypeLabel(cleaning) }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <span :class="getComplianceStatusClass(cleaning.chemical_disinfection)">
                {{ getComplianceStatusLabel(cleaning.chemical_disinfection) }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <span :class="getComplianceStatusClass(cleaning.hd_machine_cleaning)">
                {{ getComplianceStatusLabel(cleaning.hd_machine_cleaning) }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <span :class="getComplianceStatusClass(cleaning.osmosis_cleaning)">
                {{ getComplianceStatusLabel(cleaning.osmosis_cleaning) }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
              {{ cleaning.user?.name || 'N/A' }}
            </td>
            <td class="px-6 py-4 text-right">
              <button
                @click="openDetailModal(cleaning.id)"
                class="text-primary-600 dark:text-primary-400 hover:underline text-sm font-medium"
              >
                Ver Detalhes
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detail Modal -->
    <CleaningDetailModal
      :is-open="showDetailModal"
      :cleaning-id="selectedCleaningId"
      @close="closeDetailModal"
      @deleted="handleCleaningDeleted"
    />

    <!-- Wizard Modal -->
    <CleaningWizardModal
      :is-open="showWizard"
      @close="closeWizard"
      @created="handleCleaningCreated"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import CleaningDetailModal from '../components/CleaningDetailModal.vue';
import CleaningWizardModal from '../components/CleaningWizardModal.vue';

const showWizard = ref(false);
const showDetailModal = ref(false);
const selectedCleaningId = ref(null);

const cleanings = ref([]);
const loadingCleanings = ref(false);

const stats = ref({
  total_today: 0,
  daily: 0,
  weekly: 0,
  monthly: 0
});

// Column filters
const machineFilter = ref('');
const dateFilter = ref('');
const typeFilter = ref('');
const activeFilterDropdown = ref(null);

let pollingInterval = null;

// Computed property for filtered cleanings
const filteredCleanings = computed(() => {
  let filtered = cleanings.value;

  // Filter by machine name
  if (machineFilter.value) {
    const query = machineFilter.value.toLowerCase();
    filtered = filtered.filter(c =>
      c.machine?.name?.toLowerCase().includes(query)
    );
  }

  // Filter by date
  if (dateFilter.value) {
    filtered = filtered.filter(c =>
      c.cleaning_date === dateFilter.value
    );
  }

  // Filter by cleaning type
  if (typeFilter.value) {
    filtered = filtered.filter(c => {
      if (typeFilter.value === 'daily') return c.daily_cleaning;
      if (typeFilter.value === 'weekly') return c.weekly_cleaning;
      if (typeFilter.value === 'monthly') return c.monthly_cleaning;
      if (typeFilter.value === 'special') return c.special_cleaning;
      return true;
    });
  }

  return filtered;
});

// Check if there are any active filters
const hasActiveFilters = computed(() => {
  return !!(machineFilter.value || dateFilter.value || typeFilter.value);
});

// Close dropdown when clicking outside
function handleClickOutside(event) {
  activeFilterDropdown.value = null;
}

onMounted(() => {
  loadCleanings();
  loadStats();
  // Start polling every 30 seconds
  pollingInterval = setInterval(() => {
    loadCleanings();
    loadStats();
  }, 30000);
  // Add click listener to close dropdowns
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
  document.removeEventListener('click', handleClickOutside);
});

async function loadCleanings() {
  try {
    loadingCleanings.value = true;
    const response = await axios.get('/api/cleaning-controls', {
      params: {
        per_page: 50
      }
    });
    cleanings.value = response.data.data || [];
  } catch (error) {
    console.error('Error loading cleanings:', error);
  } finally {
    loadingCleanings.value = false;
  }
}

async function loadStats() {
  try {
    const response = await axios.get('/api/cleaning-controls/stats');
    if (response.data.success) {
      stats.value = response.data.stats;
    }
  } catch (error) {
    console.error('Error loading stats:', error);
  }
}

function openDetailModal(cleaningId) {
  selectedCleaningId.value = cleaningId;
  showDetailModal.value = true;
}

function closeDetailModal() {
  showDetailModal.value = false;
  selectedCleaningId.value = null;
}

function handleCleaningDeleted() {
  loadCleanings();
  loadStats();
}

function openWizard() {
  showWizard.value = true;
}

function closeWizard() {
  showWizard.value = false;
}

function handleCleaningCreated() {
  loadCleanings();
  loadStats();
}

function toggleFilterDropdown(filterName) {
  if (activeFilterDropdown.value === filterName) {
    activeFilterDropdown.value = null;
  } else {
    activeFilterDropdown.value = filterName;
  }
}

function clearAllFilters() {
  machineFilter.value = '';
  dateFilter.value = '';
  typeFilter.value = '';
  activeFilterDropdown.value = null;
}

function getCleaningTypeLabel(cleaning) {
  if (cleaning.special_cleaning) return 'Especial';
  if (cleaning.monthly_cleaning) return 'Mensal';
  if (cleaning.weekly_cleaning) return 'Semanal';
  if (cleaning.daily_cleaning) return 'Diária';
  return 'N/A';
}

function getCleaningTypeBadgeClass(cleaning) {
  if (cleaning.special_cleaning) {
    return 'px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 rounded-full';
  }
  if (cleaning.monthly_cleaning) {
    return 'px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded-full';
  }
  if (cleaning.weekly_cleaning) {
    return 'px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 rounded-full';
  }
  if (cleaning.daily_cleaning) {
    return 'px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded-full';
  }
  return 'px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 rounded-full';
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('pt-BR');
}

function getComplianceStatusLabel(status) {
  if (status === true) return 'C';
  if (status === false) return 'NC';
  return 'N/A';
}

function getComplianceStatusClass(status) {
  if (status === true) {
    return 'inline-flex items-center justify-center w-8 h-8 text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 rounded-full';
  }
  if (status === false) {
    return 'inline-flex items-center justify-center w-8 h-8 text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 rounded-full';
  }
  return 'inline-flex items-center justify-center w-8 h-8 text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 rounded-full';
}
</script>

<style scoped>
.cleaning-checklists-view {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
