<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/dashboard"></ion-back-button>
        </ion-buttons>
        <ion-title>
          <div class="header-title">
            <ion-icon :icon="peopleOutline"></ion-icon>
            <span>Pacientes</span>
          </div>
        </ion-title>
        <ion-buttons slot="end">
          <ion-button @click="showCreateModal = true" class="add-button">
            <ion-icon :icon="addOutline" slot="icon-only"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="dashboard-content">
      <!-- Search bar -->
      <div class="search-container-dash">
        <div class="search-card">
          <ion-icon :icon="peopleOutline" class="search-icon"></ion-icon>
          <ion-searchbar
            :value="searchQuery"
            placeholder="Buscar pacientes..."
            @ionInput="handleSearch($event)"
            @ionClear="searchQuery = ''"
            show-clear-button="focus"
            class="search-bar-dash"
            debounce="300"
          ></ion-searchbar>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card primary">
          <div class="stat-icon">
            <ion-icon :icon="peopleOutline"></ion-icon>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ patients.length }}</span>
            <span class="stat-label">Total</span>
          </div>
        </div>

        <div class="stat-card success">
          <div class="stat-icon">
            <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ patients.length }}</span>
            <span class="stat-label">Ativos</span>
          </div>
        </div>
      </div>

      <!-- Patients list -->
      <div class="patients-container-dash">
        <div class="section-header">
          <h2>Lista de Pacientes</h2>
          <ion-badge color="primary">{{ filteredPatients.length }}</ion-badge>
        </div>

        <div class="patients-grid">
          <div
            v-for="patient in filteredPatients"
            :key="patient.id"
            class="patient-card-dash"
            @click="selectPatient(patient)"
          >
            <div class="patient-avatar">
              <ion-icon :icon="personOutline"></ion-icon>
            </div>

            <div class="patient-content">
              <div class="patient-header-dash">
                <h3>{{ patient.full_name }}</h3>
                <ion-badge color="success" class="status-badge">Ativo</ion-badge>
              </div>

              <div class="patient-meta">
                <div class="meta-item">
                  <ion-icon :icon="calendarOutline"></ion-icon>
                  <span>{{ formatDate(patient.birth_date) }}</span>
                </div>
                <div class="meta-item">
                  <ion-icon :icon="informationCircleOutline"></ion-icon>
                  <span>{{ patient.age }} anos</span>
                </div>
              </div>

              <div class="patient-details-dash" v-if="patient.blood_type">
                <div class="detail-chip" v-if="patient.blood_type">
                  <ion-icon :icon="waterOutline"></ion-icon>
                  <span>{{ patient.blood_type }}</span>
                </div>
              </div>
            </div>

            <div class="patient-arrow">
              <ion-icon :icon="arrowForwardOutline"></ion-icon>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="filteredPatients.length === 0" class="empty-state-dash">
          <div class="empty-icon">
            <ion-icon :icon="peopleOutline"></ion-icon>
          </div>
          <h3>Nenhum paciente encontrado</h3>
          <p v-if="searchQuery">Tente ajustar os termos de busca</p>
          <p v-else>Cadastre o primeiro paciente clicando no botão +</p>
          <ion-button
            v-if="!searchQuery"
            @click="showCreateModal = true"
            class="empty-action"
          >
            <ion-icon :icon="addOutline" slot="start"></ion-icon>
            Novo Paciente
          </ion-button>
        </div>
      </div>

      <!-- Create Patient Modal - SIMPLIFICADO DASHBOARD STYLE -->
      <ion-modal :is-open="showCreateModal" @will-dismiss="showCreateModal = false">
        <ion-header>
          <ion-toolbar color="primary">
            <ion-title>
              <div class="modal-title-dash">
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

        <ion-content class="modal-content-dash">
          <form @submit.prevent="createPatient" class="form-dash">

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
                @click="showCreateModal = false"
              >
                <ion-icon :icon="closeOutline"></ion-icon>
                Cancelar
              </button>

              <button
                type="submit"
                class="btn-submit-dash"
                :disabled="!canCreatePatient"
              >
                <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
                Cadastrar
              </button>
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
  waterOutline,
  personAddOutline,
  personOutline,
  calendarOutline,
  medkitOutline,
  alertCircleOutline,
  clipboardOutline,
  checkmarkCircleOutline,
  informationCircleOutline,
  warningOutline,
  arrowForwardOutline
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
const isSearching = ref(false);
let searchTimeout: NodeJS.Timeout | null = null;

const newPatient = ref<CreatePatientData>({
  full_name: '',
  birth_date: '',
  blood_type: '',
  allergies: '',
  observations: ''
});

// Computed properties
const filteredPatients = computed(() => {
  // Como a busca agora é feita no backend, apenas retorna os pacientes
  return patients.value;
});

const canCreatePatient = computed(() => {
  return newPatient.value.full_name.length > 0 &&
         newPatient.value.birth_date.length > 0;
});

