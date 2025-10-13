<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/checklist/new"></ion-back-button>
        </ion-buttons>
        <ion-title>
          <div class="modal-title-dash">
            <ion-icon :icon="personAddOutline"></ion-icon>
            <span>Novo Paciente</span>
          </div>
        </ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="modal-content-dash">
      <form @submit.prevent="handleSubmit" class="form-dash">

        <!-- Welcome Card -->
        <div class="welcome-card-dash">
          <h2>Cadastrar Novo Paciente</h2>
          <p>Preencha as informações básicas</p>
        </div>

        <!-- Progress Steps -->
        <div class="progress-dash">
          <div class="progress-item" :class="{ done: newPatient.full_name }">
            <div class="progress-dot"></div>
            <span>Nome</span>
          </div>
          <div class="progress-line" :class="{ active: newPatient.birth_date }"></div>
          <div class="progress-item" :class="{ done: newPatient.birth_date }">
            <div class="progress-dot"></div>
            <span>Data</span>
          </div>
          <div class="progress-line" :class="{ active: newPatient.blood_type }"></div>
          <div class="progress-item" :class="{ done: newPatient.blood_type }">
            <div class="progress-dot"></div>
            <span>Fator RH</span>
          </div>
        </div>

        <!-- Input Cards -->
        <div class="input-cards-dash">

          <!-- Nome Completo -->
          <div class="input-card-dash">
            <div class="card-icon-dash primary">
              <ion-icon :icon="personOutline"></ion-icon>
            </div>
            <div class="card-content-dash">
              <label>Nome Completo</label>
              <ion-input
                v-model="newPatient.full_name"
                type="text"
                required
                placeholder="Digite o nome completo"
                class="input-dash"
              ></ion-input>
            </div>
          </div>

          <!-- Data de Nascimento -->
          <div class="input-card-dash">
            <div class="card-icon-dash secondary">
              <ion-icon :icon="calendarOutline"></ion-icon>
            </div>
            <div class="card-content-dash">
              <label>Data de Nascimento</label>
              <ion-input
                v-model="newPatient.birth_date"
                type="date"
                required
                class="input-dash"
              ></ion-input>
            </div>
          </div>

          <!-- Fator RH -->
          <div class="input-card-dash">
            <div class="card-icon-dash tertiary">
              <ion-icon :icon="waterOutline"></ion-icon>
            </div>
            <div class="card-content-dash">
              <label>Fator RH <span class="optional">(opcional)</span></label>
              <ion-select
                v-model="newPatient.blood_type"
                placeholder="Selecione o tipo sanguíneo"
                interface="action-sheet"
                class="select-dash"
              >
                <ion-select-option value="A+">A+</ion-select-option>
                <ion-select-option value="A-">A-</ion-select-option>
                <ion-select-option value="B+">B+</ion-select-option>
                <ion-select-option value="B-">B-</ion-select-option>
                <ion-select-option value="AB+">AB+</ion-select-option>
                <ion-select-option value="AB-">AB-</ion-select-option>
                <ion-select-option value="O+">O+</ion-select-option>
                <ion-select-option value="O-">O-</ion-select-option>
              </ion-select>
            </div>
          </div>

        </div>

        <!-- Buttons -->
        <div class="buttons-dash">
          <button
            type="button"
            class="btn-cancel-dash"
            @click="handleCancel"
          >
            <ion-icon :icon="closeOutline"></ion-icon>
            Cancelar
          </button>

          <button
            type="submit"
            class="btn-submit-dash"
            :disabled="!canSubmit"
          >
            <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
            Cadastrar
          </button>
        </div>

      </form>
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
  IonInput,
  IonSelect,
  IonSelectOption,
  IonIcon,
  loadingController,
  toastController,
  alertController
} from '@ionic/vue';
import {
  personAddOutline,
  personOutline,
  calendarOutline,
  waterOutline,
  closeOutline,
  checkmarkCircleOutline
} from 'ionicons/icons';

import { Container } from '@mobile/core/di/Container';
import { CreatePatientData } from '@mobile/core/domain/entities/Patient';

const router = useRouter();
const container = Container.getInstance();
const createPatientUseCase = container.getCreatePatientUseCase();

// Form state
const newPatient = ref<CreatePatientData>({
  full_name: '',
  birth_date: '',
  medical_record: '',
  blood_type: '',
  allergies: '',
  observations: ''
});

// Computed
const canSubmit = computed(() => {
  return newPatient.value.full_name.trim().length > 0 &&
         newPatient.value.birth_date.length > 0;
});

