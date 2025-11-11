<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/dashboard"></ion-back-button>
        </ion-buttons>
        <ion-title>Visão Geral das Máquinas</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="refreshData" :disabled="isLoading">
            <ion-icon :icon="refreshOutline"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="overview-content">
      <div class="overview-container">
        <!-- Pull to refresh -->
        <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
          <ion-refresher-content></ion-refresher-content>
        </ion-refresher>

        <!-- Loading State -->
        <div v-if="isLoading && machines.length === 0" class="loading-state">
          <ion-spinner name="crescent"></ion-spinner>
          <p>Carregando dados...</p>
        </div>

        <!-- Content -->
        <div v-else>
          <!-- Total Summary Card -->
          <ion-card class="summary-card">
            <ion-card-content>
              <div class="summary-header">
                <div class="summary-icon">
                  <ion-icon :icon="medicalOutline"></ion-icon>
                </div>
                <div class="summary-info">
                  <h2>{{ totalMachines }}</h2>
                  <p>Máquinas Cadastradas</p>
                </div>
              </div>
              <div class="summary-description">
                <p>Total de equipamentos de hemodiálise na unidade {{ currentUnitName }}</p>
              </div>
            </ion-card-content>
          </ion-card>

          <!-- Statistics Grid -->
          <div class="stats-grid">
            <div class="stat-box available">
              <div class="stat-box-header">
                <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
                <span class="stat-box-label">Disponíveis</span>
              </div>
              <div class="stat-box-number">{{ availableCount }}</div>
              <div class="stat-box-percentage">{{ availablePercentage }}%</div>
            </div>

            <div class="stat-box occupied">
              <div class="stat-box-header">
                <ion-icon :icon="timeOutline"></ion-icon>
                <span class="stat-box-label">Em Uso</span>
              </div>
              <div class="stat-box-number">{{ occupiedCount }}</div>
              <div class="stat-box-percentage">{{ occupiedPercentage }}%</div>
            </div>

            <div class="stat-box maintenance">
              <div class="stat-box-header">
                <ion-icon :icon="constructOutline"></ion-icon>
                <span class="stat-box-label">Manutenção</span>
              </div>
              <div class="stat-box-number">{{ maintenanceCount }}</div>
              <div class="stat-box-percentage">{{ maintenancePercentage }}%</div>
            </div>

            <div class="stat-box inactive">
              <div class="stat-box-header">
                <ion-icon :icon="closeCircleOutline"></ion-icon>
                <span class="stat-box-label">Inativas</span>
              </div>
              <div class="stat-box-number">{{ inactiveCount }}</div>
              <div class="stat-box-percentage">{{ inactivePercentage }}%</div>
            </div>
          </div>

          <!-- Utilization Chart -->
          <ion-card class="chart-card">
            <ion-card-header>
              <ion-card-title>Taxa de Utilização</ion-card-title>
            </ion-card-header>
            <ion-card-content>
              <div class="utilization-bar">
                <div
                  class="bar-segment available"
                  :style="{ width: availablePercentage + '%' }"
                  v-if="availablePercentage > 0"
                >
                  <span v-if="availablePercentage > 10">{{ availableCount }}</span>
                </div>
                <div
                  class="bar-segment occupied"
                  :style="{ width: occupiedPercentage + '%' }"
                  v-if="occupiedPercentage > 0"
                >
                  <span v-if="occupiedPercentage > 10">{{ occupiedCount }}</span>
                </div>
                <div
                  class="bar-segment maintenance"
                  :style="{ width: maintenancePercentage + '%' }"
                  v-if="maintenancePercentage > 0"
                >
                  <span v-if="maintenancePercentage > 10">{{ maintenanceCount }}</span>
                </div>
                <div
                  class="bar-segment inactive"
                  :style="{ width: inactivePercentage + '%' }"
                  v-if="inactivePercentage > 0"
                >
                  <span v-if="inactivePercentage > 10">{{ inactiveCount }}</span>
                </div>
              </div>
              <div class="bar-legend">
                <div class="legend-item">
                  <span class="legend-color available"></span>
                  <span>Disponíveis</span>
                </div>
                <div class="legend-item">
                  <span class="legend-color occupied"></span>
                  <span>Em Uso</span>
                </div>
                <div class="legend-item">
                  <span class="legend-color maintenance"></span>
                  <span>Manutenção</span>
                </div>
                <div class="legend-item">
                  <span class="legend-color inactive"></span>
                  <span>Inativas</span>
                </div>
              </div>
            </ion-card-content>
          </ion-card>

          <!-- Recent Activity -->
          <ion-card class="activity-card" v-if="recentActivity.length > 0">
            <ion-card-header>
              <ion-card-title>Atividade Recente</ion-card-title>
            </ion-card-header>
            <ion-card-content>
              <div class="activity-list">
                <div
                  v-for="activity in recentActivity"
                  :key="activity.id"
                  class="activity-item"
                >
                  <div class="activity-icon" :class="activity.type">
                    <ion-icon :icon="getActivityIcon(activity.type)"></ion-icon>
                  </div>
                  <div class="activity-content">
                    <div class="activity-title">{{ activity.title }}</div>
                    <div class="activity-subtitle">{{ activity.machine_name }}</div>
                    <div class="activity-time">{{ formatTimeAgo(activity.created_at) }}</div>
                  </div>
                </div>
              </div>
            </ion-card-content>
          </ion-card>

          <!-- Machines List -->
          <ion-card class="machines-list-card">
            <ion-card-header>
              <ion-card-title>Todas as Máquinas</ion-card-title>
            </ion-card-header>
            <ion-card-content>
              <div class="machines-list">
                <div
                  v-for="machine in sortedMachines"
                  :key="machine.id"
                  class="machine-item"
                  @click="viewMachineDetails(machine)"
                >
                  <div class="machine-status-indicator" :class="machine.status"></div>
                  <div class="machine-info">
                    <div class="machine-name">{{ machine.name }}</div>
                    <div class="machine-model">{{ machine.model }}</div>
                  </div>
                  <ion-badge :color="getStatusColor(machine.status)">
                    {{ getStatusLabel(machine.status) }}
                  </ion-badge>
                  <ion-icon :icon="chevronForwardOutline" class="chevron"></ion-icon>
                </div>
              </div>
            </ion-card-content>
          </ion-card>

          <!-- Quick Actions -->
          <div class="quick-actions">
            <ion-button expand="block" @click="goToMachinesPage">
              <ion-icon slot="start" :icon="medicalSharp"></ion-icon>
              Gerenciar Máquinas
            </ion-button>
            <ion-button expand="block" fill="outline" @click="goToNewChecklist">
              <ion-icon slot="start" :icon="clipboardOutline"></ion-icon>
              Novo Checklist
            </ion-button>
          </div>
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
  IonCardHeader,
  IonCardTitle,
  IonCardContent,
  IonRefresher,
  IonRefresherContent,
  IonSpinner,
  IonBadge,
  toastController,
  loadingController
} from '@ionic/vue';
import {
  refreshOutline,
  medicalOutline,
  checkmarkCircleOutline,
  timeOutline,
  constructOutline,
  closeCircleOutline,
  chevronForwardOutline,
  medicalSharp,
  clipboardOutline,
  sparklesOutline,
  flashOutline
} from 'ionicons/icons';

