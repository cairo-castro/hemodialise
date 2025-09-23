<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-title>Hemodiálise Mobile</ion-title>
        <ion-buttons slot="end">
          <!-- Dark Mode Toggle -->
          <ion-button @click="toggleDarkMode" fill="clear" class="theme-toggle-btn">
            <ion-icon :icon="isDarkMode ? sunnyOutline : moonOutline" slot="icon-only"></ion-icon>
          </ion-button>

          <!-- Interface Switch -->
          <ion-button @click="showInterfaceSelector" fill="clear" class="interface-switch-btn">
            <ion-icon :icon="swapHorizontalOutline" slot="icon-only"></ion-icon>
          </ion-button>

          <!-- Logout Button -->
          <ion-button @click="handleLogout" fill="clear">
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

      <!-- Welcome Section -->
      <div class="welcome-section" v-if="user">
        <div class="welcome-content">
          <ion-icon :icon="medicalOutline" class="welcome-icon"></ion-icon>
          <h1>Olá, {{ user.name.split(' ')[0] }}!</h1>
          <p>{{ user.unit?.name || 'Sistema de Hemodiálise' }}</p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="actions-container">
        <h2>Ações Principais</h2>

        <div class="actions-grid">
          <ion-card button class="action-card checklist" @click="navigateToChecklist">
            <ion-card-content>
              <ion-icon :icon="clipboardOutline" class="action-icon"></ion-icon>
              <h3>Novo Checklist</h3>
              <p>Iniciar verificação</p>
              <ion-badge v-if="availableMachinesCount > 0" color="success" class="badge-overlay">{{ availableMachinesCount }}</ion-badge>
            </ion-card-content>
          </ion-card>

          <ion-card button class="action-card active-checklists" @click="showActiveChecklists" v-if="activeChecklistsCount > 0">
            <ion-card-content>
              <ion-icon :icon="timeOutline" class="action-icon"></ion-icon>
              <h3>Checklists Ativos</h3>
              <p>{{ activeChecklistsCount }} em andamento</p>
              <ion-badge color="warning" class="badge-overlay">{{ activeChecklistsCount }}</ion-badge>
            </ion-card-content>
          </ion-card>

          <ion-card button class="action-card patients" @click="navigateToPatients">
            <ion-card-content>
              <ion-icon :icon="peopleOutline" class="action-icon"></ion-icon>
              <h3>Pacientes</h3>
              <p>Buscar e gerenciar</p>
            </ion-card-content>
          </ion-card>

          <ion-card button class="action-card machines" @click="showMachineStatus">
            <ion-card-content>
              <ion-icon :icon="medicalSharp" class="action-icon"></ion-icon>
              <h3>Máquinas</h3>
              <p>Status e controle</p>
            </ion-card-content>
          </ion-card>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="stats-container" v-if="statsLoaded">
        <h2>Resumo de Hoje</h2>

        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-number">{{ todayCount }}</div>
            <div class="stat-label">Checklists Hoje</div>
          </div>

          <div class="stat-item">
            <div class="stat-number">{{ totalMachines }}</div>
            <div class="stat-label">Máquinas Ativas</div>
          </div>

          <div class="stat-item">
            <div class="stat-number">{{ userRole }}</div>
            <div class="stat-label">Perfil</div>
          </div>
        </div>
      </div>

      <!-- Active Checklists Section -->
      <div class="checklists-container" v-if="activeChecklists.length > 0">
        <h2>Checklists em Andamento</h2>
        <div class="checklists-grid">
          <ActiveChecklistCard
            v-for="checklist in activeChecklists"
            :key="checklist.id"
            :checklist="checklist"
            @continue="continueChecklist"
            @pause="pauseChecklist"
            @resume="resumeChecklist"
          />
        </div>
      </div>

      <!-- Connection Status -->
      <div class="status-indicator">
        <ion-chip :color="isOnline ? 'success' : 'danger'">
          <ion-icon :icon="isOnline ? wifiOutline : wifiOutline" slot="start"></ion-icon>
          <ion-label>{{ isOnline ? 'Online' : 'Offline' }}</ion-label>
        </ion-chip>
      </div>

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
  IonButtons,
  IonButton,
  IonIcon,
  IonCard,
  IonCardContent,
  IonChip,
  IonLabel,
  IonBadge,
  IonRefresher,
  IonRefresherContent,
  alertController,
  toastController,
  actionSheetController
} from '@ionic/vue';
import {
  logOutOutline,
  medicalOutline,
  clipboardOutline,
  peopleOutline,
  barChartOutline,
  settingsOutline,
  wifiOutline,
  swapHorizontalOutline,
  moonOutline,
  sunnyOutline,
  timeOutline,
  medicalSharp
} from 'ionicons/icons';

