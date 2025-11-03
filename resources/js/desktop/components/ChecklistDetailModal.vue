<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="close"
      >
        <div
          class="bg-white dark:bg-gray-950 rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden border border-gray-200 dark:border-gray-800 flex flex-col"
          @click.stop
        >
          <!-- Header -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-800 flex-shrink-0">
            <div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                Detalhes do Checklist #{{ checklist?.id }}
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ formatDate(checklist?.created_at) }}
              </p>
            </div>
            <button
              @click="close"
              class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Content -->
          <div class="p-6 overflow-y-auto flex-1">
            <div v-if="loading" class="flex items-center justify-center py-12">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
            </div>

            <div v-else-if="checklist" class="space-y-6">
              <!-- Status Badge -->
              <div class="flex items-center gap-3">
                <span
                  :class="[
                    'px-3 py-1.5 text-sm font-medium rounded-lg',
                    getPhaseColor(checklist.current_phase)
                  ]"
                >
                  {{ getPhaseLabel(checklist.current_phase) }}
                </span>
                <span
                  :class="[
                    'px-3 py-1.5 text-sm font-medium rounded-lg',
                    getShiftColor(checklist.shift)
                  ]"
                >
                  {{ getShiftLabel(checklist.shift) }}
                </span>
              </div>

              <!-- Basic Info Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Patient Info -->
                <div class="space-y-4">
                  <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                    Paciente
                  </h4>
                  <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 space-y-2">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ checklist.patient?.full_name || 'N/A' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      Idade: {{ checklist.patient?.age || 'N/A' }} anos
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      Tipo Sanguíneo: {{ checklist.patient?.blood_type || 'N/A' }}
                    </p>
                  </div>
                </div>

                <!-- Machine Info -->
                <div class="space-y-4">
                  <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                    Máquina
                  </h4>
                  <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 space-y-2">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ checklist.machine?.name || 'N/A' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      ID: {{ checklist.machine?.identifier || 'N/A' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      Status: {{ checklist.machine?.status || 'N/A' }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Checklist Items: Pré-diálise -->
              <div class="space-y-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                  Pré-diálise
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div v-for="item in preDialysisItems" :key="item.label" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ item.label }}</span>
                    <span v-if="!editMode" :class="getComplianceStatusClass(item.value)">
                      {{ getComplianceStatusLabel(item.value) }}
                    </span>
                    <div v-else class="flex gap-1">
                      <button
                        type="button"
                        @click="item.value = true"
                        :class="['px-2 py-1 text-xs rounded border', item.value === true ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-600' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400']"
                      >
                        C
                      </button>
                      <button
                        type="button"
                        @click="item.value = false"
                        :class="['px-2 py-1 text-xs rounded border', item.value === false ? 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-600' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400']"
                      >
                        NC
                      </button>
                      <button
                        type="button"
                        @click="item.value = null"
                        :class="['px-2 py-1 text-xs rounded border', item.value === null ? 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-600' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400']"
                      >
                        NA
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Checklist Items: Durante a Sessão -->
              <div v-if="checklist.current_phase !== 'pre_dialysis'" class="space-y-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                  Durante a Sessão
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div v-for="item in duringSessionItems" :key="item.label" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ item.label }}</span>
                    <span v-if="!editMode" :class="getComplianceStatusClass(item.value)">
                      {{ getComplianceStatusLabel(item.value) }}
                    </span>
                    <div v-else class="flex gap-1">
                      <button
                        type="button"
                        @click="item.value = true"
                        :class="['px-2 py-1 text-xs rounded border', item.value === true ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-600' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400']"
                      >
                        C
                      </button>
                      <button
                        type="button"
                        @click="item.value = false"
                        :class="['px-2 py-1 text-xs rounded border', item.value === false ? 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-600' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400']"
                      >
                        NC
                      </button>
                      <button
                        type="button"
                        @click="item.value = null"
                        :class="['px-2 py-1 text-xs rounded border', item.value === null ? 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-600' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400']"
                      >
                        NA
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Checklist Items: Pós-diálise -->
              <div v-if="checklist.current_phase === 'post_dialysis' || checklist.current_phase === 'completed'" class="space-y-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                  Pós-diálise
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div v-for="item in postDialysisItems" :key="item.label" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ item.label }}</span>
                    <span v-if="!editMode" :class="getComplianceStatusClass(item.value)">
                      {{ getComplianceStatusLabel(item.value) }}
                    </span>
                    <div v-else class="flex gap-1">
                      <button
                        type="button"
                        @click="item.value = true"
                        :class="['px-2 py-1 text-xs rounded border', item.value === true ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-600' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400']"
                      >
                        C
                      </button>
                      <button
                        type="button"
                        @click="item.value = false"
                        :class="['px-2 py-1 text-xs rounded border', item.value === false ? 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-600' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400']"
                      >
                        NC
                      </button>
                      <button
                        type="button"
                        @click="item.value = null"
                        :class="['px-2 py-1 text-xs rounded border', item.value === null ? 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-600' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400']"
                      >
                        NA
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Observations -->
              <div v-if="checklist.observations || editMode" class="space-y-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                  Observações
                </h4>
                <div v-if="editMode" class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                  <textarea
                    v-model="checklist.observations"
                    rows="4"
                    class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Digite as observações aqui..."
                  ></textarea>
                </div>
                <div v-else class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                  <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                    {{ checklist.observations }}
                  </p>
                </div>
              </div>

              <!-- Timeline -->
              <div class="space-y-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                  Linha do Tempo
                </h4>
                <div class="space-y-3">
                  <div v-if="checklist.pre_dialysis_started_at" class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full mt-1.5 bg-blue-500"></div>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">Pré-diálise iniciada</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ formatDateTime(checklist.pre_dialysis_started_at) }}</p>
                    </div>
                  </div>
                  <div v-if="checklist.dialysis_started_at" class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full mt-1.5 bg-green-500"></div>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">Sessão de diálise iniciada</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ formatDateTime(checklist.dialysis_started_at) }}</p>
                    </div>
                  </div>
                  <div v-if="checklist.dialysis_completed_at" class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full mt-1.5 bg-purple-500"></div>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">Sessão concluída</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ formatDateTime(checklist.dialysis_completed_at) }}</p>
                    </div>
                  </div>
                  <div v-if="checklist.interrupted_at" class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full mt-1.5 bg-red-500"></div>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">Sessão interrompida</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ formatDateTime(checklist.interrupted_at) }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- User Info -->
              <div v-if="checklist.user" class="space-y-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                  Responsável
                </h4>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ checklist.user.name }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ checklist.user.email }}
                  </p>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-12">
              <p class="text-gray-500 dark:text-gray-400">Erro ao carregar detalhes do checklist</p>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-gray-800 flex-shrink-0">
            <!-- Left side: Delete button (only if not completed) -->
            <div>
              <button
                v-if="checklist && checklist.current_phase !== 'completed'"
                @click="showDeleteConfirm = true"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
              >
                Excluir
              </button>
            </div>

            <!-- Right side: Edit/Save/Cancel and Close buttons -->
            <div class="flex items-center gap-3">
              <template v-if="editMode">
                <button
                  @click="cancelEdit"
                  class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                  Cancelar
                </button>
                <button
                  @click="saveChecklist"
                  :disabled="saving"
                  class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="saving">Salvando...</span>
                  <span v-else>Salvar</span>
                </button>
              </template>
              <template v-else>
                <button
                  v-if="checklist && checklist.current_phase !== 'completed'"
                  @click="toggleEditMode"
                  class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors"
                >
                  Editar
                </button>
                <button
                  @click="close"
                  class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                  Fechar
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Delete Confirmation Modal -->
    <ConfirmDeleteModal
      :is-open="showDeleteConfirm"
      title="Confirmar Exclusão do Checklist"
      message="Tem certeza que deseja excluir este checklist de segurança? Esta ação não pode ser desfeita."
      :item-name="`Checklist #${checklist?.id} - ${checklist?.patient?.full_name}`"
      @close="showDeleteConfirm = false"
      @confirm="handleDeleteConfirm"
      ref="deleteModalRef"
    />
  </Teleport>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import ConfirmDeleteModal from './ConfirmDeleteModal.vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  checklistId: {
    type: Number,
    default: null
  }
});

