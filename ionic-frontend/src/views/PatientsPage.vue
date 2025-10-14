<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/dashboard"></ion-back-button>
        </ion-buttons>
        <ion-title>Pacientes</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="showCreateModal = true">
            <ion-icon :icon="addOutline" slot="icon-only"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <!-- Search bar -->
      <div class="search-container">
        <ion-searchbar
          v-model="searchQuery"
          placeholder="Buscar pacientes..."
          @ionInput="handleSearch"
          show-clear-button="focus"
        ></ion-searchbar>
      </div>

      <!-- Patients list -->
      <div class="patients-container">
        <ion-card
          v-for="patient in filteredPatients"
          :key="patient.id"
          class="patient-card"
          button
          @click="selectPatient(patient)"
        >
          <ion-card-content>
            <div class="patient-header">
              <div class="patient-info">
                <h3>{{ patient.full_name }}</h3>
                <p>{{ formatDate(patient.birth_date) }} • {{ patient.age }} anos</p>
              </div>
              <div class="patient-status">
                <ion-badge color="success">Ativo</ion-badge>
              </div>
            </div>

            <div class="patient-details">
              <div class="detail-item" v-if="patient.blood_type">
                <ion-icon :icon="waterOutline"></ion-icon>
                <span>{{ patient.blood_type }}</span>
              </div>
            </div>
          </ion-card-content>
        </ion-card>

        <!-- Empty state -->
        <div v-if="filteredPatients.length === 0" class="empty-state">
          <ion-icon :icon="peopleOutline"></ion-icon>
          <h3>Nenhum paciente encontrado</h3>
          <p v-if="searchQuery">Tente ajustar os termos de busca</p>
          <p v-else>Cadastre o primeiro paciente</p>
        </div>
      </div>

      <!-- Create Patient Modal - Improved -->
      <ion-modal :is-open="showCreateModal" @will-dismiss="showCreateModal = false">
        <ion-header>
          <ion-toolbar color="primary">
            <ion-title>
              <div class="modal-title">
                <ion-icon :icon="personAddOutline"></ion-icon>
                <span>Novo Paciente</span>
              </div>
            </ion-title>
            <ion-buttons slot="end">
              <ion-button @click="showCreateModal = false">
                <ion-icon :icon="closeOutline" slot="icon-only"></ion-icon>
              </ion-button>
            </ion-buttons>
          </ion-toolbar>
        </ion-header>

        <ion-content class="modal-content">
          <form @submit.prevent="createPatient" class="create-form">
            <!-- Progress indicator -->
            <div class="form-progress">
              <div class="progress-step" :class="{ active: newPatient.full_name }">
                <ion-icon :icon="personOutline"></ion-icon>
              </div>
              <div class="progress-step" :class="{ active: newPatient.birth_date }">
                <ion-icon :icon="calendarOutline"></ion-icon>
              </div>
              <div class="progress-step" :class="{ active: canCreatePatient }">
                <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
              </div>
            </div>

            <!-- Info banner -->
            <div class="info-banner">
              <ion-icon :icon="informationCircleOutline"></ion-icon>
              <span>Preencha os campos obrigatórios (*) para cadastrar</span>
            </div>

            <!-- Section: Dados Pessoais -->
            <div class="form-section">
              <div class="section-header">
                <ion-icon :icon="personOutline" class="section-icon"></ion-icon>
                <h3>Dados Pessoais</h3>
              </div>

              <div class="form-group">
                <label class="input-label">
                  <ion-icon :icon="personOutline"></ion-icon>
                  Nome Completo <span class="required">*</span>
                </label>
                <ion-item class="modern-input" lines="none">
                  <ion-input
                    v-model="newPatient.full_name"
                    type="text"
                    required
                    placeholder="Digite o nome completo do paciente"
                  ></ion-input>
                </ion-item>
              </div>

              <div class="form-row">
                <div class="form-group flex-1">
                  <label class="input-label">
                    <ion-icon :icon="calendarOutline"></ion-icon>
                    Data de Nascimento <span class="required">*</span>
                  </label>
                  <ion-item class="modern-input" lines="none">
                    <ion-input
                      v-model="newPatient.birth_date"
                      type="date"
                      required
                    ></ion-input>
                  </ion-item>
                </div>

                <div class="form-group" style="width: 120px;">
                  <label class="input-label">
                    <ion-icon :icon="waterOutline"></ion-icon>
                    Tipo Sanguíneo
                  </label>
                  <ion-item class="modern-input" lines="none">
                    <ion-select
                      v-model="newPatient.blood_type"
                      placeholder="Tipo"
                      interface="action-sheet"
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
                  </ion-item>
                </div>
              </div>
            </div>

            <!-- Section: Dados Médicos -->
            <div class="form-section">
              <div class="section-header">
                <ion-icon :icon="medkitOutline" class="section-icon"></ion-icon>
                <h3>Dados Médicos</h3>
              </div>

              <div class="form-group">
                <label class="input-label">
                  <ion-icon :icon="alertCircleOutline"></ion-icon>
                  Alergias
                </label>
                <ion-item class="modern-textarea" lines="none">
                  <ion-textarea
                    v-model="newPatient.allergies"
                    rows="3"
                    placeholder="Descreva alergias conhecidas (medicamentos, alimentos, etc.)"
                    auto-grow
                  ></ion-textarea>
                </ion-item>
              </div>

              <div class="form-group">
                <label class="input-label">
                  <ion-icon :icon="clipboardOutline"></ion-icon>
                  Observações Gerais
                </label>
                <ion-item class="modern-textarea" lines="none">
                  <ion-textarea
                    v-model="newPatient.observations"
                    rows="3"
                    placeholder="Observações adicionais sobre o paciente"
                    auto-grow
                  ></ion-textarea>
                </ion-item>
              </div>
            </div>

            <!-- Form validation summary -->
            <div class="validation-summary" v-if="!canCreatePatient">
              <ion-chip color="warning">
                <ion-icon :icon="warningOutline"></ion-icon>
                <ion-label>Preencha todos os campos obrigatórios</ion-label>
              </ion-chip>
            </div>

            <!-- Action buttons -->
            <div class="modal-actions">
              <ion-button
                fill="outline"
                expand="block"
                class="action-button"
                @click="showCreateModal = false"
              >
                <ion-icon :icon="closeOutline" slot="start"></ion-icon>
                Cancelar
              </ion-button>

              <ion-button
                color="success"
                expand="block"
                type="submit"
                class="action-button"
                :disabled="!canCreatePatient"
              >
                <ion-icon :icon="checkmarkCircleOutline" slot="start"></ion-icon>
                Cadastrar Paciente
              </ion-button>
            </div>
          </form>
        </ion-content>
      </ion-modal>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonButtons,
  IonBackButton,
  IonButton,
  IonCard,
  IonCardContent,
  IonSearchbar,
  IonBadge,
  IonIcon,
  IonModal,
  IonItem,
  IonLabel,
  IonInput,
  IonTextarea,
  IonSelect,
  IonSelectOption,
  IonChip,
  loadingController,
  toastController
} from '@ionic/vue';
import {
  addOutline,
  closeOutline,
  saveOutline,
  peopleOutline,
  documentTextOutline,
  waterOutline,
  personAddOutline,
  personOutline,
  calendarOutline,
  medkitOutline,
  alertCircleOutline,
  clipboardOutline,
  checkmarkCircleOutline,
  informationCircleOutline,
  warningOutline
} from 'ionicons/icons';