import { Container } from '@mobile/core/di/Container';
import { User } from '@mobile/core/domain/entities/User';
import ActiveChecklistCard from '../components/ActiveChecklistCard.vue';

const router = useRouter();
const container = Container.getInstance();

// Use cases
const getCurrentUserUseCase = container.getCurrentUserUseCase();
const logoutUseCase = container.getLogoutUseCase();

// Reactive state
const user = ref<User | null>(null);
const isOnline = ref(navigator.onLine);
const statsLoaded = ref(false);
const todayCount = ref(0);
const totalMachines = ref(3); // Default fallback
const loading = ref(false);
const isDarkMode = ref(false);

// New state for active checklists and machines
const activeChecklists = ref([]);
const availableMachines = ref([]);
const machines = ref([]);

// Computed properties
const userRole = computed(() => {
  if (!user.value) return 'Usuário';

  const roleMap: Record<string, string> = {
    'tecnico': 'Técnico',
    'admin': 'Admin',
    'gestor': 'Gestor',
    'coordenador': 'Coordenador',
    'supervisor': 'Supervisor'
  };

  return roleMap[user.value.role] || 'Usuário';
});

// New computed properties
const activeChecklistsCount = computed(() =>
  activeChecklists.value.filter((c: any) => !c.paused_at).length
);

const pausedChecklistsCount = computed(() =>
  activeChecklists.value.filter((c: any) => c.paused_at).length
);

const availableMachinesCount = computed(() => availableMachines.value.length);

// Methods
const loadUserData = async () => {
  try {
    user.value = await getCurrentUserUseCase.execute();
    console.log('User loaded:', user.value);
  } catch (error) {
    console.error('Error loading user:', error);

    // Show error toast
    const toast = await toastController.create({
      message: 'Erro ao carregar dados do usuário',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();

    // Redirect to login after a delay
    setTimeout(() => {
      router.replace('/login');
    }, 2000);
  }
};

const loadStats = async () => {
  try {
    // Load real data from APIs
    await Promise.all([
      loadActiveChecklists(),
      loadAvailableMachines(),
      loadMachines()
    ]);

    todayCount.value = activeChecklists.value.length;
    totalMachines.value = machines.value.length;
    statsLoaded.value = true;

    console.log('Stats loaded');
  } catch (error) {
    console.error('Error loading stats:', error);
    statsLoaded.value = true; // Show anyway with defaults
  }
};

const loadActiveChecklists = async () => {
  try {
    const response = await fetch('/api/checklists/active', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    });

    const data = await response.json();
    if (data.success) {
      activeChecklists.value = data.checklists;
    }
  } catch (error) {
    console.error('Erro ao carregar checklists ativos:', error);
  }
};

const loadAvailableMachines = async () => {
  try {
    const response = await fetch('/api/machines/available', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    });

    const data = await response.json();
    if (data.success) {
      availableMachines.value = data.machines;
    }
  } catch (error) {
    console.error('Erro ao carregar máquinas disponíveis:', error);
  }
};

const loadMachines = async () => {
  try {
    const response = await fetch('/api/machines', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    });

    const data = await response.json();
    if (data.success) {
      machines.value = data.machines;
    }
  } catch (error) {
    console.error('Erro ao carregar máquinas:', error);
  }
};

const handleRefresh = async (event: any) => {
  try {
    await Promise.all([loadUserData(), loadStats()]);

    const toast = await toastController.create({
      message: 'Dados atualizados',
      duration: 1500,
      color: 'success',
      position: 'top'
    });
    await toast.present();

  } catch (error) {
    console.error('Error refreshing:', error);
  } finally {
    event.target.complete();
  }
};

