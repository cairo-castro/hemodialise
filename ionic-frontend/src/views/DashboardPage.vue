<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-title>Dashboard</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="handleLogout">
            <ion-icon :icon="logOutOutline" slot="icon-only"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <!-- Refresher -->
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <!-- User welcome header -->
      <div class="welcome-header" v-if="user">
        <div class="user-info">
          <h2>Olá, {{ user.name }}!</h2>
          <p>{{ user.unit?.name || 'Sem unidade definida' }}</p>
        </div>
        <div class="user-avatar">
          <ion-icon :icon="personCircleOutline"></ion-icon>
        </div>
      </div>

      <!-- Quick stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon success">
            <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
          </div>
          <div class="stat-content">
            <h3>{{ todaysChecklists.length }}</h3>
            <p>Checklists Hoje</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon warning">
            <ion-icon :icon="timeOutline"></ion-icon>
          </div>
          <div class="stat-content">
            <h3>{{ pendingChecklists.length }}</h3>
            <p>Pendentes</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon primary">
            <ion-icon :icon="hardwareChipOutline"></ion-icon>
          </div>
          <div class="stat-content">
            <h3>{{ availableMachines.length }}</h3>
            <p>Máquinas Ativas</p>
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="quick-actions">
        <h3>Ações Rápidas</h3>

        <ion-card class="action-card" button @click="navigateToChecklist">
          <ion-card-content>
            <div class="action-content">
              <div class="action-icon">
                <ion-icon :icon="clipboardOutline"></ion-icon>
              </div>
              <div class="action-text">
                <h4>Novo Checklist</h4>
                <p>Iniciar verificação de segurança</p>
              </div>
              <div class="action-arrow">
                <ion-icon :icon="chevronForwardOutline"></ion-icon>
              </div>
            </div>
          </ion-card-content>
        </ion-card>

        <ion-card class="action-card" button @click="navigateToPatients">
          <ion-card-content>
            <div class="action-content">
              <div class="action-icon">
                <ion-icon :icon="peopleOutline"></ion-icon>
              </div>
              <div class="action-text">
                <h4>Pacientes</h4>
                <p>Buscar e gerenciar pacientes</p>
              </div>
              <div class="action-arrow">
                <ion-icon :icon="chevronForwardOutline"></ion-icon>
              </div>
            </div>
          </ion-card-content>
        </ion-card>
      </div>

      <!-- Recent checklists -->
      <div class="recent-section" v-if="recentChecklists.length > 0">
        <h3>Últimos Checklists</h3>

        <ion-card v-for="checklist in recentChecklists" :key="checklist.id" class="checklist-card">
          <ion-card-content>
            <div class="checklist-header">
              <h4>Máquina {{ checklist.machine_id }}</h4>
              <ion-badge :color="getShiftColor(checklist.shift)">{{ formatShift(checklist.shift) }}</ion-badge>
            </div>
            <p class="checklist-date">{{ formatDate(checklist.created_at) }}</p>
            <p class="checklist-patient">Paciente ID: {{ checklist.patient_id }}</p>
          </ion-card-content>
        </ion-card>
      </div>

      <!-- Connection status -->
      <div class="status-bar">
        <ion-icon :icon="wifiOutline" :class="{ 'status-online': isOnline, 'status-offline': !isOnline }"></ion-icon>
        <span :class="{ 'status-online': isOnline, 'status-offline': !isOnline }">
          {{ isOnline ? 'Online' : 'Offline' }}
        </span>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonButtons,
  IonButton,
  IonIcon,
  IonCard,
  IonCardContent,
  IonBadge,
  IonRefresher,
  IonRefresherContent,
  alertController,
  loadingController
} from '@ionic/vue';
import {
  logOutOutline,
  personCircleOutline,
  checkmarkCircleOutline,
  timeOutline,
  hardwareChipOutline,
  clipboardOutline,
  peopleOutline,
  chevronForwardOutline,
  wifiOutline
} from 'ionicons/icons';

import { Container } from '@/core/di/Container';
import { User } from '@/core/domain/entities/User';
import { SafetyChecklist } from '@/core/domain/entities/SafetyChecklist';
import { Machine } from '@/core/domain/entities/Machine';
import { ChecklistRepository } from '@/core/domain/repositories/ChecklistRepository';

const router = useRouter();
const container = Container.getInstance();

// Use cases
const getCurrentUserUseCase = container.getCurrentUserUseCase();
const logoutUseCase = container.getLogoutUseCase();
const checklistRepository = container.get<ChecklistRepository>('ChecklistRepository');
const machineRepository = container.getMachineRepository();

// Reactive state
const user = ref<User | null>(null);
const todaysChecklists = ref<SafetyChecklist[]>([]);
const pendingChecklists = ref<SafetyChecklist[]>([]);
const recentChecklists = ref<SafetyChecklist[]>([]);
const availableMachines = ref<Machine[]>([]);
const isOnline = ref(navigator.onLine);

