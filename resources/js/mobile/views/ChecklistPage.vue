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
        <ion-card v-if="!activeChecklist" class="patient-search-card">
          <ion-card-header>
            <ion-card-title>
              <ion-icon :icon="personAddOutline"></ion-icon>
              1. Buscar/Cadastrar Paciente
            </ion-card-title>
          </ion-card-header>
          <ion-card-content>
            <div class="form-group">
              <ion-item fill="outline" class="patient-input">
                <ion-label position="floating">Nome Completo</ion-label>
                <ion-input
                  v-model="patientForm.full_name"
                  type="text"
                  placeholder="Digite o nome completo do paciente"
                  required
                ></ion-input>
              </ion-item>
            </div>

            <div class="form-group">
              <ion-item fill="outline" class="patient-input">
                <ion-label position="floating">Data de Nascimento</ion-label>
                <ion-input
                  v-model="patientForm.birth_date"
                  type="date"
                  required
                ></ion-input>
              </ion-item>
            </div>

            <ion-button expand="block" class="mobile-button" @click="searchPatient" :disabled="!canSearchPatient">
              <ion-icon :icon="searchOutline" slot="start"></ion-icon>
              Buscar Paciente
            </ion-button>

            <!-- Patient found -->
            <div v-if="selectedPatient" class="patient-found">
              <ion-chip color="success">
                <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
                <ion-label>Paciente Selecionado</ion-label>
              </ion-chip>
              <div class="patient-info">
                <h4>{{ selectedPatient.full_name }}</h4>
                <p>Nascimento: {{ formatDate(selectedPatient.birth_date) }}</p>
                <p>Idade: {{ selectedPatient.age }} anos</p>
                <p v-if="selectedPatient.medical_record">Prontuário: {{ selectedPatient.medical_record }}</p>
              </div>
            </div>
          </ion-card-content>
        </ion-card>

        <!-- Machine Selection -->
        <ion-card v-if="selectedPatient && !activeChecklist" class="machine-selection-card">
          <ion-card-header>
            <ion-card-title>
              <ion-icon :icon="hardwareChipOutline"></ion-icon>
              2. Selecionar Máquina e Turno
            </ion-card-title>
          </ion-card-header>
          <ion-card-content>
            <div class="form-group">
              <ion-item fill="outline" class="patient-input">
                <ion-label position="floating">Máquina</ion-label>
                <ion-select v-model="checklistForm.machine_id" placeholder="Selecione uma máquina">
                  <ion-select-option v-for="machine in availableMachines" :key="machine.id" :value="machine.id">
                    {{ machine.name }}
                  </ion-select-option>
                </ion-select>
              </ion-item>
            </div>

            <div class="form-group">
              <ion-item fill="outline" class="patient-input">
                <ion-label position="floating">Turno</ion-label>
                <ion-select v-model="checklistForm.shift" placeholder="Selecione o turno">
                  <ion-select-option value="matutino">Matutino</ion-select-option>
                  <ion-select-option value="vespertino">Vespertino</ion-select-option>
                  <ion-select-option value="noturno">Noturno</ion-select-option>
                </ion-select>
              </ion-item>
            </div>

            <ion-button
              expand="block"
              class="mobile-button primary-button"
              @click="startChecklist"
              :disabled="!canStartChecklist"
            >
              <ion-icon :icon="playOutline" slot="start"></ion-icon>
              Iniciar Checklist de Segurança
            </ion-button>
          </ion-card-content>
        </ion-card>

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
                    rows="3"
                    placeholder="Digite observações sobre esta fase..."
                  ></ion-textarea>
                </ion-item>
              </div>

              <!-- Action Buttons -->
              <div class="action-buttons">
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
                  rows="4"
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
import { useRouter } from 'vue-router';
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
  timeOutline
} from 'ionicons/icons';

import { Container } from '@mobile/core/di/Container';
import ChecklistItem from '@mobile/components/ChecklistItem.vue';
import { Patient, PatientSearchCriteria } from '@mobile/core/domain/entities/Patient';
import { Machine } from '@mobile/core/domain/entities/Machine';
import { AuthService } from '@shared/auth';

const router = useRouter();
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

const patientForm = ref<PatientSearchCriteria>({
  full_name: '',
  birth_date: ''
});

const selectedPatient = ref<Patient | null>(null);
const selectedMachine = ref<Machine | null>(null);
const availableMachines = ref<Machine[]>([]);
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
let timeInterval: number;

// Computed properties
const canSearchPatient = computed(() => {
  return patientForm.value.full_name.length > 0 && patientForm.value.birth_date.length > 0;
});

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
const searchPatient = async () => {
  const loading = await loadingController.create({
    message: 'Buscando paciente...',
    spinner: 'crescent'
  });
  await loading.present();

  try {
    const patient = await searchPatientUseCase.execute(patientForm.value);

    if (patient) {
      selectedPatient.value = patient;

      const toast = await toastController.create({
        message: patient.created ? 'Paciente cadastrado e selecionado!' : 'Paciente encontrado!',
        duration: 2000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
    } else {
      const toast = await toastController.create({
        message: 'Erro ao buscar/cadastrar paciente.',
        duration: 3000,
        color: 'warning',
        position: 'top'
      });
      await toast.present();
    }
  } catch (error: any) {
    const toast = await toastController.create({
      message: error.message || 'Erro ao buscar paciente',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    await loading.dismiss();
  }
};

const startChecklist = async () => {
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
  updateTime();
  timeInterval = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval);
  }
});
</script>

<style scoped>
.checklist-container {
  padding: 1rem;
  max-width: 100%;
}

.time-header-card {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-secondary) 100%);
  color: white;
  margin-bottom: 1rem;
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

.patient-search-card,
.machine-selection-card,
.checklist-phase-card,
.phase-progress-card,
.patient-summary-card,
.interrupted-card,
.completed-card {
  margin-bottom: 1rem;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.patient-input {
  --border-radius: 12px;
  --border-width: 2px;
  --highlight-color-focused: var(--ion-color-primary);
  --highlight-color-invalid: var(--ion-color-danger);
  margin-bottom: 16px;
  transition: all 0.3s ease;
}

.patient-input ion-label {
  --color: var(--ion-color-medium);
  font-weight: 500;
  margin-bottom: 4px;
}

.mobile-button {
  --border-radius: 12px;
  --padding: 16px;
  margin: 8px 0;
  height: 52px;
  font-weight: 600;
  --transition: all 0.2s ease;
}

.primary-button {
  --background: var(--ion-color-primary);
  --color: white;
  font-size: 1.1rem;
}

.patient-found {
  margin-top: 1rem;
  padding: 1rem;
  background: var(--ion-color-success-tint);
  border-radius: 12px;
  border: 2px solid var(--ion-color-success);
}

.patient-info h4 {
  margin: 0.5rem 0 0.25rem 0;
  color: var(--ion-color-dark);
  font-weight: 600;
}

.patient-info p {
  margin: 0.25rem 0;
  color: var(--ion-color-medium);
  font-size: 0.875rem;
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