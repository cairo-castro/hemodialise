<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/dashboard"></ion-back-button>
        </ion-buttons>
        <ion-title>Checklist de Segurança</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <div class="checklist-container">

        <!-- Header com data e horário atual -->
        <ion-card class="time-header-card">
          <ion-card-content>
            <div class="time-display">
              <div class="current-date">
                <ion-icon :icon="calendarOutline" class="date-icon"></ion-icon>
                {{ currentDate }}
              </div>
              <div class="current-time">
                <ion-icon :icon="timeOutline" class="time-icon"></ion-icon>
                {{ currentTime }}
              </div>
            </div>
          </ion-card-content>
        </ion-card>

        <!-- Patient Search Section -->
        <div v-if="!activeChecklist" class="section">
          <h2 class="section-title">
            <ion-icon :icon="personAddOutline"></ion-icon>
            Etapa 1: Buscar Paciente
          </h2>
          
          <div class="search-card">
            <div class="search-input-wrapper">
              <ion-icon :icon="searchOutline" class="search-icon"></ion-icon>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Digite o nome do paciente..."
                class="search-input"
                @input="handleSearchInput"
              />
            </div>

            <!-- Patient found -->
            <div v-if="selectedPatient" class="patient-selected">
              <div class="action-btn selected">
                <div class="action-icon success">
                  <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
                </div>
                <div class="action-content">
                  <span class="action-title">{{ selectedPatient.full_name }}</span>
                  <span class="action-subtitle">
                    {{ formatDate(selectedPatient.birth_date) }} • {{ selectedPatient.age }} anos
                  </span>
                </div>
                <ion-button fill="clear" @click="clearPatientSelection">
                  <ion-icon :icon="closeOutline" slot="icon-only"></ion-icon>
                </ion-button>
              </div>
            </div>

            <!-- Search Results -->
            <div v-else-if="searchResults.length > 0 && searchQuery.length > 0" class="search-results">
              <div
                v-for="patient in searchResults"
                :key="patient.id"
                class="action-btn"
                @click="selectPatient(patient)"
              >
                <div class="action-icon primary">
                  <ion-icon :icon="personAddOutline"></ion-icon>
                </div>
                <div class="action-content">
                  <span class="action-title">{{ patient.full_name }}</span>
                  <span class="action-subtitle">
                    {{ formatDate(patient.birth_date) }} • {{ patient.age }} anos
                  </span>
                </div>
                <ion-icon :icon="chevronForwardOutline" class="chevron"></ion-icon>
              </div>
            </div>

            <!-- No results -->
            <div v-else-if="searchQuery.length > 2 && searchResults.length === 0 && !isSearching" class="no-results">
              <ion-icon :icon="alertCircleOutline" class="no-results-icon"></ion-icon>
              <h3>Paciente não encontrado</h3>
              <p>Não encontramos nenhum paciente com esse nome.</p>
              <ion-button expand="block" @click="navigateToRegisterPatient" class="register-btn">
                <ion-icon :icon="personAddOutline" slot="start"></ion-icon>
                Cadastrar Novo Paciente
              </ion-button>
            </div>

            <!-- Loading -->
            <div v-if="isSearching" class="search-loading">
              <ion-spinner name="crescent"></ion-spinner>
              <p>Buscando...</p>
            </div>
          </div>
        </div>

        <!-- Machine Selection -->
        <div v-if="selectedPatient && !activeChecklist" class="section">
          <h2 class="section-title">
            <ion-icon :icon="hardwareChipOutline"></ion-icon>
            Etapa 2: Máquina e Turno
          </h2>

          <div class="selection-card">
            <div class="form-group">
              <label class="input-label">
                <ion-icon :icon="medicalOutline"></ion-icon>
                Selecione a Máquina
              </label>
              <ion-item fill="solid" class="select-input" lines="none">
                <ion-select 
                  v-model="checklistForm.machine_id" 
                  placeholder="Escolha uma máquina"
                  interface="action-sheet"
                  cancel-text="Cancelar"
                >
                  <ion-select-option v-for="machine in availableMachines" :key="machine.id" :value="machine.id">
                    {{ machine.name }}
                  </ion-select-option>
                </ion-select>
              </ion-item>
            </div>

            <div class="form-group">
              <label class="input-label">
                <ion-icon :icon="timeOutline"></ion-icon>
                Selecione o Turno
              </label>
              <div class="shift-selector">
                <button
                  type="button"
                  class="shift-btn"
                  :class="{ active: checklistForm.shift === 'matutino' }"
                  @click="checklistForm.shift = 'matutino'"
                >
                  <ion-icon :icon="sunnyOutline"></ion-icon>
                  <span>Matutino</span>
                </button>
                <button
                  type="button"
                  class="shift-btn"
                  :class="{ active: checklistForm.shift === 'vespertino' }"
                  @click="checklistForm.shift = 'vespertino'"
                >
                  <ion-icon :icon="partlySunnyOutline"></ion-icon>
                  <span>Vespertino</span>
                </button>
                <button
                  type="button"
                  class="shift-btn"
                  :class="{ active: checklistForm.shift === 'noturno' }"
                  @click="checklistForm.shift = 'noturno'"
                >
                  <ion-icon :icon="moonOutline"></ion-icon>
                  <span>Noturno</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Primary Action -->
          <div class="primary-action">
            <button
              class="primary-btn"
              @click="startChecklist"
              :disabled="!canStartChecklist"
            >
              <ion-icon :icon="playOutline"></ion-icon>
              <div class="btn-text">
                <span class="btn-title">Iniciar Checklist</span>
                <span class="btn-subtitle">Começar verificação de segurança</span>
              </div>
              <ion-icon :icon="arrowForwardOutline"></ion-icon>
            </button>
          </div>
        </div>

        <!-- Active Checklist with Phases -->
        <div v-if="activeChecklist">
          <!-- Phase Progress -->
          <ion-card class="phase-progress-card">
            <ion-card-content>
              <div class="phase-header">
                <h3>{{ getPhaseTitle(activeChecklist.current_phase) }}</h3>
                <div class="phase-time">
                  <ion-chip :color="getPhaseColor(activeChecklist.current_phase)">
                    <ion-icon :icon="getPhaseIcon(activeChecklist.current_phase)"></ion-icon>
                    <ion-label>{{ getPhaseStatus(activeChecklist.current_phase) }}</ion-label>
                  </ion-chip>
                </div>
              </div>

              <div class="phase-progress">
                <div class="progress-steps">
                  <div
                    class="step"
                    :class="{
                      'active': step === activeChecklist.current_phase,
                      'completed': isPhaseCompleted(step),
                      'interrupted': activeChecklist.is_interrupted
                    }"
                    v-for="step in phases"
                    :key="step"
                  >
                    <div class="step-circle">
                      <ion-icon v-if="isPhaseCompleted(step)" :icon="checkmarkOutline"></ion-icon>
                      <ion-icon v-else-if="activeChecklist.is_interrupted" :icon="closeOutline"></ion-icon>
                      <span v-else>{{ getPhaseNumber(step) }}</span>
                    </div>
                    <span class="step-label">{{ getPhaseShortTitle(step) }}</span>
                  </div>
                </div>
              </div>

              <div class="phase-completion">
                <ion-progress-bar
                  :value="currentPhaseCompletion / 100"
                  :color="currentPhaseCompletion === 100 ? 'success' : 'primary'"
                ></ion-progress-bar>
                <p>{{ Math.round(currentPhaseCompletion) }}% concluído</p>
              </div>
            </ion-card-content>
          </ion-card>

          <!-- Patient Info Summary -->
          <ion-card class="patient-summary-card">
            <ion-card-content>
              <div class="patient-summary">
                <div class="patient-details">
                  <h4>{{ selectedPatient.full_name }}</h4>
                  <p>{{ selectedMachine?.name }} • Turno {{ activeChecklist.shift }}</p>
                </div>
                <div class="session-time">
                  <p>Iniciado às {{ formatTime(activeChecklist.pre_dialysis_started_at) }}</p>
                </div>
              </div>
            </ion-card-content>
          </ion-card>

          <!-- Current Phase Checklist -->
          <ion-card v-if="!activeChecklist.is_interrupted" class="checklist-phase-card">
            <ion-card-header>
              <ion-card-title>{{ getPhaseTitle(activeChecklist.current_phase) }}</ion-card-title>
              <ion-card-subtitle>{{ getPhaseDescription(activeChecklist.current_phase) }}</ion-card-subtitle>
            </ion-card-header>
            <ion-card-content>
              <div class="checklist-items">
                <ChecklistItem
                  v-for="item in getCurrentPhaseItems()"
                  :key="item.key"
                  :title="item.label"
                  :description="item.description"
                  :value="getItemStatus(item.key)"
                  :observation="getItemObservation(item.key)"
                  @update:value="setItemStatus(item.key, $event)"
                  @update:observation="setItemObservation(item.key, $event)"
                />
              </div>

              <!-- Observations -->
              <div class="form-group">
                <ion-item fill="outline" class="patient-input">
                  <ion-label position="floating">Observações (opcional)</ion-label>
                  <ion-textarea
                    v-model="checklistForm.observations"
                    :rows="3"
                    placeholder="Digite observações sobre esta fase..."
                  ></ion-textarea>
                </ion-item>
              </div>

              <!-- Action Buttons -->
              <div class="action-buttons">
                <!-- Pause and Return Button -->
                <ion-button
                  expand="block"
                  color="warning"
                  fill="outline"
                  @click="pauseAndReturn"
                  class="pause-button"
                >
                  <ion-icon :icon="pauseOutline" slot="start"></ion-icon>
                  Pausar e Voltar ao Dashboard
                </ion-button>

                <!-- Emergency Interrupt Button -->
                <ion-button
                  expand="block"
                  color="danger"
                  fill="outline"
                  @click="showInterruptModal = true"
                  class="interrupt-button"
                >
                  <ion-icon :icon="stopCircleOutline" slot="start"></ion-icon>
                  Interromper Checklist
                </ion-button>

                <!-- Continue/Complete Button -->
                <ion-button
                  expand="block"
                  :color="canAdvancePhase ? 'success' : 'medium'"
                  :disabled="!canAdvancePhase"
                  @click="advancePhase"
                  class="advance-button"
                >
                  <ion-icon :icon="canAdvancePhase ? arrowForwardOutline : lockClosedOutline" slot="start"></ion-icon>
                  {{ getAdvanceButtonText() }}
                </ion-button>
              </div>
            </ion-card-content>
          </ion-card>

          <!-- Interrupted State -->
          <ion-card v-if="activeChecklist.is_interrupted" class="interrupted-card">
            <ion-card-content>
              <div class="interrupted-content">
                <ion-icon :icon="alertCircleOutline" class="interrupted-icon"></ion-icon>
                <h3>Checklist Interrompido</h3>
                <p>O checklist foi interrompido em {{ formatTime(activeChecklist.interrupted_at) }}</p>
                <p class="interruption-reason"><strong>Motivo:</strong> {{ activeChecklist.interruption_reason }}</p>

                <ion-button
                  expand="block"
                  color="primary"
                  @click="returnToDashboard"
                  class="return-button"
                >
                  <ion-icon :icon="homeOutline" slot="start"></ion-icon>
                  Retornar ao Dashboard
                </ion-button>
              </div>
            </ion-card-content>
          </ion-card>

          <!-- Completed State -->
          <ion-card v-if="activeChecklist.current_phase === 'completed'" class="completed-card">
            <ion-card-content>
              <div class="completed-content">
                <ion-icon :icon="checkmarkCircleOutline" class="completed-icon"></ion-icon>
                <h3>Checklist Concluído com Sucesso!</h3>
                <p>Todas as fases foram completadas em {{ formatTime(activeChecklist.post_dialysis_completed_at) }}</p>

                <ion-button
                  expand="block"
                  color="success"
                  @click="returnToDashboard"
                  class="return-button"
                >
                  <ion-icon :icon="checkmarkOutline" slot="start"></ion-icon>
                  Finalizar e Retornar
                </ion-button>
              </div>
            </ion-card-content>
          </ion-card>
        </div>
      </div>

      <!-- Interrupt Modal -->
      <ion-modal :is-open="showInterruptModal" @willDismiss="showInterruptModal = false">
        <ion-header>
          <ion-toolbar>
            <ion-title>Interromper Checklist</ion-title>
            <ion-buttons slot="end">
              <ion-button @click="showInterruptModal = false">
                <ion-icon :icon="closeOutline"></ion-icon>
              </ion-button>
            </ion-buttons>
          </ion-toolbar>
        </ion-header>
        <ion-content>
          <div class="interrupt-modal-content">
            <div class="warning-section">
              <ion-icon :icon="warningOutline" class="warning-icon"></ion-icon>
              <h3>Atenção!</h3>
              <p>Você está prestes a interromper o checklist de segurança. Esta ação deve ser usada apenas em casos de emergência ou intercorrências médicas.</p>
            </div>

            <div class="form-group">
              <ion-item fill="outline" class="patient-input">
                <ion-label position="floating">Motivo da Interrupção</ion-label>
                <ion-textarea
                  v-model="interruptReason"
                  :rows="4"
                  placeholder="Descreva o motivo da interrupção (obrigatório)"
                  required
                ></ion-textarea>
              </ion-item>
            </div>

            <div class="modal-buttons">
              <ion-button
                expand="block"
                fill="outline"
                color="medium"
                @click="showInterruptModal = false"
              >
                Cancelar
              </ion-button>
              <ion-button
                expand="block"
                color="danger"
                :disabled="!interruptReason.trim()"
                @click="interruptChecklist"
              >
                <ion-icon :icon="stopCircleOutline" slot="start"></ion-icon>
                Confirmar Interrupção
              </ion-button>
            </div>
          </div>
        </ion-content>
      </ion-modal>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonButtons,
  IonBackButton,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardSubtitle,
  IonCardContent,
  IonItem,
  IonLabel,
  IonInput,
  IonTextarea,
  IonSelect,
  IonSelectOption,
  IonButton,
  IonCheckbox,
  IonChip,
  IonIcon,
  IonProgressBar,
  IonModal,
  IonSpinner,
  loadingController,
  toastController
} from '@ionic/vue';
import {
  searchOutline,
  checkmarkCircleOutline,
  personAddOutline,
  hardwareChipOutline,
  playOutline,
  arrowForwardOutline,
  stopCircleOutline,
  alertCircleOutline,
  warningOutline,
  homeOutline,
  checkmarkOutline,
  closeOutline,
  lockClosedOutline,
  calendarOutline,
  timeOutline,
  pauseOutline,
  informationCircleOutline,
  documentTextOutline,
  refreshOutline,
  chevronForwardOutline,
  medicalOutline,
  sunnyOutline,
  partlySunnyOutline,
  moonOutline
} from 'ionicons/icons';