// Methods
const loadUserData = async () => {
  try {
    user.value = await getCurrentUserUseCase.execute();
  } catch (error) {
    console.error('Error loading user:', error);
    router.replace('/login');
  }
};

const loadDashboardData = async () => {
  const loading = await loadingController.create({
    message: 'Carregando dados...',
    spinner: 'crescent'
  });
  await loading.present();

  try {
    // Load dashboard data in parallel
    const [todays, pending, machines] = await Promise.all([
      checklistRepository.getTodaysChecklists(),
      checklistRepository.getPendingChecklists(),
      machineRepository.getAvailable()
    ]);

    todaysChecklists.value = todays;
    pendingChecklists.value = pending;
    availableMachines.value = machines;

    // Get recent checklists (last 5)
    if (user.value) {
      recentChecklists.value = await checklistRepository.getByUser(user.value.id, 5);
    }

  } catch (error) {
    console.error('Error loading dashboard data:', error);
  } finally {
    await loading.dismiss();
  }
};

const handleRefresh = async (event: any) => {
  try {
    await loadDashboardData();
  } finally {
    event.target.complete();
  }
};

const handleLogout = async () => {
  const alert = await alertController.create({
    header: 'Sair do Sistema',
    message: 'Tem certeza que deseja sair?',
    buttons: [
      {
        text: 'Cancelar',
        role: 'cancel'
      },
      {
        text: 'Sair',
        role: 'confirm',
        handler: async () => {
          try {
            await logoutUseCase.execute();
            router.replace('/login');
          } catch (error) {
            console.error('Logout error:', error);
            router.replace('/login');
          }
        }
      }
    ]
  });

  await alert.present();
};

const navigateToChecklist = () => {
  router.push('/checklist');
};

const navigateToPatients = () => {
  router.push('/patients');
};

// Utility methods
const getShiftColor = (shift: string) => {
  switch (shift) {
    case 'matutino': return 'success';
    case 'vespertino': return 'warning';
    case 'noturno': return 'secondary';
    default: return 'medium';
  }
};

const formatShift = (shift: string) => {
  switch (shift) {
    case 'matutino': return 'Manhã';
    case 'vespertino': return 'Tarde';
    case 'noturno': return 'Noite';
    default: return shift;
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Network status
const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine;
};

// Lifecycle
onMounted(async () => {
  await loadUserData();
  await loadDashboardData();

  // Listen for network changes
  window.addEventListener('online', updateOnlineStatus);
  window.addEventListener('offline', updateOnlineStatus);
});
</script>

<style scoped>
.welcome-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  color: white;
  margin-bottom: 1rem;
}

.user-info h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.user-info p {
  margin: 0.25rem 0 0 0;
  opacity: 0.9;
  font-size: 0.9rem;
}

.user-avatar ion-icon {
  font-size: 3rem;
  opacity: 0.8;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
  padding: 0 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  margin-bottom: 0.5rem;
}

.stat-icon ion-icon {
  font-size: 2rem;
}

.stat-icon.success ion-icon {
  color: var(--ion-color-success);
}

.stat-icon.warning ion-icon {
  color: var(--ion-color-warning);
}

.stat-icon.primary ion-icon {
  color: var(--ion-color-primary);
}

.stat-content h3 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: bold;
  color: var(--ion-color-dark);
}

.stat-content p {
  margin: 0.25rem 0 0 0;
  font-size: 0.75rem;
  color: var(--ion-color-medium);
}

.quick-actions {
  padding: 0 1rem;
  margin-bottom: 1.5rem;
}

.quick-actions h3 {
  margin: 0 0 1rem 0;
  color: var(--ion-color-dark);
  font-size: 1.1rem;
  font-weight: 600;
}

.action-card {
  margin-bottom: 0.75rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.action-content {
  display: flex;
  align-items: center;
  padding: 0.5rem 0;
}

.action-icon {
  margin-right: 1rem;
}

.action-icon ion-icon {
  font-size: 1.5rem;
  color: var(--ion-color-primary);
}

.action-text {
  flex: 1;
}

.action-text h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--ion-color-dark);
}

.action-text p {
  margin: 0.25rem 0 0 0;
  font-size: 0.875rem;
  color: var(--ion-color-medium);
}

.action-arrow ion-icon {
  color: var(--ion-color-medium);
}

.recent-section {
  padding: 0 1rem;
  margin-bottom: 1.5rem;
}

.recent-section h3 {
  margin: 0 0 1rem 0;
  color: var(--ion-color-dark);
  font-size: 1.1rem;
  font-weight: 600;
}

.checklist-card {
  margin-bottom: 0.75rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.checklist-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.checklist-header h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--ion-color-dark);
}

.checklist-date, .checklist-patient {
  margin: 0.25rem 0 0 0;
  font-size: 0.875rem;
  color: var(--ion-color-medium);
}

.status-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  gap: 0.5rem;
  border-top: 1px solid var(--ion-color-light);
  background: var(--ion-color-light-tint);
}

.status-online {
  color: var(--ion-color-success);
}

.status-offline {
  color: var(--ion-color-danger);
}
</style>