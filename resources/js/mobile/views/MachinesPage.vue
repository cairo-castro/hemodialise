<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/dashboard"></ion-back-button>
        </ion-buttons>
        <ion-title>Gestão de Máquinas</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="refreshData" :disabled="isLoading">
            <ion-icon :icon="refreshOutline"></ion-icon>
          </ion-button>
          <ion-button @click="openCreateMachineModal">
            <ion-icon :icon="addOutline"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="machines-content">
      <div class="machines-container">
        <!-- Pull to refresh -->
        <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
          <ion-refresher-content></ion-refresher-content>
        </ion-refresher>

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

        <!-- Filter Pills -->
        <div class="filter-pills">
          <button 
            class="filter-pill" 
            :class="{ active: filterType === 'all' }"
            @click="filterType = 'all'"
          >
            <ion-icon :icon="hardwareChipOutline"></ion-icon>
            <span>Todas</span>
            <span class="pill-count">{{ machines.length }}</span>
          </button>
          <button 
            class="filter-pill available" 
            :class="{ active: filterType === 'available' }"
            @click="filterType = 'available'"
          >
            <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
            <span>Disponíveis</span>
            <span class="pill-count">{{ availableCount }}</span>
          </button>
          <button 
            class="filter-pill occupied" 
            :class="{ active: filterType === 'occupied' }"
            @click="filterType = 'occupied'"
          >
            <ion-icon :icon="timeOutline"></ion-icon>
            <span>Em Uso</span>
            <span class="pill-count">{{ occupiedCount }}</span>
          </button>
          <button 
            class="filter-pill maintenance" 
            :class="{ active: filterType === 'maintenance' }"
            @click="filterType = 'maintenance'"
          >
            <ion-icon :icon="constructOutline"></ion-icon>
            <span>Manutenção</span>
            <span class="pill-count">{{ maintenanceCount }}</span>
          </button>
        </div>

        <!-- Stats Summary -->
        <div class="stats-summary">
        <div class="stat-card available" :class="{ 'updating': isStatsRefreshing }">
          <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
          <div class="stat-info">
            <span class="stat-number">{{ availableCount }}</span>
            <span class="stat-label">Disponíveis</span>
          </div>
        </div>
        <div class="stat-card occupied" :class="{ 'updating': isStatsRefreshing }">
          <ion-icon :icon="timeOutline"></ion-icon>
          <div class="stat-info">
            <span class="stat-number">{{ occupiedCount }}</span>
            <span class="stat-label">Em Uso</span>
          </div>
        </div>
        <div class="stat-card maintenance" :class="{ 'updating': isStatsRefreshing }">
          <ion-icon :icon="constructOutline"></ion-icon>
          <div class="stat-info">
            <span class="stat-number">{{ maintenanceCount }}</span>
            <span class="stat-label">Manutenção</span>
          </div>
        </div>
        <div class="stat-card inactive" :class="{ 'updating': isStatsRefreshing }">
          <ion-icon :icon="powerOutline"></ion-icon>
          <div class="stat-info">
            <span class="stat-number">{{ inactiveCount }}</span>
            <span class="stat-label">Desativadas</span>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="loading-container">
        <ion-spinner name="crescent"></ion-spinner>
        <p>Carregando máquinas...</p>
      </div>

      <!-- Machines List -->
      <div v-else class="machines-list">
        <div
          v-for="machine in filteredMachines"
          :key="machine.id"
          class="machine-card-modern"
          :class="getMachineStatusClass(machine)"
          @click="machine.current_checklist && viewChecklist(machine.current_checklist.id)"
        >
          <div class="machine-card-header">
            <div class="machine-icon-large" :class="getMachineStatusClass(machine)">
              <ion-icon :icon="hardwareChipOutline"></ion-icon>
            </div>
            <div class="machine-primary-info">
              <h3 class="machine-name">{{ machine.name }}</h3>
              <p class="machine-identifier" v-if="machine.identifier">{{ machine.identifier }}</p>
            </div>
            <div class="machine-status-badge">
              <div class="status-dot" :class="getMachineStatusClass(machine)"></div>
              <span class="status-text">{{ getStatusLabel(machine) }}</span>
            </div>
          </div>
          
          <div class="machine-description" v-if="machine.description">
            {{ machine.description }}
          </div>

          <!-- Current Session Info (if occupied) -->
          <div v-if="machine.current_checklist" class="session-info-modern">
            <div class="session-tag">
              <ion-icon :icon="personOutline"></ion-icon>
              <span>Sessão Ativa</span>
              <ion-badge v-if="machine.current_checklist.is_paused" color="warning">Pausado</ion-badge>
            </div>
            <div class="session-details-grid">
              <div class="session-detail">
                <span class="detail-label">Paciente</span>
                <span class="detail-value">{{ machine.current_checklist.patient_name }}</span>
              </div>
              <div class="session-detail">
                <span class="detail-label">Fase</span>
                <span class="detail-value">{{ getPhaseLabel(machine.current_checklist.current_phase) }}</span>
              </div>
              <div class="session-detail">
                <span class="detail-label">Início</span>
                <span class="detail-value">{{ formatTime(machine.current_checklist.started_at) }}</span>
              </div>
            </div>
            <div class="session-action">
              <ion-icon :icon="eyeOutline"></ion-icon>
              <span>Clique para ver checklist</span>
              <ion-icon :icon="chevronForwardOutline"></ion-icon>
            </div>
          </div>

          <!-- Action Buttons -->
          <div v-if="!machine.current_checklist" class="machine-actions" @click.stop>
            <!-- Botão de Editar - sempre visível -->
            <ion-button
              fill="clear"
              size="small"
              color="medium"
              @click="openEditMachineModal(machine)"
            >
              <ion-icon slot="start" :icon="createOutline"></ion-icon>
              Editar
            </ion-button>

            <!-- Máquina disponível: pode ir para manutenção -->
            <ion-button
              v-if="machine.is_active && machine.status === 'available'"
              fill="outline"
              size="small"
              color="warning"
              @click="openMaintenanceModal(machine)"
            >
              <ion-icon slot="start" :icon="constructOutline"></ion-icon>
              Manutenção
            </ion-button>

            <!-- Máquina em manutenção: pode liberar -->
            <ion-button
              v-if="machine.is_active && machine.status === 'maintenance'"
              fill="solid"
              size="small"
              color="success"
              @click="releaseMachine(machine)"
            >
              <ion-icon slot="start" :icon="checkmarkCircleOutline"></ion-icon>
              Liberar Máquina
            </ion-button>

            <!-- Toggle Ativar/Desativar -->
            <ion-button
              v-if="machine.status !== 'occupied'"
              fill="outline"
              size="small"
              :color="machine.is_active ? 'danger' : 'success'"
              @click="openToggleActiveModal(machine)"
            >
              <ion-icon slot="start" :icon="machine.is_active ? powerOutline : checkmarkCircleOutline"></ion-icon>
              {{ machine.is_active ? 'Desativar' : 'Ativar' }}
            </ion-button>
          </div>

          <!-- Inactive Badge -->
          <div v-if="!machine.is_active" class="inactive-overlay">
            <ion-icon :icon="powerOutline"></ion-icon>
            <span>Desativada</span>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredMachines.length === 0" class="empty-state-modern">
          <div class="empty-icon">
            <ion-icon :icon="hardwareChipOutline"></ion-icon>
          </div>
          <h3>Nenhuma máquina encontrada</h3>
          <p>{{ getEmptyStateMessage() }}</p>
        </div>
      </div>

      </div>

      <!-- Modal de Criar/Editar Máquina -->
      <ion-modal :is-open="showMachineModal" @didDismiss="closeMachineModal">
        <ion-header>
          <ion-toolbar>
            <ion-title>{{ isEditMode ? 'Editar Máquina' : 'Nova Máquina' }}</ion-title>
            <ion-buttons slot="end">
              <ion-button @click="closeMachineModal">Cancelar</ion-button>
            </ion-buttons>
          </ion-toolbar>
        </ion-header>
        <ion-content class="machine-modal-content">
          <form @submit.prevent="saveMachine" class="machine-form">
            <div class="form-section">
              <h3 class="section-title">Informações Básicas</h3>
              
              <div class="form-group">
                <ion-label position="stacked" class="form-label">
                  <ion-icon :icon="hardwareChipOutline"></ion-icon>
                  Nome da Máquina *
                </ion-label>
                <ion-input
                  v-model="machineForm.name"
                  type="text"
                  placeholder="Ex: Máquina 01"
                  required
                  :disabled="isSaving"
                  class="custom-input"
                ></ion-input>
              </div>

              <div class="form-group">
                <ion-label position="stacked" class="form-label">
                  <ion-icon :icon="barcodeOutline"></ion-icon>
                  Identificador/Código *
                </ion-label>
                <ion-input
                  v-model="machineForm.identifier"
                  type="text"
                  placeholder="Ex: MAQ-001"
                  required
                  :disabled="isSaving"
                  class="custom-input"
                ></ion-input>
              </div>

              <div class="form-group">
                <ion-label position="stacked" class="form-label">
                  <ion-icon :icon="documentTextOutline"></ion-icon>
                  Descrição
                </ion-label>
                <ion-textarea
                  v-model="machineForm.description"
                  placeholder="Informações adicionais sobre a máquina"
                  :rows="4"
                  :disabled="isSaving"
                  class="custom-textarea"
                ></ion-textarea>
              </div>
            </div>

            <div class="form-actions">
              <ion-button
                expand="block"
                type="submit"
                :disabled="isSaving || !isFormValid"
                class="submit-button"
              >
                <ion-icon slot="start" :icon="isSaving ? syncOutline : saveOutline"></ion-icon>
                {{ isSaving ? 'Salvando...' : (isEditMode ? 'Atualizar Máquina' : 'Criar Máquina') }}
              </ion-button>
              
              <ion-button
                v-if="isEditMode"
                expand="block"
                fill="outline"
                color="danger"
                @click="confirmDeleteMachine"
                :disabled="isSaving"
                class="delete-button"
              >
                <ion-icon slot="start" :icon="trashOutline"></ion-icon>
                Excluir Máquina
              </ion-button>
            </div>
          </form>
        </ion-content>
      </ion-modal>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonButtons,
  IonBackButton,
  IonButton,
  IonIcon,
  IonCard,
  IonCardContent,
  IonBadge,
  IonSpinner,
  IonRefresher,
  IonRefresherContent,
  IonSegment,
  IonSegmentButton,
  IonLabel,
  IonFab,
  IonFabButton,
  IonModal,
  IonInput,
  IonTextarea,
  IonSelect,
  IonSelectOption,
  toastController,
  alertController
} from '@ionic/vue';
import {
  refreshOutline,
  medicalOutline,
  checkmarkCircleOutline,
  timeOutline,
  constructOutline,
  powerOutline,
  personOutline,
  eyeOutline,
  calendarOutline,
  hardwareChipOutline,
  chevronForwardOutline,
  addOutline,
  saveOutline,
  syncOutline,
  trashOutline,
  barcodeOutline,
  documentTextOutline,
  locationOutline,
  createOutline
} from 'ionicons/icons';