const emit = defineEmits(['close', 'deleted']);

const checklist = ref(null);
const loading = ref(false);
const editMode = ref(false);
const saving = ref(false);
const showDeleteConfirm = ref(false);
const deleteModalRef = ref(null);

// Checklist items organized by phase
const preDialysisItems = computed(() => {
  if (!checklist.value) return [];
  return [
    { label: 'Máquina desinfetada', value: checklist.value.machine_disinfected },
    { label: 'Linhas capilares identificadas', value: checklist.value.capillary_lines_identified },
    { label: 'Teste de reagente realizado', value: checklist.value.reagent_test_performed },
    { label: 'Sensores de pressão verificados', value: checklist.value.pressure_sensors_verified },
    { label: 'Detector de bolhas de ar verificado', value: checklist.value.air_bubble_detector_verified },
    { label: 'Identificação do paciente confirmada', value: checklist.value.patient_identification_confirmed },
    { label: 'Acesso vascular avaliado', value: checklist.value.vascular_access_evaluated },
    { label: 'Braço da FAV lavado', value: checklist.value.av_fistula_arm_washed },
    { label: 'Paciente pesado', value: checklist.value.patient_weighed },
    { label: 'Sinais vitais verificados', value: checklist.value.vital_signs_checked },
    { label: 'Medicamentos revisados', value: checklist.value.medications_reviewed },
    { label: 'Membrana do dialisador verificada', value: checklist.value.dialyzer_membrane_checked },
    { label: 'Funcionamento do equipamento verificado', value: checklist.value.equipment_functioning_verified },
  ];
});

