<template>
  <div class="patients-view space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Pacientes</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerenciamento de pacientes em tratamento</p>
      </div>
      <button
        @click="openNewPatientModal"
        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Novo Paciente
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total de Pacientes</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ patients.length }}</p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Em Tratamento</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ activePatients }}</p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Tipo A+</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ bloodTypeCount('A', '+') }}</p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Tipo O+</p>
        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ bloodTypeCount('O', '+') }}</p>
      </div>
    </div>

    <!-- Patients Table -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
      <div v-if="filteredPatients.length === 0" class="text-center py-12">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
          {{ hasActiveFilters ? 'Nenhum paciente encontrado' : 'Nenhum paciente cadastrado' }}
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mb-4">
          {{ hasActiveFilters ? 'Tente ajustar os filtros de busca' : 'Comece cadastrando o primeiro paciente' }}
        </p>
        <button
          v-if="hasActiveFilters"
          @click="clearAllFilters"
          class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Limpar Filtros
        </button>
        <button
          v-else
          @click="openNewPatientModal"
          class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium"
        >
          Cadastrar Primeiro Paciente
        </button>
      </div>

      <table v-else class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              <div class="flex items-center gap-2">
                Nome Completo
                <div class="relative">
                  <button
                    @click.stop="toggleFilterDropdown('name')"
                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                    :class="{ 'text-primary-600 dark:text-primary-400': nameFilter }"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                  </button>
                  <div
                    v-if="activeFilterDropdown === 'name'"
                    @click.stop
                    class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg p-3 z-10 min-w-[200px]"
                  >
                    <input
                      v-model="nameFilter"
                      type="text"
                      placeholder="Filtrar nome..."
                      class="w-full px-2 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                      @click.stop
                    />
                  </div>
                </div>
              </div>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Data de Nascimento</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              <div class="flex items-center gap-2">
                Idade
                <div class="relative">
                  <button
                    @click.stop="toggleFilterDropdown('age')"
                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                    :class="{ 'text-primary-600 dark:text-primary-400': ageFilter }"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                  </button>
                  <div
                    v-if="activeFilterDropdown === 'age'"
                    @click.stop
                    class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg p-3 z-10 min-w-[150px]"
                  >
                    <input
                      v-model="ageFilter"
                      type="text"
                      placeholder="Filtrar idade..."
                      class="w-full px-2 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                      @click.stop
                    />
                  </div>
                </div>
              </div>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              <div class="flex items-center gap-2">
                Tipo Sanguíneo
                <div class="relative">
                  <button
                    @click.stop="toggleFilterDropdown('bloodType')"
                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                    :class="{ 'text-primary-600 dark:text-primary-400': bloodTypeColumnFilter }"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                  </button>
                  <div
                    v-if="activeFilterDropdown === 'bloodType'"
                    @click.stop
                    class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg p-3 z-10 min-w-[150px]"
                  >
                    <select
                      v-model="bloodTypeColumnFilter"
                      class="w-full px-2 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                      @click.stop
                    >
                      <option value="">Todos</option>
                      <option value="A+">A+</option>
                      <option value="A-">A-</option>
                      <option value="B+">B+</option>
                      <option value="B-">B-</option>
                      <option value="AB+">AB+</option>
                      <option value="AB-">AB-</option>
                      <option value="O+">O+</option>
                      <option value="O-">O-</option>
                    </select>
                  </div>
                </div>
              </div>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
              <div class="flex items-center gap-2">
                Status
                <div class="relative">
                  <button
                    @click.stop="toggleFilterDropdown('status')"
                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                    :class="{ 'text-primary-600 dark:text-primary-400': statusFilter }"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                  </button>
                  <div
                    v-if="activeFilterDropdown === 'status'"
                    @click.stop
                    class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg p-3 z-10 min-w-[150px]"
                  >
                    <select
                      v-model="statusFilter"
                      class="w-full px-2 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                      @click.stop
                    >
                      <option value="">Todos</option>
                      <option value="ativo">Ativo</option>
                      <option value="inativo">Inativo</option>
                      <option value="transferido">Transferido</option>
                      <option value="alta">Alta Médica</option>
                      <option value="obito">Óbito</option>
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
          <tr
            v-for="patient in filteredPatients"
            :key="patient.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-800/50"
          >
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
              {{ patient.full_name }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
              {{ formatDate(patient.birth_date) }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
              {{ calculateAge(patient.birth_date) }} anos
            </td>
            <td class="px-6 py-4">
              <span
                v-if="patient.blood_group && patient.rh_factor"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400"
              >
                {{ patient.blood_group }}{{ patient.rh_factor }}
              </span>
              <span v-else class="text-sm text-gray-400 dark:text-gray-500">N/A</span>
            </td>
            <td class="px-6 py-4">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="getStatusBadgeClass(patient.status || 'ativo')"
              >
                {{ getStatusLabel(patient.status || 'ativo') }}
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button
                @click="openEditPatientModal(patient)"
                class="text-primary-600 dark:text-primary-400 hover:underline text-sm font-medium"
              >
                Editar
              </button>
              <button
                @click="openDeleteConfirm(patient)"
                class="text-red-600 dark:text-red-400 hover:underline text-sm font-medium"
              >
                Excluir
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Patient Form Modal -->
    <PatientFormModal
      :is-open="showPatientModal"
      :patient="editingPatient"
      @close="closePatientModal"
      @saved="handlePatientSaved"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmDeleteModal
      :is-open="showDeleteConfirm"
      title="Confirmar Exclusão do Paciente"
      message="Tem certeza que deseja excluir este paciente? Todos os registros associados serão mantidos."
      :item-name="patientToDelete?.full_name"
      @close="showDeleteConfirm = false"
      @confirm="handleDeleteConfirm"
      ref="deleteModalRef"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import PatientFormModal from '../components/PatientFormModal.vue';
import ConfirmDeleteModal from '../components/ConfirmDeleteModal.vue';
import api from '../utils/api';

const showPatientModal = ref(false);
const editingPatient = ref(null);
const showDeleteConfirm = ref(false);
const patientToDelete = ref(null);
const deleteModalRef = ref(null);
const nameFilter = ref('');
const ageFilter = ref('');
const bloodTypeColumnFilter = ref('');
const statusFilter = ref('');
const activeFilterDropdown = ref(null);

// Patients data from API
const patients = ref([]);
const isLoading = ref(false);

// Load patients from API
async function loadPatients() {
  isLoading.value = true;
  try {
    const response = await api.get('/api/patients?per_page=100&include_inactive=true');

    if (!response.ok) {
      throw new Error('Erro ao carregar pacientes');
    }

    const data = await response.json();

    if (data.success) {
      patients.value = data.patients || [];
    } else {
      console.error('Erro ao carregar pacientes:', data.message);
      showErrorToast(data.message || 'Erro ao carregar pacientes');
    }
  } catch (error) {
    console.error('Erro ao carregar pacientes:', error);
    showErrorToast('Erro ao carregar pacientes. Verifique sua conexão.');
  } finally {
    isLoading.value = false;
  }
}

// Computed
const activePatients = computed(() => {
  // In a real app, this would filter by status
  return patients.value.length;
});

const filteredPatients = computed(() => {
  let filtered = patients.value;

  // Column filters
  if (nameFilter.value) {
    const query = nameFilter.value.toLowerCase();
    filtered = filtered.filter(p =>
      p.full_name.toLowerCase().includes(query)
    );
  }

  if (ageFilter.value) {
    filtered = filtered.filter(p => {
      const age = calculateAge(p.birth_date);
      return age.toString().includes(ageFilter.value);
    });
  }

  if (bloodTypeColumnFilter.value) {
    filtered = filtered.filter(p =>
      p.blood_type === bloodTypeColumnFilter.value
    );
  }

  if (statusFilter.value) {
    filtered = filtered.filter(p =>
      (p.status || 'ativo') === statusFilter.value
    );
  }

  return filtered;
});

// Check if there are any active filters
const hasActiveFilters = computed(() => {
  return !!(nameFilter.value || ageFilter.value || bloodTypeColumnFilter.value || statusFilter.value);
});

// Close dropdown when clicking outside
function handleClickOutside(event) {
  activeFilterDropdown.value = null;
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  loadPatients(); // Load patients from API
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Methods
function toggleFilterDropdown(filterName) {
  if (activeFilterDropdown.value === filterName) {
    activeFilterDropdown.value = null;
  } else {
    activeFilterDropdown.value = filterName;
  }
}

function clearAllFilters() {
  nameFilter.value = '';
  ageFilter.value = '';
  bloodTypeColumnFilter.value = '';
  statusFilter.value = '';
  activeFilterDropdown.value = null;
}

function bloodTypeCount(group, factor) {
  return patients.value.filter(p =>
    p.blood_group === group && p.rh_factor === factor
  ).length;
}

function getStatusLabel(status) {
  const labels = {
    'ativo': 'Ativo',
    'inativo': 'Inativo',
    'transferido': 'Transferido',
    'alta': 'Alta Médica',
    'obito': 'Óbito'
  };
  return labels[status] || status;
}

function getStatusBadgeClass(status) {
  const classes = {
    'ativo': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    'inativo': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    'transferido': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    'alta': 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
    'obito': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  };
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString + 'T00:00:00');
  return date.toLocaleDateString('pt-BR');
}

function calculateAge(birthDate) {
  if (!birthDate) return 0;

  const birth = new Date(birthDate + 'T00:00:00');
  const today = new Date();
  let age = today.getFullYear() - birth.getFullYear();
  const monthDiff = today.getMonth() - birth.getMonth();

  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--;
  }

  return age;
}

function openNewPatientModal() {
  editingPatient.value = null;
  showPatientModal.value = true;
}

function openEditPatientModal(patient) {
  editingPatient.value = patient;
  showPatientModal.value = true;
}

function closePatientModal() {
  showPatientModal.value = false;
  editingPatient.value = null;
}

async function handlePatientSaved(patientData) {
  if (editingPatient.value) {
    showSuccessToast('Paciente atualizado com sucesso!');
  } else {
    showSuccessToast('Paciente cadastrado com sucesso!');
  }

  closePatientModal();
  await loadPatients(); // Reload patients from API
}

function openDeleteConfirm(patient) {
  patientToDelete.value = patient;
  showDeleteConfirm.value = true;
}

async function handleDeleteConfirm() {
  if (!patientToDelete.value) {
    deleteModalRef.value?.resetDeletingState();
    showDeleteConfirm.value = false;
    return;
  }

  try {
    // Make the DELETE API call
    const response = await api.delete(`/api/patients/${patientToDelete.value.id}`);

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Erro ao excluir paciente');
    }

    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Erro ao excluir paciente');
    }

    // Reset the delete modal state
    deleteModalRef.value?.resetDeletingState();

    // Close the delete confirmation modal
    showDeleteConfirm.value = false;

    // Show success toast
    showSuccessToast('Paciente excluído com sucesso!');

    // Reload patients from API
    await loadPatients();

    // Clear the patient to delete
    patientToDelete.value = null;
  } catch (error) {
    console.error('Erro ao excluir paciente:', error);

    // Reset deleting state to allow retry
    deleteModalRef.value?.resetDeletingState();

    // Show error toast
    showErrorToast(error.message || 'Erro ao excluir paciente. Tente novamente.');
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

<style scoped>
.patients-view {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