import { Container } from '../core/di/Container';
import { useStatsAutoRefresh } from '@mobile/composables/useStatsAutoRefresh';

const router = useRouter();
const container = Container.getInstance();

// Repository
const machineRepository = container.getMachineRepository();

// State
const machines = ref<any[]>([]);
const isLoading = ref(false);
const filterType = ref('all');
const currentDate = ref('');
const currentTime = ref('');

// CRUD State
const showMachineModal = ref(false);
const isEditMode = ref(false);
const isSaving = ref(false);
const machineForm = ref({
  id: null as number | null,
  name: '',
  identifier: '',
  description: '',
  unit_id: null as number | null
});

// Update time
const updateTime = () => {
  const now = new Date();
  currentDate.value = now.toLocaleDateString('pt-BR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
  currentTime.value = now.toLocaleTimeString('pt-BR', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Update time every second
setInterval(updateTime, 1000);
updateTime();

// Computed properties
const availableCount = computed(() =>
  machines.value.filter(m => m.status === 'available' && m.is_active !== false).length
);

const occupiedCount = computed(() =>
  machines.value.filter(m => m.status === 'occupied').length
);

const maintenanceCount = computed(() =>
  machines.value.filter(m => m.status === 'maintenance').length
);

const inactiveCount = computed(() =>
  machines.value.filter(m => m.is_active === false).length
);

const filteredMachines = computed(() => {
  switch (filterType.value) {
    case 'available':
      return machines.value.filter(m => m.status === 'available' && m.is_active !== false);
    case 'occupied':
      return machines.value.filter(m => m.status === 'occupied');
    case 'maintenance':
      return machines.value.filter(m => m.status === 'maintenance');
    case 'inactive':
      return machines.value.filter(m => m.is_active === false);
    default:
      return machines.value;
  }
});

// Methods
const loadMachines = async () => {
  isLoading.value = true;
  try {
    const allMachines = await machineRepository.getAll();
    machines.value = allMachines;
  } catch (error: any) {
    console.error('Error loading machines:', error);
    const toast = await toastController.create({
      message: 'Erro ao carregar máquinas',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    isLoading.value = false;
  }
};

const refreshData = async () => {
  await loadMachines();
};

// Auto-refresh dos stats cards
const {
  isRefreshing: isStatsRefreshing,
  forceRefresh: forceStatsRefresh
} = useStatsAutoRefresh(refreshData, {
  loadOnMount: false,
  interval: 15000,
  onStatsUpdated: () => {
    console.log('[Machines] Stats atualizados automaticamente');
  }
});

const handleRefresh = async (event: any) => {
  await forceStatsRefresh();
  event.target.complete();
};

const handleFilterChange = (event: any) => {
  filterType.value = event.detail.value;
};

const getMachineStatusClass = (machine: any) => {
  if (!machine.is_active || machine.is_active === false) return 'inactive';
  return machine.status;
};

const getStatusColor = (machine: any) => {
  if (!machine.is_active || machine.is_active === false) return 'dark';

  switch (machine.status) {
    case 'available':
      return 'success';
    case 'occupied':
      return 'warning';
    case 'reserved':
      return 'primary';
    case 'maintenance':
      return 'danger';
    default:
      return 'medium';
  }
};

const getStatusLabel = (machine: any) => {
  if (!machine.is_active || machine.is_active === false) return 'Desativada';

  switch (machine.status) {
    case 'available':
      return 'Disponível';
    case 'occupied':
      return 'Em Uso';
    case 'reserved':
      return 'Reservada';
    case 'maintenance':
      return 'Manutenção';
    default:
      return machine.status;
  }
};

const getPhaseLabel = (phase: string) => {
  const phases: Record<string, string> = {
    'pre_dialysis': 'Pré-Diálise',
    'during_session': 'Durante Sessão',
    'post_dialysis': 'Pós-Diálise',
    'completed': 'Concluído',
    'interrupted': 'Interrompido'
  };
  return phases[phase] || phase;
};

const formatTime = (dateString: string) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const viewChecklist = (checklistId: number) => {
  router.push(`/checklist/${checklistId}`);
};

// Machine Status Management
const openMaintenanceModal = async (machine: any) => {
  const { alertController } = await import('@ionic/vue');
  
  const alert = await alertController.create({
    header: 'Colocar em Manutenção',
    subHeader: machine.name,
    message: 'Descreva brevemente o motivo da manutenção:',
    inputs: [
      {
        name: 'reason',
        type: 'textarea',
        placeholder: 'Ex: Troca de filtros, ajuste de pressão, etc.'
      }
    ],
    buttons: [
      {
        text: 'Cancelar',
        role: 'cancel'
      },
      {
        text: 'Confirmar',
        handler: (data) => {
          putMachineInMaintenance(machine, data.reason || '');
        }
      }
    ]
  });

  await alert.present();
};

const putMachineInMaintenance = async (machine: any, reason: string) => {
  try {
    const updatedMachine = await machineRepository.updateStatus(machine.id, {
      status: 'maintenance',
      reason
    });

    // Atualizar máquina na lista local
    const index = machines.value.findIndex(m => m.id === machine.id);
    if (index !== -1) {
      machines.value[index] = { ...machines.value[index], ...updatedMachine };
    }

    const toast = await toastController.create({
      message: `${machine.name} colocada em manutenção`,
      duration: 3000,
      color: 'success',
      position: 'top'
    });
    await toast.present();
  } catch (error: any) {
    console.error('Error putting machine in maintenance:', error);
    const toast = await toastController.create({
      message: error.response?.data?.message || 'Erro ao colocar máquina em manutenção',
      duration: 4000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  }
};

const releaseMachine = async (machine: any) => {
  const { alertController } = await import('@ionic/vue');
  
  const alert = await alertController.create({
    header: 'Liberar Máquina',
    subHeader: machine.name,
    message: 'Tem certeza que deseja liberar esta máquina? Ela ficará disponível para uso.',
    buttons: [
      {
        text: 'Cancelar',
        role: 'cancel'
      },
      {
        text: 'Liberar',
        handler: async () => {
          try {
            const updatedMachine = await machineRepository.updateStatus(machine.id, {
              status: 'available'
            });

            // Atualizar máquina na lista local
            const index = machines.value.findIndex(m => m.id === machine.id);
            if (index !== -1) {
              machines.value[index] = { ...machines.value[index], ...updatedMachine };
            }

            const toast = await toastController.create({
              message: `${machine.name} liberada com sucesso`,
              duration: 3000,
              color: 'success',
              position: 'top'
            });
            await toast.present();
          } catch (error: any) {
            console.error('Error releasing machine:', error);
            const toast = await toastController.create({
              message: error.response?.data?.message || 'Erro ao liberar máquina',
              duration: 4000,
              color: 'danger',
              position: 'top'
            });
            await toast.present();
          }
        }
      }
    ]
  });

  await alert.present();
};

const openToggleActiveModal = async (machine: any) => {
  const { alertController } = await import('@ionic/vue');
  
  const isActivating = !machine.is_active;
  
  const alert = await alertController.create({
    header: isActivating ? 'Ativar Máquina' : 'Desativar Máquina',
    subHeader: machine.name,
    message: isActivating
      ? 'A máquina será ativada e ficará disponível para uso.'
      : 'Descreva brevemente o motivo da desativação:',
    inputs: !isActivating ? [
      {
        name: 'reason',
        type: 'textarea',
        placeholder: 'Ex: Equipamento danificado, aguardando reparo, etc.'
      }
    ] : [],
    buttons: [
      {
        text: 'Cancelar',
        role: 'cancel'
      },
      {
        text: 'Confirmar',
        handler: (data) => {
          toggleMachineActive(machine, data?.reason || '');
        }
      }
    ]
  });

  await alert.present();
};

const toggleMachineActive = async (machine: any, reason: string) => {
  try {
    const updatedMachine = await machineRepository.toggleActive(machine.id, { reason });

    // Atualizar máquina na lista local
    const index = machines.value.findIndex(m => m.id === machine.id);
    if (index !== -1) {
      machines.value[index] = { ...machines.value[index], ...updatedMachine };
    }

    const toast = await toastController.create({
      message: updatedMachine.is_active
        ? `${machine.name} ativada com sucesso`
        : `${machine.name} desativada com sucesso`,
      duration: 3000,
      color: 'success',
      position: 'top'
    });
    await toast.present();
  } catch (error: any) {
    console.error('Error toggling machine active:', error);
    const toast = await toastController.create({
      message: error.response?.data?.message || 'Erro ao alterar estado da máquina',
      duration: 4000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  }
};

const getEmptyStateMessage = () => {
  switch (filterType.value) {
    case 'available':
      return 'Nenhuma máquina disponível no momento';
    case 'occupied':
      return 'Nenhuma máquina em uso';
    case 'maintenance':
      return 'Nenhuma máquina em manutenção';
    case 'inactive':
      return 'Nenhuma máquina desativada';
    default:
      return 'Nenhuma máquina cadastrada';
  }
};

// ====================================
// CRUD Methods
// ====================================

// Computed para validação do formulário
const isFormValid = computed(() => {
  return machineForm.value.name.trim() !== '' &&
         machineForm.value.identifier.trim() !== '';
});

// Carregar unidade do usuário
const loadUserUnit = async () => {
  try {
    // Busca a unidade ativa do usuário (current_unit_id para admin/gestor ou unit_id para usuários normais)
    const response = await fetch('/api/user-units/current', {
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    if (!response.ok) {
      console.error('Erro ao carregar unidade');
      throw new Error('Não foi possível carregar a unidade');
    }

    const data = await response.json();
    console.log('Resposta de /api/user-units/current:', data);

    if (data.success && data.unit && data.unit.id) {
      // Unidade configurada corretamente
      machineForm.value.unit_id = data.unit.id;
      console.log('Unit ID configurado:', machineForm.value.unit_id);
    } else {
      // Nenhuma unidade disponível - usuário precisa selecionar no dashboard
      console.error('Nenhuma unidade selecionada');
      const toast = await toastController.create({
        message: 'Por favor, selecione uma unidade no Dashboard antes de continuar',
        duration: 3000,
        color: 'warning',
        position: 'top',
        buttons: [
          {
            text: 'Ir para Dashboard',
            handler: () => {
              router.push('/dashboard');
            }
          }
        ]
      });
      await toast.present();
      closeMachineModal();
    }
  } catch (error) {
    console.error('Erro ao carregar unidade:', error);
    const toast = await toastController.create({
      message: 'Erro ao carregar unidade. Por favor, tente novamente.',
      duration: 2000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  }
};

// Abrir modal para criar máquina
const openCreateMachineModal = async () => {
  isEditMode.value = false;
  machineForm.value = {
    id: null,
    name: '',
    identifier: '',
    description: '',
    unit_id: null
  };

  await loadUserUnit();
  showMachineModal.value = true;
};

// Abrir modal para editar máquina
const openEditMachineModal = async (machine: any) => {
  isEditMode.value = true;
  machineForm.value = {
    id: machine.id,
    name: machine.name,
    identifier: machine.identifier,
    description: machine.description || '',
    unit_id: machine.unit_id
  };

  showMachineModal.value = true;
};

// Fechar modal
const closeMachineModal = () => {
  showMachineModal.value = false;
  machineForm.value = {
    id: null,
    name: '',
    identifier: '',
    description: '',
    unit_id: null
  };
};

// Salvar máquina (criar ou atualizar)
const saveMachine = async () => {
  if (!isFormValid.value) return;

  isSaving.value = true;

  try {
    const machineData = {
      name: machineForm.value.name,
      identifier: machineForm.value.identifier,
      description: machineForm.value.description,
      unit_id: machineForm.value.unit_id
    };

    let data;
    if (isEditMode.value) {
      // Update existing machine
      const result = await machineRepository.update(machineForm.value.id!, machineData);
      data = { success: true, data: result };
    } else {
      // Create new machine
      const result = await machineRepository.create(machineData);
      data = { success: true, data: result };
    }
    
    if (data.success) {
      const toast = await toastController.create({
        message: data.message || (isEditMode.value ? 'Máquina atualizada com sucesso!' : 'Máquina criada com sucesso!'),
        duration: 3000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
      
      closeMachineModal();
      await loadMachines(); // Recarregar lista
    } else {
      throw new Error(data.message || 'Erro ao salvar máquina');
    }
  } catch (error: any) {
    const toast = await toastController.create({
      message: error.message || 'Erro ao salvar máquina. Tente novamente.',
      duration: 4000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    isSaving.value = false;
  }
};

// Confirmar exclusão de máquina
const confirmDeleteMachine = async () => {
  const alert = await alertController.create({
    header: 'Confirmar Exclusão',
    message: `Tem certeza que deseja excluir a máquina <strong>${machineForm.value.name}</strong>? Esta ação irá desativá-la.`,
    buttons: [
      {
        text: 'Cancelar',
        role: 'cancel'
      },
      {
        text: 'Excluir',
        role: 'destructive',
        handler: async () => {
          await deleteMachine();
        }
      }
    ]
  });
  
  await alert.present();
};

// Excluir máquina
const deleteMachine = async () => {
  if (!machineForm.value.id) return;

  isSaving.value = true;

  try {
    await machineRepository.delete(machineForm.value.id);
    const data = { success: true };
    
    if (data.success) {
      const toast = await toastController.create({
        message: data.message || 'Máquina removida com sucesso!',
        duration: 3000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
      
      closeMachineModal();
      await loadMachines(); // Recarregar lista
    } else {
      throw new Error(data.message || 'Erro ao excluir máquina');
    }
  } catch (error: any) {
    const toast = await toastController.create({
      message: error.message || 'Erro ao excluir máquina. Tente novamente.',
      duration: 4000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    isSaving.value = false;
  }
};

// ====================================
// End CRUD Methods
// ====================================

// Lifecycle
onMounted(() => {
  loadMachines();
});
</script>

<style scoped>
/* Content Background */
.machines-content {
  --background: var(--ion-background-color);
}

.machines-container {
  padding-bottom: 24px;
}

/* Time Header Card */
.time-header-card {
  margin: 16px;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
}

.time-header-card ion-card-content {
  padding: 16px 20px;
}

.time-display {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.current-date,
.current-time {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}

.current-date {
  font-size: 0.9rem;
  text-transform: capitalize;
}

.current-time {
  font-size: 1.3rem;
}

.date-icon,
.time-icon {
  font-size: 1.2rem;
}

/* Filter Pills */
.filter-pills {
  display: flex;
  gap: 8px;
  padding: 0 16px 16px 16px;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.filter-pills::-webkit-scrollbar {
  display: none;
}

.filter-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  background: var(--ion-card-background);
  border: 2px solid var(--ion-color-step-150);
  border-radius: 24px;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--ion-color-step-600);
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  flex-shrink: 0;
}

.filter-pill ion-icon {
  font-size: 1.2rem;
}

.pill-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  background: var(--ion-color-step-100);
  border-radius: 11px;
  font-size: 0.75rem;
  font-weight: 700;
}

.filter-pill.active {
  border-color: var(--ion-color-primary);
  background: linear-gradient(135deg, rgba(var(--ion-color-primary-rgb), 0.1) 0%, rgba(var(--ion-color-primary-rgb), 0.05) 100%);
  color: var(--ion-color-primary);
}

.filter-pill.active ion-icon {
  color: var(--ion-color-primary);
}

.filter-pill.active .pill-count {
  background: var(--ion-color-primary);
  color: white;
}

/* Available Pills */
.filter-pill.available.active {
  border-color: var(--color-available);
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
  color: var(--color-available);
}

.filter-pill.available.active ion-icon {
  color: var(--color-available);
}

.filter-pill.available.active .pill-count {
  background: var(--color-available);
}

/* Occupied Pills */
.filter-pill.occupied.active {
  border-color: var(--color-occupied);
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
  color: var(--color-occupied);
}

.filter-pill.occupied.active ion-icon {
  color: var(--color-occupied);
}

.filter-pill.occupied.active .pill-count {
  background: var(--color-occupied);
}

/* Maintenance Pills */
.filter-pill.maintenance.active {
  border-color: var(--color-maintenance);
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
  color: var(--color-maintenance);
}

.filter-pill.maintenance.active ion-icon {
  color: var(--color-maintenance);
}

.filter-pill.maintenance.active .pill-count {
  background: var(--color-maintenance);
}

/* Stats Summary */
.stats-summary {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  padding: 0 16px 16px 16px;
}

.stat-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  background: var(--ion-card-background);
  padding: 20px 16px;
  border-radius: 16px;
  box-shadow: var(--shadow-card);
  transition: all 0.3s ease;
  border-top: 4px solid transparent;
  text-align: center;
}

.stat-card:active {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.stat-card ion-icon {
  font-size: 2.8rem;
  flex-shrink: 0;
}

/* Available - Green */
.stat-card.available {
  border-top-color: var(--color-available);
  background: linear-gradient(180deg, rgba(16, 185, 129, 0.05) 0%, var(--ion-card-background) 100%);
}

.stat-card.available ion-icon {
  color: var(--color-available);
}

/* Occupied - Orange */
.stat-card.occupied {
  border-top-color: var(--color-occupied);
  background: linear-gradient(180deg, rgba(245, 158, 11, 0.05) 0%, var(--ion-card-background) 100%);
}

.stat-card.occupied ion-icon {
  color: var(--color-occupied);
}

/* Maintenance - Red */
.stat-card.maintenance {
  border-top-color: var(--color-maintenance);
  background: linear-gradient(180deg, rgba(239, 68, 68, 0.05) 0%, var(--ion-card-background) 100%);
}

.stat-card.maintenance ion-icon {
  color: var(--color-maintenance);
}

/* Inactive - Gray */
.stat-card.inactive {
  border-top-color: var(--color-inactive);
  background: linear-gradient(180deg, rgba(107, 114, 128, 0.05) 0%, var(--ion-card-background) 100%);
}

.stat-card.inactive ion-icon {
  color: var(--color-inactive);
}

.stat-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-card .stat-number {
  font-size: 2rem;
  font-weight: 800;
  color: var(--ion-text-color);
  line-height: 1;
}

.stat-card .stat-label {
  font-size: 0.8rem;
  color: var(--ion-color-step-600);
  font-weight: 600;
  line-height: 1.2;
}

/* Animação de atualização dos stats */
.stat-card.updating {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(0.98);
  }
}

/* Loading */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: var(--ion-color-step-500);
}

.loading-container ion-spinner {
  margin-bottom: 1rem;
}

/* Machines List */
.machines-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 0 16px 24px 16px;
}

/* Modern Machine Card */
.machine-card-modern {
  position: relative;
  background: var(--ion-card-background);
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  border-left: 4px solid #e5e7eb;
  overflow: hidden;
}

.machine-card-modern:active {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.machine-card-modern.available {
  border-left-color: var(--color-available);
}

.machine-card-modern.occupied {
  border-left-color: var(--color-occupied);
  cursor: pointer;
}

.machine-card-modern.maintenance {
  border-left-color: var(--color-maintenance);
}

.machine-card-modern.inactive {
  border-left-color: var(--ion-color-step-600);
  opacity: 0.6;
}

/* Card Header */
.machine-card-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 12px;
}

.machine-icon-large {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.machine-icon-large ion-icon {
  font-size: 2rem;
  color: white;
}

.machine-icon-large.available {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.machine-icon-large.occupied {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.machine-icon-large.maintenance {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.machine-icon-large.inactive {
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
}

.machine-primary-info {
  flex: 1;
}

.machine-name {
  margin: 0 0 4px 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--ion-text-color);
}

.machine-identifier {
  margin: 0;
  font-size: 0.8rem;
  color: var(--ion-color-step-500);
  font-family: 'Courier New', monospace;
  font-weight: 600;
}

.machine-status-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: var(--ion-background-color);
  border-radius: 20px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-dot.available {
  background: var(--color-available);
  box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);
  animation: pulse 2s infinite;
}

.status-dot.occupied {
  background: var(--color-occupied);
  box-shadow: 0 0 8px rgba(245, 158, 11, 0.6);
}

.status-dot.maintenance {
  background: var(--color-maintenance);
  box-shadow: 0 0 8px rgba(239, 68, 68, 0.6);
}

.status-dot.inactive {
  background: var(--color-inactive);
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.status-text {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--ion-color-step-600);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.machine-description {
  font-size: 0.85rem;
  color: var(--ion-color-step-600);
  line-height: 1.4;
  margin-bottom: 12px;
}

/* Session Info Modern */
.session-info-modern {
  background: rgba(var(--ion-color-primary-rgb), 0.1);
  border: 2px solid rgba(var(--ion-color-primary-rgb), 0.3);
  border-radius: 12px;
  padding: 16px;
  margin-top: 12px;
}

.session-tag {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  font-weight: 700;
  font-size: 0.9rem;
  color: var(--ion-color-primary-shade);
}

.session-tag ion-icon {
  font-size: 1.2rem;
  color: var(--ion-color-primary);
}

.session-details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-bottom: 12px;
}

.session-detail {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-label {
  font-size: 0.7rem;
  color: var(--ion-color-step-600);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-value {
  font-size: 0.9rem;
  color: var(--ion-text-color);
  font-weight: 700;
}

.session-action {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  background: var(--ion-card-background);
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--ion-color-primary);
}

.session-action ion-icon:first-child {
  font-size: 1.1rem;
}

.session-action ion-icon:last-child {
  font-size: 1rem;
  color: var(--ion-color-step-500);
}

/* Machine Actions */
.machine-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.machine-actions ion-button {
  --padding-start: 12px;
  --padding-end: 12px;
  height: 36px;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.3px;
}

.machine-actions ion-button ion-icon {
  font-size: 1.1rem;
}

/* Inactive Overlay */
.inactive-overlay {
  position: absolute;
  top: 12px;
  right: 12px;
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: rgba(107, 114, 128, 0.95);
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  color: white;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.inactive-overlay ion-icon {
  font-size: 1rem;
}

/* Empty State Modern */
.empty-state-modern {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 4rem 2rem;
  text-align: center;
}

.empty-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: var(--ion-color-step-100);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.empty-icon ion-icon {
  font-size: 2.5rem;
  color: var(--ion-color-step-500);
}

.empty-state-modern h3 {
  margin: 0 0 0.75rem 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--ion-text-color);
}

.empty-state-modern p {
  margin: 0;
  font-size: 0.9rem;
  color: var(--ion-color-step-600);
  line-height: 1.5;
}

/* ====================================
   CRUD Modal Styles
   ==================================== */

/* Modal Content */
.machine-modal-content {
  --background: var(--ion-background-color);
}

/* Machine Form */
.machine-form {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-section {
  background: var(--ion-card-background);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.section-title {
  margin: 0 0 1.25rem 0;
  font-size: 1rem;
  font-weight: 700;
  color: var(--ion-text-color);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--ion-text-color);
  margin-bottom: 0.5rem;
}

.form-label ion-icon {
  font-size: 1.1rem;
  color: var(--ion-color-primary);
}

.custom-input,
.custom-textarea,
.custom-select {
  --background: var(--ion-background-color);
  --border-color: #e5e7eb;
  --border-width: 2px;
  --border-style: solid;
  --border-radius: 12px;
  --padding-start: 1rem;
  --padding-end: 1rem;
  --padding-top: 0.875rem;
  --padding-bottom: 0.875rem;
  font-size: 1rem;
  color: var(--ion-text-color);
}

.custom-input:focus-within,
.custom-textarea:focus-within,
.custom-select:focus-within {
  --border-color: var(--ion-color-primary);
  --background: var(--ion-card-background);
}

.custom-textarea {
  --padding-top: 1rem;
  --padding-bottom: 1rem;
}

/* Form Actions */
.form-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.submit-button {
  --background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  --box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
  font-weight: 700;
  text-transform: none;
  letter-spacing: 0.3px;
  height: 48px;
  margin: 0;
}

.delete-button {
  height: 44px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.3px;
  margin: 0;
}

.submit-button ion-icon,
.delete-button ion-icon {
  font-size: 1.25rem;
}

/* FAB Button */
ion-fab-button {
  --background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  --box-shadow: 0 4px 16px rgba(59, 130, 246, 0.4);
}

ion-fab-button::part(native) {
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.4);
}

ion-fab-button ion-icon {
  font-size: 1.75rem;
}
</style>
