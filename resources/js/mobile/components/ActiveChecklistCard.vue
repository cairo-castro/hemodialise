<template>
  <div class="checklist-card" :class="[statusClass, { paused: checklist.paused_at }]">
    <div class="card-header">
      <div class="machine-info">
        <h3>{{ checklist.machine.name }}</h3>
        <p class="machine-id">{{ checklist.machine.identifier }}</p>
      </div>
      <ion-badge :color="badgeColor" class="phase-badge">
        {{ phaseTitle }}
      </ion-badge>
    </div>

    <div class="patient-info">
      <ion-icon :icon="personOutline" class="patient-icon"></ion-icon>
      <span class="patient-name">{{ checklist.patient.name }}</span>
    </div>

    <div class="user-info">
      <ion-icon :icon="personCircleOutline" class="user-icon"></ion-icon>
      <span class="user-label">Técnico:</span>
      <span class="user-name">{{ checklist.user.name }}</span>
    </div>

    <div class="progress-section">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
      </div>
      <span class="progress-text">{{ progressPercentage }}% completo</span>
    </div>

    <div class="time-info">
      <div class="time-item">
        <ion-icon :icon="timeOutline" class="time-icon"></ion-icon>
        <span class="time-label">Iniciado há</span>
        <span class="time-value">{{ timeElapsed }}</span>
      </div>
      <div class="time-item" v-if="checklist.paused_at">
        <ion-icon :icon="pauseOutline" class="time-icon paused"></ion-icon>
        <span class="time-label">Pausado há</span>
        <span class="time-value">{{ timePaused }}</span>
      </div>
    </div>

    <div class="actions">
      <ion-button
        v-if="!checklist.paused_at"
        fill="outline"
        size="small"
        @click="$emit('continue', checklist)"
        class="action-button continue"
      >
        <ion-icon :icon="playOutline" slot="start"></ion-icon>
        Continuar
      </ion-button>

      <ion-button
        v-if="checklist.paused_at"
        fill="solid"
        size="small"
        @click="$emit('resume', checklist)"
        class="action-button resume"
        color="success"
      >
        <ion-icon :icon="playOutline" slot="start"></ion-icon>
        Retomar
      </ion-button>

      <ion-button
        v-if="!checklist.paused_at"
        fill="outline"
        size="small"
        @click="$emit('pause', checklist)"
        class="action-button pause"
        color="warning"
      >
        <ion-icon :icon="pauseOutline" slot="start"></ion-icon>
        Pausar
      </ion-button>

      <ion-button
        fill="clear"
        size="small"
        @click="showDetails"
        class="action-button details"
      >
        <ion-icon :icon="informationCircleOutline" slot="icon-only"></ion-icon>
      </ion-button>
    </div>

    <div class="status-indicator" :class="statusClass"></div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { IonButton, IonIcon, IonBadge, alertController } from '@ionic/vue';
import {
  personOutline,
  personCircleOutline,
  timeOutline,
  pauseOutline,
  playOutline,
  informationCircleOutline
} from 'ionicons/icons';

interface Props {
  checklist: {
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
    user: {
      id: number;
      name: string;
      email: string;
    };
    created_at: string;
    paused_at?: string;
    phase_completion?: number;
  };
}

