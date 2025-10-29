<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/dashboard"></ion-back-button>
        </ion-buttons>
        <ion-title>Checklist de Segurança</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content ref="contentRef" :fullscreen="true">
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
            <!-- ✅ Machine Availability Status Badge -->
            <div v-if="machineAvailability" class="availability-badge" :class="machineAvailability.overall_status">
              <div class="availability-icon">
                <ion-icon v-if="machineAvailability.overall_status === 'good'" :icon="checkmarkCircleOutline"></ion-icon>
                <ion-icon v-else-if="machineAvailability.overall_status === 'critical'" :icon="closeCircleOutline"></ion-icon>
                <ion-icon v-else :icon="alertCircleOutline"></ion-icon>
              </div>
              <div class="availability-info">
                <span class="availability-label">{{ machineAvailability.message }}</span>
                <span class="availability-count">{{ machineAvailability.available }} de {{ machineAvailability.total }} disponíveis</span>
              </div>
            </div>

            <div class="form-group">
              <label class="input-label">
                <ion-icon :icon="medicalOutline"></ion-icon>
                Selecione a Máquina
              </label>
              
              <!-- Machine Cards Grid -->
              <div class="machine-grid">
                <button
                  v-for="machine in availableMachines"
                  :key="machine.id"
                  type="button"
                  class="machine-card"
                  :class="{ selected: checklistForm.machine_id === machine.id }"
                  @click="checklistForm.machine_id = machine.id"
                >
                  <div class="machine-icon">
                    <ion-icon :icon="hardwareChipOutline"></ion-icon>
                  </div>
                  <div class="machine-info">
                    <span class="machine-name">{{ machine.name }}</span>
                    <span class="machine-status">Disponível</span>
                  </div>
                  <div class="machine-check" v-if="checklistForm.machine_id === machine.id">
                    <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
                  </div>
                </button>
              </div>
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
                  <span class="shift-time">06:00 - 12:00</span>
                </button>
                <button
                  type="button"
                  class="shift-btn"
                  :class="{ active: checklistForm.shift === 'vespertino' }"
                  @click="checklistForm.shift = 'vespertino'"
                >
                  <ion-icon :icon="partlySunnyOutline"></ion-icon>
                  <span>Vespertino</span>
                  <span class="shift-time">12:00 - 18:00</span>
                </button>
                <button
                  type="button"
                  class="shift-btn"
                  :class="{ active: checklistForm.shift === 'noturno' }"
                  @click="checklistForm.shift = 'noturno'"
                >
                  <ion-icon :icon="moonOutline"></ion-icon>
                  <span>Noturno</span>
                  <span class="shift-time">18:00 - 00:00</span>
                </button>
                <button
                  type="button"
                  class="shift-btn"
                  :class="{ active: checklistForm.shift === 'madrugada' }"
                  @click="checklistForm.shift = 'madrugada'"
                >
                  <ion-icon :icon="cloudyNightOutline"></ion-icon>
                  <span>Madrugada</span>
                  <span class="shift-time">00:00 - 06:00</span>
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
          <ion-card v-if="activeChecklist.current_phase !== 'completed'" class="phase-progress-card">
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
          <ion-card v-if="activeChecklist.current_phase !== 'completed'" class="patient-summary-card">
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
          <div v-if="!activeChecklist.is_interrupted && activeChecklist.current_phase !== 'completed'" class="phase-section">
            <!-- Phase Header Card -->
            <div class="phase-header-card">
              <div class="phase-header-icon">
                <ion-icon :icon="getPhaseIcon(activeChecklist.current_phase)"></ion-icon>
              </div>
              <div class="phase-header-content">
                <h3>{{ getPhaseTitle(activeChecklist.current_phase) }}</h3>
                <p>{{ getPhaseDescription(activeChecklist.current_phase) }}</p>
              </div>
              <div class="phase-header-badge">
                <ion-chip :color="getPhaseColor(activeChecklist.current_phase)">
                  {{ Math.round(currentPhaseCompletion) }}%
                </ion-chip>
              </div>
            </div>

            <!-- Checklist Items as Cards -->
            <div class="checklist-items-grid">
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

            <!-- Observations Card -->
            <div class="observations-card">
              <label class="card-label">
                <ion-icon :icon="documentTextOutline"></ion-icon>
                Observações Adicionais
              </label>
              <textarea
                v-model="checklistForm.observations"
                class="observations-input"
                rows="4"
                placeholder="Digite observações sobre esta fase do checklist..."
              ></textarea>
            </div>

            <!-- Primary Continue Button - MOVED TO TOP FOR EMPHASIS -->
            <button
              class="primary-continue-btn"
              :class="{ disabled: !canAdvancePhase }"
              :disabled="!canAdvancePhase"
              @click="advancePhase"
            >
              <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
              <div class="btn-text">
                <span class="btn-title">{{ isLastPhase ? 'Concluir Checklist' : 'Avançar para Próxima Fase' }}</span>
                <span class="btn-subtitle">{{ canAdvancePhase ? 'Todos os itens verificados' : 'Complete todos os itens obrigatórios' }}</span>
              </div>
              <ion-icon :icon="arrowForwardOutline"></ion-icon>
            </button>

            <!-- Secondary Action Buttons Dashboard Style -->
            <div class="dashboard-actions">
              <!-- Pause Button -->
              <button class="action-card warning" @click="pauseAndReturn">
                <div class="action-card-icon warning">
                  <ion-icon :icon="pauseOutline"></ion-icon>
                </div>
                <div class="action-card-content">
                  <span class="action-card-title">Pausar</span>
                  <span class="action-card-subtitle">Voltar depois</span>
                </div>
              </button>

              <!-- Interrupt Button -->
              <button class="action-card danger" @click="showInterruptModal = true">
                <div class="action-card-icon danger">
                  <ion-icon :icon="stopCircleOutline"></ion-icon>
                </div>
                <div class="action-card-content">
                  <span class="action-card-title">Interromper</span>
                  <span class="action-card-subtitle">Cancelar processo</span>
                </div>
              </button>
            </div>
          </div>

          <!-- Interrupted State -->
          <div v-else class="interrupted-section">
            <ion-card class="interrupted-card">
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
          </div>

          <!-- Completed State -->
          <div v-if="activeChecklist.current_phase === 'completed'" class="completed-section">
            <ion-card class="completed-card">
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRouter, useRoute, onBeforeRouteLeave } from 'vue-router';
import { useIonRouter } from '@ionic/vue';
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
  toastController,
  alertController,
  onIonViewWillEnter
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
  closeCircleOutline,
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
  moonOutline,
  cloudyNightOutline
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
const contentRef = ref();
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

