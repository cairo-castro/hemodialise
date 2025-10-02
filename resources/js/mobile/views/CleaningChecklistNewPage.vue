<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/cleaning-controls"></ion-back-button>
        </ion-buttons>
        <ion-title>Checklist de Limpeza</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="cleaning-checklist-content">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
      </div>

      <div class="form-container">
        <!-- Step 1: Basic Info -->
        <div class="step-card">
          <div class="step-header">
            <div class="step-number">1</div>
            <h2>Informações Básicas</h2>
          </div>

          <div class="quick-selectors">
            <!-- Shift Quick Select -->
            <div class="quick-group">
              <label class="quick-label">Turno</label>
              <div class="shift-buttons">
                <button
                  v-for="shift in ['1', '2', '3', '4']"
                  :key="shift"
                  @click="formData.shift = shift"
                  :class="['shift-btn', { active: formData.shift === shift }]"
                >
                  {{ shift }}º
                </button>
              </div>
            </div>

            <!-- Machine Select -->
            <div class="quick-group">
              <label class="quick-label">Máquina</label>
              <ion-select
                v-model="formData.machine_id"
                placeholder="Selecione"
                interface="action-sheet"
                class="machine-select"
              >
                <ion-select-option v-for="machine in machines" :key="machine.id" :value="machine.id">
                  {{ machine.name }}
                </ion-select-option>
              </ion-select>
            </div>

            <!-- Date -->
            <div class="quick-group">
              <label class="quick-label">Data</label>
              <ion-datetime-button datetime="checklist-date" class="date-btn"></ion-datetime-button>
              <ion-modal :keep-contents-mounted="true">
                <ion-datetime
                  id="checklist-date"
                  v-model="formData.checklist_date"
                  presentation="date"
                  :max="maxDate"
                ></ion-datetime>
              </ion-modal>
            </div>
          </div>
        </div>

        <!-- Step 2: Chemical Disinfection -->
        <div class="step-card">
          <div class="step-header">
            <div class="step-number">2</div>
            <h2>Desinfecção Química</h2>
          </div>

          <div class="toggle-card" @click="formData.chemical_disinfection_completed = !formData.chemical_disinfection_completed">
            <div class="toggle-content">
              <ion-icon :icon="flaskOutline" class="toggle-icon"></ion-icon>
              <div class="toggle-text">
                <span class="toggle-title">Desinfecção Realizada?</span>
                <span class="toggle-hint">Toque para {{ formData.chemical_disinfection_completed ? 'desmarcar' : 'marcar' }}</span>
              </div>
            </div>
            <ion-toggle v-model="formData.chemical_disinfection_completed" @click.stop></ion-toggle>
          </div>

          <div v-if="formData.chemical_disinfection_completed" class="time-selector">
            <label class="quick-label">Horário da Desinfecção</label>
            <ion-datetime-button datetime="disinfection-time" class="time-btn"></ion-datetime-button>
            <ion-modal :keep-contents-mounted="true">
              <ion-datetime
                id="disinfection-time"
                v-model="formData.chemical_disinfection_time"
                presentation="time"
              ></ion-datetime>
            </ion-modal>
          </div>
        </div>

        <!-- Step 3: Surface Cleaning -->
        <div class="step-card">
          <div class="step-header">
            <div class="step-number">3</div>
            <div class="step-header-text">
              <h2>Limpeza de Superfície</h2>
              <p class="step-legend">Marque a conformidade de cada item</p>
            </div>
          </div>

          <div class="legend-pills">
            <div class="legend-pill conforme">
              <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
              <span>C - Conforme</span>
            </div>
            <div class="legend-pill nao-conforme">
              <ion-icon :icon="closeCircleOutline"></ion-icon>
              <span>NC - Não Conforme</span>
            </div>
            <div class="legend-pill nao-aplica">
              <ion-icon :icon="removeCircleOutline"></ion-icon>
              <span>NA - Não se Aplica</span>
            </div>
          </div>

          <!-- Cleaning Items -->
          <div class="cleaning-items">
            <!-- HD Machine -->
            <div class="cleaning-item-card">
              <div class="item-header">
                <ion-icon :icon="hardwareChipOutline" class="item-icon hd"></ion-icon>
                <span class="item-name">Máquina de HD</span>
              </div>
              <div class="conformity-buttons">
                <button
                  @click="formData.hd_machine_cleaning = 'C'"
                  :class="['conf-btn', 'conforme', { active: formData.hd_machine_cleaning === 'C' }]"
                >
                  <ion-icon :icon="checkmarkOutline"></ion-icon>
                  <span>C</span>
                </button>
                <button
                  @click="formData.hd_machine_cleaning = 'NC'"
                  :class="['conf-btn', 'nao-conforme', { active: formData.hd_machine_cleaning === 'NC' }]"
                >
                  <ion-icon :icon="closeOutline"></ion-icon>
                  <span>NC</span>
                </button>
                <button
                  @click="formData.hd_machine_cleaning = 'NA'"
                  :class="['conf-btn', 'nao-aplica', { active: formData.hd_machine_cleaning === 'NA' }]"
                >
                  <ion-icon :icon="removeOutline"></ion-icon>
                  <span>NA</span>
                </button>
              </div>
            </div>

            <!-- Osmose -->
            <div class="cleaning-item-card">
              <div class="item-header">
                <ion-icon :icon="waterOutline" class="item-icon osmose"></ion-icon>
                <span class="item-name">Osmose</span>
              </div>
              <div class="conformity-buttons">
                <button
                  @click="formData.osmosis_cleaning = 'C'"
                  :class="['conf-btn', 'conforme', { active: formData.osmosis_cleaning === 'C' }]"
                >
                  <ion-icon :icon="checkmarkOutline"></ion-icon>
                  <span>C</span>
                </button>
                <button
                  @click="formData.osmosis_cleaning = 'NC'"
                  :class="['conf-btn', 'nao-conforme', { active: formData.osmosis_cleaning === 'NC' }]"
                >
                  <ion-icon :icon="closeOutline"></ion-icon>
                  <span>NC</span>
                </button>
                <button
                  @click="formData.osmosis_cleaning = 'NA'"
                  :class="['conf-btn', 'nao-aplica', { active: formData.osmosis_cleaning === 'NA' }]"
                >
                  <ion-icon :icon="removeOutline"></ion-icon>
                  <span>NA</span>
                </button>
              </div>
            </div>

            <!-- Suporte de Soro -->
            <div class="cleaning-item-card">
              <div class="item-header">
                <ion-icon :icon="medicalOutline" class="item-icon soro"></ion-icon>
                <span class="item-name">Suporte de Soro</span>
              </div>
              <div class="conformity-buttons">
                <button
                  @click="formData.serum_support_cleaning = 'C'"
                  :class="['conf-btn', 'conforme', { active: formData.serum_support_cleaning === 'C' }]"
                >
                  <ion-icon :icon="checkmarkOutline"></ion-icon>
                  <span>C</span>
                </button>
                <button
                  @click="formData.serum_support_cleaning = 'NC'"
                  :class="['conf-btn', 'nao-conforme', { active: formData.serum_support_cleaning === 'NC' }]"
                >
                  <ion-icon :icon="closeOutline"></ion-icon>
                  <span>NC</span>
                </button>
                <button
                  @click="formData.serum_support_cleaning = 'NA'"
                  :class="['conf-btn', 'nao-aplica', { active: formData.serum_support_cleaning === 'NA' }]"
                >
                  <ion-icon :icon="removeOutline"></ion-icon>
                  <span>NA</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 4: Observations (Optional) -->
        <div class="step-card optional">
          <div class="step-header">
            <div class="step-number optional">4</div>
            <div class="step-header-text">
              <h2>Observações</h2>
              <span class="optional-badge">Opcional</span>
            </div>
          </div>

          <ion-textarea
            v-model="formData.observations"
            placeholder="Adicione observações sobre a limpeza (opcional)..."
            :rows="3"
            auto-grow
            class="obs-textarea"
          ></ion-textarea>
        </div>

        <!-- Submit Button -->
        <ion-button
          expand="block"
          @click="submitChecklist"
          :disabled="!isFormValid || isSubmitting"
          class="submit-button"
          size="large"
        >
          <ion-icon slot="start" :icon="checkmarkCircleOutline"></ion-icon>
          {{ isSubmitting ? 'Salvando...' : 'Concluir Checklist' }}
        </ion-button>

        <div class="completion-info" v-if="completionPercentage < 100">
          <ion-icon :icon="informationCircleOutline"></ion-icon>
          <span>{{ completionMessage }}</span>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script lang="ts" setup>
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
  IonCard,
  IonCardContent,
  IonItem,
  IonLabel,
  IonSelect,
  IonSelectOption,
  IonToggle,
  IonSegment,
  IonSegmentButton,
  IonTextarea,
  IonButton,
  IonIcon,
  IonDatetime,
  IonDatetimeButton,
  IonModal,
  alertController,
  toastController,
} from '@ionic/vue';
import {
  calendarOutline,
  flaskOutline,
  waterOutline,
  hardwareChipOutline,
  medicalOutline,
  helpBuoyOutline,
  documentTextOutline,
  checkmarkCircleOutline,
  checkmarkOutline,
  closeOutline,
  removeOutline,
  checkmarkCircle,
  closeCircleOutline,
  removeCircleOutline,
  informationCircleOutline,
} from 'ionicons/icons';
import { Container } from '../core/di/Container';
import type { CleaningChecklistCreate } from '../core/domain/entities/CleaningChecklist';

