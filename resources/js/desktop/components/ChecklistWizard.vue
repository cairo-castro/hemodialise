<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal -->
        <div class="flex min-h-full items-start justify-center p-4 py-8">
          <div
            class="relative w-full max-w-6xl bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
            @click.stop
          >
            <!-- Header -->
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-800/50">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-xl font-bold text-gray-900 dark:text-white">Novo Checklist de Segurança</h2>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ stepDescription }}</p>
                </div>
                <button
                  @click="handleClose"
                  class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                >
                  <XMarkIcon class="w-6 h-6" />
                </button>
              </div>

              <!-- Progress Steps -->
              <div class="flex items-center justify-between mt-6">
                <div
                  v-for="(step, index) in steps"
                  :key="step.id"
                  class="flex items-center flex-1"
                >
                  <div class="flex items-center">
                    <div
                      class="flex items-center justify-center w-10 h-10 rounded-full font-semibold text-sm transition-all"
                      :class="[
                        currentStep > index
                          ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                          : currentStep === index
                          ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 ring-4 ring-primary-100 dark:ring-primary-900/30'
                          : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500'
                      ]"
                    >
                      <CheckIcon v-if="currentStep > index" class="w-5 h-5" />
                      <span v-else>{{ index + 1 }}</span>
                    </div>
                    <div class="ml-4">
                      <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ step.label }}</div>
                    </div>
                  </div>
                  <div
                    v-if="index < steps.length - 1"
                    class="flex-1 h-1 mx-4 rounded-full transition-all"
                    :class="currentStep > index ? 'bg-green-300 dark:bg-green-700' : 'bg-gray-200 dark:bg-gray-700'"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6 max-h-[calc(100vh-16rem)] overflow-y-auto">
              <!-- Step 1: Patient Selection -->
              <div v-if="currentStep === 0" class="space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Buscar Paciente
                  </label>
                  <div class="relative">
                    <input
                      v-model="patientSearch"
                      type="text"
                      placeholder="Digite o nome do paciente..."
                      class="w-full px-4 py-3 pl-10 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                      @input="searchPatients"
                    />
                    <MagnifyingGlassIcon class="absolute left-3 top-3.5 w-5 h-5 text-gray-400" />
                  </div>
                </div>

                <!-- Patient Results -->
                <div v-if="patientSearch" class="space-y-2">
                  <div v-if="searchingPatients" class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Buscando pacientes...</p>
                  </div>
                  <div v-else-if="filteredPatients.length === 0" class="text-center py-8">
                    <UsersIcon class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum paciente encontrado</p>
                  </div>
                  <button
                    v-else
                    v-for="patient in filteredPatients"
                    :key="patient.id"
                    @click="selectPatient(patient)"
                    class="w-full flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors text-left"
                    :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': selectedPatient?.id === patient.id }"
                  >
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">{{ patient.full_name }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">CPF: {{ patient.cpf }}</p>
                    </div>
                    <CheckCircleIcon
                      v-if="selectedPatient?.id === patient.id"
                      class="w-6 h-6 text-primary-600 dark:text-primary-400"
                    />
                  </button>
                </div>

                <!-- Selected Patient -->
                <div v-if="selectedPatient" class="p-4 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-sm font-medium text-primary-900 dark:text-primary-100">Paciente Selecionado</p>
                      <p class="text-lg font-bold text-primary-700 dark:text-primary-300 mt-1">{{ selectedPatient.full_name }}</p>
                      <p class="text-sm text-primary-600 dark:text-primary-400">CPF: {{ selectedPatient.cpf }}</p>
                    </div>
                    <button
                      @click="clearPatient"
                      class="p-2 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded-lg transition-colors"
                    >
                      <XMarkIcon class="w-5 h-5" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Step 2: Machine and Shift Selection -->
              <div v-if="currentStep === 1" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Machine Selection -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Selecione a Máquina
                    </label>
                    <select
                      v-model="selectedMachine"
                      class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                    >
                      <option :value="null">Selecione uma máquina...</option>
                      <option v-for="machine in machines" :key="machine.id" :value="machine.id">
                        {{ machine.name }} - {{ machine.identifier }}
                      </option>
                    </select>
                  </div>

                  <!-- Shift Selection -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Selecione o Turno
                    </label>
                    <div class="space-y-2">
                      <button
                        v-for="shift in shifts"
                        :key="shift.value"
                        @click="selectedShift = shift.value"
                        class="w-full flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-colors"
                        :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': selectedShift === shift.value }"
                      >
                        <div class="flex items-center">
                          <component :is="shift.icon" class="w-5 h-5 mr-3 text-gray-400" />
                          <span class="font-medium text-gray-900 dark:text-white">{{ shift.label }}</span>
                        </div>
                        <CheckCircleIcon
                          v-if="selectedShift === shift.value"
                          class="w-5 h-5 text-primary-600 dark:text-primary-400"
                        />
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Session Date and Time -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Data da Sessão
                    </label>
                    <input
                      v-model="sessionDate"
                      type="date"
                      :min="minDate"
                      :max="maxDate"
                      class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                    />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      Até 3 dias atrás
                    </p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Horário da Sessão
                    </label>
                    <input
                      v-model="sessionTime"
                      type="time"
                      class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                    />
                  </div>
                </div>
              </div>

              <!-- Step 3: Pre-Dialysis Checklist -->
              <div v-if="currentStep === 2" class="space-y-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                  <h3 class="font-semibold text-blue-900 dark:text-blue-100 flex items-center">
                    <ClipboardDocumentCheckIcon class="w-5 h-5 mr-2" />
                    Pré-Diálise
                  </h3>
                  <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">Verificações antes de iniciar a sessão</p>
                </div>
                <ChecklistItems :items="preDialysisItems" v-model="checklistData.pre_dialysis" />
              </div>

              <!-- Step 4: During Session Checklist -->
              <div v-if="currentStep === 3" class="space-y-6">
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                  <h3 class="font-semibold text-yellow-900 dark:text-yellow-100 flex items-center">
                    <ClipboardDocumentCheckIcon class="w-5 h-5 mr-2" />
                    Durante a Sessão
                  </h3>
                  <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">Verificações durante o procedimento</p>
                </div>
                <ChecklistItems :items="duringSessionItems" v-model="checklistData.during_session" />
              </div>

              <!-- Step 5: Post-Dialysis Checklist -->
              <div v-if="currentStep === 4" class="space-y-6">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
                  <h3 class="font-semibold text-green-900 dark:text-green-100 flex items-center">
                    <ClipboardDocumentCheckIcon class="w-5 h-5 mr-2" />
                    Pós-Diálise
                  </h3>
                  <p class="text-sm text-green-700 dark:text-green-300 mt-1">Verificações após o término da sessão</p>
                </div>
                <ChecklistItems :items="postDialysisItems" v-model="checklistData.post_dialysis" />
              </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between">
              <button
                v-if="currentStep > 0"
                @click="previousStep"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors font-medium"
              >
                Voltar
              </button>
              <div v-else></div>

              <div class="flex items-center space-x-3">
                <button
                  v-if="currentStep < steps.length - 1"
                  @click="nextStep"
                  :disabled="!canProceed"
                  class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Próximo
                </button>
                <button
                  v-else
                  @click="saveChecklist"
                  :disabled="saving"
                  class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ saving ? 'Salvando...' : 'Concluir Checklist' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import {
  XMarkIcon,
  CheckIcon,
  MagnifyingGlassIcon,
  UsersIcon,
  CheckCircleIcon,
  ClipboardDocumentCheckIcon,
  SunIcon,
  MoonIcon,
  SparklesIcon,
} from '@heroicons/vue/24/outline';
import ChecklistItems from './ChecklistItems.vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'saved']);

