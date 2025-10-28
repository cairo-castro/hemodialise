<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/dashboard"></ion-back-button>
        </ion-buttons>
        <ion-title>Controle de Limpeza</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="cleaning-content">
      <!-- Pull to refresh -->
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <div class="content-wrapper">
        <!-- Hero Section -->
        <div class="hero-section">
          <ion-icon :icon="sparklesOutline" class="hero-icon"></ion-icon>
          <h1>Controle de Limpeza</h1>
          <p>Registre a limpeza e desinfecção das máquinas de hemodiálise</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon-wrapper today">
              <ion-icon :icon="todayOutline"></ion-icon>
            </div>
            <div class="stat-content">
              <div class="stat-number">{{ todayCount }}</div>
              <div class="stat-label">Registros Hoje</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon-wrapper month">
              <ion-icon :icon="calendarOutline"></ion-icon>
            </div>
            <div class="stat-content">
              <div class="stat-number">{{ cleaningCount }}</div>
              <div class="stat-label">Total do Mês</div>
            </div>
          </div>
        </div>

        <!-- Main Action Button -->
        <ion-button
          expand="block"
          size="large"
          @click="router.push('/cleaning-checklist/new')"
          class="new-checklist-button"
        >
          <ion-icon :icon="addCircleOutline" slot="start"></ion-icon>
          Novo Registro de Limpeza
        </ion-button>

        <!-- Quick Info Cards -->
        <div class="info-section">
          <h2>O que registrar?</h2>

          <ion-card class="info-card">
            <ion-card-content>
              <div class="info-header">
                <ion-icon :icon="flaskOutline" color="primary"></ion-icon>
                <h3>Desinfecção Química</h3>
              </div>
              <p>Registre o horário da desinfecção química realizada na máquina.</p>
            </ion-card-content>
          </ion-card>

          <ion-card class="info-card">
            <ion-card-content>
              <div class="info-header">
                <ion-icon :icon="waterOutline" color="primary"></ion-icon>
                <h3>Limpeza de Superfície</h3>
              </div>
              <p>Marque a conformidade da limpeza da máquina, osmose e suporte de soro.</p>
            </ion-card-content>
          </ion-card>

          <ion-card class="info-card">
            <ion-card-content>
              <div class="info-header">
                <ion-icon :icon="timeOutline" color="primary"></ion-icon>
                <h3>Por Turno</h3>
              </div>
              <p>Registre por turno (1º, 2º, 3º ou 4º) e por máquina específica.</p>
            </ion-card-content>
          </ion-card>
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
  IonRefresher,
  IonRefresherContent,
  IonCard,
  IonCardContent,
} from '@ionic/vue';
import {
  sparklesOutline,
  addCircleOutline,
  waterOutline,
  flaskOutline,
  todayOutline,
  calendarOutline,
  timeOutline,
} from 'ionicons/icons';
import { Container } from '../core/di/Container';
import { useStatsAutoRefresh } from '@mobile/composables/useStatsAutoRefresh';

const router = useRouter();
const container = Container.getInstance();
const getStatsUseCase = container.getGetCleaningChecklistStatsUseCase();

// State
const stats = ref({
  total_today: 0,
  total_this_month: 0,
  chemical_disinfection_today: 0,
  surface_cleaning_today: 0,
});

// Computed
const cleaningCount = computed(() => stats.value.total_this_month);
const todayCount = computed(() => stats.value.total_today);

// Methods
const loadStats = async () => {
  try {
    stats.value = await getStatsUseCase.execute();
  } catch (error) {
    console.error('Error loading stats:', error);
  }
};

// Auto-refresh dos stats
const { forceRefresh: forceStatsRefresh } = useStatsAutoRefresh(loadStats, {
  loadOnMount: false,
  interval: 15000,
  onStatsUpdated: () => console.log('[CleaningControls] Stats updated')
});

const handleRefresh = async (event: any) => {
  await forceStatsRefresh();
  event.target.complete();
};

onMounted(() => {
  loadStats();
});
</script>

<style scoped>
.cleaning-content {
  --background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
}

.content-wrapper {
  padding: 0;
  max-width: 600px;
  margin: 0 auto;
}

/* Hero Section */
.hero-section {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  color: white;
  padding: 2.5rem 1.5rem;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.hero-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  animation: sparkle 2s ease-in-out infinite;
}

@keyframes sparkle {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.1); opacity: 0.8; }
}

.hero-section h1 {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0 0 0.5rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.hero-section p {
  font-size: 1rem;
  opacity: 0.95;
  margin: 0;
  line-height: 1.5;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  padding: 1.5rem;
  margin-top: -1rem;
  background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-left: 4px solid transparent;
}

.stat-card:active {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.stat-card:nth-child(1) {
  border-left-color: #10b981;
}

.stat-card:nth-child(2) {
  border-left-color: #3b82f6;
}

.stat-icon-wrapper {
  flex-shrink: 0;
}

.stat-icon-wrapper ion-icon {
  font-size: 2.5rem;
}

.stat-icon-wrapper.today ion-icon {
  color: #10b981;
}

.stat-icon-wrapper.month ion-icon {
  color: #3b82f6;
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  line-height: 1.2;
}

/* Main Action Button */
.new-checklist-button {
  margin: 0 1.5rem 2rem;
  height: 56px;
  font-size: 1.1rem;
  font-weight: 600;
  --border-radius: 14px;
  --box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

/* Info Section */
.info-section {
  padding: 0 1.5rem 2rem;
}

.info-section h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--ion-color-dark);
  margin: 0 0 1rem;
}

.info-card {
  margin-bottom: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border-radius: 12px;
}

.info-card ion-card-content {
  padding: 1.25rem;
}

.info-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.info-header ion-icon {
  font-size: 1.5rem;
}

.info-header h3 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--ion-color-dark);
  margin: 0;
}

.info-card p {
  font-size: 0.9rem;
  color: var(--ion-color-medium);
  margin: 0;
  line-height: 1.5;
}
</style>
