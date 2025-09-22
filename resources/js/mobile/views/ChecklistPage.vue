<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/dashboard"></ion-back-button>
        </ion-buttons>
        <ion-title>Checklist de Segurança</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <div class="checklist-container">
        <!-- Patient Search Section -->
        <ion-card class="patient-search-card">
          <ion-card-header>
            <ion-card-title>1. Buscar/Cadastrar Paciente</ion-card-title>
          </ion-card-header>
          <ion-card-content>
            <div class="form-group">
              <ion-item class="mobile-input" fill="outline">
                <ion-label position="floating">Nome Completo</ion-label>
                <ion-input
                  v-model="patientForm.full_name"
                  type="text"
                  placeholder="Digite o nome completo do paciente"
                  required
                ></ion-input>
              </ion-item>
            </div>

            <div class="form-group">
              <ion-item class="mobile-input" fill="outline">
                <ion-label position="floating">Data de Nascimento</ion-label>
                <ion-input
                  v-model="patientForm.birth_date"
                  type="date"
                  required
                ></ion-input>
              </ion-item>
            </div>

            <ion-button expand="block" class="mobile-button" @click="searchPatient" :disabled="!canSearchPatient">
              <ion-icon :icon="searchOutline" slot="start"></ion-icon>
              Buscar Paciente
            </ion-button>

            <!-- Patient found -->
            <div v-if="selectedPatient" class="patient-found">
              <ion-chip color="success">
                <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
                <ion-label>Paciente Encontrado</ion-label>
              </ion-chip>
              <div class="patient-info">
                <h4>{{ selectedPatient.full_name }}</h4>
                <p>Nascimento: {{ formatDate(selectedPatient.birth_date) }}</p>
                <p>Idade: {{ selectedPatient.age }} anos</p>
                <p v-if="selectedPatient.blood_type">Tipo Sanguíneo: {{ selectedPatient.blood_type }}</p>
              </div>
            </div>
          </ion-card-content>
        </ion-card>

        <!-- Checklist Form (only shows if patient is selected) -->
        <ion-card v-if="selectedPatient" class="checklist-form-card">
          <ion-card-header>
            <ion-card-title>2. Informações do Procedimento</ion-card-title>
          </ion-card-header>
          <ion-card-content>
            <div class="form-group">
              <ion-item class="mobile-input" fill="outline">
                <ion-label position="floating">Máquina</ion-label>
                <ion-select v-model="checklistForm.machine_id" placeholder="Selecione uma máquina">
                  <ion-select-option v-for="machine in availableMachines" :key="machine.id" :value="machine.id">
                    {{ machine.name }}
                  </ion-select-option>
                </ion-select>
              </ion-item>
            </div>

            <div class="form-group">
              <ion-item class="mobile-input" fill="outline">
                <ion-label position="floating">Turno</ion-label>
                <ion-select v-model="checklistForm.shift" placeholder="Selecione o turno">
                  <ion-select-option value="matutino">Matutino</ion-select-option>
                  <ion-select-option value="vespertino">Vespertino</ion-select-option>
                  <ion-select-option value="noturno">Noturno</ion-select-option>
                </ion-select>
              </ion-item>
            </div>
          </ion-card-content>
        </ion-card>

        <!-- Safety Checks (only shows if basic info is filled) -->
        <ion-card v-if="canShowSafetyChecks" class="safety-checks-card">
          <ion-card-header>
            <ion-card-title>3. Verificações de Segurança</ion-card-title>
            <ion-card-subtitle>Todos os itens devem ser verificados</ion-card-subtitle>
          </ion-card-header>
          <ion-card-content>
            <div class="safety-checks">
              <ion-item v-for="(check, index) in safetyCheckItems" :key="index" class="safety-check-item">
                <ion-checkbox
                  slot="start"
                  :checked="getCheckValue(check.key)"
                  @ionChange="setCheckValue(check.key, $event.detail.checked)"
                ></ion-checkbox>
                <ion-label class="safety-check-label">
                  <h3>{{ check.label }}</h3>
                  <p>{{ check.description }}</p>
                </ion-label>
              </ion-item>
            </div>

            <div class="form-group">
              <ion-item class="mobile-input" fill="outline">
                <ion-label position="floating">Observações (opcional)</ion-label>
                <ion-textarea
                  v-model="checklistForm.observations"
                  rows="3"
                  placeholder="Digite observações adicionais..."
                ></ion-textarea>
              </ion-item>
            </div>

            <ion-button
              expand="block"
              class="mobile-button"
              :disabled="!canSubmitChecklist"
              @click="submitChecklist"
            >
              <ion-icon :icon="saveOutline" slot="start"></ion-icon>
              Salvar Checklist
            </ion-button>
          </ion-card-content>
        </ion-card>
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
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardSubtitle,
  IonCardContent,
  IonItem,
  IonLabel,
  IonInput,
  IonTextarea,
  IonSelect,
  IonSelectOption,
  IonButton,
  IonCheckbox,
  IonChip,
  IonIcon,
  loadingController,
  toastController
} from '@ionic/vue';
import {
  searchOutline,
  checkmarkCircleOutline,
  saveOutline
} from 'ionicons/icons';

import { Container } from '@mobile/core/di/Container';
import { Patient, PatientSearchCriteria } from '@mobile/core/domain/entities/Patient';
import { Machine } from '@mobile/core/domain/entities/Machine';
import { CreateChecklistData } from '@mobile/core/domain/entities/SafetyChecklist';

const router = useRouter();
const container = Container.getInstance();

