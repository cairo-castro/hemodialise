<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/dashboard"></ion-back-button>
        </ion-buttons>
        <ion-title>Checklists Ativos</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="refreshData" :disabled="isLoading">
            <ion-icon :icon="refreshOutline"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <!-- Pull to refresh -->
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <!-- Header com gradient similar à imagem -->
      <div class="header-gradient">
        <div class="header-content">
          <div class="unit-info" v-if="user">
            <h2>{{ user.unit?.name || 'Unidade de Hemodiálise' }}</h2>
            <p>{{ activeChecklists.length }} checklist{{ activeChecklists.length !== 1 ? 's' : '' }}
               {{ activeChecklists.length === 1 ? 'ativo' : 'ativos' }}</p>
          </div>

          <!-- Status indicators similar to Location 1/2 in image -->
          <div class="status-indicators">
            <div class="status-item available" :class="{ 'updating': isStatsRefreshing }">
              <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
              <span>{{ availableMachinesCount }} Disponível{{ availableMachinesCount !== 1 ? 'is' : '' }}</span>
            </div>
            <div class="status-item occupied" :class="{ 'updating': isStatsRefreshing }">
              <ion-icon :icon="timeOutline"></ion-icon>
              <span>{{ occupiedMachinesCount }} Em Uso</span>
            </div>
          </div>
        </div>
      </div>

      <div class="content-container">
        <!-- Active Checklists List -->
        <div class="checklists-section" v-if="activeChecklists.length > 0">
          <div
            v-for="checklist in activeChecklists"
            :key="checklist.id"
            class="checklist-card"
            @click="continueChecklist(checklist)"
          >
            <div class="card-content">
              <!-- Left side - Machine and patient info -->
              <div class="checklist-info">
                <div class="machine-section">
                  <div class="machine-icon">
                    <ion-icon :icon="medicalOutline"></ion-icon>
                  </div>
                  <div class="machine-details">
                    <h3>{{ checklist.machine.name }}</h3>
                    <p class="machine-id">{{ checklist.machine.identifier }}</p>
                  </div>
                </div>

                <div class="patient-section">
                  <div class="patient-icon">
                    <ion-icon :icon="personOutline"></ion-icon>
                  </div>
                  <div class="patient-details">
                    <h4>{{ checklist.patient.name }}</h4>
                    <p class="phase-info">{{ getPhaseTitle(checklist.current_phase) }}</p>
                  </div>
                </div>
              </div>

              <!-- Right side - Status and actions -->
              <div class="checklist-actions">
                <div class="status-section">
                  <div class="progress-circle" :class="getPhaseClass(checklist.current_phase)">
                    <span class="progress-text">{{ getProgressPercentage(checklist) }}%</span>
                  </div>
                  <div class="time-info">
                    <span class="time-label">{{ getTimeElapsed(checklist.created_at) }}</span>
                  </div>
                </div>

                <!-- Action button similar to "BUY TICKET" in image -->
                <ion-button
                  :color="getPhaseColor(checklist.current_phase)"
                  size="small"
                  class="action-button"
                  @click.stop="continueChecklist(checklist)"
                >
                  {{ checklist.paused_at ? 'RETOMAR' : 'CONTINUAR' }}
                </ion-button>

                <!-- Pause button if not paused -->
                <ion-button
                  v-if="!checklist.paused_at"
                  fill="clear"
                  size="small"
                  color="medium"
                  @click.stop="pauseChecklist(checklist)"
                  class="pause-btn"
                >
                  <ion-icon :icon="pauseOutline" slot="icon-only"></ion-icon>
                </ion-button>
              </div>
            </div>

            <!-- Paused indicator -->
            <div v-if="checklist.paused_at" class="paused-indicator">
              <ion-icon :icon="pauseCircleOutline"></ion-icon>
              <span>Pausado há {{ getTimePaused(checklist.paused_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Empty state when no active checklists -->
        <div class="empty-state" v-if="activeChecklists.length === 0 && !isLoading">
          <div class="empty-content">
            <ion-icon :icon="clipboardOutline" class="empty-icon"></ion-icon>
            <h3>Nenhum checklist ativo</h3>
            <p>Inicie um novo checklist de segurança para começar.</p>
            <ion-button
              color="primary"
              @click="startNewChecklist"
              class="start-button"
            >
              <ion-icon :icon="addOutline" slot="start"></ion-icon>
              Iniciar Checklist
            </ion-button>
          </div>
        </div>

        <!-- Loading state -->
        <div class="loading-state" v-if="isLoading">
          <ion-spinner name="crescent"></ion-spinner>
          <p>Carregando checklists...</p>
        </div>
      </div>

      <!-- Floating Action Button (similar to + in image) -->
      <ion-fab vertical="bottom" horizontal="end" slot="fixed">
        <ion-fab-button
          color="primary"
          @click="startNewChecklist"
          class="add-fab"
        >
          <ion-icon :icon="addOutline"></ion-icon>
        </ion-fab-button>
      </ion-fab>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonButton,
  IonButtons,
  IonBackButton,
  IonIcon,
  IonSpinner,
  IonRefresher,
  IonRefresherContent,
  IonFab,
  IonFabButton,
  loadingController,
  alertController,
  toastController
} from '@ionic/vue';
import {
  refreshOutline,
  checkmarkCircleOutline,
  timeOutline,
  medicalOutline,
  personOutline,
  pauseOutline,
  pauseCircleOutline,
  clipboardOutline,
  addOutline
} from 'ionicons/icons';

import { Container } from '@mobile/core/di/Container';
import { User } from '@mobile/core/domain/entities/User';
import { useStatsAutoRefresh } from '@mobile/composables/useStatsAutoRefresh';
import { AuthService } from '@shared/auth';

const router = useRouter();
const container = Container.getInstance();
const getCurrentUserUseCase = container.getCurrentUserUseCase();

interface Checklist {
  id: number;
  current_phase: string;
  machine: {
    id: number;
    name: string;
    identifier: string;
  };
  patient: {
    id: number;
    name: string;
  };
  created_at: string;
  paused_at?: string;
  phase_completion?: number;
}

interface Machine {
  id: number;
  name: string;
  identifier: string;
  status: string;
}

// Reactive state
const isLoading = ref(false);
const user = ref<User | null>(null);
const activeChecklists = ref<Checklist[]>([]);
const machines = ref<Machine[]>([]);

// Computed properties
const availableMachinesCount = computed(() =>
  machines.value.filter(m => m.status === 'available').length
);

const occupiedMachinesCount = computed(() =>
  machines.value.filter(m => m.status === 'occupied').length
);

// Methods
const loadUserData = async () => {
  try {
    user.value = await getCurrentUserUseCase.execute();
  } catch (error) {
    console.error('Error loading user:', error);
  }
};

const loadActiveChecklists = async () => {
  try {
    const response = await fetch('/api/checklists/active', AuthService.getFetchConfig());

    const data = await response.json();
    if (data.success) {
      activeChecklists.value = data.checklists;
    }
  } catch (error) {
    console.error('Erro ao carregar checklists ativos:', error);
  }
};

const loadMachines = async () => {
  try {
    const response = await fetch('/api/machines', AuthService.getFetchConfig());

    if (!response.ok) {
      console.warn(`Failed to load machines: ${response.status}`);
      // Don't throw error, just set empty array
      machines.value = [];
      return;
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
  }
};

const refreshData = async () => {
  isLoading.value = true;
  try {
    await Promise.all([
      loadActiveChecklists(),
      loadMachines()
    ]);
  } finally {
    isLoading.value = false;
  }
};

// Auto-refresh dos stats - atualiza automaticamente quando há mudanças
const {
  isRefreshing: isStatsRefreshing,
  forceRefresh: forceStatsRefresh
} = useStatsAutoRefresh(refreshData, {
  loadOnMount: false, // Carregaremos manualmente no onMounted
  interval: 15000,
  onStatsUpdated: () => {
    console.log('[ChecklistList] Stats atualizados automaticamente');
  }
});

const handleRefresh = async (event: any) => {
  await forceStatsRefresh();
  event.target.complete();
};

const continueChecklist = (checklist: Checklist) => {
  router.push(`/checklist/${checklist.id}`);
};

const pauseChecklist = async (checklist: Checklist) => {
  const alert = await alertController.create({
    header: 'Pausar Checklist',
    message: `Deseja pausar o checklist da máquina ${checklist.machine.name}?`,
    buttons: [
      {
        text: 'Cancelar',
        role: 'cancel'
      },
      {
        text: 'Pausar',
        handler: async () => {
          const loading = await loadingController.create({
            message: 'Pausando checklist...'
          });
          await loading.present();

          try {
            const response = await fetch(`/api/checklists/${checklist.id}/pause`, {
              method: 'POST',
              ...AuthService.getFetchConfig()
            });

            const data = await response.json();
            if (data.success) {
              const toast = await toastController.create({
                message: 'Checklist pausado com sucesso.',
                duration: 3000,
                color: 'success'
              });
              await toast.present();
              await refreshData();
            } else {
              throw new Error(data.message);
            }
          } catch (error) {
            const toast = await toastController.create({
              message: 'Erro ao pausar checklist.',
              duration: 3000,
              color: 'danger'
            });
            await toast.present();
          } finally {
            await loading.dismiss();
          }
        }
      }
    ]
  });

  await alert.present();
};

const startNewChecklist = () => {
  router.push('/checklist/new');
};

// Helper functions
const getPhaseTitle = (phase: string) => {
  const phases = {
    'pre_dialysis': 'Pré-Diálise',
    'during_session': 'Durante Sessão',
    'post_dialysis': 'Pós-Diálise',
    'completed': 'Concluído',
    'interrupted': 'Interrompido'
  };
  return phases[phase] || phase;
};

const getPhaseColor = (phase: string) => {
  const colors = {
    'pre_dialysis': 'primary',
    'during_session': 'warning',
    'post_dialysis': 'secondary',
    'completed': 'success',
    'interrupted': 'danger'
  };
  return colors[phase] || 'medium';
};

const getPhaseClass = (phase: string) => {
  return phase.replace('_', '-');
};

const getProgressPercentage = (checklist: Checklist) => {
  if (checklist.phase_completion !== undefined) {
    return Math.round(checklist.phase_completion);
  }

  // Fallback calculation based on phase
  const phaseProgress = {
    'pre_dialysis': 25,
    'during_session': 50,
    'post_dialysis': 75,
    'completed': 100,
    'interrupted': 0
  };
  return phaseProgress[checklist.current_phase] || 0;
};

const getTimeElapsed = (startTime: string) => {
  const start = new Date(startTime);
  const now = new Date();
  const diff = now.getTime() - start.getTime();

  const hours = Math.floor(diff / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

  if (hours > 0) {
    return `${hours}h ${minutes}m`;
  }
  return `${minutes}m`;
};

const getTimePaused = (pauseTime: string) => {
  const pause = new Date(pauseTime);
  const now = new Date();
  const diff = now.getTime() - pause.getTime();

  const hours = Math.floor(diff / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

  if (hours > 0) {
    return `${hours}h ${minutes}m`;
  }
  return `${minutes}m`;
};

// Lifecycle
onMounted(async () => {
  await loadUserData();
  await refreshData();
});
</script>

<style scoped>
.header-gradient {
  background: var(--ion-card-background);
  padding: 24px 20px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border-bottom: 2px solid var(--ion-color-step-150);
}

.header-content {
  position: relative;
  z-index: 2;
}

.unit-info h2 {
  margin: 0 0 8px 0;
  font-size: 24px;
  font-weight: 700;
  color: var(--ion-text-color);
}

.unit-info p {
  margin: 0;
  font-size: 16px;
  color: var(--ion-color-step-600);
  font-weight: 500;
}

.status-indicators {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-top: 16px;
}

.status-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: var(--ion-card-background);
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-left: 4px solid transparent;
}

.status-item:active {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.status-item ion-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

.status-item span {
  color: var(--ion-text-color);
  font-size: 0.75rem;
  line-height: 1.2;
  font-weight: 500;
  flex: 1;
}

.status-item.available {
  border-left-color: var(--color-available);
}

.status-item.available ion-icon {
  color: var(--color-available);
}

.status-item.occupied {
  border-left-color: var(--color-occupied);
}

.status-item.occupied ion-icon {
  color: var(--color-occupied);
}

/* Animação de atualização */
.status-item.updating {
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

.content-container {
  padding: 0 16px 100px; /* Bottom padding for FAB */
}

.checklists-section {
  margin-top: 16px;
}

.checklist-card {
  background: var(--ion-card-background);
  border-radius: 16px;
  margin-bottom: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.checklist-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.card-content {
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.checklist-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.machine-section {
  display: flex;
  align-items: center;
  gap: 12px;
}

.machine-icon {
  width: 40px;
  height: 40px;
  background: rgba(var(--ion-color-primary-rgb), 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.machine-icon ion-icon {
  font-size: 20px;
  color: var(--ion-color-primary);
}

.machine-details h3 {
  margin: 0 0 4px 0;
  font-size: 18px;
  font-weight: 600;
  color: var(--ion-text-color);
}

.machine-id {
  margin: 0;
  font-size: 14px;
  color: var(--ion-color-step-600);
  font-family: 'Courier New', monospace;
}

.patient-section {
  display: flex;
  align-items: center;
  gap: 12px;
}

.patient-icon {
  width: 32px;
  height: 32px;
  background: rgba(var(--ion-color-success-rgb), 0.1);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.patient-icon ion-icon {
  font-size: 16px;
  color: var(--ion-color-success);
}

.patient-details h4 {
  margin: 0 0 4px 0;
  font-size: 16px;
  font-weight: 500;
  color: var(--ion-text-color);
}

.phase-info {
  margin: 0;
  font-size: 12px;
  color: var(--ion-color-step-600);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.checklist-actions {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  min-width: 100px;
}

.status-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.progress-circle {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 12px;
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.progress-circle.pre-dialysis {
  background: var(--ion-color-primary);
}

.progress-circle.during-session {
  background: var(--ion-color-warning);
}

.progress-circle.post-dialysis {
  background: var(--ion-color-secondary);
}

.progress-text {
  font-size: 11px;
  font-weight: 600;
}

.time-info {
  text-align: center;
}

.time-label {
  font-size: 12px;
  color: var(--ion-color-step-600);
  font-weight: 500;
}

.action-button {
  --padding-start: 16px;
  --padding-end: 16px;
  --height: 32px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.5px;
  min-width: 80px;
}

.pause-btn {
  --padding-start: 8px;
  --padding-end: 8px;
  --height: 32px;
  min-width: 32px;
}

.paused-indicator {
  background: rgba(var(--ion-color-warning-rgb), 0.1);
  border-top: 1px solid rgba(var(--ion-color-warning-rgb), 0.3);
  padding: 8px 20px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: var(--ion-color-warning);
}

.paused-indicator ion-icon {
  font-size: 14px;
}

.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  padding: 40px 20px;
}

.empty-content {
  text-align: center;
  max-width: 280px;
}

.empty-icon {
  font-size: 64px;
  color: var(--ion-color-step-300);
  margin-bottom: 16px;
}

.empty-content h3 {
  margin: 0 0 8px 0;
  font-size: 20px;
  color: var(--ion-text-color);
}

.empty-content p {
  margin: 0 0 24px 0;
  font-size: 16px;
  color: var(--ion-color-step-600);
  line-height: 1.5;
}

.start-button {
  --border-radius: 12px;
  height: 48px;
  font-weight: 600;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: var(--ion-color-step-600);
}

.loading-state p {
  margin-top: 16px;
  font-size: 14px;
}

.add-fab {
  --box-shadow: 0 8px 24px rgba(0, 123, 255, 0.3);
}

/* Responsive adjustments */
@media (max-width: 480px) {
  .header-gradient {
    padding: 20px 16px;
  }

  .status-indicators {
    flex-direction: column;
    gap: 12px;
  }

  .card-content {
    padding: 16px;
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }

  .checklist-actions {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    min-width: auto;
  }

  .status-section {
    flex-direction: row;
    gap: 12px;
  }

  .progress-circle {
    width: 40px;
    height: 40px;
    font-size: 10px;
  }
}
</style>