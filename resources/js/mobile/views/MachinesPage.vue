<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/dashboard"></ion-back-button>
        </ion-buttons>
        <ion-title>Máquinas</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="refreshData" :disabled="isLoading">
            <ion-icon :icon="refreshOutline"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>

      <!-- Filter Tabs -->
      <ion-toolbar>
        <ion-segment v-model="filterType" @ionChange="handleFilterChange">
          <ion-segment-button value="all">
            <ion-label>Todas ({{ machines.length }})</ion-label>
          </ion-segment-button>
          <ion-segment-button value="available">
            <ion-label>Disponíveis ({{ availableCount }})</ion-label>
          </ion-segment-button>
          <ion-segment-button value="occupied">
            <ion-label>Em Uso ({{ occupiedCount }})</ion-label>
          </ion-segment-button>
          <ion-segment-button value="maintenance">
            <ion-label>Manutenção ({{ maintenanceCount }})</ion-label>
          </ion-segment-button>
        </ion-segment>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <!-- Pull to refresh -->
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <!-- Stats Summary -->
      <div class="stats-summary">
        <div class="stat-card available">
          <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
          <div class="stat-info">
            <span class="stat-number">{{ availableCount }}</span>
            <span class="stat-label">Disponíveis</span>
          </div>
        </div>
        <div class="stat-card occupied">
          <ion-icon :icon="timeOutline"></ion-icon>
          <div class="stat-info">
            <span class="stat-number">{{ occupiedCount }}</span>
            <span class="stat-label">Em Uso</span>
          </div>
        </div>
        <div class="stat-card maintenance">
          <ion-icon :icon="constructOutline"></ion-icon>
          <div class="stat-info">
            <span class="stat-number">{{ maintenanceCount }}</span>
            <span class="stat-label">Manutenção</span>
          </div>
        </div>
        <div class="stat-card inactive">
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
      <div v-else class="machines-container">
        <ion-card
          v-for="machine in filteredMachines"
          :key="machine.id"
          class="machine-card"
          :class="getMachineStatusClass(machine)"
        >
          <ion-card-content>
            <div class="machine-header">
              <div class="machine-info">
                <div class="machine-icon" :class="getMachineStatusClass(machine)">
                  <ion-icon :icon="medicalOutline"></ion-icon>
                </div>
                <div class="machine-details">
                  <h3>{{ machine.name }}</h3>
                  <p class="machine-id">{{ machine.identifier }}</p>
                  <p class="machine-description">{{ machine.description }}</p>
                </div>
              </div>
              <div class="machine-status">
                <ion-badge :color="getStatusColor(machine)">
                  {{ getStatusLabel(machine) }}
                </ion-badge>
                <ion-badge v-if="!machine.is_active" color="dark" class="inactive-badge">
                  Desativada
                </ion-badge>
              </div>
            </div>

            <!-- Current Session Info (if occupied) -->
            <div v-if="machine.current_checklist" class="session-info">
              <div class="session-header">
                <ion-icon :icon="personOutline"></ion-icon>
                <span>Sessão Ativa</span>
              </div>
              <div class="session-details">
                <div class="session-item">
                  <span class="label">Paciente:</span>
                  <span class="value">{{ machine.current_checklist.patient_name }}</span>
                </div>
                <div class="session-item">
                  <span class="label">Fase:</span>
                  <span class="value">{{ getPhaseLabel(machine.current_checklist.current_phase) }}</span>
                </div>
                <div class="session-item">
                  <span class="label">Início:</span>
                  <span class="value">{{ formatTime(machine.current_checklist.started_at) }}</span>
                </div>
                <div class="session-item" v-if="machine.current_checklist.is_paused">
                  <ion-badge color="warning">Pausado</ion-badge>
                </div>
              </div>
            </div>

            <!-- Action Button -->
            <ion-button
              v-if="machine.current_checklist"
              expand="block"
              :color="machine.current_checklist.is_paused ? 'warning' : 'primary'"
              @click="viewChecklist(machine.current_checklist.id)"
              class="action-button"
            >
              <ion-icon :icon="eyeOutline" slot="start"></ion-icon>
              Ver Checklist
            </ion-button>
          </ion-card-content>
        </ion-card>

        <!-- Empty State -->
        <div v-if="filteredMachines.length === 0" class="empty-state">
          <ion-icon :icon="medicalOutline"></ion-icon>
          <h3>Nenhuma máquina encontrada</h3>
          <p>{{ getEmptyStateMessage() }}</p>
        </div>
      </div>
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
  toastController
} from '@ionic/vue';
import {
  refreshOutline,
  medicalOutline,
  checkmarkCircleOutline,
  timeOutline,
  constructOutline,
  powerOutline,
  personOutline,
  eyeOutline
} from 'ionicons/icons';

