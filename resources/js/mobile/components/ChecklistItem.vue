<template>
  <div class="checklist-item-card" :class="{ 
    'has-value': value !== null,
    'is-conforme': value === 'C',
    'is-nao-conforme': value === 'NC',
    'is-nao-aplica': value === 'NA'
  }">
    
    <!-- Header com status visual -->
    <div class="item-card-header">
      <div class="header-content">
        <h3 class="item-card-title">{{ title }}</h3>
        <p v-if="description" class="item-card-description">{{ description }}</p>
      </div>
      <div class="header-badge" v-if="value">
        <div class="status-badge" :class="value.toLowerCase()">
          <ion-icon :icon="getStatusIcon(value)"></ion-icon>
        </div>
      </div>
    </div>

    <!-- Action Buttons Style Dashboard -->
    <div class="status-actions">
      <button
        class="action-status-btn conforme"
        :class="{ selected: value === 'C' }"
        @click="selectStatus('C')"
        type="button"
      >
        <div class="action-status-icon success">
          <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
        </div>
        <div class="action-status-content">
          <span class="action-status-label">Conforme</span>
          <span class="action-status-hint">Tudo OK</span>
        </div>
        <ion-icon v-if="value === 'C'" :icon="checkmarkOutline" class="selected-check"></ion-icon>
      </button>

      <button
        class="action-status-btn nao-conforme"
        :class="{ selected: value === 'NC' }"
        @click="selectStatus('NC')"
        type="button"
      >
        <div class="action-status-icon danger">
          <ion-icon :icon="closeCircleOutline"></ion-icon>
        </div>
        <div class="action-status-content">
          <span class="action-status-label">Não Conforme</span>
          <span class="action-status-hint">Problema</span>
        </div>
        <ion-icon v-if="value === 'NC'" :icon="checkmarkOutline" class="selected-check"></ion-icon>
      </button>

      <button
        class="action-status-btn nao-aplica"
        :class="{ selected: value === 'NA' }"
        @click="selectStatus('NA')"
        type="button"
      >
        <div class="action-status-icon neutral">
          <ion-icon :icon="removeCircleOutline"></ion-icon>
        </div>
        <div class="action-status-content">
          <span class="action-status-label">Não Aplica</span>
          <span class="action-status-hint">N/A</span>
        </div>
        <ion-icon v-if="value === 'NA'" :icon="checkmarkOutline" class="selected-check"></ion-icon>
      </button>
    </div>

    <!-- Observação obrigatória para NC -->
    <div v-if="value === 'NC'" class="observation-card">
      <label class="observation-label">
        <ion-icon :icon="alertCircleOutline"></ion-icon>
        Descreva o Problema *
      </label>
      <textarea
        :value="observation"
        @input="updateObservation(($event.target as HTMLTextAreaElement).value)"
        class="observation-textarea"
        rows="3"
        placeholder="Ex: Equipamento apresentando ruído anormal..."
        required
      ></textarea>
    </div>
  </div>
</template>

<script setup lang="ts">
import { IonIcon } from '@ionic/vue';
import { 
  checkmarkCircleOutline, 
  closeCircleOutline, 
  removeCircleOutline,
  checkmarkOutline,
  alertCircleOutline
} from 'ionicons/icons';

interface Props {
  title: string;
  description?: string;
  value?: 'C' | 'NC' | 'NA' | null;
  observation?: string;
  required?: boolean;
}

interface Emits {
  (e: 'update:value', value: 'C' | 'NC' | 'NA' | null): void;
  (e: 'update:observation', observation: string): void;
}

const props = withDefaults(defineProps<Props>(), {
  description: '',
  value: null,
  observation: '',
  required: false
});

const emit = defineEmits<Emits>();

const selectStatus = (status: 'C' | 'NC' | 'NA') => {
  if (props.value === status) {
    // Se clicar no mesmo status, desmarca
    emit('update:value', null);
    emit('update:observation', '');
  } else {
    emit('update:value', status);
    // Limpa observação se não for NC
    if (status !== 'NC') {
      emit('update:observation', '');
    }
  }
};

const updateObservation = (value: string) => {
  emit('update:observation', value);
};

const getStatusIcon = (status: 'C' | 'NC' | 'NA' | null) => {
  switch (status) {
    case 'C':
      return checkmarkCircleOutline;
    case 'NC':
      return closeCircleOutline;
    case 'NA':
      return removeCircleOutline;
    default:
      return checkmarkCircleOutline;
  }
};
</script>

