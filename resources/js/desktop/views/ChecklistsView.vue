<template>
  <div class="checklists-view space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Checklists de Segurança</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Verificações de segurança para hemodiálise</p>
      </div>
      <button
        @click="showWizard = true"
        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium"
      >
        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Novo Checklist
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
        <p class="text-sm text-gray-500 dark:text-gray-400">Em Andamento</p>
        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">
          {{ stats.in_progress }}
        </p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Concluídos</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
          {{ stats.completed }}
        </p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Interrompidos</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
          {{ stats.interrupted }}
        </p>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
      <div v-if="loadingChecklists" class="flex items-center justify-center py-12">
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
                Paciente
                <div class="relative">
                  <button
                    @click.stop="toggleFilterDropdown('patient')"
                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                    :class="{ 'text-primary-600 dark:text-primary-400': patientFilter }"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                  </button>
                  <div
                    v-if="activeFilterDropdown === 'patient'"
                    @click.stop
                    class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg p-3 z-10 min-w-[200px]"
                  >
                    <input
                      v-model="patientFilter"
                      type="text"
                      placeholder="Filtrar paciente..."
                      class="w-full px-2 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                      @click.stop
                    />
                  </div>
                </div>
              </div>
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
                Turno
                <div class="relative">
                  <button
                    @click.stop="toggleFilterDropdown('shift')"
                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                    :class="{ 'text-primary-600 dark:text-primary-400': shiftFilter }"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                  </button>
                  <div
                    v-if="activeFilterDropdown === 'shift'"
                    @click.stop
                    class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg p-3 z-10 min-w-[180px]"
                  >
                    <select
                      v-model="shiftFilter"
                      class="w-full px-2 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                      @click.stop
                    >
                      <option value="">Todos</option>
                      <option value="matutino">Matutino</option>
                      <option value="vespertino">Vespertino</option>
                      <option value="noturno">Noturno</option>
                    </select>
                  </div>
                </div>
              </div>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              <div class="flex items-center gap-2">
                Fase
                <div class="relative">
                  <button
                    @click.stop="toggleFilterDropdown('phase')"
                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                    :class="{ 'text-primary-600 dark:text-primary-400': phaseFilter }"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                  </button>
                  <div
                    v-if="activeFilterDropdown === 'phase'"
                    @click.stop
                    class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg p-3 z-10 min-w-[200px]"
                  >
                    <select
                      v-model="phaseFilter"
                      class="w-full px-2 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                      @click.stop
                    >
                      <option value="">Todos</option>
                      <option value="pre_dialysis">Pré-diálise</option>
                      <option value="during_session">Durante a Sessão</option>
                      <option value="post_dialysis">Pós-diálise</option>
                      <option value="completed">Concluído</option>
                      <option value="interrupted">Interrompido</option>
                    </select>
                  </div>
                </div>
              </div>
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
          <tr v-if="filteredChecklists.length === 0">
            <td colspan="6" class="px-6 py-12 text-center">
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                {{ patientFilter || machineFilter || shiftFilter || phaseFilter ? 'Nenhum checklist encontrado com os filtros aplicados' : 'Nenhum checklist encontrado hoje' }}
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
            v-for="checklist in filteredChecklists"
            :key="checklist.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-800/50"
          >
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
              #{{ checklist.id }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              {{ checklist.patient?.full_name || 'N/A' }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
              {{ checklist.machine?.name || 'N/A' }}
            </td>
            <td class="px-6 py-4">
              <span :class="getShiftBadgeClass(checklist.shift)">
                {{ getShiftLabel(checklist.shift) }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span :class="getPhaseBadgeClass(checklist.current_phase)">
                {{ getPhaseLabel(checklist.current_phase) }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <button
                @click="openDetailModal(checklist.id)"
                class="text-primary-600 dark:text-primary-400 hover:underline text-sm font-medium"
              >
                Ver Detalhes
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Checklist Wizard -->
    <ChecklistWizard
      :is-open="showWizard"
      @close="showWizard = false"
      @saved="handleChecklistSaved"
    />

    <!-- Detail Modal -->
    <ChecklistDetailModal
      :is-open="showDetailModal"
      :checklist-id="selectedChecklistId"
      @close="closeDetailModal"
      @deleted="handleChecklistDeleted"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import ChecklistWizard from '../components/ChecklistWizard.vue';
import ChecklistDetailModal from '../components/ChecklistDetailModal.vue';

const showWizard = ref(false);
const showDetailModal = ref(false);
const selectedChecklistId = ref(null);

const checklists = ref([]);
const loadingChecklists = ref(false);

const stats = ref({
  total_today: 0,
  in_progress: 0,
  completed: 0,
  interrupted: 0
});

// Column filters
const patientFilter = ref('');
const machineFilter = ref('');
const shiftFilter = ref('');
const phaseFilter = ref('');
const activeFilterDropdown = ref(null);

let pollingInterval = null;

// Computed property for filtered checklists
const filteredChecklists = computed(() => {
  let filtered = checklists.value;

  // Filter by patient name
  if (patientFilter.value) {
    const query = patientFilter.value.toLowerCase();
    filtered = filtered.filter(c =>
      c.patient?.full_name?.toLowerCase().includes(query)
    );
  }

  // Filter by machine name
  if (machineFilter.value) {
    const query = machineFilter.value.toLowerCase();
    filtered = filtered.filter(c =>
      c.machine?.name?.toLowerCase().includes(query)
    );
  }

  // Filter by shift
  if (shiftFilter.value) {
    filtered = filtered.filter(c => c.shift === shiftFilter.value);
  }

  // Filter by phase
  if (phaseFilter.value) {
    filtered = filtered.filter(c => c.current_phase === phaseFilter.value);
  }

  return filtered;
});

// Check if there are any active filters
const hasActiveFilters = computed(() => {
  return !!(patientFilter.value || machineFilter.value || shiftFilter.value || phaseFilter.value);
});

// Close dropdown when clicking outside
function handleClickOutside(event) {
  activeFilterDropdown.value = null;
}

onMounted(() => {
  loadChecklists();
  loadStats();
  // Start polling every 30 seconds
  pollingInterval = setInterval(() => {
    loadChecklists();
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

async function loadChecklists() {
  try {
    loadingChecklists.value = true;
    const response = await axios.get('/api/checklists', {
      params: {
        per_page: 20
      }
    });
    checklists.value = response.data.data || [];
  } catch (error) {
    console.error('Error loading checklists:', error);
  } finally {
    loadingChecklists.value = false;
  }
}

async function loadStats() {
  try {
    const response = await axios.get('/api/checklists/stats');
    if (response.data.success) {
      stats.value = response.data.stats;
    }
  } catch (error) {
    console.error('Error loading stats:', error);
  }
}

function handleChecklistSaved(checklist) {
  console.log('Checklist saved:', checklist);
  loadChecklists();
  loadStats();
}

function openDetailModal(checklistId) {
  selectedChecklistId.value = checklistId;
  showDetailModal.value = true;
}

function closeDetailModal() {
  showDetailModal.value = false;
  selectedChecklistId.value = null;
}

function handleChecklistDeleted() {
  loadChecklists();
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
  patientFilter.value = '';
  machineFilter.value = '';
  shiftFilter.value = '';
  phaseFilter.value = '';
  activeFilterDropdown.value = null;
}

function getShiftLabel(shift) {
  const labels = {
    matutino: 'Matutino',
    vespertino: 'Vespertino',
    noturno: 'Noturno'
  };
  return labels[shift] || shift;
}

function getShiftBadgeClass(shift) {
  const classes = {
    matutino: 'px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded-full',
    vespertino: 'px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400 rounded-full',
    noturno: 'px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400 rounded-full'
  };
  return classes[shift] || 'px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 rounded-full';
}

function getPhaseLabel(phase) {
  const labels = {
    pre_dialysis: 'Pré-diálise',
    during_session: 'Durante a Sessão',
    dialysis: 'Em Sessão',
    post_dialysis: 'Pós-diálise',
    completed: 'Concluído',
    interrupted: 'Interrompido'
  };
  return labels[phase] || phase;
}

function getPhaseBadgeClass(phase) {
  const classes = {
    pre_dialysis: 'px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded-full',
    during_session: 'px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 rounded-full',
    dialysis: 'px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 rounded-full',
    post_dialysis: 'px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded-full',
    completed: 'px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 rounded-full',
    interrupted: 'px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 rounded-full'
  };
  return classes[phase] || 'px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 rounded-full';
}
</script>

<style scoped>
.checklists-view {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