// Wizard Steps
const steps = [
  { id: 'patient', label: 'Paciente' },
  { id: 'machine', label: 'Máquina e Turno' },
  { id: 'pre_dialysis', label: 'Pré-Diálise' },
  { id: 'during_session', label: 'Durante Sessão' },
  { id: 'post_dialysis', label: 'Pós-Diálise' }
];

const currentStep = ref(0);
const saving = ref(false);

// Step 1: Patient Selection
const patientSearch = ref('');
const searchingPatients = ref(false);
const selectedPatient = ref(null);
const filteredPatients = ref([]);

// Step 2: Machine and Shift
const selectedMachine = ref(null);
const selectedShift = ref(null);
const sessionDate = ref(new Date().toISOString().split('T')[0]);
const sessionTime = ref(new Date().toTimeString().slice(0, 5));

// Calculate min date (72 hours ago) and max date (today)
const minDate = ref(() => {
  const date = new Date();
  date.setHours(date.getHours() - 72);
  return date.toISOString().split('T')[0];
});

const maxDate = ref(new Date().toISOString().split('T')[0]);

const machines = ref([]);
const loadingMachines = ref(false);

const shifts = [
  { value: 'morning', label: 'Matutino', icon: SunIcon },
  { value: 'afternoon', label: 'Vespertino', icon: SunIcon },
  { value: 'night', label: 'Noturno', icon: MoonIcon },
];