import { Container } from '@mobile/core/di/Container';
import ChecklistItem from '@mobile/components/ChecklistItem.vue';
import { Patient, PatientSearchCriteria } from '@mobile/core/domain/entities/Patient';
import { Machine } from '@mobile/core/domain/entities/Machine';
import { AuthService } from '@shared/auth';

const router = useRouter();
const route = useRoute();
const container = Container.getInstance();

// Use cases
const searchPatientUseCase = container.getSearchPatientUseCase();
const machineRepository = container.getMachineRepository();

// Reactive state
const currentTime = ref('');
const currentDate = ref('');
const showInterruptModal = ref(false);
const interruptReason = ref('');
const currentPhaseCompletion = ref(0);
const currentPhase = ref<'machine-patient' | 'pre-dialysis' | 'during-session' | 'post-dialysis'>('machine-patient');

// Search state
const searchQuery = ref('');
const searchResults = ref<Patient[]>([]);
const isSearching = ref(false);
const selectedPatient = ref<Patient | null>(null);
let searchTimeout: ReturnType<typeof setTimeout>;
const selectedMachine = ref<Machine | null>(null);
const availableMachines = ref<Machine[]>([]);
const existingChecklistId = ref<number | null>(null);
const isEditingExisting = ref<boolean>(false);
const activeChecklist = ref<any>(null);

