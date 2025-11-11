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

      <!-- Badge de Disponibilidade de Máquinas -->
      <div v-if="machineAvailability" class="availability-badge" :class="machineAvailability.overall_status">
        <div class="availability-icon">
          <ion-icon v-if="machineAvailability.overall_status === 'good'" :icon="checkmarkCircleOutline"></ion-icon>
          <ion-icon v-else-if="machineAvailability.overall_status === 'critical'" :icon="closeCircleOutline"></ion-icon>
          <ion-icon v-else :icon="alertCircleOutline"></ion-icon>
        </div>
        <div class="availability-info">
          <span class="availability-label">{{ machineAvailability.message }}</span>
          <span class="availability-count">
            {{ machineAvailability.available }} de {{ machineAvailability.total }} disponíveis para limpeza
          </span>
        </div>
      </div>

      <div class="form-container">
        <!-- Step 1: Máquina e Turno -->
        <div class="step-card">
          <div class="step-header">
            <div class="step-number">1</div>
            <h2>Máquina e Turno</h2>
          </div>

          <!-- Machine Selection -->
          <div class="form-group">
            <label class="input-label">
              <ion-icon :icon="medicalOutline"></ion-icon>
              Selecione a Máquina para Limpeza
            </label>
            
            <!-- Machine Cards Grid -->
            <div class="machine-grid">
              <button
                v-for="machine in availableMachines"
                :key="machine.id"
                type="button"
                class="machine-card"
                :class="{ selected: formData.machine_id === machine.id }"
                @click="formData.machine_id = machine.id"
              >
                <div class="machine-icon">
                  <ion-icon :icon="hardwareChipOutline"></ion-icon>
                </div>
                <div class="machine-info">
                  <span class="machine-name">{{ machine.name }}</span>
                  <span class="machine-status">Disponível</span>
                </div>
                <div class="machine-check" v-if="formData.machine_id === machine.id">
                  <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
                </div>
              </button>
            </div>

            <!-- Mensagem se não houver máquinas -->
            <div v-if="availableMachines.length === 0" class="no-machines-message">
              <ion-icon :icon="alertCircleOutline"></ion-icon>
              <p>Nenhuma máquina disponível no momento. Aguarde até que uma máquina fique disponível.</p>
            </div>
          </div>

          <!-- Shift Selection -->
          <div class="form-group">
            <label class="input-label">
              <ion-icon :icon="timeOutline"></ion-icon>
              Selecione o Turno da Limpeza
            </label>
            <div class="shift-selector">
              <button
                type="button"
                class="shift-btn"
                :class="{ active: selectedShift === 'matutino' }"
                @click="selectedShift = 'matutino'"
              >
                <ion-icon :icon="sunnyOutline"></ion-icon>
                <span>Matutino</span>
                <span class="shift-time">06:00 - 12:00</span>
              </button>
              <button
                type="button"
                class="shift-btn"
                :class="{ active: selectedShift === 'vespertino' }"
                @click="selectedShift = 'vespertino'"
              >
                <ion-icon :icon="partlySunnyOutline"></ion-icon>
                <span>Vespertino</span>
                <span class="shift-time">12:00 - 18:00</span>
              </button>
              <button
                type="button"
                class="shift-btn"
                :class="{ active: selectedShift === 'noturno' }"
                @click="selectedShift = 'noturno'"
              >
                <ion-icon :icon="moonOutline"></ion-icon>
                <span>Noturno</span>
                <span class="shift-time">18:00 - 00:00</span>
              </button>
              <button
                type="button"
                class="shift-btn"
                :class="{ active: selectedShift === 'madrugada' }"
                @click="selectedShift = 'madrugada'"
              >
                <ion-icon :icon="cloudyNightOutline"></ion-icon>
                <span>Madrugada</span>
                <span class="shift-time">00:00 - 06:00</span>
              </button>
            </div>
          </div>

          <!-- Date -->
          <div class="form-group">
            <label class="input-label">
              <ion-icon :icon="calendarOutline"></ion-icon>
              Data da Limpeza
            </label>
            <ion-datetime-button datetime="checklist-date" class="date-btn"></ion-datetime-button>
            <ion-modal :keep-contents-mounted="true">
              <ion-datetime
                id="checklist-date"
                v-model="formData.checklist_date"
                presentation="date"
                :min="minDate"
                :max="maxDate"
              ></ion-datetime>
            </ion-modal>
            <p class="datetime-hint">Pode registrar até 3 dias atrás</p>
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
  alertCircleOutline,
  sunnyOutline,
  partlySunnyOutline,
  moonOutline,
  cloudyNightOutline,
  timeOutline,
} from 'ionicons/icons';
import { Container } from '../core/di/Container';
import type { CleaningChecklistCreate } from '../core/domain/entities/CleaningChecklist';
import { AuthService } from '@shared/auth';