// Step 3-5: Checklist Items
const checklistData = ref({
  pre_dialysis: {},
  during_session: {},
  post_dialysis: {}
});

// Pre-Dialysis Items (13 items)
const preDialysisItems = [
  { id: 'patient_identification', label: 'Identificação do paciente confirmada', required: true },
  { id: 'access_verification', label: 'Verificação do acesso vascular', required: true },
  { id: 'vital_signs', label: 'Sinais vitais aferidos e registrados', required: true },
  { id: 'weight_check', label: 'Peso pré-diálise registrado', required: true },
  { id: 'machine_inspection', label: 'Inspeção visual da máquina', required: true },
  { id: 'dialyzer_check', label: 'Verificação do dialisador', required: true },
  { id: 'lines_check', label: 'Verificação das linhas e conexões', required: true },
  { id: 'solution_check', label: 'Verificação da solução de diálise', required: true },
  { id: 'anticoagulation', label: 'Anticoagulação preparada conforme prescrição', required: true },
  { id: 'alarm_test', label: 'Teste dos alarmes da máquina', required: true },
  { id: 'prescription_check', label: 'Prescrição médica verificada', required: true },
  { id: 'patient_comfort', label: 'Conforto do paciente verificado', required: false },
  { id: 'emergency_equipment', label: 'Equipamentos de emergência disponíveis', required: true },
];

// During Session Items (8 items)
const duringSessionItems = [
  { id: 'connection_verification', label: 'Verificação da conexão inicial', required: true },
  { id: 'blood_flow', label: 'Fluxo sanguíneo adequado', required: true },
  { id: 'pressure_monitoring', label: 'Pressões arterial e venosa monitoradas', required: true },
  { id: 'patient_monitoring', label: 'Monitoramento contínuo do paciente', required: true },
  { id: 'vital_signs_hourly', label: 'Sinais vitais verificados a cada hora', required: true },
  { id: 'alarm_response', label: 'Resposta adequada aos alarmes', required: true },
  { id: 'complications_check', label: 'Verificação de complicações', required: true },
  { id: 'fluid_removal', label: 'Remoção de fluido conforme prescrição', required: true },
];

// Post-Dialysis Items (5 items)
const postDialysisItems = [
  { id: 'disconnection_procedure', label: 'Procedimento de desconexão adequado', required: true },
  { id: 'hemostasis', label: 'Hemostasia adequada no local do acesso', required: true },
  { id: 'post_weight', label: 'Peso pós-diálise registrado', required: true },
  { id: 'post_vital_signs', label: 'Sinais vitais pós-diálise registrados', required: true },
  { id: 'patient_condition', label: 'Condição geral do paciente avaliada', required: true },
];

// Computed
const stepDescription = computed(() => {
  const descriptions = [
    'Selecione o paciente que realizará a sessão',
    'Escolha a máquina e o turno da sessão',
    'Preencha as verificações pré-diálise',
    'Preencha as verificações durante a sessão',
    'Preencha as verificações pós-diálise'
  ];
  return descriptions[currentStep.value];
});

const canProceed = computed(() => {
  if (currentStep.value === 0) return selectedPatient.value !== null;
  if (currentStep.value === 1) return selectedMachine.value !== null && selectedShift.value !== null;
  if (currentStep.value === 2) {
    return preDialysisItems.filter(item => item.required).every(item =>
      checklistData.value.pre_dialysis[item.id]?.status
    );
  }
  if (currentStep.value === 3) {
    return duringSessionItems.filter(item => item.required).every(item =>
      checklistData.value.during_session[item.id]?.status
    );
  }
  if (currentStep.value === 4) {
    return postDialysisItems.filter(item => item.required).every(item =>
      checklistData.value.post_dialysis[item.id]?.status
    );
  }
  return true;
});