const handleLogout = async () => {
  const alert = await alertController.create({
    header: 'Confirmar Saída',
    message: 'Deseja realmente sair do aplicativo?',
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

            const toast = await toastController.create({
              message: 'Logout realizado com sucesso',
              duration: 2000,
              color: 'success',
              position: 'top'
            });
            await toast.present();

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
  console.log('Navigating to checklist list...');
  router.push('/checklists');
};

const navigateToPatients = () => {
  console.log('Navigating to patients...');
  router.push('/patients');
};

const showActiveChecklists = () => {
  router.push('/checklists/active');
};

const showMachineStatus = () => {
  router.push('/machines');
};

const continueChecklist = (checklist: any) => {
  router.push(`/checklist/${checklist.id}`);
};

const pauseChecklist = async (checklist: any) => {
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
          try {
            const response = await fetch(`/api/checklists/${checklist.id}/pause`, {
              method: 'POST',
              headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
              }
            });

            const data = await response.json();
            if (data.success) {
              const toast = await toastController.create({
                message: 'Checklist pausado com sucesso.',
                duration: 3000,
                color: 'success'
              });
              await toast.present();
              await loadStats(); // Refresh data
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
          }
        }
      }
    ]
  });

  await alert.present();
};

const resumeChecklist = async (checklist: any) => {
  try {
    const response = await fetch(`/api/checklists/${checklist.id}/resume`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    });

    const data = await response.json();
    if (data.success) {
      const toast = await toastController.create({
        message: 'Checklist retomado com sucesso.',
        duration: 3000,
        color: 'success'
      });
      await toast.present();
      await loadStats(); // Refresh data
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    const toast = await toastController.create({
      message: 'Erro ao retomar checklist.',
      duration: 3000,
      color: 'danger'
    });
    await toast.present();
  }
};

const showComingSoon = async () => {
  const toast = await toastController.create({
    message: 'Funcionalidade em breve',
    duration: 2000,
    color: 'warning',
    position: 'middle'
  });
  await toast.present();
};

const showInterfaceSelector = async () => {
  const actionSheet = await actionSheetController.create({
    header: 'Mudar Interface',
    subHeader: 'Escolha a interface que deseja usar',
    buttons: [
      {
        text: '📱 Mobile (Atual)',
        icon: 'phone-portrait-outline',
        data: {
          interface: 'mobile'
        },
        handler: () => {
          console.log('Mobile interface selected (already current)');
        }
      },
      {
        text: '🖥️ Desktop',
        icon: 'desktop-outline',
        data: {
          interface: 'desktop'
        },
        handler: () => {
          console.log('Switching to desktop interface...');
          // Redirect to desktop interface
          window.location.href = '/desktop';
        }
      },
      {
        text: '⚙️ Admin (Filament)',
        icon: 'settings-outline',
        data: {
          interface: 'admin'
        },
        handler: () => {
          console.log('Switching to admin interface...');
          // Redirect to admin interface
          window.location.href = '/admin';
        }
      },
      {
        text: 'Cancelar',
        icon: 'close-outline',
        role: 'cancel',
        data: {
          action: 'cancel'
        }
      }
    ]
  });

  await actionSheet.present();
};

// Dark mode functionality
const initializeDarkMode = () => {
  // Check if user has a preference saved
  const savedPreference = localStorage.getItem('dark-mode');

  if (savedPreference !== null) {
    isDarkMode.value = savedPreference === 'true';
  } else {
    // Use system preference if no saved preference
    isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
  }

  // Apply the theme
  applyDarkMode();
};

const applyDarkMode = () => {
  if (isDarkMode.value) {
    document.body.classList.add('dark');
  } else {
    document.body.classList.remove('dark');
  }
};

const toggleDarkMode = async () => {
  isDarkMode.value = !isDarkMode.value;

  // Save preference
  localStorage.setItem('dark-mode', isDarkMode.value.toString());

  // Apply theme
  applyDarkMode();

  // Show feedback toast
  const toast = await toastController.create({
    message: `Modo ${isDarkMode.value ? 'escuro' : 'claro'} ativado`,
    duration: 1500,
    color: 'medium',
    position: 'bottom'
  });
  await toast.present();

  console.log('Dark mode toggled:', isDarkMode.value);
};

