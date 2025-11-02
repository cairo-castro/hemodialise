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

    <!-- Search and Filters -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
      <div class="flex items-center space-x-4">
        <div class="flex-1 relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar por nome ou CPF..."
            class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
          />
          <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <select
          v-model="bloodTypeFilter"
          class="px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
        >
          <option value="">Todos os tipos sanguíneos</option>
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

    <!-- Patients Table -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
      <div v-if="filteredPatients.length === 0" class="text-center py-12">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
          {{ searchQuery || bloodTypeFilter ? 'Nenhum paciente encontrado' : 'Nenhum paciente cadastrado' }}
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mb-4">
          {{ searchQuery || bloodTypeFilter ? 'Tente ajustar os filtros de busca' : 'Comece cadastrando o primeiro paciente' }}
        </p>
        <button
          v-if="!searchQuery && !bloodTypeFilter"
          @click="openNewPatientModal"
          class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium"
        >
          Cadastrar Primeiro Paciente
        </button>
      </div>

      <table v-else class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nome Completo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Data de Nascimento</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Idade</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo Sanguíneo</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ações</th>
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
            <td class="px-6 py-4 text-right space-x-2">
              <button
                @click="openEditPatientModal(patient)"
                class="text-primary-600 dark:text-primary-400 hover:underline text-sm font-medium"
              >
                Editar
              </button>
              <button
                @click="confirmDelete(patient)"
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
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import PatientFormModal from '../components/PatientFormModal.vue';

const showPatientModal = ref(false);
const editingPatient = ref(null);
const searchQuery = ref('');
const bloodTypeFilter = ref('');

// Mock patients data (will be replaced with API call)
const patients = ref([
  {
    id: 1,
    full_name: 'Maria Silva Santos',
    birth_date: '1965-03-15',
    blood_group: 'A',
    rh_factor: '+'
  },
  {
    id: 2,
    full_name: 'João Pedro Oliveira',
    birth_date: '1972-08-22',
    blood_group: 'O',
    rh_factor: '+'
  },
  {
    id: 3,
    full_name: 'Ana Carolina Souza',
    birth_date: '1980-11-05',
    blood_group: 'B',
    rh_factor: '-'
  },
]);

// Computed
const activePatients = computed(() => {
  // In a real app, this would filter by status
  return patients.value.length;
});

const filteredPatients = computed(() => {
  let filtered = patients.value;

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(p =>
      p.full_name.toLowerCase().includes(query)
    );
  }

  // Filter by blood type
  if (bloodTypeFilter.value) {
    const [group, factor] = bloodTypeFilter.value.includes('+')
      ? [bloodTypeFilter.value.replace('+', ''), '+']
      : [bloodTypeFilter.value.replace('-', ''), '-'];

    filtered = filtered.filter(p =>
      p.blood_group === group && p.rh_factor === factor
    );
  }

  return filtered;
});

// Methods
function bloodTypeCount(group, factor) {
  return patients.value.filter(p =>
    p.blood_group === group && p.rh_factor === factor
  ).length;
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

function handlePatientSaved(patientData) {
  if (editingPatient.value) {
    // Update existing patient
    const index = patients.value.findIndex(p => p.id === editingPatient.value.id);
    if (index !== -1) {
      patients.value[index] = { ...patients.value[index], ...patientData };
    }
    alert('Paciente atualizado com sucesso!');
  } else {
    // Add new patient
    patients.value.push(patientData);
    alert('Paciente cadastrado com sucesso!');
  }

  closePatientModal();
}

function confirmDelete(patient) {
  if (confirm(`Deseja realmente excluir o paciente ${patient.full_name}?`)) {
    const index = patients.value.findIndex(p => p.id === patient.id);
    if (index !== -1) {
      patients.value.splice(index, 1);
      alert('Paciente excluído com sucesso!');
    }
  }
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