const duringSessionItems = computed(() => {
  if (!checklist.value) return [];
  return [
    { label: 'Parâmetros de diálise verificados', value: checklist.value.dialysis_parameters_verified },
    { label: 'Heparina conferida duplamente', value: checklist.value.heparin_double_checked },
    { label: 'Antissepsia realizada', value: checklist.value.antisepsis_performed },
    { label: 'Acesso vascular monitorado', value: checklist.value.vascular_access_monitored },
    { label: 'Sinais vitais monitorados', value: checklist.value.vital_signs_monitored_during },
    { label: 'Conforto do paciente avaliado', value: checklist.value.patient_comfort_assessed },
    { label: 'Balanço hídrico monitorado', value: checklist.value.fluid_balance_monitored },
    { label: 'Alarmes respondidos', value: checklist.value.alarms_responded },
  ];
});

const postDialysisItems = computed(() => {
  if (!checklist.value) return [];
  return [
    { label: 'Sessão concluída com segurança', value: checklist.value.session_completed_safely },
    { label: 'Acesso vascular protegido', value: checklist.value.vascular_access_secured },
    { label: 'Sinais vitais do paciente estáveis', value: checklist.value.patient_vital_signs_stable },
    { label: 'Complicações avaliadas', value: checklist.value.complications_assessed },
    { label: 'Equipamento limpo', value: checklist.value.equipment_cleaned },
  ];
});

watch(() => props.isOpen, async (newValue) => {
  if (newValue && props.checklistId) {
    await loadChecklistDetails();
  }
});

async function loadChecklistDetails() {
  loading.value = true;
  try {
    const response = await axios.get(`/api/checklists/${props.checklistId}`);
    checklist.value = response.data;
  } catch (error) {
    console.error('Error loading checklist details:', error);
  } finally {
    loading.value = false;
  }
}

function close() {
  emit('close');
  checklist.value = null;
  editMode.value = false;
}