// ✅ Machine availability tracking
const machineAvailability = ref<any>(null);
const isCheckingAvailability = ref(false);

const checklistForm = ref({
  machine_id: 0,
  shift: 'matutino' as 'matutino' | 'vespertino' | 'noturno' | 'madrugada',
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

  const allComplete = items.every(item => {
    const status = getItemStatus(item.key);
    
    // Item não preenchido
    if (status === null) return false;
    
    // Conforme ou Não Aplica = OK
    if (status === 'C' || status === 'NA') return true;
    
    // Não Conforme = precisa ter observação
    if (status === 'NC') {
      const observation = getItemObservation(item.key);
      return observation && observation.trim().length > 0;
    }
    
    return false;
  });

  return allComplete;
});

const isLastPhase = computed(() => {
  if (!activeChecklist.value) return false;
  return activeChecklist.value.current_phase === 'post_dialysis';
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
  router.push('/patients/new');
};

const startChecklist = async () => {
  // Se já estamos editando um checklist existente, não criar novo
  if (isEditingExisting.value) {
    currentPhase.value = 'pre-dialysis';
    return;
  }

  // ✅ VALIDAÇÃO ROBUSTA: Verificar disponibilidade antes de criar
  const availabilityCheck = await checkMachineAvailability();
  if (!availabilityCheck.canCreate) {
    return; // Alerta já foi exibido
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

    if (response.ok && data.success) {
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
      // Tratar erros de validação do backend
      const errorMessage = data.message || data.errors?.machine_id?.[0] || 'Erro ao iniciar checklist';
      throw new Error(errorMessage);
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

      // Scroll to top of page when advancing phase
      if (contentRef.value && contentRef.value.$el) {
        await contentRef.value.$el.scrollToTop(400); // 400ms smooth scroll
      }

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

  try {
    const phaseData = {};
    const itemsWithObservations = {};
    const items = getCurrentPhaseItems();

    items.forEach(item => {
      const status = getItemStatus(item.key);
      const observation = getItemObservation(item.key);
      
      // Item considerado "completo" se:
      // - Status = 'C' (Conforme) OU
      // - Status = 'NC' (Não Conforme) COM observação preenchida OU
      // - Status = 'NA' (Não Aplica)
      let isComplete = false;
      
      if (status === 'C' || status === 'NA') {
        isComplete = true;
      } else if (status === 'NC' && observation && observation.trim().length > 0) {
        isComplete = true;
      }
      
      phaseData[item.key] = isComplete;
      
      // Guarda observação se existir
      if (observation && observation.trim().length > 0) {
        itemsWithObservations[item.key] = observation;
      }
    });

    const response = await fetch(`/api/checklists/${activeChecklist.value.id}/phase`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      },
      body: JSON.stringify({
        phase_data: phaseData,
        observations: checklistForm.value.observations,
        item_observations: itemsWithObservations
      })
    });

    if (!response.ok) {
      throw new Error('Erro ao salvar dados');
    }

    const data = await response.json();
    if (data.success) {
      currentPhaseCompletion.value = data.phase_completion;
    }
  } catch (error) {
    console.error('Erro no updatePhaseData:', error);
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
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ reason: 'manual' })
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

      // Mark as paused to allow navigation without triggering guard
      activeChecklist.value.paused_at = data.checklist.paused_at;

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
    // Também carrega estatísticas de disponibilidade
    await fetchMachineAvailability();
  } catch (error) {
    console.error('Error loading machines:', error);
  }
};

