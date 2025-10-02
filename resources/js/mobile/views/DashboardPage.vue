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

    <ion-content :fullscreen="true" class="dashboard-content">
      <!-- Refresher -->
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section" v-if="user">
          <div class="welcome-header">
            <div>
              <h1>Olá, {{ user.name.split(' ')[0] }}! 👋</h1>
              <p class="unit-name">{{ user.unit?.name || 'Sistema de Hemodiálise' }}</p>
            </div>
            <div class="user-badge">
              <span class="role-tag">{{ userRole }}</span>
            </div>
          </div>
        </div>

        <!-- Quick Stats Summary -->
        <div class="quick-stats" v-if="statsLoaded">
          <div class="stat-card available" @click="showMachineStatus">
            <div class="stat-icon">
              <ion-icon :icon="medicalSharp"></ion-icon>
            </div>
            <div class="stat-content">
              <div class="stat-number">{{ availableMachinesCount }}</div>
              <div class="stat-label">Disponíveis</div>
            </div>
          </div>

          <div class="stat-card active" @click="showActiveChecklists">
            <div class="stat-icon">
              <ion-icon :icon="timeOutline"></ion-icon>
            </div>
            <div class="stat-content">
              <div class="stat-number">{{ activeChecklistsCount }}</div>
              <div class="stat-label">Em Andamento</div>
            </div>
          </div>

          <div class="stat-card total">
            <div class="stat-icon">
              <ion-icon :icon="medicalOutline"></ion-icon>
            </div>
            <div class="stat-content">
              <div class="stat-number">{{ totalMachines }}</div>
              <div class="stat-label">Total Máquinas</div>
            </div>
          </div>
        </div>

        <!-- Primary Action -->
        <div class="primary-action">
          <button class="primary-btn" @click="navigateToChecklist">
            <ion-icon :icon="clipboardOutline"></ion-icon>
            <div class="btn-text">
              <span class="btn-title">Novo Checklist</span>
              <span class="btn-subtitle">Iniciar verificação de segurança</span>
            </div>
            <ion-badge v-if="availableMachinesCount > 0" color="success">{{ availableMachinesCount }}</ion-badge>
          </button>
        </div>

        <!-- Quick Actions Grid -->
        <div class="section">
          <h2 class="section-title">Acesso Rápido</h2>

          <div class="quick-actions">
            <div class="action-btn" @click="showActiveChecklists" v-if="activeChecklistsCount > 0">
              <div class="action-icon warning">
                <ion-icon :icon="timeOutline"></ion-icon>
              </div>
              <div class="action-content">
                <span class="action-title">Checklists Ativos</span>
                <span class="action-subtitle">{{ activeChecklistsCount }} em andamento</span>
              </div>
              <ion-icon :icon="chevronForwardOutline" class="chevron"></ion-icon>
            </div>

            <div class="action-btn" @click="showMachineStatus">
              <div class="action-icon tertiary">
                <ion-icon :icon="medicalSharp"></ion-icon>
              </div>
              <div class="action-content">
                <span class="action-title">Máquinas</span>
                <span class="action-subtitle">Status e controle</span>
              </div>
              <ion-icon :icon="chevronForwardOutline" class="chevron"></ion-icon>
            </div>

            <div class="action-btn" @click="navigateToCleaningControls">
              <div class="action-icon cleaning">
                <ion-icon :icon="sparklesOutline"></ion-icon>
              </div>
              <div class="action-content">
                <span class="action-title">Controle de Limpeza</span>
                <span class="action-subtitle">Limpeza e desinfecção</span>
              </div>
              <ion-icon :icon="chevronForwardOutline" class="chevron"></ion-icon>
            </div>

            <div class="action-btn" @click="navigateToPatients">
              <div class="action-icon primary">
                <ion-icon :icon="peopleOutline"></ion-icon>
              </div>
              <div class="action-content">
                <span class="action-title">Pacientes</span>
                <span class="action-subtitle">Buscar e gerenciar</span>
              </div>
              <ion-icon :icon="chevronForwardOutline" class="chevron"></ion-icon>
            </div>
          </div>
        </div>

        <!-- Active Checklists Section -->
        <div class="section" v-if="activeChecklists.length > 0">
          <h2 class="section-title">Em Andamento</h2>
          <div class="checklists-list">
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
        <div class="connection-status">
          <ion-chip :color="isOnline ? 'success' : 'danger'" class="status-chip">
            <ion-icon :icon="wifiOutline"></ion-icon>
            <ion-label>{{ isOnline ? 'Online' : 'Offline' }}</ion-label>
          </ion-chip>
        </div>
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
  medicalSharp,
  sparklesOutline,
  chevronForwardOutline
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
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

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
  console.log('Navigating to new checklist...');
  router.push('/checklist/new');
};