const checklistForm = ref({
  machine_id: 0,
  shift: 'matutino' as 'matutino' | 'vespertino' | 'noturno',
  observations: '',
  // Pre-dialysis items
  machine_disinfected: false,
  capillary_lines_identified: false,
  patient_identification_confirmed: false,
  vascular_access_evaluated: false,
  vital_signs_checked: false,
  medications_reviewed: false,
  dialyzer_membrane_checked: false,
  equipment_functioning_verified: false,
  // During session items
  dialysis_parameters_verified: false,
  patient_comfort_assessed: false,
  fluid_balance_monitored: false,
  alarms_responded: false,
  // Post-dialysis items
  session_completed_safely: false,
  vascular_access_secured: false,
  patient_vital_signs_stable: false,
  equipment_cleaned: false
});

// Estrutura para armazenar status C/NC/NA dos itens
type ItemStatus = 'C' | 'NC' | 'NA' | null;
const itemStatuses = ref<Record<string, ItemStatus>>({});
const itemObservations = ref<Record<string, string>>({});

const phases = ['pre_dialysis', 'during_session', 'post_dialysis'];

// Time update interval
let timeInterval: ReturnType<typeof setInterval>;

// Computed properties

const canStartChecklist = computed(() => {
  return selectedPatient.value && checklistForm.value.machine_id && checklistForm.value.shift;
});