/**
 * ✅ Busca estatísticas de disponibilidade de máquinas
 */
const fetchMachineAvailability = async () => {
  try {
    isCheckingAvailability.value = true;
    const response = await fetch('/api/machines/availability', {
      headers: AuthService.getAuthHeaders()
    });

    if (response.ok) {
      const data = await response.json();
      machineAvailability.value = data.availability;
    }
  } catch (error) {
    console.error('Error fetching machine availability:', error);
  } finally {
    isCheckingAvailability.value = false;
  }
};

/**
 * ✅ VALIDAÇÃO ROBUSTA: Verifica se há máquinas disponíveis antes de criar checklist
 */
const checkMachineAvailability = async (): Promise<{ canCreate: boolean }> => {
  // Recarregar dados mais recentes
  await fetchMachineAvailability();

  if (!machineAvailability.value) {
    // Se não conseguiu carregar, assume que pode tentar (erro será tratado no backend)
    return { canCreate: true };
  }

  const { available, overall_status, message } = machineAvailability.value;

  // ❌ Nenhuma máquina disponível
  if (available === 0) {
    const alert = await alertController.create({
      header: 'Máquinas Indisponíveis',
      message: message || 'Não há máquinas disponíveis no momento. Por favor, aguarde até que uma máquina fique livre.',
      buttons: ['OK']
    });
    await alert.present();
    return { canCreate: false };
  }

  // ⚠️ Poucas máquinas disponíveis (warning)
  if (overall_status === 'warning' || overall_status === 'alert') {
    const alert = await alertController.create({
      header: 'Atenção',
      message: `${message}. Apenas ${available} máquina(s) disponível(is). Deseja continuar?`,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel'
        },
        {
          text: 'Continuar',
          role: 'confirm'
        }
      ]
    });

    await alert.present();
    const { role } = await alert.onDidDismiss();
    return { canCreate: role === 'confirm' };
  }

  // ✅ Disponibilidade OK
  return { canCreate: true };
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

  // Carregar observações de itens individuais
  if (checklist.item_observations) {
    itemObservations.value = { ...checklist.item_observations };
  }

  // Carregar status dos itens baseado nos valores booleanos
  const items = getCurrentPhaseItems();
  items.forEach(item => {
    const value = checklist[item.key];
    if (value !== undefined) {
      // Converter booleano em status
      // Se true = 'C', se false e tem observação = 'NC', se false sem observação = null
      if (value === true) {
        itemStatuses.value[item.key] = 'C';
      } else if (value === false && itemObservations.value[item.key]) {
        itemStatuses.value[item.key] = 'NC';
      }
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

// Debounce timer para auto-save
let saveTimer: NodeJS.Timeout | null = null;

const setItemObservation = (key: string, observation: string) => {
  itemObservations.value[key] = observation;

  // Debounce: Só salva após 1 segundo sem digitação
  if (saveTimer) {
    clearTimeout(saveTimer);
  }
  
  saveTimer = setTimeout(() => {
    if (activeChecklist.value) {
      updatePhaseData();
    }
  }, 1000);
};

const loadNewPatient = async (patientId: number) => {
  try {
    console.log('🔄 Carregando paciente ID:', patientId);
    const patientRepository = container.getPatientRepository();
    const patient = await patientRepository.getById(patientId);
    
    if (patient) {
      console.log('✅ Paciente carregado:', patient);
      selectedPatient.value = patient;
      searchQuery.value = patient.full_name;
      searchResults.value = [];
      
      // Show success toast
      const toast = await toastController.create({
        message: `Paciente ${patient.full_name} selecionado!`,
        duration: 2000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
    }
  } catch (error) {
    console.error('❌ Erro ao carregar paciente:', error);
    
    const toast = await toastController.create({
      message: 'Erro ao carregar paciente cadastrado',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  }
};

const checkAndLoadNewPatient = () => {
  const returnToChecklist = localStorage.getItem('return_to_checklist');
  const newPatientId = localStorage.getItem('new_patient_id');
  
  console.log('🔍 Verificando retorno - return_to_checklist:', returnToChecklist, 'new_patient_id:', newPatientId);
  
  if (returnToChecklist === 'true' && newPatientId) {
    console.log('🎯 Detectado retorno de cadastro, carregando paciente...');
    
    // Load the newly created patient
    loadNewPatient(parseInt(newPatientId));
    
    // Clean up localStorage
    localStorage.removeItem('return_to_checklist');
    localStorage.removeItem('new_patient_id');
    localStorage.removeItem('patient_search_query');
  }
};

// Ionic lifecycle hook - CALLED EVERY TIME THE VIEW IS ENTERED
onIonViewWillEnter(() => {
  console.log('🚀 onIonViewWillEnter - Página sendo exibida');
  checkAndLoadNewPatient();
});

// Watch for route changes (additional safeguard)
watch(() => route.path, (newPath) => {
  console.log('🔄 Rota mudou para:', newPath);
  if (newPath === '/checklist/new') {
    console.log('📍 Detectado retorno para /checklist/new via watch');
    // Small delay to ensure localStorage is set
    setTimeout(checkAndLoadNewPatient, 100);
  }
});

// Lifecycle
onMounted(() => {
  console.log('🏁 onMounted - Página montada');
  loadMachines();
  loadExistingChecklist();
  updateTime();
  timeInterval = setInterval(updateTime, 1000);
  
  // Also check on mount (for first load)
  checkAndLoadNewPatient();
});

// Navigation guard to prevent accidental exit
onBeforeRouteLeave(async (to, from) => {
  // Allow navigation if no active checklist or checklist is already paused/completed/interrupted
  if (!activeChecklist.value ||
      activeChecklist.value.paused_at ||
      activeChecklist.value.current_phase === 'completed' ||
      activeChecklist.value.is_interrupted) {
    return true;
  }

  // Show confirmation alert
  const alert = await alertController.create({
    header: 'Pausar Checklist?',
    message: 'Você está saindo do checklist ativo. Deseja pausá-lo para continuar depois?',
    buttons: [
      {
        text: 'Cancelar',
        role: 'cancel',
        handler: () => {
          return false; // Stay on page
        }
      },
      {
        text: 'Sair sem Pausar',
        role: 'destructive',
        handler: async () => {
          // Just leave without pausing
          return true;
        }
      },
      {
        text: 'Pausar e Sair',
        handler: async () => {
          // Pause automatically with reason 'auto'
          await pauseChecklistAutomatically();
          return true;
        }
      }
    ]
  });

  await alert.present();

  const { role } = await alert.onDidDismiss();

  // If user clicked cancel, prevent navigation
  if (role === 'cancel' || !role) {
    return false;
  }

  // If user chose to pause, wait for pause to complete
  if (role === 'handler') {
    await pauseChecklistAutomatically();
  }

  return true;
});

// Function to pause checklist automatically when user navigates away
const pauseChecklistAutomatically = async () => {
  if (!activeChecklist.value) return;

  try {
    const response = await fetch(`/api/checklists/${activeChecklist.value.id}/pause`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ reason: 'auto' })
    });

    const data = await response.json();
    if (data.success) {
      const toast = await toastController.create({
        message: 'Checklist pausado automaticamente.',
        duration: 2000,
        color: 'warning',
        position: 'top'
      });
      await toast.present();
    }
  } catch (error) {
    console.error('Erro ao pausar checklist automaticamente:', error);
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
  background: var(--ion-background-color);
  min-height: 100vh;
}

.time-header-card {
  background: var(--ion-color-primary);
  color: white;
  margin: 0;
  border-radius: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
  color: var(--ion-text-color);
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
  background: var(--ion-card-background);
  border: 2px solid var(--ion-color-step-150);
  border-radius: 12px;
  padding: 16px;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: var(--ion-background-color);
  border: 2px solid var(--ion-color-step-150);
  border-radius: 12px;
  transition: all 0.2s ease;
}

.search-input-wrapper:focus-within {
  background: var(--ion-card-background);
  border-color: var(--ion-color-primary);
}

.search-icon {
  font-size: 1.5rem;
  color: var(--ion-color-step-500);
  flex-shrink: 0;
}

.search-input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  font-size: 1rem;
  color: var(--ion-text-color);
}

.search-input::placeholder {
  color: var(--ion-color-step-500);
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
  background: var(--ion-card-background);
  border: 2px solid var(--ion-color-step-150);
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
  background: rgba(var(--ion-color-success-rgb), 0.1);
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
  background: var(--ion-color-primary);
}

.action-icon.success {
  background: var(--ion-color-success);
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
  color: var(--ion-text-color);
}

.action-subtitle {
  font-size: 0.85rem;
  color: var(--ion-color-step-600);
}

.chevron {
  font-size: 1.2rem;
  color: var(--ion-color-step-500);
  flex-shrink: 0;
}

/* No Results */
.no-results {
  text-align: center;
  padding: 32px 16px;
}

.no-results-icon {
  font-size: 4rem;
  color: var(--color-occupied);
  margin-bottom: 16px;
}

.no-results h3 {
  margin: 0 0 8px 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--ion-text-color);
}

.no-results p {
  margin: 0 0 24px 0;
  font-size: 0.95rem;
  color: var(--ion-color-step-600);
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
  color: var(--ion-color-step-600);
}

/* Patient Selected */
.patient-selected {
  margin-top: 12px;
}

/* Selection Card */
.selection-card {
  background: var(--ion-card-background);
  border: 2px solid var(--ion-color-step-150);
  border-radius: 12px;
  padding: 16px;
}

/* ✅ Machine Availability Badge */
.availability-badge {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 12px;
  margin-bottom: 16px;
  border: 2px solid;
  transition: all 0.3s ease;
}

.availability-badge.good {
  background: rgba(var(--ion-color-success-rgb), 0.1);
  border-color: var(--color-available);
}

.availability-badge.alert {
  background: rgba(var(--ion-color-warning-rgb), 0.1);
  border-color: var(--color-occupied);
}

.availability-badge.warning {
  background: rgba(249, 115, 22, 0.1);
  border-color: #f97316;
}

.availability-badge.critical {
  background: rgba(var(--ion-color-danger-rgb), 0.1);
  border-color: var(--color-maintenance);
}

.availability-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
}

.availability-badge.good .availability-icon {
  background: var(--color-available);
  color: white;
}

.availability-badge.alert .availability-icon {
  background: var(--color-occupied);
  color: white;
}

.availability-badge.warning .availability-icon {
  background: #f97316;
  color: white;
}

.availability-badge.critical .availability-icon {
  background: var(--color-maintenance);
  color: white;
}

.availability-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.availability-label {
  font-size: 15px;
  font-weight: 600;
  color: #111827;
}

.availability-count {
  font-size: 13px;
  color: var(--ion-color-step-600);
  font-weight: 500;
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
  color: var(--ion-text-color);
}

.input-label ion-icon {
  font-size: 1.2rem;
  color: var(--ion-color-primary);
}

.select-input {
  --border-radius: 12px;
  --background: var(--ion-background-color);
  --border-width: 2px;
  --border-color: var(--ion-color-step-150);
  --padding-start: 16px;
  --padding-end: 16px;
}

.select-input:focus-within {
  --background: var(--ion-card-background);
  --border-color: var(--ion-color-primary);
}

/* Machine Grid */
.machine-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
  margin-top: 12px;
}

.machine-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 20px 16px;
  background: var(--ion-card-background);
  border: 3px solid #e5e7eb;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-height: 140px;
}