import { Container } from '@/core/di/Container';
import { Patient, CreatePatientData } from '@/core/domain/entities/Patient';
import { PatientRepository } from '@/core/domain/repositories/PatientRepository';

const container = Container.getInstance();

// Use cases
const createPatientUseCase = container.getCreatePatientUseCase();
const patientRepository = container.get<PatientRepository>('PatientRepository');

// Reactive state
const patients = ref<Patient[]>([]);
const searchQuery = ref('');
const showCreateModal = ref(false);

const newPatient = ref<CreatePatientData>({
  full_name: '',
  birth_date: '',
  blood_type: '',
  allergies: '',
  observations: ''
});

// Computed properties
const filteredPatients = computed(() => {
  if (!searchQuery.value) {
    return patients.value;
  }

  const query = searchQuery.value.toLowerCase();
  return patients.value.filter(patient =>
    patient.full_name.toLowerCase().includes(query) ||
    (patient.blood_type && patient.blood_type.toLowerCase().includes(query))
  );
});

const canCreatePatient = computed(() => {
  return newPatient.value.full_name.length > 0 &&
         newPatient.value.birth_date.length > 0;
});

// Methods
const loadPatients = async () => {
  const loading = await loadingController.create({
    message: 'Carregando pacientes...',
    spinner: 'crescent'
  });
  await loading.present();

  try {
    patients.value = await patientRepository.getAll();
  } catch (error) {
    console.error('Error loading patients:', error);
    const toast = await toastController.create({
      message: 'Erro ao carregar pacientes',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    await loading.dismiss();
  }
};

const handleSearch = (event: any) => {
  searchQuery.value = event.target.value;
};

const selectPatient = (patient: Patient) => {
  // Navigate to patient details or perform action
  console.log('Selected patient:', patient);
};

const createPatient = async () => {
  const loading = await loadingController.create({
    message: 'Cadastrando paciente...',
    spinner: 'crescent'
  });
  await loading.present();

  try {
    const patient = await createPatientUseCase.execute(newPatient.value);

    // Add to local list
    patients.value.unshift(patient);

    // Reset form
    newPatient.value = {
      full_name: '',
      birth_date: '',
      blood_type: '',
      allergies: '',
      observations: ''
    };

    showCreateModal.value = false;

    const toast = await toastController.create({
      message: 'Paciente cadastrado com sucesso!',
      duration: 2000,
      color: 'success',
      position: 'top'
    });
    await toast.present();

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

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR');
};

// Lifecycle
onMounted(() => {
  loadPatients();
});
</script>

<style scoped>
.search-container {
  padding: 1rem;
  background: var(--ion-color-light);
}

.patients-container {
  padding: 0 1rem 1rem 1rem;
}

.patient-card {
  margin-bottom: 1rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
}

.patient-card:active {
  transform: scale(0.98);
}

.patient-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.patient-info h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--ion-color-dark);
}

.patient-info p {
  margin: 0.25rem 0 0 0;
  font-size: 0.875rem;
  color: var(--ion-color-medium);
}

.patient-details {
  display: flex;
  gap: 1rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: var(--ion-color-medium);
}

.detail-item ion-icon {
  font-size: 1rem;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--ion-color-medium);
}