<style scoped>
/* ======================================
   Checklist Item Card - Dashboard Style
   ====================================== */

.checklist-item-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 16px;
  border: 2px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.checklist-item-card.has-value {
  border-color: #d1d5db;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.checklist-item-card.is-conforme {
  border-color: #10b981;
  background: linear-gradient(to bottom, white 0%, #ecfdf5 100%);
}

.checklist-item-card.is-nao-conforme {
  border-color: #ef4444;
  background: linear-gradient(to bottom, white 0%, #fef2f2 100%);
}

.checklist-item-card.is-nao-aplica {
  border-color: #6b7280;
  background: linear-gradient(to bottom, white 0%, #f9fafb 100%);
}

/* Header Section */
.item-card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.header-content {
  flex: 1;
  margin-right: 12px;
}

.item-card-title {
  font-size: 17px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 6px 0;
  line-height: 1.4;
}

.item-card-description {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
  line-height: 1.5;
}

.header-badge {
  flex-shrink: 0;
}

.status-badge {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  animation: badgePop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes badgePop {
  0% { transform: scale(0); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

.status-badge.c {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.status-badge.nc {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.status-badge.na {
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
  color: white;
}

/* Status Actions Grid */
.status-actions {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 0;
}

@media (max-width: 768px) {
  .status-actions {
    grid-template-columns: 1fr;
  }
}

.action-status-btn {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 14px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 14px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  text-align: left;
}

.action-status-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.8) 0%, transparent 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.action-status-btn:hover::before {
  opacity: 1;
}

.action-status-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
  border-color: #d1d5db;
}

.action-status-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Selected States */
.action-status-btn.conforme.selected {
  border-color: #10b981;
  background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
}

.action-status-btn.nao-conforme.selected {
  border-color: #ef4444;
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
}

.action-status-btn.nao-aplica.selected {
  border-color: #6b7280;
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  box-shadow: 0 4px 12px rgba(107, 114, 128, 0.25);
}

/* Icon Box */
.action-status-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.action-status-icon.success {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #059669;
}

.action-status-btn.conforme.selected .action-status-icon.success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  transform: scale(1.1);
}

.action-status-icon.danger {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  color: #dc2626;
}

.action-status-btn.nao-conforme.selected .action-status-icon.danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  transform: scale(1.1);
}

.action-status-icon.neutral {
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  color: #4b5563;
}

.action-status-btn.nao-aplica.selected .action-status-icon.neutral {
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
  color: white;
  transform: scale(1.1);
}

/* Content */
.action-status-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.action-status-label {
  font-size: 15px;
  font-weight: 600;
  color: #111827;
  line-height: 1.3;
}

.action-status-hint {
  font-size: 12px;
  color: #9ca3af;
  font-weight: 500;
}

.action-status-btn.selected .action-status-label {
  color: #111827;
}

.action-status-btn.conforme.selected .action-status-hint {
  color: #059669;
}

.action-status-btn.nao-conforme.selected .action-status-hint {
  color: #dc2626;
}

.action-status-btn.nao-aplica.selected .action-status-hint {
  color: #4b5563;
}

/* Selected Check Icon */
.selected-check {
  font-size: 24px;
  flex-shrink: 0;
  animation: checkPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes checkPop {
  0% { transform: scale(0) rotate(-180deg); opacity: 0; }
  100% { transform: scale(1) rotate(0deg); opacity: 1; }
}

.action-status-btn.conforme.selected .selected-check {
  color: #059669;
}

.action-status-btn.nao-conforme.selected .selected-check {
  color: #dc2626;
}

.action-status-btn.nao-aplica.selected .selected-check {
  color: #4b5563;
}

/* Observation Card (NC Only) */
.observation-card {
  margin-top: 16px;
  padding: 16px;
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  border: 2px solid #ef4444;
  border-radius: 12px;
  animation: slideDown 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.observation-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #dc2626;
  margin-bottom: 12px;
}

.observation-label ion-icon {
  font-size: 20px;
}

.observation-textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #fca5a5;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  line-height: 1.5;
  background: white;
  color: #111827;
  resize: vertical;
  transition: all 0.3s ease;
}

.observation-textarea:focus {
  outline: none;
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.observation-textarea::placeholder {
  color: #9ca3af;
  font-style: italic;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .checklist-item-card {
    padding: 16px;
  }

  .item-card-title {
    font-size: 16px;
  }

  .action-status-btn {
    padding: 14px;
  }

  .action-status-icon {
    width: 44px;
    height: 44px;
    font-size: 22px;
  }
}
</style>