.machine-card:active {
  transform: scale(0.97);
}

.machine-card.selected {
  border-color: var(--ion-color-primary);
  background: rgba(var(--ion-color-primary-rgb), 0.08);
  box-shadow: 0 4px 12px rgba(var(--ion-color-primary-rgb), 0.2);
}

.machine-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: var(--ion-color-step-100);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.machine-card.selected .machine-icon {
  background: var(--ion-color-primary);
}

.machine-icon ion-icon {
  font-size: 2rem;
  color: var(--ion-color-step-600);
  transition: all 0.3s ease;
}

.machine-card.selected .machine-icon ion-icon {
  color: white;
}

.machine-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  flex: 1;
}

.machine-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--ion-text-color);
  text-align: center;
}

.machine-status {
  font-size: 0.75rem;
  color: var(--color-available);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.machine-check {
  position: absolute;
  top: 8px;
  right: 8px;
}

.machine-check ion-icon {
  font-size: 1.5rem;
  color: var(--ion-color-primary);
}

/* Shift Selector */
.shift-selector {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-top: 12px;
}

@media (min-width: 640px) {
  .shift-selector {
    grid-template-columns: repeat(4, 1fr);
  }
}

.shift-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 16px 12px;
  background: var(--ion-card-background);
  border: 3px solid #e5e7eb;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--ion-color-step-600);
  position: relative;
}