const router = useRouter();

// State
const machines = ref<any[]>([]);
const recentActivity = ref<any[]>([]);
const isLoading = ref(false);
const currentUnitName = ref('');

// Computed
const totalMachines = computed(() => machines.value.length);

const availableCount = computed(() =>
  machines.value.filter(m => m.status === 'available').length
);

const occupiedCount = computed(() =>
  machines.value.filter(m => m.status === 'occupied').length
);

const maintenanceCount = computed(() =>
  machines.value.filter(m => m.status === 'maintenance').length
);

const inactiveCount = computed(() =>
  machines.value.filter(m => !m.active).length
);

const availablePercentage = computed(() =>
  totalMachines.value > 0 ? Math.round((availableCount.value / totalMachines.value) * 100) : 0
);

const occupiedPercentage = computed(() =>
  totalMachines.value > 0 ? Math.round((occupiedCount.value / totalMachines.value) * 100) : 0
);

const maintenancePercentage = computed(() =>
  totalMachines.value > 0 ? Math.round((maintenanceCount.value / totalMachines.value) * 100) : 0
);

const inactivePercentage = computed(() =>
  totalMachines.value > 0 ? Math.round((inactiveCount.value / totalMachines.value) * 100) : 0
);

const sortedMachines = computed(() => {
  return [...machines.value].sort((a, b) => {
    // Sort by status priority: occupied > available > maintenance > inactive
    const statusPriority: any = {
      'occupied': 1,
      'available': 2,
      'maintenance': 3,
      'inactive': 4
    };

    const aPriority = statusPriority[a.status] || 5;
    const bPriority = statusPriority[b.status] || 5;

    if (aPriority !== bPriority) {
      return aPriority - bPriority;
    }

    // If same status, sort by name
    return a.name.localeCompare(b.name);
  });
});