async function saveChecklist() {
  if (!checklist.value) return;

  saving.value = true;
  try {
    const data = {
      observations: checklist.value.observations,
      // Pre-dialysis
      machine_disinfected: checklist.value.machine_disinfected,
      capillary_lines_identified: checklist.value.capillary_lines_identified,
      reagent_test_performed: checklist.value.reagent_test_performed,
      pressure_sensors_verified: checklist.value.pressure_sensors_verified,
      air_bubble_detector_verified: checklist.value.air_bubble_detector_verified,
      patient_identification_confirmed: checklist.value.patient_identification_confirmed,
      vascular_access_evaluated: checklist.value.vascular_access_evaluated,
      av_fistula_arm_washed: checklist.value.av_fistula_arm_washed,
      patient_weighed: checklist.value.patient_weighed,
      vital_signs_checked: checklist.value.vital_signs_checked,
      medications_reviewed: checklist.value.medications_reviewed,
      dialyzer_membrane_checked: checklist.value.dialyzer_membrane_checked,
      equipment_functioning_verified: checklist.value.equipment_functioning_verified,
      // During session
      dialysis_parameters_verified: checklist.value.dialysis_parameters_verified,
      heparin_double_checked: checklist.value.heparin_double_checked,
      antisepsis_performed: checklist.value.antisepsis_performed,
      vascular_access_monitored: checklist.value.vascular_access_monitored,
      vital_signs_monitored_during: checklist.value.vital_signs_monitored_during,
      patient_comfort_assessed: checklist.value.patient_comfort_assessed,
      fluid_balance_monitored: checklist.value.fluid_balance_monitored,
      alarms_responded: checklist.value.alarms_responded,
      // Post-dialysis
      session_completed_safely: checklist.value.session_completed_safely,
      vascular_access_secured: checklist.value.vascular_access_secured,
      patient_vital_signs_stable: checklist.value.patient_vital_signs_stable,
      complications_assessed: checklist.value.complications_assessed,
      equipment_cleaned: checklist.value.equipment_cleaned,
    };

    const response = await axios.put(`/api/checklists/${checklist.value.id}`, data);

    if (response.data.success) {
      checklist.value = response.data.checklist;
      editMode.value = false;
      showSuccessToast('Checklist atualizado com sucesso!');
    }
  } catch (error) {
    console.error('Error saving checklist:', error);
    showErrorToast(error.response?.data?.message || 'Erro ao salvar checklist');
  } finally {
    saving.value = false;
  }
}

async function handleDeleteConfirm() {
  if (!checklist.value) return;

  try {
    const response = await axios.delete(`/api/checklists/${checklist.value.id}`);

    if (response.data.success) {
      // Reset the delete modal state
      deleteModalRef.value?.resetDeletingState();

      // Close the delete confirmation modal
      showDeleteConfirm.value = false;

      // Show success toast
      showSuccessToast('Checklist excluído com sucesso!');

      // Clear checklist data
      checklist.value = null;

      // Emit deleted event and close detail modal
      emit('deleted');
      emit('close');
    }
  } catch (error) {
    console.error('Error deleting checklist:', error);

    // Reset the delete modal state
    deleteModalRef.value?.resetDeletingState();

    // Show error toast
    const errorMessage = error.response?.data?.message || 'Erro ao excluir checklist';
    showErrorToast(errorMessage);
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
  }, 4000);
}

function toggleEditMode() {
  editMode.value = !editMode.value;
}

function cancelEdit() {
  editMode.value = false;
  loadChecklistDetails(); // Reload to discard changes
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
}

function formatDateTime(dateString) {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
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

function getPhaseColor(phase) {
  const colors = {
    pre_dialysis: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    during_session: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    dialysis: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    post_dialysis: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    interrupted: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  };
  return colors[phase] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
}

function getShiftLabel(shift) {
  const labels = {
    matutino: 'Matutino',
    vespertino: 'Vespertino',
    noturno: 'Noturno'
  };
  return labels[shift] || shift;
}

function getShiftColor(shift) {
  const colors = {
    matutino: 'bg-sky-100 text-sky-800 dark:bg-sky-900/20 dark:text-sky-400',
    vespertino: 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400',
    noturno: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400'
  };
  return colors[shift] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
}

function getComplianceStatusLabel(status) {
  if (status === true) return 'Conforme';
  if (status === false) return 'Não Conforme';
  return 'N/A';
}

function getComplianceStatusClass(status) {
  if (status === true) {
    return 'px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 rounded-full';
  }
  if (status === false) {
    return 'px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 rounded-full';
  }
  return 'px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 rounded-full';
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active > div,
.modal-leave-active > div {
  transition: transform 0.2s ease;
}

.modal-enter-from > div,
.modal-leave-to > div {
  transform: scale(0.95);
}
</style>