// Network status monitoring
const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine;
};

// Lifecycle
onMounted(async () => {
  console.log('Dashboard mounted, loading data...');

  // Initialize dark mode first
  initializeDarkMode();

  // Show loading state
  loading.value = true;

  try {
    // Load user data first (required)
    await loadUserData();

    // Load stats in background
    loadStats();

    // Setup network listeners
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);

  } catch (error) {
    console.error('Dashboard initialization error:', error);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
/* Welcome Section */
.welcome-section {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  color: white;
  padding: 2rem 1.5rem;
  text-align: center;
  margin-bottom: 1rem;
}

.welcome-content {
  max-width: 300px;
  margin: 0 auto;
}

.welcome-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.9;
}

.welcome-section h1 {
  font-size: 1.8rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
}

.welcome-section p {
  font-size: 1rem;
  opacity: 0.9;
  margin: 0;
}

/* Actions Container */
.actions-container {
  padding: 0 1rem 1.5rem;
}

.actions-container h2 {
  font-size: 1.3rem;
  font-weight: 600;
  margin: 0 0 1rem 0;
  color: var(--ion-color-dark);
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.action-card {
  margin: 0;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  overflow: hidden;
}

.action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.action-card ion-card-content {
  text-align: center;
  padding: 1.5rem 1rem;
  position: relative;
}

.action-icon {
  font-size: 2.5rem;
  margin-bottom: 0.8rem;
}

.action-card h3 {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 0.3rem 0;
  color: var(--ion-color-dark);
}

.action-card p {
  font-size: 0.85rem;
  color: var(--ion-color-medium);
  margin: 0;
}

/* Action Card Colors */
.action-card.checklist .action-icon {
  color: var(--ion-color-success);
}

.action-card.patients .action-icon {
  color: var(--ion-color-primary);
}

.action-card.reports .action-icon {
  color: var(--ion-color-warning);
}

.action-card.settings .action-icon {
  color: var(--ion-color-medium);
}

.action-card.active-checklists .action-icon {
  color: var(--ion-color-warning);
}

.action-card.machines .action-icon {
  color: var(--ion-color-tertiary);
}

/* Badge overlay for action cards */
.badge-overlay {
  position: absolute;
  top: 8px;
  right: 8px;
  min-width: 20px;
  height: 20px;
  font-size: 12px;
  font-weight: 600;
}

/* Checklists Container */
.checklists-container {
  padding: 0 1rem 1.5rem;
}

.checklists-container h2 {
  font-size: 1.3rem;
  font-weight: 600;
  margin: 0 0 1rem 0;
  color: var(--ion-color-dark);
}

.checklists-grid {
  display: grid;
  gap: 16px;
}

/* Stats Container */
.stats-container {
  padding: 0 1rem 1.5rem;
}

.stats-container h2 {
  font-size: 1.3rem;
  font-weight: 600;
  margin: 0 0 1rem 0;
  color: var(--ion-color-dark);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  background: white;
  border-radius: 16px;
  padding: 1.5rem 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.stat-item {
  text-align: center;
}

.stat-number {
  font-size: 1.8rem;
  font-weight: bold;
  color: var(--ion-color-primary);
  margin-bottom: 0.3rem;
}

.stat-label {
  font-size: 0.8rem;
  color: var(--ion-color-medium);
  line-height: 1.2;
}

/* Status Indicator */
.status-indicator {
  padding: 1rem;
  text-align: center;
}

/* Responsive adjustments */
@media (max-width: 480px) {
  .actions-grid {
    gap: 0.8rem;
  }

  .action-card ion-card-content {
    padding: 1.2rem 0.8rem;
  }

  .action-icon {
    font-size: 2rem;
  }

  .stats-grid {
    gap: 0.8rem;
    padding: 1.2rem 0.8rem;
  }

  .stat-number {
    font-size: 1.5rem;
  }
}

/* Loading state */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 60vh;
  color: var(--ion-color-medium);
}

.loading-container ion-spinner {
  margin-bottom: 1rem;
}

/* Interface Switch Button */
.interface-switch-btn {
  margin-right: 0.5rem;
}

.interface-switch-btn ion-icon {
  opacity: 0.9;
}
</style>