// Methods
const loadData = async () => {
  try {
    isLoading.value = true;

    // Load machines
    const machinesResponse = await fetch('/api/machines', {
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const machinesData = await machinesResponse.json();
    if (machinesData.success) {
      machines.value = machinesData.machines;
    }

    // Load current user to get unit name
    const userResponse = await fetch('/api/me', {
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const userData = await userResponse.json();
    if (userData.success && userData.user) {
      currentUnitName.value = userData.user.unit?.name || 'sua unidade';
    }

    // Load recent activity (checklists from last 24h)
    const activityResponse = await fetch('/api/checklists/recent?limit=5', {
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const activityData = await activityResponse.json();
    if (activityData.success && activityData.checklists && Array.isArray(activityData.checklists)) {
      recentActivity.value = activityData.checklists.map((c: any) => ({
        id: c.id,
        type: c.completed_at ? 'completed' : (c.paused_at ? 'paused' : 'started'),
        title: c.completed_at ? 'Checklist Concluído' : (c.paused_at ? 'Checklist Pausado' : 'Checklist Iniciado'),
        machine_name: c.machine?.name || 'Máquina desconhecida',
        created_at: c.completed_at || c.paused_at || c.created_at
      }));
    } else {
      recentActivity.value = [];
    }

  } catch (error) {
    console.error('Error loading overview data:', error);
    const toast = await toastController.create({
      message: 'Erro ao carregar dados',
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
  const loading = await loadingController.create({
    message: 'Atualizando...',
    spinner: 'crescent'
  });
  await loading.present();

  await loadData();

  await loading.dismiss();

  const toast = await toastController.create({
    message: 'Dados atualizados',
    duration: 1500,
    color: 'success',
    position: 'top'
  });
  await toast.present();
};

const handleRefresh = async (event: any) => {
  await loadData();
  event.target.complete();
};

const getStatusColor = (status: string) => {
  const colors: any = {
    'available': 'success',
    'occupied': 'warning',
    'maintenance': 'danger',
    'inactive': 'medium'
  };
  return colors[status] || 'medium';
};

const getStatusLabel = (status: string) => {
  const labels: any = {
    'available': 'Disponível',
    'occupied': 'Em Uso',
    'maintenance': 'Manutenção',
    'inactive': 'Inativa'
  };
  return labels[status] || 'Desconhecido';
};

const getActivityIcon = (type: string) => {
  const icons: any = {
    'started': clipboardOutline,
    'paused': timeOutline,
    'completed': checkmarkCircleOutline,
    'cleaning': sparklesOutline,
    'disinfection': flashOutline
  };
  return icons[type] || clipboardOutline;
};

const formatTimeAgo = (dateString: string) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMins / 60);
  const diffDays = Math.floor(diffHours / 24);

  if (diffMins < 1) return 'Agora';
  if (diffMins < 60) return `${diffMins}min atrás`;
  if (diffHours < 24) return `${diffHours}h atrás`;
  if (diffDays === 1) return 'Ontem';
  return `${diffDays} dias atrás`;
};

const viewMachineDetails = (machine: any) => {
  router.push(`/machines`);
};

const goToMachinesPage = () => {
  router.push('/machines');
};

const goToNewChecklist = () => {
  router.push('/checklist/new');
};

// Lifecycle
onMounted(() => {
  loadData();
});
</script>

<style scoped>
.overview-content {
  --background: #f5f7fa;
}

.overview-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 16px;
  padding-bottom: 32px;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  gap: 16px;
}

.loading-state p {
  color: var(--ion-color-step-600);
  font-size: 0.9rem;
}

/* Summary Card */
.summary-card {
  margin-bottom: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.summary-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 16px;
}

.summary-icon {
  width: 64px;
  height: 64px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.summary-icon ion-icon {
  font-size: 2.5rem;
  color: white;
}

.summary-info h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0;
  line-height: 1;
}

.summary-info p {
  font-size: 0.95rem;
  margin: 4px 0 0 0;
  opacity: 0.9;
  font-weight: 500;
}

.summary-description p {
  margin: 0;
  font-size: 0.9rem;
  opacity: 0.85;
  line-height: 1.5;
}

/* Statistics Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}

.stat-box {
  background: var(--ion-card-background);
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border-left: 4px solid transparent;
}

.stat-box.available {
  border-left-color: var(--color-available);
}

.stat-box.occupied {
  border-left-color: var(--color-occupied);
}

.stat-box.maintenance {
  border-left-color: var(--color-maintenance);
}

.stat-box.inactive {
  border-left-color: var(--ion-color-step-600);
}

.stat-box-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}

.stat-box-header ion-icon {
  font-size: 1.5rem;
}

.stat-box.available .stat-box-header ion-icon {
  color: var(--color-available);
}

.stat-box.occupied .stat-box-header ion-icon {
  color: var(--color-occupied);
}

.stat-box.maintenance .stat-box-header ion-icon {
  color: var(--color-maintenance);
}

.stat-box.inactive .stat-box-header ion-icon {
  color: var(--ion-color-step-600);
}

.stat-box-label {
  font-size: 0.8rem;
  color: var(--ion-color-step-600);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-box-number {
  font-size: 2rem;
  font-weight: 700;
  color: var(--ion-text-color);
  line-height: 1;
  margin-bottom: 4px;
}

.stat-box-percentage {
  font-size: 0.85rem;
  color: var(--ion-color-step-600);
  font-weight: 500;
}

/* Chart Card */
.chart-card {
  margin-bottom: 20px;
}

.chart-card ion-card-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--ion-text-color);
}

.utilization-bar {
  display: flex;
  width: 100%;
  height: 60px;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.bar-segment {
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  position: relative;
}

.bar-segment span {
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.bar-segment.available {
  background: var(--color-available);
}

.bar-segment.occupied {
  background: var(--color-occupied);
}

.bar-segment.maintenance {
  background: var(--color-maintenance);
}

.bar-segment.inactive {
  background: #6b7280;
}

.bar-legend {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: #4b5563;
}

.legend-color {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  flex-shrink: 0;
}

.legend-color.available {
  background: var(--color-available);
}

.legend-color.occupied {
  background: var(--color-occupied);
}

.legend-color.maintenance {
  background: var(--color-maintenance);
}

.legend-color.inactive {
  background: #6b7280;
}

/* Activity Card */
.activity-card {
  margin-bottom: 20px;
}

.activity-card ion-card-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--ion-text-color);
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: var(--ion-background-color);
  border-radius: 8px;
  border-left: 3px solid #e5e7eb;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.activity-icon ion-icon {
  font-size: 1.5rem;
  color: white;
}

.activity-icon.started {
  background: #3b82f6;
}

.activity-icon.paused {
  background: var(--color-occupied);
}

.activity-icon.completed {
  background: var(--color-available);
}

.activity-content {
  flex: 1;
  min-width: 0;
}

.activity-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--ion-text-color);
  margin-bottom: 2px;
}

.activity-subtitle {
  font-size: 0.85rem;
  color: var(--ion-color-step-600);
  margin-bottom: 2px;
}

.activity-time {
  font-size: 0.75rem;
  color: var(--ion-color-step-500);
}

/* Machines List Card */
.machines-list-card {
  margin-bottom: 20px;
}

.machines-list-card ion-card-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--ion-text-color);
}