.empty-state ion-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
  color: var(--ion-color-dark);
}

.empty-state p {
  margin: 0;
  font-size: 0.875rem;
}

/* Modal Improvements */
.modal-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.1rem;
  font-weight: 600;
}

.modal-title ion-icon {
  font-size: 1.5rem;
}

.modal-content {
  --padding: 0;
}

.create-form {
  padding: 1rem;
}

/* Progress Indicator */
.form-progress {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.75rem;
  padding: 1.5rem 0;
  background: linear-gradient(135deg, var(--ion-color-primary-tint) 0%, var(--ion-color-primary) 100%);
  margin-bottom: 1rem;
}

.progress-step {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.progress-step ion-icon {
  font-size: 1.5rem;
  color: white;
  opacity: 0.5;
}

.progress-step.active {
  background: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: scale(1.1);
}

.progress-step.active ion-icon {
  color: var(--ion-color-primary);
  opacity: 1;
}

/* Info Banner */
.info-banner {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  background: #e0f2fe;
  border-left: 4px solid var(--ion-color-primary);
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
  color: var(--ion-color-primary-shade);
}

.info-banner ion-icon {
  font-size: 1.5rem;
  color: var(--ion-color-primary);
  flex-shrink: 0;
}

/* Form Sections */
.form-section {
  margin-bottom: 2rem;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid var(--ion-color-light);
}

.section-icon {
  font-size: 1.5rem;
  color: var(--ion-color-primary);
}

.section-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--ion-color-dark);
}

/* Form Groups */
.form-group {
  margin-bottom: 1rem;
}

.form-row {
  display: flex;
  gap: 0.75rem;
  align-items: flex-end;
}

.flex-1 {
  flex: 1;
}

/* Input Labels */
.input-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--ion-color-medium);
  margin-bottom: 0.5rem;
}

.input-label ion-icon {
  font-size: 1rem;
}

.required {
  color: var(--ion-color-danger);
  font-weight: bold;
}

/* Modern Inputs */
.modern-input {
  --background: #f5f7fa;
  --border-radius: 12px;
  --padding-start: 16px;
  --padding-end: 16px;
  --padding-top: 14px;
  --padding-bottom: 14px;
  border-radius: 12px;
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.modern-textarea {
  --background: #f5f7fa;
  --border-radius: 12px;
  --padding-start: 16px;
  --padding-end: 16px;
  --padding-top: 14px;
  --padding-bottom: 14px;
  border-radius: 12px;
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Validation Summary */
.validation-summary {
  display: flex;
  justify-content: center;
  margin: 1rem 0;
}

.validation-summary ion-chip {
  font-size: 0.875rem;
}

/* Modal Actions */
.modal-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 2px solid var(--ion-color-light);
}

.action-button {
  --border-radius: 12px;
  height: 52px;
  font-weight: 600;
  font-size: 1rem;
  margin: 0;
}

/* Legacy support */
.mobile-input {
  --background: #f9fafb;
  --border-color: #d1d5db;
  --border-radius: 8px;
  margin-bottom: 8px;
}

.mobile-button {
  --border-radius: 8px;
  margin: 8px 0;
  height: 48px;
  font-weight: 600;
}

.modal-buttons {
  margin-top: 1.5rem;
}
</style>