const container = Container.getInstance();
const createCleaningChecklistUseCase = container.getCreateCleaningChecklistUseCase();
const machineRepository = container.getMachineRepository();
const router = useRouter();

const availableMachines = ref<any[]>([]);
const isSubmitting = ref(false);
const machineAvailability = ref<any>(null);

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

// Mapeamento de turnos numéricos para nomes
const shiftMap: Record<string, '1' | '2' | '3' | '4'> = {
  'matutino': '1',
  'vespertino': '2',
  'noturno': '3',
  'madrugada': '4',
};

const selectedShift = ref<string>('matutino');

const maxDate = computed(() => new Date().toISOString());

// Min date: 72 hours (3 days) ago for retroactive checklists
const minDate = computed(() => {
  const date = new Date();
  date.setHours(date.getHours() - 72);
  return date.toISOString();
});

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
  if (selectedShift.value) completed++;
  if (formData.value.hd_machine_cleaning) completed++;
  if (formData.value.osmosis_cleaning) completed++;
  if (formData.value.serum_support_cleaning) completed++;

  return (completed / total) * 100;
});

// Computed para contar máquinas disponíveis
const availableMachinesCount = computed(() => {
  return availableMachines.value.length;
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
    // Buscar apenas máquinas disponíveis
    availableMachines.value = await machineRepository.getAvailable();
    await fetchMachineAvailability();
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

/**
 * Buscar disponibilidade de máquinas
 */
const fetchMachineAvailability = async () => {
  try {
    const response = await fetch('/api/machines/availability',
      AuthService.getFetchConfig()
    );

    if (!response.ok) {
      throw new Error('Failed to fetch machine availability');
    }

    const data = await response.json();
    machineAvailability.value = data.availability;
  } catch (error) {
    console.error('Error fetching machine availability:', error);
    // Não mostra toast para não poluir UI, apenas loga erro
  }
};

/**
 * Validar disponibilidade antes de criar checklist
 */
const checkMachineAvailability = async (): Promise<{ canCreate: boolean }> => {
  await fetchMachineAvailability();

  if (!machineAvailability.value) {
    return { canCreate: true }; // Se não conseguiu buscar, permite continuar
  }

  const { available, overall_status, message } = machineAvailability.value;

  // ❌ Nenhuma máquina disponível
  if (available === 0) {
    const alert = await alertController.create({
      header: 'Máquinas Indisponíveis',
      message: 'Não há máquinas disponíveis no momento. Por favor, aguarde até que uma máquina fique disponível para realizar a limpeza.',
      buttons: ['OK'],
    });
    await alert.present();
    return { canCreate: false };
  }

  // ⚠️ Poucas máquinas disponíveis (confirmação)
  if (overall_status === 'warning' || overall_status === 'alert') {
    const alert = await alertController.create({
      header: 'Atenção',
      message: `${message}. Apenas ${available} máquina(s) disponível(is). Deseja continuar com o checklist de limpeza?`,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel',
        },
        {
          text: 'Continuar',
          role: 'confirm',
        },
      ],
    });
    await alert.present();
    const { role } = await alert.onDidDismiss();
    return { canCreate: role === 'confirm' };
  }

  // ✅ OK - disponibilidade boa
  return { canCreate: true };
};