import { Container } from '@mobile/core/di/Container';

const router = useRouter();
const container = Container.getInstance();

// Repository
const machineRepository = container.getMachineRepository();

// State
const machines = ref<any[]>([]);
const isLoading = ref(false);
const filterType = ref('all');

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

const handleRefresh = async (event: any) => {
  await refreshData();
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

// Lifecycle
onMounted(() => {
  loadMachines();
});
</script>

<style scoped>
.stats-summary {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  padding: 1.5rem;
  background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: white;
  padding: 1.25rem;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-left: 4px solid transparent;
}

.stat-card:active {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.stat-card ion-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

/* Available - Green */
.stat-card.available {
  border-left-color: #10b981;
}

.stat-card.available ion-icon {
  color: #10b981;
}

/* Occupied - Yellow/Orange */
.stat-card.occupied {
  border-left-color: #f59e0b;
}

.stat-card.occupied ion-icon {
  color: #f59e0b;
}

/* Maintenance - Red */
.stat-card.maintenance {
  border-left-color: #ef4444;
}

.stat-card.maintenance ion-icon {
  color: #ef4444;
}

/* Inactive - Gray */
.stat-card.inactive {
  border-left-color: #6b7280;
}

.stat-card.inactive ion-icon {
  color: #6b7280;
}

.stat-info {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.stat-card .stat-number {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stat-card .stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  line-height: 1.2;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: var(--ion-color-medium);
}

.loading-container ion-spinner {
  margin-bottom: 1rem;
}

.machines-container {
  padding: 1rem;
}

.machine-card {
  margin-bottom: 1rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.machine-card.available {
  border-left: 4px solid var(--ion-color-success);
}

.machine-card.occupied {
  border-left: 4px solid var(--ion-color-warning);
}

.machine-card.maintenance {
  border-left: 4px solid var(--ion-color-danger);
}

.machine-card.inactive {
  border-left: 4px solid var(--ion-color-dark);
  opacity: 0.7;
}

.machine-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.machine-info {
  display: flex;
  gap: 1rem;
  flex: 1;
}

.machine-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.machine-icon ion-icon {
  font-size: 1.5rem;
  color: white;
}

.machine-icon.available {
  background: var(--ion-color-success);
}

.machine-icon.occupied {
  background: var(--ion-color-warning);
}

.machine-icon.maintenance {
  background: var(--ion-color-danger);
}

.machine-icon.inactive {
  background: var(--ion-color-dark);
}

.machine-details {
  flex: 1;
}

.machine-details h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--ion-color-dark);
}

.machine-id {
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
  color: var(--ion-color-medium);
  font-family: monospace;
}

.machine-description {
  margin: 0;
  font-size: 0.8rem;
  color: var(--ion-color-medium);
}

.machine-status {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  align-items: flex-end;
}

.inactive-badge {
  font-size: 0.7rem;
}

.session-info {
  background: var(--ion-color-light);
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.session-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  font-weight: 600;
  color: var(--ion-color-dark);
}

.session-header ion-icon {
  font-size: 1.2rem;
  color: var(--ion-color-primary);
}

.session-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.session-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
}

.session-item .label {
  color: var(--ion-color-medium);
  font-weight: 500;
}

.session-item .value {
  color: var(--ion-color-dark);
  font-weight: 600;
}

.action-button {
  margin-top: 0.5rem;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--ion-color-medium);
}

.empty-state ion-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
  color: var(--ion-color-dark);
}

.empty-state p {
  margin: 0;
  font-size: 0.875rem;
}
</style>