// Methods
const handleSubmit = async () => {
  if (!canSubmit.value) return;

  const loading = await loadingController.create({
    message: 'Cadastrando paciente...',
    spinner: 'crescent'
  });
  await loading.present();

  try {
    const patient = await createPatientUseCase.execute(newPatient.value);

    if (patient) {
      // Store patient ID to load in checklist page
      localStorage.setItem('new_patient_id', patient.id.toString());

      const toast = await toastController.create({
        message: 'Paciente cadastrado com sucesso!',
        duration: 2000,
        color: 'success',
        position: 'top'
      });
      await toast.present();

      // Return to checklist
      router.replace('/checklist/new');
    }
  } catch (error: any) {
    const toast = await toastController.create({
      message: error.message || 'Erro ao cadastrar paciente',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    await loading.dismiss();
  }
};

const handleCancel = async () => {
  if (newPatient.value.full_name || newPatient.value.birth_date) {
    const alert = await alertController.create({
      header: 'Descartar alterações?',
      message: 'Tem certeza que deseja cancelar? Os dados preenchidos serão perdidos.',
      buttons: [
        {
          text: 'Continuar Editando',
          role: 'cancel'
        },
        {
          text: 'Descartar',
          role: 'destructive',
          handler: () => {
            localStorage.removeItem('patient_search_query');
            localStorage.removeItem('return_to_checklist');
            router.back();
          }
        }
      ]
    });

    await alert.present();
  } else {
    localStorage.removeItem('patient_search_query');
    localStorage.removeItem('return_to_checklist');
    router.back();
  }
};

// Lifecycle
onMounted(() => {
  // Pre-fill with search query if available
  const searchQuery = localStorage.getItem('patient_search_query');
  if (searchQuery) {
    newPatient.value.full_name = searchQuery;
  }
});
</script>

<style scoped>
/* ===== MODAL DASHBOARD STYLE ===== */

/* Modal Title */
.modal-title-dash {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.1rem;
  font-weight: 700;
}

.modal-title-dash ion-icon {
  font-size: 1.5rem;
}

/* Modal Content */
.modal-content-dash {
  --background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
  --padding: 0;
}

.form-dash {
  max-width: 600px;
  margin: 0 auto;
  padding: 16px;
}

/* Welcome Card */
.welcome-card-dash {
  background: white;
  padding: 20px;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 20px;
  text-align: center;
}

.welcome-card-dash h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.welcome-card-dash p {
  font-size: 0.9rem;
  color: #6b7280;
  margin: 0;
}

/* Progress Steps */
.progress-dash {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0;
  margin-bottom: 24px;
  padding: 0 20px;
}

.progress-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.progress-dot {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: white;
  border: 3px solid #d1d5db;
  transition: all 0.3s ease;
}

.progress-item.done .progress-dot {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  border-color: var(--ion-color-primary);
}

.progress-item span {
  font-size: 0.7rem;
  color: #6b7280;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.progress-item.done span {
  color: var(--ion-color-primary);
}

.progress-line {
  width: 40px;
  height: 3px;
  background: #e5e7eb;
  margin: 0 -8px;
  margin-bottom: 20px;
  transition: all 0.3s ease;
}

.progress-line.active {
  background: var(--ion-color-primary);
}

/* Input Cards */
.input-cards-dash {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 24px;
}

.input-card-dash {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  transition: all 0.2s ease;
}

.input-card-dash:focus-within {
  border-color: var(--ion-color-primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.card-icon-dash {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.card-icon-dash ion-icon {
  font-size: 1.8rem;
  color: white;
}

.card-icon-dash.primary {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
}

.card-icon-dash.secondary {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.card-icon-dash.tertiary {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.card-content-dash {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.card-content-dash label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.optional {
  font-size: 0.75rem;
  font-weight: 400;
  color: #9ca3af;
}

.input-dash,
.select-dash {
  --padding-start: 0;
  --padding-end: 0;
  --padding-top: 0;
  --padding-bottom: 0;
  font-size: 1rem;
  color: #1f2937;
}

.input-dash::part(native),
.select-dash::part(container) {
  padding: 0 !important;
}

/* Buttons */
.buttons-dash {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.btn-cancel-dash,
.btn-submit-dash {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 20px;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel-dash {
  background: white;
  color: #6b7280;
  border: 2px solid #e5e7eb;
}

.btn-cancel-dash:active {
  transform: scale(0.98);
  border-color: var(--ion-color-primary);
}

.btn-submit-dash {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-submit-dash:active {
  transform: scale(0.98);
}

.btn-submit-dash:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-cancel-dash ion-icon,
.btn-submit-dash ion-icon {
  font-size: 1.2rem;
}

/* Responsive */
@media (max-width: 480px) {
  .progress-dash {
    padding: 0 10px;
  }

  .progress-line {
    width: 30px;
  }

  .card-icon-dash {
    width: 40px;
    height: 40px;
  }

  .card-icon-dash ion-icon {
    font-size: 1.5rem;
  }

  .buttons-dash {
    grid-template-columns: 1fr;
  }
}
</style>