const container = Container.getInstance();
const createCleaningChecklistUseCase = container.getCreateCleaningChecklistUseCase();
const machineRepository = container.getMachineRepository();
const router = useRouter();

const machines = ref<any[]>([]);
const isSubmitting = ref(false);

const formData = ref<CleaningChecklistCreate>({
  machine_id: 0,
  checklist_date: new Date().toISOString(),
  shift: '1',
  chemical_disinfection_time: undefined,
  chemical_disinfection_completed: false,
  hd_machine_cleaning: undefined,
  osmosis_cleaning: undefined,
  serum_support_cleaning: undefined,
  observations: undefined,
});

const maxDate = computed(() => new Date().toISOString());

const isFormValid = computed(() => {
  return formData.value.machine_id > 0 &&
         formData.value.checklist_date &&
         formData.value.shift;
});

// Progress tracking
const progressPercentage = computed(() => {
  let completed = 0;
  const total = 6; // machine, date, shift, hd_machine, osmosis, serum_support

  if (formData.value.machine_id > 0) completed++;
  if (formData.value.checklist_date) completed++;
  if (formData.value.shift) completed++;
  if (formData.value.hd_machine_cleaning) completed++;
  if (formData.value.osmosis_cleaning) completed++;
  if (formData.value.serum_support_cleaning) completed++;

  return (completed / total) * 100;
});