const canAdvancePhase = computed(() => {
  if (!activeChecklist.value) return false;

  const items = getCurrentPhaseItems();

  const allComplete = items.every(item => getItemStatus(item.key) !== null);

  return allComplete;
});

// Phase management
const getPhaseTitle = (phase: string) => {
  const titles = {
    'pre_dialysis': 'Pré-Diálise',
    'during_session': 'Durante a Sessão',
    'post_dialysis': 'Pós-Diálise',
    'completed': 'Concluído',
    'interrupted': 'Interrompido'
  };
  return titles[phase] || phase;
};

const getPhaseShortTitle = (phase: string) => {
  const titles = {
    'pre_dialysis': 'Pré',
    'during_session': 'Durante',
    'post_dialysis': 'Pós'
  };
  return titles[phase] || phase;
};

const getPhaseDescription = (phase: string) => {
  const descriptions = {
    'pre_dialysis': 'Verificações de segurança antes de iniciar a sessão de diálise',
    'during_session': 'Monitoramento contínuo durante o procedimento',
    'post_dialysis': 'Finalização segura da sessão e limpeza dos equipamentos'
  };
  return descriptions[phase] || '';
};

const getPhaseColor = (phase: string) => {
  const colors = {
    'pre_dialysis': 'warning',
    'during_session': 'primary',
    'post_dialysis': 'secondary',
    'completed': 'success',
    'interrupted': 'danger'
  };
  return colors[phase] || 'medium';
};

const getPhaseIcon = (phase: string) => {
  return checkmarkCircleOutline; // Simplified for now
};

const getPhaseStatus = (phase: string) => {
  const status = {
    'pre_dialysis': 'Preparando',
    'during_session': 'Em Andamento',
    'post_dialysis': 'Finalizando',
    'completed': 'Concluído',
    'interrupted': 'Interrompido'
  };
  return status[phase] || phase;
};

const getPhaseNumber = (phase: string) => {
  const numbers = {
    'pre_dialysis': '1',
    'during_session': '2',
    'post_dialysis': '3'
  };
  return numbers[phase] || '';
};

const isPhaseCompleted = (phase: string) => {
  if (!activeChecklist.value) return false;

  switch (phase) {
    case 'pre_dialysis':
      return activeChecklist.value.pre_dialysis_completed_at !== null;
    case 'during_session':
      return activeChecklist.value.during_session_completed_at !== null;
    case 'post_dialysis':
      return activeChecklist.value.post_dialysis_completed_at !== null;
    default:
      return false;
  }
};