// Methods
const loadPatients = async (search: string = '') => {
  const loading = await loadingController.create({
    message: search ? 'Buscando pacientes...' : 'Carregando pacientes...',
    spinner: 'crescent'
  });
  await loading.present();

  try {
    // Carrega os 100 últimos pacientes ou busca com o termo
    patients.value = await patientRepository.getAll(search, 100);
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
  const query = event.target?.value || event.detail?.value || '';
  searchQuery.value = query;
  
  // Limpa o timeout anterior
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
  
  // Se a busca estiver vazia, carrega os pacientes padrão
  if (!query || query.length === 0) {
    loadPatients();
    return;
  }
  
  // Aguarda o usuário parar de digitar (debounce de 500ms)
  searchTimeout = setTimeout(async () => {
    if (query.length >= 2) {
      isSearching.value = true;
      try {
        // Usa busca rápida otimizada
        patients.value = await patientRepository.quickSearch(query, 50);
      } catch (error) {
        console.error('Error searching patients:', error);
        const toast = await toastController.create({
          message: 'Erro ao buscar pacientes',
          duration: 2000,
          color: 'warning',
          position: 'top'
        });
        await toast.present();
      } finally {
        isSearching.value = false;
      }
    }
  }, 500);
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
/* ===== HEADER ===== */
.header-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
}

.header-title ion-icon {
  font-size: 1.5rem;
}

.add-button {
  --background: rgba(255, 255, 255, 0.2);
  --border-radius: 12px;
}

/* ===== CONTENT ===== */
.dashboard-content {
  --background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
}

/* ===== SEARCH ===== */
.search-container-dash {
  padding: 20px 16px;
}

.search-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: white;
  border-radius: 16px;
  padding: 8px 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.search-icon {
  font-size: 1.5rem;
  color: var(--ion-color-primary);
  flex-shrink: 0;
}

.search-bar-dash {
  --background: transparent;
  --box-shadow: none;
  --border-radius: 0;
  --placeholder-opacity: 0.6;
  --color: #1f2937;
  padding: 0;
  width: 100%;
}

.search-bar-dash::part(native) {
  padding-inline-start: 0 !important;
  padding-inline-end: 0 !important;
}

/* ===== STATS GRID ===== */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  padding: 0 16px 20px 16px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: white;
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: currentColor;
}

.stat-card.primary {
  color: var(--ion-color-primary);
}

.stat-card.success {
  color: var(--ion-color-success);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.stat-card.primary .stat-icon {
  background: rgba(59, 130, 246, 0.2);
}

.stat-card.success .stat-icon {
  background: rgba(16, 185, 129, 0.2);
}

.stat-icon ion-icon {
  font-size: 2rem;
  position: relative;
  z-index: 1;
  font-weight: bold;
}

.stat-card.primary .stat-icon ion-icon {
  color: #2563eb;
}

.stat-card.success .stat-icon ion-icon {
  color: #059669;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin-top: 4px;
}

/* ===== PATIENTS CONTAINER ===== */
.patients-container-dash {
  padding: 0 16px 80px 16px;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.section-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.section-header ion-badge {
  font-size: 0.875rem;
  font-weight: 700;
  padding: 8px 16px;
  border-radius: 12px;
}

/* ===== PATIENTS GRID ===== */
.patients-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.patient-card-dash {
  display: flex;
  align-items: center;
  gap: 16px;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.patient-card-dash:active {
  transform: scale(0.98);
  border-color: var(--ion-color-primary);
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.15);
}

.patient-avatar {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.patient-avatar ion-icon {
  font-size: 2rem;
  color: white;
}

.patient-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
}

.patient-header-dash {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.patient-header-dash h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.status-badge {
  flex-shrink: 0;
  font-size: 0.75rem;
  padding: 4px 8px;
}

.patient-meta {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.813rem;
  color: #6b7280;
}

.meta-item ion-icon {
  font-size: 1rem;
  flex-shrink: 0;
}

.patient-details-dash {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.detail-chip {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  background: #f3f4f6;
  border-radius: 8px;
  font-size: 0.75rem;
  color: #4b5563;
}

.detail-chip ion-icon {
  font-size: 0.875rem;
}

.patient-arrow {
  flex-shrink: 0;
  color: #d1d5db;
}

.patient-arrow ion-icon {
  font-size: 1.25rem;
}

/* ===== EMPTY STATE ===== */
.empty-state-dash {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 16px;
  border: 2px dashed #e5e7eb;
}

.empty-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.empty-icon ion-icon {
  font-size: 3rem;
  color: #9ca3af;
}

.empty-state-dash h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.empty-state-dash p {
  font-size: 0.938rem;
  color: #6b7280;
  margin: 0 0 20px 0;
}

.empty-action {
  --border-radius: 12px;
  --padding-start: 20px;
  --padding-end: 20px;
  font-weight: 600;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .patient-avatar {
    width: 48px;
    height: 48px;
  }

  .patient-avatar ion-icon {
    font-size: 1.75rem;
  }
}

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