.machines-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.machine-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 12px;
  background: var(--ion-background-color);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 2px solid transparent;
}

.machine-item:active {
  transform: scale(0.98);
  border-color: var(--ion-color-primary);
}

.machine-status-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.machine-status-indicator.available {
  background: var(--color-available);
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.machine-status-indicator.occupied {
  background: var(--color-occupied);
  box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
}

.machine-status-indicator.maintenance {
  background: var(--color-maintenance);
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
}

.machine-status-indicator.inactive {
  background: #6b7280;
  box-shadow: 0 0 0 2px rgba(107, 114, 128, 0.2);
}

.machine-info {
  flex: 1;
  min-width: 0;
}

.machine-name {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--ion-text-color);
  margin-bottom: 2px;
}

.machine-model {
  font-size: 0.8rem;
  color: var(--ion-color-step-600);
}

.machine-item ion-badge {
  font-size: 0.75rem;
  padding: 4px 10px;
}

.chevron {
  font-size: 1.2rem;
  color: var(--ion-color-step-500);
  flex-shrink: 0;
}

/* Quick Actions */
.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.quick-actions ion-button {
  --border-radius: 12px;
  height: 48px;
  font-weight: 600;
}

/* Responsive */
@media (max-width: 480px) {
  .overview-container {
    padding: 12px;
  }

  .summary-info h2 {
    font-size: 2rem;
  }

  .stat-box-number {
    font-size: 1.75rem;
  }

  .bar-legend {
    grid-template-columns: 1fr;
  }
}
</style>
