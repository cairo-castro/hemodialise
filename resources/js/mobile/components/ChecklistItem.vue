<template>
  <div class="checklist-item-container">
    <div class="item-header">
      <h3 class="item-title">{{ title }}</h3>
      <p v-if="description" class="item-description">{{ description }}</p>
    </div>

    <div class="status-selector">
      <div class="status-options">
        <button
          class="status-button conforme"
          :class="{ active: value === 'C' }"
          @click="selectStatus('C')"
          type="button"
        >
          <ion-icon :icon="checkmarkCircleOutline" class="status-icon"></ion-icon>
          <span class="status-label">C</span>
          <span class="status-text">Conforme</span>
        </button>

        <button
          class="status-button nao-conforme"
          :class="{ active: value === 'NC' }"
          @click="selectStatus('NC')"
          type="button"
        >
          <ion-icon :icon="closeCircleOutline" class="status-icon"></ion-icon>
          <span class="status-label">NC</span>
          <span class="status-text">Não Conforme</span>
        </button>

        <button
          class="status-button nao-aplica"
          :class="{ active: value === 'NA' }"
          @click="selectStatus('NA')"
          type="button"
        >
          <ion-icon :icon="removeCircleOutline" class="status-icon"></ion-icon>
          <span class="status-label">NA</span>
          <span class="status-text">Não se Aplica</span>
        </button>
      </div>
    </div>

    <!-- Observação obrigatória para NC -->
    <div v-if="value === 'NC'" class="observation-section">
      <ion-item fill="outline" class="observation-input">
        <ion-label position="floating">Observação Obrigatória *</ion-label>
        <ion-textarea
          :value="observation"
          @ionInput="updateObservation($event.detail.value)"
          rows="2"
          placeholder="Descreva o problema identificado..."
          required
        ></ion-textarea>
      </ion-item>
    </div>
  </div>
</template>

<script setup lang="ts">
import { IonIcon, IonItem, IonLabel, IonTextarea } from '@ionic/vue';
import { checkmarkCircleOutline, closeCircleOutline, removeCircleOutline } from 'ionicons/icons';

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
</script>

<style scoped>
.checklist-item-container {
  background: white;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
  border: 2px solid #e0e0e0;
  transition: all 0.3s ease;
}

.checklist-item-container:has(.status-button.active) {
  border-color: var(--selected-color, #007bff);
  box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15);
}

.item-header {
  margin-bottom: 16px;
}

.item-title {
  font-size: 16px;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 4px 0;
  line-height: 1.4;
}

.item-description {
  font-size: 14px;
  color: #6c757d;
  margin: 0;
  line-height: 1.4;
}

.status-selector {
  margin-bottom: 12px;
}

.status-options {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.status-button {
  flex: 1;
  min-width: 90px;
  padding: 12px 8px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  position: relative;
  overflow: hidden;
}

.status-button:active {
  transform: scale(0.98);
}

.status-icon {
  font-size: 20px;
  transition: all 0.2s ease;
}

.status-label {
  font-weight: bold;
  font-size: 14px;
}

.status-text {
  font-size: 10px;
  opacity: 0.8;
  white-space: nowrap;
}

/* Conforme - Verde */
.status-button.conforme {
  border-color: #28a745;
  color: #28a745;
}

.status-button.conforme.active {
  background: #28a745;
  color: white;
  border-color: #28a745;
  box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.status-button.conforme:hover:not(.active) {
  background: #f8f9fa;
  border-color: #28a745;
}

/* Não Conforme - Vermelho */
.status-button.nao-conforme {
  border-color: #dc3545;
  color: #dc3545;
}

.status-button.nao-conforme.active {
  background: #dc3545;
  color: white;
  border-color: #dc3545;
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

.status-button.nao-conforme:hover:not(.active) {
  background: #f8f9fa;
  border-color: #dc3545;
}

/* Não se Aplica - Cinza */
.status-button.nao-aplica {
  border-color: #6c757d;
  color: #6c757d;
}

.status-button.nao-aplica.active {
  background: #6c757d;
  color: white;
  border-color: #6c757d;
  box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
}

.status-button.nao-aplica:hover:not(.active) {
  background: #f8f9fa;
  border-color: #6c757d;
}

.observation-section {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e0e0e0;
}

.observation-input {
  --border-radius: 8px;
  --border-color: #dc3545;
  --color: #2c3e50;
}

.observation-input ion-textarea {
  --color: #2c3e50;
}

/* Animação para seleção */
.status-button::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  transition: all 0.3s ease;
}

.status-button.active::before {
  width: 100%;
  height: 100%;
}

/* Responsividade */
@media (max-width: 480px) {
  .status-options {
    flex-direction: column;
  }

  .status-button {
    flex-direction: row;
    justify-content: flex-start;
    padding: 10px 12px;
    gap: 8px;
  }

  .status-text {
    font-size: 12px;
  }
}

/* Cores CSS custom properties para container */
.checklist-item-container:has(.conforme.active) {
  --selected-color: #28a745;
}

.checklist-item-container:has(.nao-conforme.active) {
  --selected-color: #dc3545;
}

.checklist-item-container:has(.nao-aplica.active) {
  --selected-color: #6c757d;
}
</style>