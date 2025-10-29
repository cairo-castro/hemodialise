<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-title>Hemodiálise Mobile</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="toggleDarkMode" fill="clear">
            <ion-icon :icon="isDarkMode ? sunnyOutline : moonOutline" slot="icon-only"></ion-icon>
          </ion-button>
          <ion-button @click="handleLogout" fill="clear">
            <ion-icon :icon="logOutOutline" slot="icon-only"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <div class="container">
        <!-- Welcome Card using AppCard component with gradient variant -->
        <AppCard variant="gradient-primary" class="welcome-section mx-md mt-md">
          <div class="welcome-header">
            <h1 @click="navigateToProfile" class="welcome-title user-name-link">
              Olá, {{ user?.name.split(' ')[0] }}! 👋
            </h1>
          </div>

          <!-- Unit Display (keep existing logic) -->
          <div class="unit-selector">
            <div v-if="availableUnits.length <= 1" class="unit-info-static">
              <ion-icon :icon="locationOutline" class="unit-icon-static"></ion-icon>
              <div class="unit-text-static">
                <span class="unit-label-static">Unidade</span>
                <span class="unit-name-static">{{ currentUnit?.name || user?.unit?.name }}</span>
              </div>
            </div>

            <div v-else class="unit-info-interactive" @click="openUnitSelector">
              <ion-icon :icon="locationOutline" class="unit-icon-static"></ion-icon>
              <div class="unit-text-static flex-1">
                <span class="unit-label-static">Unidade Atual</span>
                <span class="unit-name-static">{{ currentUnit?.name }}</span>
              </div>
              <ion-button fill="clear" size="small">
                <ion-icon slot="icon-only" :icon="swapHorizontalOutline"></ion-icon>
              </ion-button>
            </div>
          </div>
        </AppCard>

        <!-- Stats Grid using StatCard components -->
        <div v-if="statsLoaded" class="stats-grid px-md mt-md">
          <!-- Update badge -->
          <div v-if="statsHasUpdates || isStatsRefreshing" class="stats-update-badge">
            <ion-chip color="success" class="update-chip">
              <ion-icon
                :icon="isStatsRefreshing ? syncOutline : checkmarkCircleOutline"
                :class="{ 'spin': isStatsRefreshing }"
              ></ion-icon>
              <ion-label>{{ isStatsRefreshing ? 'Atualizando...' : 'Atualizado' }}</ion-label>
            </ion-chip>
          </div>

          <StatCard
            variant="available"
            :value="availableMachinesCount"
            label="Disponíveis"
            :icon="medicalSharp"
            @click="showMachineStatus"
          />

          <StatCard
            variant="active"
            :value="activeChecklistsCount"
            label="Em Andamento"
            :icon="timeOutline"
            @click="showActiveChecklists"
          />

          <StatCard
            variant="total"
            :value="totalMachines"
            label="Total Máquinas"
            :icon="medicalOutline"
            @click="showMachinesOverview"
          />
        </div>

        <!-- Primary Action using ActionButton component -->
        <div class="px-md mt-lg">
          <ActionButton
            title="Novo Checklist"
            subtitle="Iniciar verificação de segurança"
            :icon="clipboardOutline"
            :badge="availableMachinesCount > 0 ? availableMachinesCount : undefined"
            @click="navigateToChecklist"
          />
        </div>

        <!-- Quick Actions using ActionCard components -->
        <div class="section">
          <h2 class="section-title">Acesso Rápido</h2>

          <div class="flex-col gap-sm">
            <ActionCard
              v-if="activeChecklistsCount > 0"
              title="Checklists Ativos"
              :subtitle="`${activeChecklistsCount} em andamento`"
              :icon="timeOutline"
              icon-variant="warning"
              @click="showActiveChecklists"
            />

            <ActionCard
              title="Máquinas"
              subtitle="Status e controle"
              :icon="medicalSharp"
              icon-variant="tertiary"
              @click="showMachineStatus"
            />

            <ActionCard
              title="Controle de Limpeza"
              subtitle="Registrar limpezas"
              :icon="sparklesOutline"
              icon-variant="cleaning"
              @click="navigateToCleaningControls"
            />

            <ActionCard
              title="Pacientes"
              subtitle="Gerenciar pacientes"
              :icon="peopleOutline"
              icon-variant="primary"
              @click="navigateToPatients"
            />
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
  IonButton,
  IonIcon,
  IonRefresher,
  IonRefresherContent,
  IonChip,
  IonLabel,
} from '@ionic/vue';
import {
  logOutOutline,
  moonOutline,
  sunnyOutline,
  locationOutline,
  swapHorizontalOutline,
  medicalSharp,
  medicalOutline,
  timeOutline,
  clipboardOutline,
  sparklesOutline,
  peopleOutline,
  syncOutline,
  checkmarkCircleOutline,
} from 'ionicons/icons';

// Import shared components
import { AppCard, StatCard, ActionButton, ActionCard } from '@/components/shared';

// Import composables and services
import { useDarkMode } from '@/composables/useDarkMode';
import { useStatsAutoRefresh } from '@/composables/useStatsAutoRefresh';
// ... other imports

const { isDarkMode, toggleDarkMode } = useDarkMode();
const { statsHasUpdates, isStatsRefreshing } = useStatsAutoRefresh();

// ... rest of the script logic (unchanged)
</script>

<style scoped>
/**
 * REFACTORED STYLES
 *
 * Before: ~1370 lines of CSS
 * After: ~50 lines of CSS (96% reduction!)
 *
 * All component styles moved to:
 * - theme/components/cards.css
 * - theme/components/buttons.css
 * - theme/utilities/*.css
 */

/* Container layout */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--spacing-sm);
  position: relative;
}

.stats-update-badge {
  position: absolute;
  top: -8px;
  right: 0;
  z-index: 10;
}

.update-chip {
  font-size: var(--text-sm);
  height: 28px;
  animation: slideIn var(--duration-slow) var(--ease-out);
}

/* Welcome section specific layout */
.welcome-section {
  padding: var(--spacing-lg);
}

.welcome-header {
  margin-bottom: var(--spacing-sm);
}

.welcome-title {
  font-size: var(--text-3xl);
  font-weight: var(--font-bold);
  margin: 0;
  color: white;
}

.user-name-link {
  cursor: pointer;
  transition: var(--transition-base);
  display: inline-block;
}

.user-name-link:hover {
  color: rgba(255, 255, 255, 0.9);
  transform: translateX(2px);
}

.user-name-link:active {
  transform: scale(0.98);
}

/* That's it! All other styles come from the theme system */
</style>