.shift-btn ion-icon {
  font-size: 2rem;
  color: var(--ion-color-step-500);
  transition: all 0.3s ease;
}

.shift-btn span:first-of-type {
  font-size: 0.9rem;
  font-weight: 700;
}

.shift-time {
  font-size: 0.7rem;
  color: var(--ion-color-step-500);
  font-weight: 500;
}

.shift-btn.active {
  border-color: var(--ion-color-primary);
  background: rgba(var(--ion-color-primary-rgb), 0.08);
  color: var(--ion-color-primary);
  box-shadow: 0 4px 12px rgba(var(--ion-color-primary-rgb), 0.2);
}

.shift-btn.active ion-icon {
  color: var(--ion-color-primary);
}

.shift-btn.active .shift-time {
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
  background: var(--ion-color-primary);
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

/* ===== NEW DASHBOARD STYLE FOR CHECKLIST ===== */

/* Phase Section */
.phase-section {
  padding: 16px;
}

/* Phase Header Card */
.phase-header-card {
  display: flex;
  align-items: center;
  gap: 16px;
  background: var(--ion-card-background);
  border: 2px solid var(--ion-color-step-150);
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.phase-header-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: var(--ion-color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.phase-header-icon ion-icon {
  font-size: 2rem;
  color: white;
}

.phase-header-content {
  flex: 1;
}

.phase-header-content h3 {
  margin: 0 0 4px 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--ion-text-color);
}

.phase-header-content p {
  margin: 0;
  font-size: 0.9rem;
  color: var(--ion-color-step-600);
}

.phase-header-badge {
  flex-shrink: 0;
}

.phase-header-badge ion-chip {
  font-weight: 700;
  font-size: 1rem;
}

/* Checklist Items Grid */
.checklist-items-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

/* Observations Card */
.observations-card {
  background: var(--ion-card-background);
  border: 2px solid var(--ion-color-step-150);
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 20px;
}

.card-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--ion-text-color);
  margin-bottom: 12px;
}