const navigateToPatients = () => {
  console.log('Navigating to patients...');
  router.push('/patients');
};

const showActiveChecklists = () => {
  console.log('Navigating to active checklists...');
  router.push('/checklists');
};

const showMachineStatus = () => {
  router.push('/machines');
};

const navigateToCleaningControls = () => {
  console.log('Navigating to cleaning controls...');
  router.push('/cleaning-controls');
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
.dashboard-content {
  --background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
}

.dashboard-container {
  max-width: 600px;
  margin: 0 auto;
  padding-bottom: 24px;
}

/* Welcome Section */
.welcome-section {
  background: white;
  padding: 20px;
  margin: 16px 16px 0;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.welcome-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.welcome-section h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.unit-name {
  font-size: 0.9rem;
  color: #6b7280;
  margin: 0;
  font-weight: 500;
}

.user-badge {
  flex-shrink: 0;
}

.role-tag {
  display: inline-block;
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Quick Stats */
.quick-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  padding: 16px;
  background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: white;
  border-left: 4px solid transparent;
  padding: 16px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  cursor: pointer;
  transition: all 0.2s ease;
}

.stat-card:active {
  transform: scale(0.97);
}

.stat-card.available {
  border-left-color: #10b981;
}

.stat-card.active {
  border-left-color: #f59e0b;
}

.stat-card.total {
  border-left-color: #6b7280;
}

.stat-icon {
  flex-shrink: 0;
}

.stat-icon ion-icon {
  font-size: 2rem;
}

.stat-card.available .stat-icon ion-icon {
  color: #10b981;
}

.stat-card.active .stat-icon ion-icon {
  color: #f59e0b;
}

.stat-card.total .stat-icon ion-icon {
  color: #6b7280;
}

.stat-content {
  flex: 1;
  min-width: 0;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.stat-label {
  font-size: 0.7rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 2px;
  font-weight: 600;
}

/* Primary Action */
.primary-action {
  padding: 0 16px;
  margin-top: 16px;
}

.primary-btn {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 16px;
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  color: white;
  border: none;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.primary-btn:active {
  transform: scale(0.98);
}

.primary-btn ion-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

.btn-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
  text-align: left;
}

.btn-title {
  font-size: 1.2rem;
  font-weight: 700;
}

.btn-subtitle {
  font-size: 0.85rem;
  opacity: 0.9;
}

.primary-btn ion-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  font-size: 0.85rem;
  font-weight: 700;
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
}

/* Quick Actions */
.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

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

.action-icon.warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.action-icon.tertiary {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.action-icon.cleaning {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
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

/* Checklists List */
.checklists-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Connection Status */
.connection-status {
  padding: 16px;
  text-align: center;
}

.status-chip {
  font-size: 0.85rem;
  font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 480px) {
  .quick-stats {
    gap: 8px;
    padding: 12px;
  }

  .stat-card {
    flex-direction: column;
    text-align: center;
    gap: 8px;
    padding: 12px;
  }

  .stat-icon ion-icon {
    font-size: 1.8rem;
  }

  .stat-number {
    font-size: 1.3rem;
  }

  .stat-label {
    font-size: 0.65rem;
  }

  .primary-btn {
    padding: 16px;
  }

  .primary-btn ion-icon {
    font-size: 2rem;
  }

  .btn-title {
    font-size: 1.1rem;
  }

  .btn-subtitle {
    font-size: 0.8rem;
  }
}

/* Interface Switch Button */
.interface-switch-btn {
  margin-right: 0.5rem;
}

.interface-switch-btn ion-icon {
  opacity: 0.9;
}
</style>