const submitChecklist = async () => {
  if (!isFormValid.value || isSubmitting.value) return;

  // ✅ Validar disponibilidade de máquinas antes de criar
  const availabilityCheck = await checkMachineAvailability();
  if (!availabilityCheck.canCreate) {
    return; // Bloqueado - alerta já foi mostrado
  }

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

    // Mapear turno selecionado para número
    const shiftNumber = shiftMap[selectedShift.value];

    const submitData: CleaningChecklistCreate = {
      ...formData.value,
      shift: shiftNumber,
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
  --background: var(--ion-background-color);
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
  background: var(--ion-card-background);
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.step-card.optional {
  border: 2px dashed #d1d5db;
  background: var(--ion-background-color);
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
  color: var(--ion-text-color);
  margin: 0;
  flex: 1;
}

.step-header-text {
  flex: 1;
}

.step-legend {
  font-size: 0.9rem;
  color: var(--ion-color-step-600);
  margin: 4px 0 0 0;
  font-weight: 400;
}

.optional-badge {
  display: inline-block;
  background: #e5e7eb;
  color: var(--ion-color-step-600);
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  margin-left: 8px;
}

/* Time Button */
.time-btn {
  --background: var(--ion-card-background);
  --border-width: 2px;
  --border-color: var(--ion-color-step-150);
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
  border: 2px solid var(--ion-color-step-150);
  border-radius: 12px;
  background: var(--ion-card-background);
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
  color: var(--color-occupied);
}

.toggle-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.toggle-title {
  font-weight: 600;
  color: var(--ion-text-color);
  font-size: 1rem;
}

.toggle-hint {
  font-size: 0.8rem;
  color: var(--ion-color-step-500);
}

/* Time Selector */
.time-selector {
  margin-top: 16px;
  padding: 16px;
  background: rgba(var(--ion-color-success-rgb), 0.1);
  border: 2px solid rgba(var(--ion-color-success-rgb), 0.3);
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
  background: rgba(var(--ion-color-success-rgb), 0.15);
  color: var(--color-available);
}

.legend-pill.conforme ion-icon {
  color: var(--color-available);
}

.legend-pill.nao-conforme {
  background: rgba(var(--ion-color-danger-rgb), 0.15);
  color: var(--color-maintenance);
}

.legend-pill.nao-conforme ion-icon {
  color: var(--color-maintenance);
}

.legend-pill.nao-aplica {
  background: var(--ion-color-step-150);
  color: var(--ion-text-color);
}

.legend-pill.nao-aplica ion-icon {
  color: var(--ion-color-step-600);
}

/* Cleaning Items */
.cleaning-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.cleaning-item-card {
  border: 2px solid var(--ion-color-step-150);
  border-radius: 12px;
  padding: 16px;
  background: var(--ion-card-background);
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
  color: var(--ion-color-primary);
}

.item-icon.osmose {
  color: #06b6d4;
}

.item-icon.soro {
  color: #8b5cf6;
}

.item-name {
  font-weight: 600;
  color: var(--ion-text-color);
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
  border: 2px solid var(--ion-color-step-150);
  border-radius: 12px;
  background: var(--ion-card-background);
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
  border-color: var(--ion-color-step-150);
  color: var(--ion-color-step-600);
}

.conf-btn.conforme.active {
  border-color: var(--color-available);
  background: #ecfdf5;
  color: #065f46;
}

.conf-btn.conforme.active ion-icon {
  color: var(--color-available);
}

.conf-btn.nao-conforme {
  border-color: var(--ion-color-step-150);
  color: var(--ion-color-step-600);
}

.conf-btn.nao-conforme.active {
  border-color: var(--color-maintenance);
  background: rgba(var(--ion-color-danger-rgb), 0.1);
  color: var(--color-maintenance);
}

.conf-btn.nao-conforme.active ion-icon {
  color: var(--color-maintenance);
}

.conf-btn.nao-aplica {
  border-color: var(--ion-color-step-150);
  color: var(--ion-color-step-600);
}

.conf-btn.nao-aplica.active {
  border-color: var(--ion-color-step-600);
  background: var(--ion-color-step-50);
  color: var(--ion-text-color);
}

.conf-btn.nao-aplica.active ion-icon {
  color: var(--ion-color-step-600);
}

.conf-btn:active {
  transform: scale(0.95);
}

/* Observations */
.obs-textarea {
  --background: var(--ion-card-background);
  --color: var(--ion-text-color);
  --placeholder-color: var(--ion-color-step-500);
  border: 2px solid var(--ion-color-step-150);
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
  color: var(--ion-color-primary);
}

.completion-info span {
  color: var(--ion-color-primary-shade);
  font-weight: 600;
  font-size: 0.9rem;
}

/* Availability Badge */
.availability-badge {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  margin: 16px 16px 0 16px;
  border-radius: 12px;
  border: 2px solid;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  animation: slideDown 0.4s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.availability-badge.good {
  background: rgba(var(--ion-color-success-rgb), 0.1);
  border-color: var(--color-available);
}

.availability-badge.alert {
  background: rgba(var(--ion-color-warning-rgb), 0.1);
  border-color: var(--color-occupied);
}

.availability-badge.warning {
  background: rgba(249, 115, 22, 0.1);
  border-color: #f97316;
}

.availability-badge.critical {
  background: rgba(var(--ion-color-danger-rgb), 0.1);
  border-color: var(--color-maintenance);
}

.availability-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  flex-shrink: 0;
}

.availability-badge.good .availability-icon {
  background: var(--color-available);
}

.availability-badge.alert .availability-icon {
  background: var(--color-occupied);
}

.availability-badge.warning .availability-icon {
  background: #f97316;
}

.availability-badge.critical .availability-icon {
  background: var(--color-maintenance);
}

.availability-icon ion-icon {
  font-size: 1.5rem;
  color: white;
}

.availability-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.availability-label {
  font-weight: 700;
  font-size: 0.95rem;
  color: var(--ion-text-color);
}

.availability-count {
  font-size: 0.85rem;
  color: var(--ion-color-step-600);
  font-weight: 500;
}

/* Form Group */
.form-group {
  margin-bottom: 24px;
}

.input-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--ion-text-color);
  margin-bottom: 12px;
}