// Methods
async function loadMachines() {
  loadingMachines.value = true;

  try {
    const response = await api.get('/api/machines/available');

    if (!response.ok) {
      throw new Error('Erro ao carregar máquinas');
    }

    const data = await response.json();

    if (data.success && data.machines) {
      machines.value = data.machines;
    } else {
      machines.value = [];
    }
  } catch (error) {
    console.error('Erro ao carregar máquinas:', error);
    machines.value = [];
  } finally {
    loadingMachines.value = false;
  }
}

async function searchPatients() {
  searchingPatients.value = true;

  try {
    if (patientSearch.value.length >= 2) {
      const response = await api.get(`/api/patients?search=${encodeURIComponent(patientSearch.value)}&per_page=20&include_inactive=false`);

      if (!response.ok) {
        throw new Error('Erro ao buscar pacientes');
      }

      const data = await response.json();

      if (data.success && data.patients) {
        filteredPatients.value = data.patients;
      } else {
        filteredPatients.value = [];
      }
    } else {
      filteredPatients.value = [];
    }
  } catch (error) {
    console.error('Erro ao buscar pacientes:', error);
    filteredPatients.value = [];
  } finally {
    searchingPatients.value = false;
  }
}

function selectPatient(patient) {
  selectedPatient.value = patient;
}

function clearPatient() {
  selectedPatient.value = null;
  patientSearch.value = '';
  filteredPatients.value = [];
}

function nextStep() {
  if (canProceed.value && currentStep.value < steps.length - 1) {
    currentStep.value++;
  }
}

function previousStep() {
  if (currentStep.value > 0) {
    currentStep.value--;
  }
}

async function saveChecklist() {
  saving.value = true;

  try {
    // Prepare checklist data
    const payload = {
      patient_id: selectedPatient.value.id,
      machine_id: selectedMachine.value,
      shift: selectedShift.value,
      session_date: sessionDate.value,
      session_time: sessionTime.value,
      pre_dialysis: checklistData.value.pre_dialysis,
      during_session: checklistData.value.during_session,
      post_dialysis: checklistData.value.post_dialysis,
    };

    // Save checklist via API
    const response = await api.post('/api/checklists', payload);

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Erro ao salvar checklist');
    }

    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || 'Erro ao salvar checklist');
    }

    // Show success toast
    showToast('success', 'Checklist salvo com sucesso!');

    emit('saved', result.checklist || result.data);
    handleClose();
  } catch (error) {
    console.error('Error saving checklist:', error);
    // Show error toast
    showToast('error', 'Erro ao salvar checklist. Tente novamente.');
  } finally {
    saving.value = false;
  }
}

function showToast(type, message) {
  const toast = document.createElement('div');
  toast.className = `fixed top-4 right-4 z-[9999] px-6 py-4 rounded-lg shadow-2xl border transform transition-all duration-300 translate-x-0 ${
    type === 'success'
      ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200'
      : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'
  }`;

  const icon = type === 'success'
    ? '<svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
    : '<svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';

  toast.innerHTML = `
    <div class="flex items-center">
      ${icon}
      <span class="font-medium">${message}</span>
    </div>
  `;

  document.body.appendChild(toast);

  // Animate in
  setTimeout(() => {
    toast.style.transform = 'translateX(0)';
  }, 10);

  // Remove after 4 seconds
  setTimeout(() => {
    toast.style.transform = 'translateX(400px)';
    toast.style.opacity = '0';
    setTimeout(() => {
      document.body.removeChild(toast);
    }, 300);
  }, 4000);
}

function handleBackdropClick() {
  if (confirm('Deseja realmente sair? Os dados não salvos serão perdidos.')) {
    handleClose();
  }
}

function handleClose() {
  // Reset wizard state
  currentStep.value = 0;
  selectedPatient.value = null;
  selectedMachine.value = null;
  selectedShift.value = null;
  patientSearch.value = '';
  filteredPatients.value = [];
  checklistData.value = {
    pre_dialysis: {},
    during_session: {},
    post_dialysis: {}
  };

  emit('close');
}

// Auto-search on mount
watch(() => props.isOpen, async (isOpen) => {
  if (isOpen) {
    // Load machines when wizard opens
    await loadMachines();
  }
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
  opacity: 0;
}
</style>