const getCurrentPhaseItems = () => {
  const allItems = {
    'pre_dialysis': [
      {
        key: 'machine_disinfected',
        label: 'Máquina Desinfetada',
        description: 'Verificar se a máquina foi devidamente desinfetada'
      },
      {
        key: 'capillary_lines_identified',
        label: 'Linhas Capilares Identificadas',
        description: 'Identificar e verificar as linhas capilares'
      },
      {
        key: 'patient_identification_confirmed',
        label: 'Identificação do Paciente',
        description: 'Confirmar identidade com dois identificadores'
      },
      {
        key: 'vascular_access_evaluated',
        label: 'Acesso Vascular Avaliado',
        description: 'Avaliar condições do acesso vascular'
      },
      {
        key: 'vital_signs_checked',
        label: 'Sinais Vitais Verificados',
        description: 'Aferir pressão arterial, temperatura e peso'
      },
      {
        key: 'medications_reviewed',
        label: 'Medicações Revisadas',
        description: 'Revisar medicações e dosagens prescritas'
      },
      {
        key: 'dialyzer_membrane_checked',
        label: 'Membrana do Dialisador Verificada',
        description: 'Verificar integridade da membrana'
      },
      {
        key: 'equipment_functioning_verified',
        label: 'Funcionamento dos Equipamentos',
        description: 'Testar funcionamento de todos os equipamentos'
      }
    ],
    'during_session': [
      {
        key: 'dialysis_parameters_verified',
        label: 'Parâmetros de Diálise Verificados',
        description: 'Confirmar e ajustar parâmetros de diálise'
      },
      {
        key: 'patient_comfort_assessed',
        label: 'Conforto do Paciente Avaliado',
        description: 'Verificar conforto e bem-estar do paciente'
      },
      {
        key: 'fluid_balance_monitored',
        label: 'Balanço Hídrico Monitorado',
        description: 'Acompanhar remoção e balanço de fluidos'
      },
      {
        key: 'alarms_responded',
        label: 'Resposta a Alarmes',
        description: 'Verificar e responder adequadamente aos alarmes'
      }
    ],
    'post_dialysis': [
      {
        key: 'session_completed_safely',
        label: 'Sessão Finalizada com Segurança',
        description: 'Finalizar sessão seguindo protocolos de segurança'
      },
      {
        key: 'vascular_access_secured',
        label: 'Acesso Vascular Protegido',
        description: 'Proteger e cuidar do acesso vascular'
      },
      {
        key: 'patient_vital_signs_stable',
        label: 'Sinais Vitais Estáveis',
        description: 'Confirmar estabilidade dos sinais vitais'
      },
      {
        key: 'equipment_cleaned',
        label: 'Equipamentos Limpos',
        description: 'Limpar e preparar equipamentos para próximo uso'
      }
    ]
  };

  return allItems[activeChecklist.value?.current_phase] || [];
};

const getAdvanceButtonText = () => {
  if (!canAdvancePhase.value) {
    return 'Complete todos os itens para continuar';
  }

  switch (activeChecklist.value?.current_phase) {
    case 'pre_dialysis':
      return 'Iniciar Sessão de Diálise';
    case 'during_session':
      return 'Finalizar Sessão';
    case 'post_dialysis':
      return 'Concluir Checklist';
    default:
      return 'Continuar';
  }
};

