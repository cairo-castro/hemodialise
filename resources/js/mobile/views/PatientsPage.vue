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
              <div class="detail-item" v-if="patient.medical_record">
                <ion-icon :icon="documentTextOutline"></ion-icon>
                <span>{{ patient.medical_record }}</span>
              </div>
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

      <!-- Create Patient Modal -->
      <ion-modal :is-open="showCreateModal" @will-dismiss="showCreateModal = false">
        <ion-header>
          <ion-toolbar>
            <ion-title>Novo Paciente</ion-title>
            <ion-buttons slot="end">
              <ion-button @click="showCreateModal = false">
                <ion-icon :icon="closeOutline" slot="icon-only"></ion-icon>
              </ion-button>
            </ion-buttons>
          </ion-toolbar>
        </ion-header>

        <ion-content class="modal-content">
          <form @submit.prevent="createPatient" class="create-form">
            <div class="form-group">
              <ion-item class="mobile-input" fill="outline">
                <ion-label position="floating">Nome Completo *</ion-label>
                <ion-input
                  v-model="newPatient.full_name"
                  type="text"
                  required
                  placeholder="Digite o nome completo"
                ></ion-input>
              </ion-item>
            </div>

            <div class="form-group">
              <ion-item class="mobile-input" fill="outline">
                <ion-label position="floating">Data de Nascimento *</ion-label>
                <ion-input
                  v-model="newPatient.birth_date"
                  type="date"
                  required
                ></ion-input>
              </ion-item>
            </div>

            <div class="form-group">
              <ion-item class="mobile-input" fill="outline">
                <ion-label position="floating">Prontuário Médico *</ion-label>
                <ion-input
                  v-model="newPatient.medical_record"
                  type="text"
                  required
                  placeholder="Número do prontuário"
                ></ion-input>
              </ion-item>
            </div>

            <div class="form-group">
              <ion-item class="mobile-input" fill="outline">
                <ion-label position="floating">Tipo Sanguíneo</ion-label>
                <ion-select v-model="newPatient.blood_type" placeholder="Selecione">
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

            <div class="form-group">
              <ion-item class="mobile-input" fill="outline">
                <ion-label position="floating">Alergias</ion-label>
                <ion-textarea
                  v-model="newPatient.allergies"
                  :rows="3"
                  placeholder="Descreva alergias conhecidas..."
                ></ion-textarea>
              </ion-item>
            </div>

            <div class="form-group">
              <ion-item class="mobile-input" fill="outline">
                <ion-label position="floating">Observações</ion-label>
                <ion-textarea
                  v-model="newPatient.observations"
                  :rows="3"
                  placeholder="Observações gerais..."
                ></ion-textarea>
              </ion-item>
            </div>

            <div class="modal-buttons">
              <ion-button
                expand="block"
                type="submit"
                class="mobile-button"
                :disabled="!canCreatePatient"
              >
                <ion-icon :icon="saveOutline" slot="start"></ion-icon>
                Cadastrar Paciente
              </ion-button>

              <ion-button
                expand="block"
                fill="outline"
                class="mobile-button"
                @click="showCreateModal = false"
              >
                Cancelar
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
  loadingController,
  toastController
} from '@ionic/vue';
import {
  addOutline,
  closeOutline,
  saveOutline,
  peopleOutline,
  documentTextOutline,
  waterOutline
} from 'ionicons/icons';

import { Container } from '@mobile/core/di/Container';
import { Patient, CreatePatientData } from '@mobile/core/domain/entities/Patient';
import { PatientRepository } from '@mobile/core/domain/repositories/PatientRepository';

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
  medical_record: '',
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
    patient.medical_record.toLowerCase().includes(query) ||
    (patient.blood_type && patient.blood_type.toLowerCase().includes(query))
  );
});

const canCreatePatient = computed(() => {
  return newPatient.value.full_name.length > 0 &&
         newPatient.value.birth_date.length > 0 &&
         newPatient.value.medical_record.length > 0;
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
      medical_record: '',
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
  if (!dateString) return 'Data não informada';

  // Parse date in YYYY-MM-DD format (UTC)
  const parts = dateString.split('-');
  if (parts.length === 3) {
    const year = parseInt(parts[0]);
    const month = parseInt(parts[1]) - 1; // Month is 0-indexed
    const day = parseInt(parts[2]);
    const date = new Date(year, month, day);

    if (!isNaN(date.getTime())) {
      return date.toLocaleDateString('pt-BR');
    }
  }

  return 'Data inválida';
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

.modal-content {
  padding: 1rem;
}

.create-form {
  max-width: 100%;
}

.form-group {
  margin-bottom: 1rem;
}

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