const completionPercentage = computed(() => {
  let completed = 0;
  const total = 3; // hd_machine, osmosis, serum_support

  if (formData.value.hd_machine_cleaning) completed++;
  if (formData.value.osmosis_cleaning) completed++;
  if (formData.value.serum_support_cleaning) completed++;

  return (completed / total) * 100;
});

const completionMessage = computed(() => {
  const percentage = completionPercentage.value;
  if (percentage === 0) return 'Preencha os itens de limpeza de superfície';
  if (percentage < 100) return `${Math.round(percentage)}% completo - Continue preenchendo`;
  return 'Checklist completo!';
});

onMounted(async () => {
  try {
    machines.value = await machineRepository.getAll();
  } catch (error) {
    console.error('Error loading machines:', error);
    const toast = await toastController.create({
      message: 'Erro ao carregar máquinas',
      duration: 2000,
      color: 'danger',
    });
    await toast.present();
  }
});

const submitChecklist = async () => {
  if (!isFormValid.value || isSubmitting.value) return;

  isSubmitting.value = true;

  try {
    // Format date to YYYY-MM-DD
    const date = new Date(formData.value.checklist_date);
    const formattedDate = date.toISOString().split('T')[0];

    // Format time to HH:mm if chemical disinfection is completed
    let formattedTime = undefined;
    if (formData.value.chemical_disinfection_completed && formData.value.chemical_disinfection_time) {
      const timeDate = new Date(formData.value.chemical_disinfection_time);
      formattedTime = `${String(timeDate.getHours()).padStart(2, '0')}:${String(timeDate.getMinutes()).padStart(2, '0')}`;
    }

    const submitData: CleaningChecklistCreate = {
      ...formData.value,
      checklist_date: formattedDate,
      chemical_disinfection_time: formattedTime,
    };

    await createCleaningChecklistUseCase.execute(submitData);

    const toast = await toastController.create({
      message: 'Checklist salvo com sucesso!',
      duration: 2000,
      color: 'success',
    });
    await toast.present();

    router.push('/cleaning-controls');
  } catch (error: any) {
    console.error('Error creating checklist:', error);

    let errorMessage = 'Erro ao salvar checklist';
    if (error.message && error.message.includes('Já existe')) {
      errorMessage = 'Já existe um checklist para esta máquina, data e turno';
    }

    const alert = await alertController.create({
      header: 'Erro',
      message: errorMessage,
      buttons: ['OK'],
    });
    await alert.present();
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.cleaning-checklist-content {
  --background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
}

/* Progress Bar */
.progress-bar {
  position: sticky;
  top: 0;
  z-index: 100;
  height: 4px;
  background: rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981 0%, #059669 100%);
  transition: width 0.3s ease;
}

/* Form Container */
.form-container {
  padding: 16px;
  max-width: 600px;
  margin: 0 auto;
}

/* Step Cards */
.step-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.step-card.optional {
  border: 2px dashed #d1d5db;
  background: #f9fafb;
}

.step-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 20px;
}

.step-number {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.step-number.optional {
  background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);
}

.step-header h2 {
  font-size: 1.3rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
  flex: 1;
}

.step-header-text {
  flex: 1;
}

.step-legend {
  font-size: 0.9rem;
  color: #6b7280;
  margin: 4px 0 0 0;
  font-weight: 400;
}

.optional-badge {
  display: inline-block;
  background: #e5e7eb;
  color: #6b7280;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  margin-left: 8px;
}

/* Quick Selectors */
.quick-selectors {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.quick-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.quick-label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151;
}

/* Shift Buttons */
.shift-buttons {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
}

.shift-btn {
  padding: 12px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: white;
  color: #6b7280;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.shift-btn.active {
  border-color: var(--ion-color-primary);
  background: var(--ion-color-primary);
  color: white;
}

/* Machine Select */
.machine-select {
  width: 100%;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 12px;
  background: white;
  --placeholder-color: #9ca3af;
}

/* Date Button */
.date-btn, .time-btn {
  --background: white;
  --border-width: 2px;
  --border-color: #e5e7eb;
  --border-radius: 12px;
  --padding-start: 16px;
  --padding-end: 16px;
  width: 100%;
}

/* Toggle Card */
.toggle-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
}