// Time management
const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('pt-BR', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
  currentDate.value = now.toLocaleDateString('pt-BR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Methods
const handleSearchInput = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  if (searchQuery.value.length < 3) {
    searchResults.value = [];
    return;
  }

  isSearching.value = true;
  searchTimeout = setTimeout(async () => {
    await searchPatients();
  }, 500);
};

const searchPatients = async () => {
  if (searchQuery.value.length < 3) {
    searchResults.value = [];
    isSearching.value = false;
    return;
  }

  try {
    const patientRepository = container.getPatientRepository();
    const results = await patientRepository.quickSearch(searchQuery.value, 10);
    searchResults.value = results;
  } catch (error) {
    console.error('Erro ao buscar pacientes:', error);
    searchResults.value = [];
  } finally {
    isSearching.value = false;
  }
};

const selectPatient = (patient: Patient) => {
  selectedPatient.value = patient;
  searchQuery.value = patient.full_name;
  searchResults.value = [];
};

const clearPatientSelection = () => {
  selectedPatient.value = null;
  searchQuery.value = '';
  searchResults.value = [];
};

const navigateToRegisterPatient = () => {
  // Store search query in localStorage to pre-fill the register form
  localStorage.setItem('patient_search_query', searchQuery.value);
  localStorage.setItem('return_to_checklist', 'true');
  router.push('/patients/new');
};

const startChecklist = async () => {
  // Se já estamos editando um checklist existente, não criar novo
  if (isEditingExisting.value) {
    currentPhase.value = 'pre-dialysis';
    return;
  }

  const loading = await loadingController.create({
    message: 'Iniciando checklist...',
    spinner: 'crescent'
  });
  await loading.present();

  try {
    // Create new checklist via API
    const response = await fetch('/api/checklists', {
      method: 'POST',
      headers: AuthService.getAuthHeaders(),
      body: JSON.stringify({
        patient_id: selectedPatient.value?.id,
        machine_id: checklistForm.value.machine_id,
        shift: checklistForm.value.shift,
        observations: checklistForm.value.observations
      })
    });

    const data = await response.json();

    if (data.success) {
      activeChecklist.value = data.checklist;
      selectedMachine.value = availableMachines.value.find(m => m.id === checklistForm.value.machine_id) || null;
      updatePhaseCompletion();

      const message = data.resumed
        ? 'Checklist existente carregado!'
        : 'Novo checklist iniciado com sucesso!';

      const toast = await toastController.create({
        message: message,
        duration: 2000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
    } else {
      throw new Error(data.message || 'Erro ao iniciar checklist');
    }
  } catch (error: any) {
    const toast = await toastController.create({
      message: error.message || 'Erro ao iniciar checklist',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    await loading.dismiss();
  }
};

const advancePhase = async () => {
  if (!canAdvancePhase.value) return;

  const loading = await loadingController.create({
    message: 'Avançando para próxima fase...',
    spinner: 'crescent'
  });
  await loading.present();

  try {
    // Update current phase
    await updatePhaseData();

    // Advance phase
    const response = await fetch(`/api/checklists/${activeChecklist.value.id}/advance`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...AuthService.getAuthHeaders()
      }
    });

    const data = await response.json();

    if (data.success) {
      activeChecklist.value = data.checklist;
      updatePhaseCompletion();

      const toast = await toastController.create({
        message: data.message,
        duration: 2000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
    } else {
      throw new Error(data.message || 'Erro ao avançar fase');
    }
  } catch (error: any) {
    const toast = await toastController.create({
      message: error.message || 'Erro ao avançar fase',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    await loading.dismiss();
  }
};

const updatePhaseData = async () => {
  if (!activeChecklist.value) return;

  const phaseData = {};
  const items = getCurrentPhaseItems();

  items.forEach(item => {
    // Para o backend, só marca como true se o status for 'C' (Conforme)
    const status = getItemStatus(item.key);
    phaseData[item.key] = status === 'C';
  });


  const response = await fetch(`/api/checklists/${activeChecklist.value.id}/phase`, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
    },
    body: JSON.stringify({
      phase_data: phaseData,
      observations: checklistForm.value.observations
    })
  });

  const data = await response.json();
  if (data.success) {
    currentPhaseCompletion.value = data.phase_completion;
  }
};

const interruptChecklist = async () => {
  const loading = await loadingController.create({
    message: 'Interrompendo checklist...',
    spinner: 'crescent'
  });
  await loading.present();

  try {
    const response = await fetch(`/api/checklists/${activeChecklist.value.id}/interrupt`, {
      method: 'POST',
      headers: AuthService.getAuthHeaders(),
      body: JSON.stringify({
        reason: interruptReason.value
      })
    });

    const data = await response.json();

    if (data.success) {
      activeChecklist.value = data.checklist;
      showInterruptModal.value = false;

      const toast = await toastController.create({
        message: 'Checklist interrompido com sucesso.',
        duration: 3000,
        color: 'warning',
        position: 'top'
      });
      await toast.present();
    } else {
      throw new Error(data.message || 'Erro ao interromper checklist');
    }
  } catch (error: any) {
    const toast = await toastController.create({
      message: error.message || 'Erro ao interromper checklist',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    await loading.dismiss();
  }
};

const returnToDashboard = () => {
  router.replace('/dashboard');
};

const pauseAndReturn = async () => {
  if (!activeChecklist.value) return;

  const loading = await loadingController.create({
    message: 'Pausando checklist...',
  });
  await loading.present();

  try {
    const response = await fetch(`/api/checklists/${activeChecklist.value.id}/pause`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    });

    const data = await response.json();
    if (data.success) {
      const toast = await toastController.create({
        message: 'Checklist pausado com sucesso. Você pode retomá-lo a qualquer momento.',
        duration: 3000,
        color: 'success',
        position: 'top'
      });
      await toast.present();

      // Return to dashboard
      router.replace('/dashboard');
    } else {
      throw new Error(data.message || 'Erro ao pausar checklist');
    }
  } catch (error) {
    console.error('Erro ao pausar checklist:', error);
    const toast = await toastController.create({
      message: 'Erro ao pausar checklist. Tente novamente.',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    await loading.dismiss();
  }
};

const updatePhaseCompletion = () => {
  const items = getCurrentPhaseItems();
  const completedItems = items.filter(item => getItemStatus(item.key) !== null).length;
  currentPhaseCompletion.value = items.length > 0 ? (completedItems / items.length) * 100 : 0;
};

const loadMachines = async () => {
  try {
    availableMachines.value = await machineRepository.getAvailable();
  } catch (error) {
    console.error('Error loading machines:', error);
  }
};

const loadExistingChecklist = async () => {
  const checklistId = route.params.id;
  if (checklistId && checklistId !== 'new') {
    try {
      const response = await fetch(`/api/checklists/${checklistId}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          'Accept': 'application/json'
        }
      });

      if (response.ok) {
        const data = await response.json();
        activeChecklist.value = data;
        existingChecklistId.value = data.id;
        isEditingExisting.value = true;

        // Carregar dados do checklist existente
        selectedMachine.value = data.machine;
        selectedPatient.value = data.patient;

        // Definir a fase atual baseada no checklist
        switch (data.current_phase) {
          case 'pre_dialysis':
            currentPhase.value = 'pre-dialysis';
            break;
          case 'during_session':
            currentPhase.value = 'during-session';
            break;
          case 'post_dialysis':
            currentPhase.value = 'post-dialysis';
            break;
          default:
            currentPhase.value = 'pre-dialysis';
        }

        // Se checklist estava pausado, retomar automaticamente
        if (data.paused_at && !data.resumed_at) {
          await resumeChecklist();
        }

        // Atualizar os dados do formulário com os valores do checklist
        updateFormFromChecklist(data);
        updatePhaseCompletion();

        console.log('Checklist carregado:', data);
      }
    } catch (error) {
      console.error('Erro ao carregar checklist:', error);
    }
  }
};

const resumeChecklist = async () => {
  if (!existingChecklistId.value) return;

  try {
    const response = await fetch(`/api/checklists/${existingChecklistId.value}/resume`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      }
    });

    if (response.ok) {
      console.log('Checklist retomado com sucesso');
    }
  } catch (error) {
    console.error('Erro ao retomar checklist:', error);
  }
};

const updateFormFromChecklist = (checklist: any) => {
  // Atualizar checklistForm com os dados do checklist carregado
  Object.keys(checklistForm.value).forEach(key => {
    if (checklist[key] !== undefined) {
      (checklistForm.value as any)[key] = checklist[key];
    }
  });
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR');
};

const formatTime = (dateString: string) => {
  return new Date(dateString).toLocaleTimeString('pt-BR', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getCheckValue = (key: string): boolean => {
  return (checklistForm.value as any)[key] || false;
};

const setCheckValue = (key: string, value: boolean) => {
  (checklistForm.value as any)[key] = value;
  updatePhaseCompletion();

  // Auto-save phase data
  if (activeChecklist.value) {
    updatePhaseData();
  }
};

// Funções para gerenciar status C/NC/NA
const getItemStatus = (key: string): ItemStatus => {
  return itemStatuses.value[key] || null;
};

const setItemStatus = (key: string, status: ItemStatus) => {
  itemStatuses.value[key] = status;

  // Atualiza também o valor booleano para compatibilidade
  (checklistForm.value as any)[key] = status === 'C';

  updatePhaseCompletion();

  // Auto-save phase data
  if (activeChecklist.value) {
    updatePhaseData();
  }
};

const getItemObservation = (key: string): string => {
  return itemObservations.value[key] || '';
};

const setItemObservation = (key: string, observation: string) => {
  itemObservations.value[key] = observation;

  // Auto-save phase data
  if (activeChecklist.value) {
    updatePhaseData();
  }
};

// Lifecycle
onMounted(() => {
  loadMachines();
  loadExistingChecklist();
  updateTime();
  timeInterval = setInterval(updateTime, 1000);
  
  // Check if returning from patient registration
  const returnToChecklist = localStorage.getItem('return_to_checklist');
  const newPatientId = localStorage.getItem('new_patient_id');
  
  if (returnToChecklist === 'true' && newPatientId) {
    // Load the newly created patient
    loadNewPatient(parseInt(newPatientId));
    localStorage.removeItem('return_to_checklist');
    localStorage.removeItem('new_patient_id');
    localStorage.removeItem('patient_search_query');
  }
});

const loadNewPatient = async (patientId: number) => {
  try {
    const patientRepository = container.getPatientRepository();
    const patient = await patientRepository.getById(patientId);
    if (patient) {
      selectedPatient.value = patient;
      searchQuery.value = patient.full_name;
      
      const toast = await toastController.create({
        message: 'Paciente cadastrado com sucesso!',
        duration: 2000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
    }
  } catch (error) {
    console.error('Erro ao carregar paciente:', error);
  }
};

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval);
  }
});
</script>

<style scoped>
.checklist-container {
  padding: 0;
  max-width: 100%;
  background: #f9fafb;
  min-height: 100vh;
}

.time-header-card {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  color: white;
  margin: 0;
  border-radius: 0;
}

.time-display {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.current-date, .current-time {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
}

.date-icon, .time-icon {
  font-size: 1.2rem;
}

/* Section */
.section {
  padding: 0 16px;
  margin-top: 24px;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 12px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.section-title ion-icon {
  font-size: 1.3rem;
  color: var(--ion-color-primary);
}

/* Search Card */
.search-card {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  transition: all 0.2s ease;
}

.search-input-wrapper:focus-within {
  background: white;
  border-color: var(--ion-color-primary);
}

.search-icon {
  font-size: 1.5rem;
  color: #9ca3af;
  flex-shrink: 0;
}

.search-input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  font-size: 1rem;
  color: #1f2937;
}

.search-input::placeholder {
  color: #9ca3af;
}

/* Search Results */
.search-results {
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 400px;
  overflow-y: auto;
}

/* Action Button (reusable) */
.action-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn:active {
  transform: scale(0.98);
  border-color: var(--ion-color-primary);
}

.action-btn.selected {
  border-color: var(--ion-color-success);
  background: #f0fdf4;
}

.action-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.action-icon ion-icon {
  font-size: 1.8rem;
  color: white;
}

.action-icon.primary {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
}

.action-icon.success {
  background: linear-gradient(135deg, var(--ion-color-success) 0%, var(--ion-color-success-shade) 100%);
}

.action-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.action-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

.action-subtitle {
  font-size: 0.85rem;
  color: #6b7280;
}

.chevron {
  font-size: 1.2rem;
  color: #9ca3af;
  flex-shrink: 0;
}

/* No Results */
.no-results {
  text-align: center;
  padding: 32px 16px;
}

.no-results-icon {
  font-size: 4rem;
  color: #f59e0b;
  margin-bottom: 16px;
}

.no-results h3 {
  margin: 0 0 8px 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: #1f2937;
}

.no-results p {
  margin: 0 0 24px 0;
  font-size: 0.95rem;
  color: #6b7280;
}

.register-btn {
  --border-radius: 12px;
  height: 48px;
  font-weight: 600;
}

/* Search Loading */
.search-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 32px 16px;
}

.search-loading p {
  margin: 0;
  font-size: 0.95rem;
  color: #6b7280;
}

/* Patient Selected */
.patient-selected {
  margin-top: 12px;
}

/* Selection Card */
.selection-card {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group:last-child {
  margin-bottom: 0;
}

.input-label {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  color: #1f2937;
}

.input-label ion-icon {
  font-size: 1.2rem;
  color: var(--ion-color-primary);
}

.select-input {
  --border-radius: 12px;
  --background: #f9fafb;
  --border-width: 2px;
  --border-color: #e5e7eb;
  --padding-start: 16px;
  --padding-end: 16px;
}

.select-input:focus-within {
  --background: white;
  --border-color: var(--ion-color-primary);
}

/* Shift Selector */
.shift-selector {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}

.shift-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 16px 8px;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.85rem;
  font-weight: 600;
  color: #6b7280;
}

.shift-btn ion-icon {
  font-size: 1.8rem;
  color: #9ca3af;
}

.shift-btn.active {
  border-color: var(--ion-color-primary);
  background: linear-gradient(135deg, rgba(var(--ion-color-primary-rgb), 0.1) 0%, rgba(var(--ion-color-primary-rgb), 0.05) 100%);
  color: var(--ion-color-primary);
}

.shift-btn.active ion-icon {
  color: var(--ion-color-primary);
}

.shift-btn:active {
  transform: scale(0.95);
}

/* Primary Action */
.primary-action {
  padding: 0 16px;
  margin-top: 24px;
  margin-bottom: 24px;
}

.primary-btn {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 16px;
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  border: none;
  border-radius: 16px;
  padding: 20px 24px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(var(--ion-color-primary-rgb), 0.3);
}

.primary-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.primary-btn:active:not(:disabled) {
  transform: scale(0.98);
}

.primary-btn > ion-icon:first-child {
  font-size: 2rem;
  color: white;
  flex-shrink: 0;
}

.btn-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
  text-align: left;
  flex: 1;
}

.btn-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: white;
}

.btn-subtitle {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.9);
}

.primary-btn > ion-icon:last-child {
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
}

/* Checklist Phase Cards */
.checklist-phase-card,
.phase-progress-card,
.patient-summary-card,
.interrupted-card,
.completed-card {
  margin: 16px;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.phase-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.phase-header h3 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 700;
}

.progress-steps {
  display: flex;
  justify-content: space-between;
  margin: 1rem 0;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
}

.step-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--ion-color-light);
  color: var(--ion-color-medium);
  font-weight: bold;
  transition: all 0.3s ease;
}

.step.active .step-circle {
  background: var(--ion-color-primary);
  color: white;
}

.step.completed .step-circle {
  background: var(--ion-color-success);
  color: white;
}

.step.interrupted .step-circle {
  background: var(--ion-color-danger);
  color: white;
}

.step-label {
  font-size: 0.75rem;
  text-align: center;
  font-weight: 500;
  color: var(--ion-color-medium);
}

.step.active .step-label {
  color: var(--ion-color-primary);
  font-weight: 600;
}

.phase-completion {
  margin-top: 1rem;
}

.phase-completion p {
  text-align: center;
  margin-top: 0.5rem;
  font-weight: 600;
  color: var(--ion-color-dark);
}

.patient-summary {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.patient-details h4 {
  margin: 0;
  font-weight: 600;
}

.patient-details p {
  margin: 0.25rem 0 0 0;
  color: var(--ion-color-medium);
  font-size: 0.875rem;
}

.session-time p {
  margin: 0;
  font-size: 0.875rem;
  color: var(--ion-color-primary);
  font-weight: 500;
}

.checklist-items {
  margin-bottom: 1.5rem;
}

.checklist-item {
  --border-color: var(--ion-color-light);
  --padding: 16px 0;
  margin-bottom: 12px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.checklist-item.completed {
  --background: var(--ion-color-success-tint);
  --border-color: var(--ion-color-success);
}

.checklist-label h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--ion-color-dark);
}

.checklist-label p {
  margin: 0.25rem 0 0 0;
  font-size: 0.875rem;
  color: var(--ion-color-medium);
}

.completed-icon {
  font-size: 1.5rem;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 1.5rem;
}

.interrupt-button {
  --border-color: var(--ion-color-danger);
  --color: var(--ion-color-danger);
}

.advance-button {
  font-size: 1.1rem;
  font-weight: 700;
}

.interrupted-content,
.completed-content {
  text-align: center;
  padding: 2rem 1rem;
}

.interrupted-icon,
.completed-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  color: var(--ion-color-danger);
}

.completed-icon {
  color: var(--ion-color-success);
}

.interrupted-content h3,
.completed-content h3 {
  margin: 0 0 1rem 0;
  font-size: 1.5rem;
  font-weight: 700;
}

.interruption-reason {
  padding: 1rem;
  background: var(--ion-color-light);
  border-radius: 8px;
  margin: 1rem 0;
  text-align: left;
}

.interrupt-modal-content {
  padding: 1rem;
}

.warning-section {
  text-align: center;
  padding: 1rem;
  margin-bottom: 1.5rem;
  background: var(--ion-color-warning-tint);
  border-radius: 12px;
  border: 2px solid var(--ion-color-warning);
}

.warning-icon {
  font-size: 3rem;
  color: var(--ion-color-warning);
  margin-bottom: 0.5rem;
}

.warning-section h3 {
  margin: 0 0 0.5rem 0;
  color: var(--ion-color-warning-shade);
}

.modal-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.return-button {
  margin-top: 1rem;
  font-size: 1.1rem;
  font-weight: 600;
}
</style>