.input-label ion-icon {
  font-size: 1.2rem;
  color: var(--ion-color-primary);
}

.datetime-hint {
  font-size: 0.85rem;
  color: var(--ion-color-step-550);
  margin-top: 4px;
  margin-left: 4px;
}

/* Machine Grid */
.machine-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
  margin-top: 12px;
}

.machine-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 20px 16px;
  background: var(--ion-card-background);
  border: 3px solid #e5e7eb;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-height: 140px;
}

.machine-card:active {
  transform: scale(0.97);
}

.machine-card.selected {
  border-color: var(--ion-color-primary);
  background: linear-gradient(135deg, rgba(var(--ion-color-primary-rgb), 0.1) 0%, rgba(var(--ion-color-primary-rgb), 0.05) 100%);
  box-shadow: 0 4px 12px rgba(var(--ion-color-primary-rgb), 0.2);
}

.machine-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: var(--ion-color-step-100);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.machine-card.selected .machine-icon {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
}

.machine-icon ion-icon {
  font-size: 2rem;
  color: var(--ion-color-step-600);
  transition: all 0.3s ease;
}

.machine-card.selected .machine-icon ion-icon {
  color: white;
}

.machine-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  flex: 1;
}

.machine-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--ion-text-color);
  text-align: center;
}

.machine-status {
  font-size: 0.75rem;
  color: var(--color-available);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.machine-check {
  position: absolute;
  top: 8px;
  right: 8px;
}

.machine-check ion-icon {
  font-size: 1.5rem;
  color: var(--ion-color-primary);
}

/* No Machines Message */
.no-machines-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 24px;
  background: rgba(var(--ion-color-danger-rgb), 0.1);
  border: 2px solid rgba(var(--ion-color-danger-rgb), 0.3);
  border-radius: 12px;
  text-align: center;
  margin-top: 12px;
}

.no-machines-message ion-icon {
  font-size: 3rem;
  color: var(--color-maintenance);
}

.no-machines-message p {
  margin: 0;
  color: var(--color-maintenance);
  font-weight: 600;
  font-size: 0.9rem;
}

/* Shift Selector */
.shift-selector {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-top: 12px;
}

@media (min-width: 640px) {
  .shift-selector {
    grid-template-columns: repeat(4, 1fr);
  }
}

.shift-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 16px 12px;
  background: var(--ion-card-background);
  border: 3px solid #e5e7eb;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--ion-color-step-600);
  position: relative;
}

.shift-btn ion-icon {
  font-size: 2rem;
  color: var(--ion-color-step-500);
  transition: all 0.3s ease;
}

.shift-btn span:first-of-type {
  font-size: 0.9rem;
  font-weight: 700;
}

.shift-time {
  font-size: 0.7rem;
  color: var(--ion-color-step-500);
  font-weight: 500;
}

.shift-btn.active {
  border-color: var(--ion-color-primary);
  background: linear-gradient(135deg, rgba(var(--ion-color-primary-rgb), 0.1) 0%, rgba(var(--ion-color-primary-rgb), 0.05) 100%);
  color: var(--ion-color-primary);
  box-shadow: 0 4px 12px rgba(var(--ion-color-primary-rgb), 0.2);
}

.shift-btn.active ion-icon {
  color: var(--ion-color-primary);
}

.shift-btn.active .shift-time {
  color: var(--ion-color-primary);
}

.shift-btn:active {
  transform: scale(0.95);
}

/* Date Button */
.date-btn {
  --background: var(--ion-card-background);
  --border-width: 3px;
  --border-color: var(--ion-color-step-150);
  --border-radius: 12px;
  --padding-start: 16px;
  --padding-end: 16px;
  width: 100%;
  height: 48px;
  font-weight: 600;
  transition: all 0.2s ease;
}

.date-btn:hover {
  --border-color: var(--ion-color-primary);
}
</style>