// Use cases
const searchPatientUseCase = container.getSearchPatientUseCase();
const createChecklistUseCase = container.getCreateChecklistUseCase();
const machineRepository = container.getMachineRepository();

// Reactive state
const patientForm = ref<PatientSearchCriteria>({
  full_name: '',
  birth_date: ''
});

const selectedPatient = ref<Patient | null>(null);
const availableMachines = ref<Machine[]>([]);

const checklistForm = ref<CreateChecklistData>({
  patient_id: 0,
  machine_id: 0,
  shift: 'matutino' as 'matutino' | 'vespertino' | 'noturno',
  patient_identification: false,
  access_patency: false,
  vital_signs: false,
  weight_measurement: false,
  machine_parameters: false,
  anticoagulation: false,
  alarm_tests: false,
  emergency_procedures: false,
  observations: ''
});

// Safety check items definition
const safetyCheckItems = [
  {
    key: 'patient_identification',
    label: 'Identificação do Paciente',
    description: 'Confirmar identidade com dois identificadores'
  },
  {
    key: 'access_patency',
    label: 'Permeabilidade do Acesso',
    description: 'Verificar funcionamento do acesso vascular'
  },
  {
    key: 'vital_signs',
    label: 'Sinais Vitais',
    description: 'Aferir pressão arterial, temperatura e peso'
  },
  {
    key: 'weight_measurement',
    label: 'Pesagem do Paciente',
    description: 'Peso seco e ganho de peso interdialítico'
  },
  {
    key: 'machine_parameters',
    label: 'Parâmetros da Máquina',
    description: 'Configurar parâmetros de diálise prescritos'
  },
  {
    key: 'anticoagulation',
    label: 'Anticoagulação',
    description: 'Verificar prescrição e dosagem de heparina'
  },
  {
    key: 'alarm_tests',
    label: 'Teste de Alarmes',
    description: 'Verificar funcionamento dos alarmes de segurança'
  },
  {
    key: 'emergency_procedures',
    label: 'Procedimentos de Emergência',
    description: 'Confirmar conhecimento dos procedimentos'
  }
];

// Computed properties
const canSearchPatient = computed(() => {
  return patientForm.value.full_name.length > 0 && patientForm.value.birth_date.length > 0;
});

const canShowSafetyChecks = computed(() => {
  return selectedPatient.value && checklistForm.value.machine_id && checklistForm.value.shift;
});

const canSubmitChecklist = computed(() => {
  const allChecksCompleted = safetyCheckItems.every(item => {
    const key = item.key as keyof CreateChecklistData;
    return checklistForm.value[key] === true;
  });
  return canShowSafetyChecks.value && allChecksCompleted;
});

// Methods
const searchPatient = async () => {
  const loading = await loadingController.create({
    message: 'Buscando paciente...',
    spinner: 'crescent'
  });
  await loading.present();

  try {
    const patient = await searchPatientUseCase.execute(patientForm.value);

    if (patient) {
      selectedPatient.value = patient;
      checklistForm.value.patient_id = Number(patient.id);

      const toast = await toastController.create({
        message: 'Paciente encontrado!',
        duration: 2000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
    } else {
      const toast = await toastController.create({
        message: 'Paciente não encontrado. Você pode cadastrá-lo na seção de pacientes.',
        duration: 3000,
        color: 'warning',
        position: 'top'
      });
      await toast.present();
    }
  } catch (error: any) {
    const toast = await toastController.create({
      message: error.message || 'Erro ao buscar paciente',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    await loading.dismiss();
  }
};

const submitChecklist = async () => {
  const loading = await loadingController.create({
    message: 'Salvando checklist...',
    spinner: 'crescent'
  });
  await loading.present();

  try {
    await createChecklistUseCase.execute(checklistForm.value);

    const toast = await toastController.create({
      message: 'Checklist salvo com sucesso!',
      duration: 2000,
      color: 'success',
      position: 'top'
    });
    await toast.present();

    // Navigate back to dashboard
    router.replace('/dashboard');

  } catch (error: any) {
    const toast = await toastController.create({
      message: error.message || 'Erro ao salvar checklist',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    await loading.dismiss();
  }
};

const loadMachines = async () => {
  try {
    availableMachines.value = await machineRepository.getAvailable();
  } catch (error) {
    console.error('Error loading machines:', error);
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR');
};

const getCheckValue = (key: string): boolean => {
  return (checklistForm.value as any)[key] || false;
};

const setCheckValue = (key: string, value: boolean) => {
  (checklistForm.value as any)[key] = value;
};

// Lifecycle
onMounted(() => {
  loadMachines();
});
</script>

<style scoped>
.checklist-container {
  padding: 1rem;
}

.patient-search-card,
.checklist-form-card,
.safety-checks-card {
  margin-bottom: 1rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

.patient-found {
  margin-top: 1rem;
  padding: 1rem;
  background: #f0fdf4;
  border-radius: 8px;
  border: 1px solid #bbf7d0;
}

.patient-info h4 {
  margin: 0.5rem 0 0.25rem 0;
  color: var(--ion-color-dark);
}

.patient-info p {
  margin: 0.25rem 0;
  color: var(--ion-color-medium);
  font-size: 0.875rem;
}

.safety-checks {
  margin-bottom: 1rem;
}

.safety-check-item {
  --padding: 16px 0;
  --border-color: #e5e7eb;
  margin-bottom: 8px;
}

.safety-check-label h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--ion-color-dark);
}

.safety-check-label p {
  margin: 0.25rem 0 0 0;
  font-size: 0.875rem;
  color: var(--ion-color-medium);
}
</style>