interface Emits {
  (e: 'continue', checklist: Props['checklist']): void;
  (e: 'pause', checklist: Props['checklist']): void;
  (e: 'resume', checklist: Props['checklist']): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const phaseTitle = computed(() => {
  const phases = {
    'pre_dialysis': 'Pré-Diálise',
    'during_session': 'Durante Sessão',
    'post_dialysis': 'Pós-Diálise',
    'completed': 'Concluído',
    'interrupted': 'Interrompido'
  };
  return phases[props.checklist.current_phase] || props.checklist.current_phase;
});

const badgeColor = computed(() => {
  const colors = {
    'pre_dialysis': 'primary',
    'during_session': 'warning',
    'post_dialysis': 'secondary',
    'completed': 'success',
    'interrupted': 'danger'
  };
  return colors[props.checklist.current_phase] || 'medium';
});

const statusClass = computed(() => {
  return props.checklist.current_phase.replace('_', '-');
});

const progressPercentage = computed(() => {
  if (props.checklist.phase_completion !== undefined) {
    return Math.round(props.checklist.phase_completion);
  }

  // Fallback calculation based on phase
  const phaseProgress = {
    'pre_dialysis': 25,
    'during_session': 50,
    'post_dialysis': 75,
    'completed': 100,
    'interrupted': 0
  };
  return phaseProgress[props.checklist.current_phase] || 0;
});

const timeElapsed = computed(() => {
  const startTime = new Date(props.checklist.created_at);
  const now = new Date();
  const diff = now.getTime() - startTime.getTime();

  const hours = Math.floor(diff / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

  if (hours > 0) {
    return `${hours}h ${minutes}m`;
  }
  return `${minutes}m`;
});

const timePaused = computed(() => {
  if (!props.checklist.paused_at) return '';

  const pauseTime = new Date(props.checklist.paused_at);
  const now = new Date();
  const diff = now.getTime() - pauseTime.getTime();

  const hours = Math.floor(diff / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

  if (hours > 0) {
    return `${hours}h ${minutes}m`;
  }
  return `${minutes}m`;
});

const showDetails = async () => {
  const alert = await alertController.create({
    header: 'Detalhes do Checklist',
    subHeader: `${props.checklist.machine.name} - ${props.checklist.patient.name}`,
    message: `
      <strong>Técnico:</strong> ${props.checklist.user.name}<br>
      <strong>Fase:</strong> ${phaseTitle.value}<br>
      <strong>Progresso:</strong> ${progressPercentage.value}%<br>
      <strong>Iniciado:</strong> ${new Date(props.checklist.created_at).toLocaleString('pt-BR')}<br>
      ${props.checklist.paused_at ? `<strong>Pausado:</strong> ${new Date(props.checklist.paused_at).toLocaleString('pt-BR')}<br>` : ''}
    `,
    buttons: ['Fechar']
  });

  await alert.present();
};
</script>

<style scoped>
.checklist-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-left: 5px solid #007bff;
  position: relative;
  transition: all 0.3s ease;
}

.checklist-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.checklist-card.paused {
  opacity: 0.8;
  border-left-color: #ffc107;
}

.checklist-card.pre-dialysis {
  border-left-color: #007bff;
}

.checklist-card.during-session {
  border-left-color: #ffc107;
}

.checklist-card.post-dialysis {
  border-left-color: #6f42c1;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.machine-info h3 {
  margin: 0 0 4px 0;
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
}

.machine-id {
  margin: 0;
  font-size: 14px;
  color: #6c757d;
  font-family: 'Courier New', monospace;
}

.phase-badge {
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 20px;
  font-weight: 600;
  margin-top: 8px;
  align-self: flex-start;
}

.patient-info {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  padding: 8px 12px;
  background: #f8f9fa;
  border-radius: 8px;
}

.patient-icon {
  font-size: 16px;
  color: #6c757d;
}

.patient-name {
  font-size: 14px;
  font-weight: 500;
  color: #495057;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 16px;
  padding: 6px 12px;
  background: #e7f3ff;
  border-radius: 8px;
  border-left: 3px solid #007bff;
}

.user-icon {
  font-size: 18px;
  color: #007bff;
  flex-shrink: 0;
}

.user-label {
  font-size: 12px;
  color: #6c757d;
  font-weight: 500;
}

.user-name {
  font-size: 13px;
  font-weight: 600;
  color: #007bff;
}

.progress-section {
  margin-bottom: 16px;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 4px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #28a745, #20c997);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 12px;
  color: #6c757d;
  font-weight: 500;
}

.time-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
}

.time-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
}

.time-icon {
  font-size: 14px;
  color: #6c757d;
}

.time-icon.paused {
  color: #ffc107;
}

.time-label {
  color: #6c757d;
  min-width: 70px;
}

.time-value {
  font-weight: 600;
  color: #495057;
}

.actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.action-button {
  --padding-start: 12px;
  --padding-end: 12px;
  height: 32px;
  font-size: 12px;
  font-weight: 600;
}

.action-button.continue {
  --color: #007bff;
  --border-color: #007bff;
}

.action-button.resume {
  flex: 1;
}

.action-button.pause {
  flex: 1;
}

.action-button.details {
  --color: #6c757d;
  min-width: 32px;
}

.status-indicator {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #28a745;
  box-shadow: 0 0 0 2px white, 0 0 6px rgba(40, 167, 69, 0.3);
  z-index: 1;
}

.status-indicator.paused {
  background: #ffc107;
  box-shadow: 0 0 0 2px white, 0 0 6px rgba(255, 193, 7, 0.3);
}

/* Responsive adjustments */
@media (max-width: 480px) {
  .checklist-card {
    padding: 16px;
  }

  .card-header {
    margin-bottom: 12px;
  }

  .machine-info h3 {
    font-size: 16px;
  }

  .actions {
    gap: 6px;
  }

  .action-button {
    font-size: 11px;
    height: 28px;
  }
}
</style>