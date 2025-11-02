<template>
  <div class="machines-view space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Máquinas de Hemodiálise</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerenciamento e controle das máquinas</p>
      </div>
      <button
        @click="openNewMachineModal"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nova Máquina
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total de Máquinas</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ machines.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Disponíveis</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ statusCount('available') }}</p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Em Uso</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ statusCount('occupied') }}</p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Manutenção</p>
        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ statusCount('maintenance') }}</p>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
      <div class="flex items-center space-x-4">
        <div class="flex-1 relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar por nome ou identificador..."
            class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-white"
          />
          <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <select
          v-model="statusFilter"
          class="px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-white"
        >
          <option value="">Todos os status</option>
          <option value="available">Disponível</option>
          <option value="occupied">Em Uso</option>
          <option value="maintenance">Manutenção</option>
          <option value="reserved">Reservada</option>
        </select>
        <select
          v-model="activeFilter"
          class="px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-white"
        >
          <option value="">Todas</option>
          <option value="active">Ativas</option>
          <option value="inactive">Inativas</option>
        </select>
      </div>
    </div>

    <!-- Machines Grid -->
    <div v-if="filteredMachines.length === 0" class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
      <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
        {{ searchQuery || statusFilter || activeFilter ? 'Nenhuma máquina encontrada' : 'Nenhuma máquina cadastrada' }}
      </h3>
      <p class="text-gray-500 dark:text-gray-400 mb-4">
        {{ searchQuery || statusFilter || activeFilter ? 'Tente ajustar os filtros de busca' : 'Comece cadastrando a primeira máquina' }}
      </p>
      <button
        v-if="!searchQuery && !statusFilter && !activeFilter"
        @click="openNewMachineModal"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
      >
        Cadastrar Primeira Máquina
      </button>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="machine in filteredMachines"
        :key="machine.id"
        class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-5 hover:shadow-lg transition-shadow"
      >
        <!-- Machine Header -->
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <CpuChipIcon class="w-7 h-7 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">{{ machine.name }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ machine.identifier }}</p>
            </div>
          </div>
          <div
            class="w-3 h-3 rounded-full"
            :class="machine.active ? 'bg-green-500' : 'bg-gray-400'"
            :title="machine.active ? 'Ativa' : 'Inativa'"
          ></div>
        </div>

        <!-- Status Badge -->
        <div class="mb-4">
          <span
            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
            :class="getStatusBadgeClass(machine.status)"
          >
            <component :is="getStatusIcon(machine.status)" class="w-4 h-4 mr-1" />
            {{ getStatusLabel(machine.status) }}
          </span>
        </div>

        <!-- Description -->
        <p v-if="machine.description" class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
          {{ machine.description }}
        </p>
        <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic mb-4">
          Sem descrição
        </p>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-2 pt-4 border-t border-gray-200 dark:border-gray-700">
          <button
            @click="openEditMachineModal(machine)"
            class="px-3 py-1.5 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors font-medium"
          >
            Editar
          </button>
          <button
            @click="confirmDelete(machine)"
            class="px-3 py-1.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors font-medium"
          >
            Excluir
          </button>
        </div>
      </div>
    </div>

    <!-- Machine Form Modal -->
    <MachineFormModal
      :is-open="showMachineModal"
      :machine="editingMachine"
      @close="closeMachineModal"
      @saved="handleMachineSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { CpuChipIcon } from '@heroicons/vue/24/outline';
import {
  CheckCircleIcon,
  ClockIcon,
  WrenchScrewdriverIcon,
  ShieldExclamationIcon,
} from '@heroicons/vue/24/solid';
import MachineFormModal from '../components/MachineFormModal.vue';

const showMachineModal = ref(false);
const editingMachine = ref(null);
const searchQuery = ref('');
const statusFilter = ref('');
const activeFilter = ref('');

// Mock machines data
const machines = ref([
  {
    id: 1,
    name: 'Máquina 01',
    identifier: 'HD-001',
    description: 'Fresenius 2008K - Unidade Principal',
    status: 'available',
    active: true
  },
  {
    id: 2,
    name: 'Máquina 02',
    identifier: 'HD-002',
    description: 'Fresenius 4008S - Sala 2',
    status: 'occupied',
    active: true
  },
  {
    id: 3,
    name: 'Máquina 03',
    identifier: 'HD-003',
    description: 'Baxter AK96 - Sala 1',
    status: 'maintenance',
    active: true
  },
  {
    id: 4,
    name: 'Máquina 04',
    identifier: 'HD-004',
    description: '',
    status: 'available',
    active: true
  },
  {
    id: 5,
    name: 'Máquina 05',
    identifier: 'HD-005',
    description: 'Fresenius 2008K - Unidade Secundária',
    status: 'reserved',
    active: false
  },
]);

// Computed
const filteredMachines = computed(() => {
  let filtered = machines.value;

  // Filter by search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(m =>
      m.name.toLowerCase().includes(query) ||
      m.identifier.toLowerCase().includes(query)
    );
  }

  // Filter by status
  if (statusFilter.value) {
    filtered = filtered.filter(m => m.status === statusFilter.value);
  }

  // Filter by active
  if (activeFilter.value) {
    const isActive = activeFilter.value === 'active';
    filtered = filtered.filter(m => m.active === isActive);
  }

  return filtered;
});

// Methods
function statusCount(status) {
  return machines.value.filter(m => m.status === status).length;
}

function getStatusLabel(status) {
  const labels = {
    available: 'Disponível',
    occupied: 'Em Uso',
    maintenance: 'Manutenção',
    reserved: 'Reservada'
  };
  return labels[status] || status;
}

function getStatusBadgeClass(status) {
  const classes = {
    available: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    occupied: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    maintenance: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
    reserved: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400'
  };
  return classes[status] || '';
}

function getStatusIcon(status) {
  const icons = {
    available: CheckCircleIcon,
    occupied: ClockIcon,
    maintenance: WrenchScrewdriverIcon,
    reserved: ShieldExclamationIcon
  };
  return icons[status] || CheckCircleIcon;
}

function openNewMachineModal() {
  editingMachine.value = null;
  showMachineModal.value = true;
}

function openEditMachineModal(machine) {
  editingMachine.value = machine;
  showMachineModal.value = true;
}

function closeMachineModal() {
  showMachineModal.value = false;
  editingMachine.value = null;
}

function handleMachineSaved(machineData) {
  if (editingMachine.value) {
    // Update existing machine
    const index = machines.value.findIndex(m => m.id === editingMachine.value.id);
    if (index !== -1) {
      machines.value[index] = { ...machines.value[index], ...machineData };
    }
    alert('Máquina atualizada com sucesso!');
  } else {
    // Add new machine
    machines.value.push(machineData);
    alert('Máquina cadastrada com sucesso!');
  }

  closeMachineModal();
}

function confirmDelete(machine) {
  if (confirm(`Deseja realmente excluir a máquina ${machine.name}?`)) {
    const index = machines.value.findIndex(m => m.id === machine.id);
    if (index !== -1) {
      machines.value.splice(index, 1);
      alert('Máquina excluída com sucesso!');
    }
  }
}
</script>

<style scoped>
.machines-view {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