.card-label ion-icon {
  font-size: 1.3rem;
  color: var(--ion-color-primary);
}

.observations-input {
  width: 100%;
  border: 2px solid var(--ion-color-step-150);
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 0.95rem;
  color: var(--ion-text-color);
  font-family: inherit;
  resize: vertical;
  transition: all 0.2s ease;
}

.observations-input:focus {
  outline: none;
  border-color: var(--ion-color-primary);
  background: var(--ion-background-color);
}

.observations-input::placeholder {
  color: var(--ion-color-step-500);
}

/* Dashboard Actions (Pause/Interrupt) */
.dashboard-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 20px;
}

.action-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  background: var(--ion-card-background);
  border: 3px solid #e5e7eb;
  border-radius: 16px;
  padding: 20px 16px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-card:active {
  transform: scale(0.95);
}

.action-card.warning {
  border-color: #fbbf24;
}

.action-card.warning:hover {
  background: #fef3c7;
}

.action-card.danger {
  border-color: var(--color-maintenance);
}

.action-card.danger:hover {
  background: #fee2e2;
}

.action-card-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-card-icon ion-icon {
  font-size: 1.8rem;
  color: white;
}

.action-card-icon.warning {
  background: var(--color-occupied);
}

.action-card-icon.danger {
  background: var(--color-maintenance);
}