.toggle-card:active {
  transform: scale(0.98);
}

.toggle-content {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.toggle-icon {
  font-size: 2rem;
  color: #f59e0b;
}

.toggle-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.toggle-title {
  font-weight: 600;
  color: #1f2937;
  font-size: 1rem;
}

.toggle-hint {
  font-size: 0.8rem;
  color: #9ca3af;
}

/* Time Selector */
.time-selector {
  margin-top: 16px;
  padding: 16px;
  background: #f0fdf4;
  border: 2px solid #bbf7d0;
  border-radius: 12px;
}

/* Legend Pills */
.legend-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 16px;
}

.legend-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.legend-pill ion-icon {
  font-size: 1.2rem;
}

.legend-pill.conforme {
  background: #d1fae5;
  color: #065f46;
}

.legend-pill.conforme ion-icon {
  color: #10b981;
}

.legend-pill.nao-conforme {
  background: #fee2e2;
  color: #991b1b;
}

.legend-pill.nao-conforme ion-icon {
  color: #ef4444;
}

.legend-pill.nao-aplica {
  background: #e5e7eb;
  color: #374151;
}

.legend-pill.nao-aplica ion-icon {
  color: #6b7280;
}

/* Cleaning Items */
.cleaning-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.cleaning-item-card {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  background: white;
}

.item-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}

.item-icon {
  font-size: 1.8rem;
  flex-shrink: 0;
}

.item-icon.hd {
  color: #3b82f6;
}

.item-icon.osmose {
  color: #06b6d4;
}

.item-icon.soro {
  color: #8b5cf6;
}

.item-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 1rem;
}

/* Conformity Buttons */
.conformity-buttons {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}

.conf-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 16px 12px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 80px;
}

.conf-btn ion-icon {
  font-size: 1.8rem;
}

.conf-btn span {
  font-weight: 600;
  font-size: 0.9rem;
}

.conf-btn.conforme {
  border-color: #e5e7eb;
  color: #6b7280;
}

.conf-btn.conforme.active {
  border-color: #10b981;
  background: #ecfdf5;
  color: #065f46;
}

.conf-btn.conforme.active ion-icon {
  color: #10b981;
}

.conf-btn.nao-conforme {
  border-color: #e5e7eb;
  color: #6b7280;
}

.conf-btn.nao-conforme.active {
  border-color: #ef4444;
  background: #fef2f2;
  color: #991b1b;
}

.conf-btn.nao-conforme.active ion-icon {
  color: #ef4444;
}

.conf-btn.nao-aplica {
  border-color: #e5e7eb;
  color: #6b7280;
}

.conf-btn.nao-aplica.active {
  border-color: #6b7280;
  background: #f3f4f6;
  color: #374151;
}

.conf-btn.nao-aplica.active ion-icon {
  color: #6b7280;
}

.conf-btn:active {
  transform: scale(0.95);
}

/* Observations */
.obs-textarea {
  --background: white;
  --color: #1f2937;
  --placeholder-color: #9ca3af;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 12px;
  font-size: 0.95rem;
}

/* Submit Button */
.submit-button {
  margin-top: 24px;
  height: 56px;
  font-size: 1.1rem;
  font-weight: 700;
  --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Completion Info */
.completion-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  background: #eff6ff;
  border: 2px solid #bfdbfe;
  border-radius: 12px;
  margin-top: 16px;
  margin-bottom: 24px;
}

.completion-info ion-icon {
  font-size: 1.3rem;
  color: #3b82f6;
}

.completion-info span {
  color: #1e40af;
  font-weight: 600;
  font-size: 0.9rem;
}
</style>