.action-card-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.action-card-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--ion-text-color);
}

.action-card-subtitle {
  font-size: 0.8rem;
  color: var(--ion-color-step-600);
}

/* Primary Continue Button */
.primary-continue-btn {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 16px;
  background: linear-gradient(135deg, var(--ion-color-success) 0%, var(--ion-color-success-shade) 100%);
  border: none;
  border-radius: 16px;
  padding: 24px 28px;
  margin-bottom: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 6px 20px rgba(var(--ion-color-success-rgb), 0.4);
  position: relative;
  overflow: hidden;
}

/* Subtle pulse animation when enabled */
.primary-continue-btn:not(.disabled) {
  animation: subtle-pulse 2s ease-in-out infinite;
}

@keyframes subtle-pulse {
  0%, 100% {
    box-shadow: 0 6px 20px rgba(var(--ion-color-success-rgb), 0.4);
  }
  50% {
    box-shadow: 0 6px 24px rgba(var(--ion-color-success-rgb), 0.5);
  }
}

.primary-continue-btn:hover:not(.disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(var(--ion-color-success-rgb), 0.5);
}

.primary-continue-btn:active:not(.disabled) {
  transform: scale(0.98);
}

.primary-continue-btn.disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: var(--ion-color-step-500);
  box-shadow: 0 4px 12px rgba(156, 163, 175, 0.3);
  animation: none;
}

.primary-continue-btn > ion-icon:first-child {
  font-size: 2rem;
  color: white;
  flex-shrink: 0;
}

.primary-continue-btn > ion-icon:last-child {
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
}

/* Responsive */
@media (max-width: 480px) {
  .dashboard-actions {
    grid-template-columns: 1fr;
  }
  
  .phase-header-card {
    flex-wrap: wrap;
  }
  
  .phase-header-badge {
    width: 100%;
    display: flex;
    justify-content: center;
    margin-top: 8px;
  }
}
</style>