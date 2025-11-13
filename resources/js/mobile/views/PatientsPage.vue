<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/dashboard"></ion-back-button>
        </ion-buttons>
        <ion-title>Pacientes</ion-title>
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
      <div class="stats-grid-extended">
        <!-- Total -->
        <div
          class="stat-card primary clickable"
          :class="{ 'updating': isStatsRefreshing, 'active': activeStatusFilter === null }"
          @click="filterByStatus(null)"
        >
          <div class="stat-icon">
            <ion-icon :icon="peopleOutline"></ion-icon>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ totalPatients }}</span>
            <span class="stat-label">Total</span>
          </div>
        </div>

        <!-- Ativos -->
        <div
          class="stat-card success clickable"
          :class="{ 'updating': isStatsRefreshing, 'active': activeStatusFilter === 'ativo' }"
          @click="filterByStatus('ativo')"
        >
          <div class="stat-icon">
            <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ statusCounts.ativo }}</span>
            <span class="stat-label">Ativos</span>
          </div>
        </div>

        <!-- Inativos -->
        <div
          class="stat-card warning clickable"
          :class="{ 'updating': isStatsRefreshing, 'active': activeStatusFilter === 'inativo' }"
          @click="filterByStatus('inativo')"
        >
          <div class="stat-icon">
            <ion-icon :icon="pauseCircleOutline"></ion-icon>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ statusCounts.inativo }}</span>
            <span class="stat-label">Inativos</span>
          </div>
        </div>

        <!-- Transferidos -->
        <div
          class="stat-card info clickable"
          :class="{ 'updating': isStatsRefreshing, 'active': activeStatusFilter === 'transferido' }"
          @click="filterByStatus('transferido')"
        >
          <div class="stat-icon">
            <ion-icon :icon="swapHorizontalOutline"></ion-icon>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ statusCounts.transferido }}</span>
            <span class="stat-label">Transferidos</span>
          </div>
        </div>

        <!-- Alta -->
        <div
          class="stat-card medium clickable"
          :class="{ 'updating': isStatsRefreshing, 'active': activeStatusFilter === 'alta' }"
          @click="filterByStatus('alta')"
        >
          <div class="stat-icon">
            <ion-icon :icon="arrowUpCircleOutline"></ion-icon>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ statusCounts.alta }}</span>
            <span class="stat-label">Alta</span>
          </div>
        </div>

        <!-- Óbito -->
        <div
          class="stat-card danger clickable"
          :class="{ 'updating': isStatsRefreshing, 'active': activeStatusFilter === 'obito' }"
          @click="filterByStatus('obito')"
        >
          <div class="stat-icon">
            <ion-icon :icon="closeCircleOutline"></ion-icon>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ statusCounts.obito }}</span>
            <span class="stat-label">Óbito</span>
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
                <ion-badge :color="getStatusColor(patient.status)" class="status-badge">
                  {{ patient.status_label || 'Ativo' }}
                </ion-badge>
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

      <!-- Create Patient Modal - COMPLETE FORM -->
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
              <div class="patient-avatar-large">
                <ion-icon :icon="personAddOutline"></ion-icon>
              </div>
              <h2>Cadastrar Novo Paciente</h2>
              <p>Preencha todas as informações necessárias</p>
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

              <!-- Tipo Sanguíneo -->
              <div class="input-card-dash">
                <div class="card-icon-dash tertiary">
                  <ion-icon :icon="waterOutline"></ion-icon>
                </div>
                <div class="card-content-dash">
                  <label>Tipo Sanguíneo <span class="optional">(opcional)</span></label>
                  <ion-select
                    v-model="newPatient.blood_type"
                    placeholder="Selecione o tipo sanguíneo"
                    interface="action-sheet"
                    class="select-dash"
                  >
                    <ion-select-option value="">Não informado</ion-select-option>
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

              <!-- Prontuário Médico -->
              <div class="input-card-dash">
                <div class="card-icon-dash quaternary">
                  <ion-icon :icon="clipboardOutline"></ion-icon>
                </div>
                <div class="card-content-dash">
                  <label>Prontuário Médico <span class="optional">(opcional)</span></label>
                  <ion-input
                    v-model="newPatient.medical_record"
                    type="text"
                    placeholder="Número do prontuário"
                    class="input-dash"
                  ></ion-input>
                </div>
              </div>

              <!-- Alergias -->
              <div class="input-card-dash">
                <div class="card-icon-dash danger">
                  <ion-icon :icon="alertCircleOutline"></ion-icon>
                </div>
                <div class="card-content-dash">
                  <label>Alergias <span class="optional">(opcional)</span></label>
                  <ion-textarea
                    v-model="newPatient.allergies"
                    rows="3"
                    placeholder="Descreva alergias conhecidas"
                    class="textarea-dash"
                  ></ion-textarea>
                </div>
              </div>

              <!-- Observações -->
              <div class="input-card-dash">
                <div class="card-icon-dash medium">
                  <ion-icon :icon="medkitOutline"></ion-icon>
                </div>
                <div class="card-content-dash">
                  <label>Observações Médicas <span class="optional">(opcional)</span></label>
                  <ion-textarea
                    v-model="newPatient.observations"
                    rows="4"
                    placeholder="Observações gerais sobre o paciente"
                    class="textarea-dash"
                  ></ion-textarea>
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
                Cadastrar Paciente
              </button>
            </div>

          </form>
        </ion-content>
      </ion-modal>

      <!-- Patient Details Modal - COMPACT EDITABLE -->
      <ion-modal :is-open="showDetailsModal" @will-dismiss="showDetailsModal = false">
        <ion-header>
          <ion-toolbar color="primary">
            <ion-title>
              <div class="modal-title-dash">
                <ion-icon :icon="personOutline"></ion-icon>
                <span>Editar Paciente</span>
              </div>
            </ion-title>
            <ion-buttons slot="end">
              <ion-button @click="showDetailsModal = false">
                <ion-icon :icon="closeOutline" slot="icon-only"></ion-icon>
              </ion-button>
            </ion-buttons>
          </ion-toolbar>
        </ion-header>

        <ion-content class="modal-content-dash" v-if="selectedPatientDetails">
          <form @submit.prevent="updatePatient" class="form-dash">
            <!-- Header Info -->
            <div class="edit-header-card">
              <div class="patient-avatar-large">
                <ion-icon :icon="personOutline"></ion-icon>
              </div>
              <h2>Editar Informações</h2>
              <p>Atualize os dados do paciente</p>
            </div>

            <!-- Editable Input Cards -->
            <div class="input-cards-dash">
              <!-- Nome Completo -->
              <div class="input-card-dash">
                <div class="card-icon-dash primary">
                  <ion-icon :icon="personOutline"></ion-icon>
                </div>
                <div class="card-content-dash">
                  <label>Nome Completo</label>
                  <ion-input
                    v-model="selectedPatientDetails.full_name"
                    type="text"
                    required
                    placeholder="Nome completo do paciente"
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
                    v-model="selectedPatientDetails.birth_date"
                    type="date"
                    required
                    class="input-dash"
                  ></ion-input>
                  <span class="detail-hint" v-if="selectedPatientDetails.age">{{ selectedPatientDetails.age }} anos</span>
                </div>
              </div>

              <!-- Tipo Sanguíneo -->
              <div class="input-card-dash">
                <div class="card-icon-dash tertiary">
                  <ion-icon :icon="waterOutline"></ion-icon>
                </div>
                <div class="card-content-dash">
                  <label>Tipo Sanguíneo <span class="optional">(opcional)</span></label>
                  <ion-select
                    v-model="selectedPatientDetails.blood_type"
                    placeholder="Selecione o tipo"
                    interface="action-sheet"
                    class="select-dash"
                  >
                    <ion-select-option value="">Não informado</ion-select-option>
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

              <!-- Status - DROPDOWN COMPACTO -->
              <div class="input-card-dash status-card">
                <div class="card-icon-dash" :class="getStatusIconClass(selectedStatus)">
                  <ion-icon :icon="getStatusIcon(selectedStatus)"></ion-icon>
                </div>
                <div class="card-content-dash">
                  <label>Status do Paciente</label>
                  <ion-select
                    v-model="selectedStatus"
                    placeholder="Selecione o status"
                    interface="action-sheet"
                    class="select-dash"
                  >
                    <ion-select-option value="ativo">✅ Ativo - Pode realizar sessões</ion-select-option>
                    <ion-select-option value="inativo">⏸️ Inativo - Temporariamente suspenso</ion-select-option>
                    <ion-select-option value="transferido">🔄 Transferido - Outra unidade</ion-select-option>
                    <ion-select-option value="alta">⬆️ Alta Médica</ion-select-option>
                    <ion-select-option value="obito">❌ Óbito - Falecimento</ion-select-option>
                  </ion-select>
                  <span class="detail-hint" v-if="selectedPatientDetails.status === 'alta' && selectedStatus === 'ativo'">
                    ℹ️ Paciente retornou à unidade após alta
                  </span>
                  <span class="detail-hint" v-if="selectedPatientDetails.status === 'obito'">
                    ⚠️ Paciente com status de óbito
                  </span>
                </div>
              </div>

              <!-- Prontuário Médico -->
              <div class="input-card-dash">
                <div class="card-icon-dash quaternary">
                  <ion-icon :icon="clipboardOutline"></ion-icon>
                </div>
                <div class="card-content-dash">
                  <label>Prontuário Médico <span class="optional">(opcional)</span></label>
                  <ion-input
                    v-model="selectedPatientDetails.medical_record"
                    type="text"
                    placeholder="Número do prontuário"
                    class="input-dash"
                  ></ion-input>
                </div>
              </div>

              <!-- Alergias -->
              <div class="input-card-dash">
                <div class="card-icon-dash danger">
                  <ion-icon :icon="alertCircleOutline"></ion-icon>
                </div>
                <div class="card-content-dash">
                  <label>Alergias <span class="optional">(opcional)</span></label>
                  <ion-textarea
                    v-model="selectedPatientDetails.allergies"
                    rows="3"
                    placeholder="Descreva alergias conhecidas"
                    class="textarea-dash"
                  ></ion-textarea>
                </div>
              </div>

              <!-- Observações -->
              <div class="input-card-dash">
                <div class="card-icon-dash medium">
                  <ion-icon :icon="medkitOutline"></ion-icon>
                </div>
                <div class="card-content-dash">
                  <label>Observações Médicas <span class="optional">(opcional)</span></label>
                  <ion-textarea
                    v-model="selectedPatientDetails.observations"
                    rows="4"
                    placeholder="Observações gerais sobre o paciente"
                    class="textarea-dash"
                  ></ion-textarea>
                </div>
              </div>

              <!-- Info Card: Checklists -->
              <div class="info-card-compact">
                <ion-icon :icon="clipboardOutline"></ion-icon>
                <span><strong>{{ selectedPatientDetails.checklists_count }}</strong> checklists realizados</span>
              </div>
            </div>

            <!-- Buttons -->
            <div class="buttons-dash">
              <button
                type="button"
                class="btn-cancel-dash"
                @click="showDetailsModal = false"
              >
                <ion-icon :icon="closeOutline"></ion-icon>
                Cancelar
              </button>

              <button
                type="submit"
                class="btn-submit-dash"
                :disabled="isSavingPatient"
              >
                <ion-icon :icon="saveOutline"></ion-icon>
                {{ isSavingPatient ? 'Salvando...' : 'Salvar Alterações' }}
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
  arrowForwardOutline,
  pauseCircleOutline,
  swapHorizontalOutline,
  arrowUpCircleOutline,
  closeCircleOutline
} from 'ionicons/icons';

import { Container } from '@mobile/core/di/Container';
import { Patient, CreatePatientData } from '@mobile/core/domain/entities/Patient';
import { PatientRepository } from '@mobile/core/domain/repositories/PatientRepository';
import { useStatsAutoRefresh } from '@mobile/composables/useStatsAutoRefresh';
import { AuthService } from '@shared/auth';

const container = Container.getInstance();

// Use cases
const createPatientUseCase = container.getCreatePatientUseCase();
const patientRepository = container.get<PatientRepository>('PatientRepository');

// Reactive state
const patients = ref<Patient[]>([]);
const searchQuery = ref('');
const showCreateModal = ref(false);
const showDetailsModal = ref(false);
const selectedPatientDetails = ref<any>(null);
const selectedStatus = ref<string>('');
const isSearching = ref(false);
const isTogglingStatus = ref(false);
const isSavingPatient = ref(false);
const activeStatusFilter = ref<string | null>(null);
let searchTimeout: NodeJS.Timeout | null = null;

const newPatient = ref<CreatePatientData>({
  full_name: '',
  birth_date: '',
  blood_type: '',
  medical_record: '',
  allergies: '',
  observations: ''
});

// Computed properties
const filteredPatients = computed(() => {
  // Se há um filtro de status ativo, filtra os pacientes
  if (activeStatusFilter.value !== null) {
    return patients.value.filter(patient => patient.status === activeStatusFilter.value);
  }
  // Caso contrário, retorna todos os pacientes
  return patients.value;
});

const canCreatePatient = computed(() => {
  return newPatient.value.full_name.length > 0 &&
         newPatient.value.birth_date.length > 0;
});

// Status statistics
const totalPatients = computed(() => patients.value.length);

const statusCounts = computed(() => {
  const counts = {
    ativo: 0,
    inativo: 0,
    transferido: 0,
    alta: 0,
    obito: 0
  };

  patients.value.forEach(patient => {
    const status = patient.status || 'ativo';
    if (counts.hasOwnProperty(status)) {
      counts[status]++;
    }
  });

  return counts;
});

// Methods
const loadPatients = async (search: string = '') => {
  const loading = await loadingController.create({
    message: search ? 'Buscando pacientes...' : 'Carregando pacientes...',
    spinner: 'crescent'
  });
  await loading.present();

  try {
    // Carrega TODOS os pacientes (incluindo inativos) para mostrar estatísticas completas
    // Usa parâmetro include_inactive=true na API
    const response = await fetch(`/api/patients?per_page=100&include_inactive=true${search ? `&search=${encodeURIComponent(search)}` : ''}`,
      AuthService.getFetchConfig()
    );

    if (!response.ok) {
      throw new Error('Erro ao carregar pacientes');
    }

    const data = await response.json();

    if (data.success) {
      patients.value = data.patients || [];
    } else {
      throw new Error(data.message || 'Erro ao carregar pacientes');
    }
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

const filterByStatus = (status: string | null) => {
  activeStatusFilter.value = status;
};

const selectPatient = async (patient: Patient) => {
  try {
    const loading = await loadingController.create({
      message: 'Carregando detalhes...',
      spinner: 'crescent'
    });
    await loading.present();

    // Busca detalhes completos do paciente
    const response = await fetch(`/api/patients/${patient.id}`,
      AuthService.getFetchConfig()
    );

    const data = await response.json();

    if (data.success) {
      selectedPatientDetails.value = data.patient;
      selectedStatus.value = data.patient.status || 'ativo';
      showDetailsModal.value = true;
    } else {
      throw new Error(data.message || 'Erro ao carregar detalhes');
    }

    await loading.dismiss();
  } catch (error: any) {
    console.error('Error loading patient details:', error);
    const toast = await toastController.create({
      message: error.message || 'Erro ao carregar detalhes do paciente',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  }
};

const togglePatientStatus = async () => {
  if (!selectedPatientDetails.value) return;

  try {
    isTogglingStatus.value = true;

    const response = await fetch(`/api/patients/${selectedPatientDetails.value.id}/toggle-active`,
      AuthService.getFetchConfig({ method: 'PATCH' })
    );

    const data = await response.json();

    if (data.success) {
      // Atualiza o status no modal
      selectedPatientDetails.value.status = data.patient.status;
      selectedPatientDetails.value.status_label = data.patient.status_label;
      selectedPatientDetails.value.status_color = data.patient.status_color;
      selectedStatus.value = data.patient.status;

      // Atualiza na lista também
      const patientIndex = patients.value.findIndex(p => p.id === selectedPatientDetails.value.id);
      if (patientIndex !== -1) {
        patients.value[patientIndex] = {
          ...patients.value[patientIndex],
          status: data.patient.status,
          status_label: data.patient.status_label,
          status_color: data.patient.status_color
        };
      }

      const toast = await toastController.create({
        message: data.message,
        duration: 2000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
    } else {
      throw new Error(data.message || 'Erro ao alterar status');
    }
  } catch (error: any) {
    console.error('Error toggling patient status:', error);
    const toast = await toastController.create({
      message: error.message || 'Erro ao alterar status do paciente',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    isTogglingStatus.value = false;
  }
};

const updatePatient = async () => {
  if (!selectedPatientDetails.value) return;

  try {
    isSavingPatient.value = true;

    const response = await fetch(`/api/patients/${selectedPatientDetails.value.id}`,
      AuthService.getFetchConfig({
        method: 'PUT',
        body: JSON.stringify({
          full_name: selectedPatientDetails.value.full_name,
          birth_date: selectedPatientDetails.value.birth_date,
          blood_type: selectedPatientDetails.value.blood_type || null,
          medical_record: selectedPatientDetails.value.medical_record || null,
          allergies: selectedPatientDetails.value.allergies || null,
          observations: selectedPatientDetails.value.observations || null,
          status: selectedStatus.value
        })
      })
    );

    const data = await response.json();

    if (data.success) {
      // Atualiza na lista
      const patientIndex = patients.value.findIndex(p => p.id === selectedPatientDetails.value.id);
      if (patientIndex !== -1) {
        patients.value[patientIndex] = {
          ...patients.value[patientIndex],
          ...data.patient
        };
      }

      // Fecha o modal
      showDetailsModal.value = false;

      const toast = await toastController.create({
        message: 'Paciente atualizado com sucesso!',
        duration: 2000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
    } else {
      throw new Error(data.message || 'Erro ao atualizar paciente');
    }
  } catch (error: any) {
    console.error('Error updating patient:', error);
    const toast = await toastController.create({
      message: error.message || 'Erro ao atualizar paciente',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    isSavingPatient.value = false;
  }
};

const updatePatientStatus = async () => {
  if (!selectedPatientDetails.value || !selectedStatus.value) return;

  try {
    isTogglingStatus.value = true;

    const response = await fetch(`/api/patients/${selectedPatientDetails.value.id}/status`,
      AuthService.getFetchConfig({
        method: 'PATCH',
        body: JSON.stringify({ status: selectedStatus.value })
      })
    );

    const data = await response.json();

    if (data.success) {
      // Atualiza o status no modal
      selectedPatientDetails.value.status = data.patient.status;
      selectedPatientDetails.value.status_label = data.patient.status_label;
      selectedPatientDetails.value.status_color = data.patient.status_color;
      selectedPatientDetails.value.can_have_sessions = data.patient.can_have_sessions;

      // Atualiza na lista também
      const patientIndex = patients.value.findIndex(p => p.id === selectedPatientDetails.value.id);
      if (patientIndex !== -1) {
        patients.value[patientIndex] = {
          ...patients.value[patientIndex],
          status: data.patient.status,
          status_label: data.patient.status_label,
          status_color: data.patient.status_color,
          can_have_sessions: data.patient.can_have_sessions
        };
      }

      const toast = await toastController.create({
        message: data.message,
        duration: 2000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
    } else {
      throw new Error(data.message || 'Erro ao atualizar status');
    }
  } catch (error: any) {
    console.error('Error updating patient status:', error);
    const toast = await toastController.create({
      message: error.message || 'Erro ao atualizar status do paciente',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    isTogglingStatus.value = false;
  }
};

// Helper function to get status color
const getStatusColor = (status: string): string => {
  const colorMap: Record<string, string> = {
    'ativo': 'success',
    'inativo': 'warning',
    'transferido': 'primary',
    'alta': 'medium',
    'obito': 'danger'
  };
  return colorMap[status] || 'success';
};

// Helper function to check if status is terminal
const isTerminalStatus = (status: string): boolean => {
  return status === 'alta' || status === 'obito';
};

// Helper function to select status option
const selectStatusOption = (status: string) => {
  if (!isTogglingStatus.value && !isTerminalStatus(selectedPatientDetails.value?.status)) {
    selectedStatus.value = status;
  }
};

// Helper function to get status label
const getStatusLabel = (status: string): string => {
  const labelMap: Record<string, string> = {
    'ativo': 'Ativo',
    'inativo': 'Inativo',
    'transferido': 'Transferido',
    'alta': 'Alta Médica',
    'obito': 'Óbito'
  };
  return labelMap[status] || status;
};

// Helper function to get status icon
const getStatusIcon = (status: string) => {
  const iconMap: Record<string, any> = {
    'ativo': checkmarkCircleOutline,
    'inativo': pauseCircleOutline,
    'transferido': swapHorizontalOutline,
    'alta': arrowUpCircleOutline,
    'obito': closeCircleOutline
  };
  return iconMap[status] || checkmarkCircleOutline;
};

// Helper function to get status icon class
const getStatusIconClass = (status: string): string => {
  const classMap: Record<string, string> = {
    'ativo': 'success',
    'inativo': 'warning',
    'transferido': 'info',
    'alta': 'medium',
    'obito': 'danger'
  };
  return classMap[status] || 'success';
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
      medical_record: '',
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

// Auto-refresh dos stats cards
const {
  isRefreshing: isStatsRefreshing,
  forceRefresh: forceStatsRefresh
} = useStatsAutoRefresh(loadPatients, {
  loadOnMount: false,
  interval: 15000,
  onStatsUpdated: () => {
    console.log('[Patients] Stats atualizados automaticamente');
  }
});

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
  --background: var(--ion-background-color);
}

/* ===== SEARCH ===== */
.search-container-dash {
  padding: 20px 16px;
}

.search-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: var(--ion-card-background);
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
  --color: var(--ion-text-color);
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

.stats-grid-extended {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  padding: 0 16px 20px 16px;
}

@media (min-width: 640px) {
  .stats-grid-extended {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 1024px) {
  .stats-grid-extended {
    grid-template-columns: repeat(6, 1fr);
  }
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: var(--ion-card-background);
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

.stat-card.warning {
  color: #f59e0b;
}

.stat-card.info {
  color: var(--ion-color-primary);
}

.stat-card.medium {
  color: #6b7280;
}

.stat-card.danger {
  color: #ef4444;
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

.stat-card.warning .stat-icon {
  background: rgba(245, 158, 11, 0.2);
}

.stat-card.info .stat-icon {
  background: rgba(59, 130, 246, 0.2);
}

.stat-card.medium .stat-icon {
  background: rgba(107, 114, 128, 0.2);
}

.stat-card.danger .stat-icon {
  background: rgba(239, 68, 68, 0.2);
}

.stat-icon ion-icon {
  font-size: 2rem;
  position: relative;
  z-index: 1;
  font-weight: bold;
}

.stat-card.primary .stat-icon ion-icon {
  color: var(--ion-color-primary);
}

.stat-card.success .stat-icon ion-icon {
  color: #059669;
}

.stat-card.warning .stat-icon ion-icon {
  color: #d97706;
}

.stat-card.info .stat-icon ion-icon {
  color: var(--ion-color-primary);
}

.stat-card.medium .stat-icon ion-icon {
  color: #4b5563;
}

.stat-card.danger .stat-icon ion-icon {
  color: #dc2626;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--ion-text-color);
  line-height: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: var(--ion-color-step-600);
  margin-top: 4px;
}

/* Animação de atualização dos stats */
.stat-card.updating {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(0.98);
  }
}

/* Clickable and Active States */
.stat-card.clickable {
  cursor: pointer;
  transition: all 0.2s ease;
}

.stat-card.clickable:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.stat-card.clickable:active {
  transform: translateY(0);
}

.stat-card.active {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  border: 2px solid currentColor;
}

.stat-card.active::before {
  height: 6px;
}

.stat-card.active .stat-value {
  font-size: 2rem;
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
  color: var(--ion-text-color);
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
  background: var(--ion-card-background);
  border: 2px solid var(--ion-color-step-150);
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
  color: var(--ion-text-color);
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
  color: var(--ion-color-step-600);
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
  background: var(--ion-color-step-50);
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
  background: var(--ion-card-background);
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
  color: var(--ion-color-step-500);
}

.empty-state-dash h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--ion-text-color);
  margin: 0 0 8px 0;
}

.empty-state-dash p {
  font-size: 0.938rem;
  color: var(--ion-color-step-600);
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
  --background: var(--ion-background-color);
  --padding: 0;
}

.form-dash {
  max-width: 600px;
  margin: 0 auto;
  padding: 16px;
}

/* Welcome Card */
.welcome-card-dash {
  background: var(--ion-card-background);
  padding: 20px;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 20px;
  text-align: center;
}

.welcome-card-dash h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--ion-text-color);
  margin: 0 0 8px 0;
}

.welcome-card-dash p {
  font-size: 0.9rem;
  color: var(--ion-color-step-600);
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
  background: var(--ion-card-background);
  border: 3px solid #d1d5db;
  transition: all 0.3s ease;
}

.progress-item.done .progress-dot {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  border-color: var(--ion-color-primary);
}

.progress-item span {
  font-size: 0.7rem;
  color: var(--ion-color-step-600);
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
  background: var(--ion-card-background);
  border: 2px solid var(--ion-color-step-150);
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
  color: var(--ion-text-color);
  margin: 0;
}

.optional {
  font-size: 0.75rem;
  font-weight: 400;
  color: var(--ion-color-step-500);
}

.input-dash,
.select-dash {
  --padding-start: 0;
  --padding-end: 0;
  --padding-top: 0;
  --padding-bottom: 0;
  font-size: 1rem;
  color: var(--ion-text-color);
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
  background: var(--ion-card-background);
  color: var(--ion-color-step-600);
  border: 2px solid var(--ion-color-step-150);
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

/* ===== EDIT MODAL STYLES ===== */

/* Edit Header Card */
.edit-header-card {
  background: var(--ion-card-background);
  padding: 24px 20px;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 20px;
  text-align: center;
}

.patient-avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.patient-avatar-large ion-icon {
  font-size: 3rem;
  color: white;
}

.edit-header-card h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--ion-text-color);
  margin: 0 0 8px 0;
}

.edit-header-card p {
  font-size: 0.9rem;
  color: var(--ion-color-step-600);
  margin: 0;
}

/* Detail Hint */
.detail-hint {
  display: block;
  font-size: 0.813rem;
  color: var(--ion-color-step-500);
  margin-top: 6px;
  font-style: italic;
}

.terminal-hint {
  color: #dc2626;
  font-weight: 600;
}

/* Textarea Styles */
.textarea-dash {
  --padding-start: 0;
  --padding-end: 0;
  --padding-top: 8px;
  --padding-bottom: 8px;
  font-size: 0.938rem;
  color: var(--ion-text-color);
  min-height: 80px;
}

.textarea-dash::part(native) {
  padding: 0 !important;
}

/* Status Card Specific */
.status-card {
  border: 2px solid var(--ion-color-primary);
  background: rgba(59, 130, 246, 0.03);
}

/* Additional Icon Classes */
.card-icon-dash.quaternary {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.card-icon-dash.info {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.card-icon-dash.success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.card-icon-dash.warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.card-icon-dash.danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.card-icon-dash.medium {
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
}

/* Info Card Compact */
.info-card-compact {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  border-radius: 12px;
  border: 2px solid var(--ion-color-step-150);
}

.info-card-compact ion-icon {
  font-size: 2rem;
  color: var(--ion-color-primary);
}

.info-card-compact span {
  font-size: 0.938rem;
  color: var(--ion-color-step-700);
}

.info-card-compact strong {
  font-size: 1.25rem;
  color: var(--ion-color-primary);
  font-weight: 700;
}

/* Patient Details Modal Styles */
.patient-details-modal {
  padding: 0;
}

.details-card {
  margin: 0 0 16px 0;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.details-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
}

.details-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 40px;
}

.details-title {
  flex: 1;
}

.details-title h2 {
  margin: 0 0 8px 0;
  font-size: 24px;
  font-weight: 600;
  color: #2c3e50;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.detail-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px;
  background: var(--ion-background-color);
  border-radius: 12px;
}

.detail-item ion-icon {
  font-size: 24px;
  color: var(--ion-color-primary);
  margin-top: 4px;
}

.detail-item div {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-label {
  font-size: 12px;
  color: #6c757d;
  font-weight: 500;
  text-transform: uppercase;
}

.detail-value {
  font-size: 16px;
  color: #2c3e50;
  font-weight: 600;
}

.detail-section {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e9ecef;
}

.detail-section h4 {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 0 12px 0;
  font-size: 16px;
  font-weight: 600;
  color: #495057;
}

.detail-section h4 ion-icon {
  font-size: 20px;
  color: var(--ion-color-primary);
}

.detail-section p {
  margin: 0;
  font-size: 14px;
  line-height: 1.6;
  color: #6c757d;
  padding: 12px;
  background: var(--ion-background-color);
  border-radius: 8px;
}

.details-actions {
  padding: 0 16px 16px 16px;
}

.details-actions ion-button {
  --border-radius: 12px;
  font-weight: 600;
  text-transform: none;
  height: 48px;
}

@media (max-width: 480px) {
  .details-grid {
    grid-template-columns: 1fr;
  }

  .details-avatar {
    width: 60px;
    height: 60px;
    font-size: 30px;
  }

  .details-title h2 {
    font-size: 20px;
  }
}

/* Status Management Card */
.status-management-card {
  margin-top: 16px;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.status-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 18px;
  font-weight: 700;
  color: var(--ion-text-color);
  margin-bottom: 8px;
}

.status-title ion-icon {
  font-size: 24px;
  color: var(--ion-color-primary);
}

.status-description {
  font-size: 14px;
  color: var(--ion-color-step-600);
  margin-bottom: 24px;
  line-height: 1.6;
}

/* Terminal Alert (when status is already terminal) */
.terminal-alert {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px;
  background: linear-gradient(135deg, #fff3cd 0%, #ffe4a8 100%);
  border: 2px solid #ffc107;
  border-radius: 12px;
  margin-bottom: 20px;
}

.terminal-alert ion-icon {
  font-size: 32px;
  color: #856404;
  flex-shrink: 0;
}

.terminal-alert strong {
  display: block;
  font-size: 16px;
  color: #856404;
  margin-bottom: 4px;
}

.terminal-alert p {
  margin: 0;
  font-size: 14px;
  color: #856404;
  line-height: 1.5;
}

/* Status Options Grid */
.status-options-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}

@media (max-width: 480px) {
  .status-options-grid {
    grid-template-columns: 1fr;
  }
}

/* Status Option Card */
.status-option {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 20px 16px;
  background: var(--ion-background-color);
  border: 2px solid var(--ion-color-step-150);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.status-option:active {
  transform: scale(0.96);
}

.status-option.selected {
  border-color: var(--ion-color-primary);
  background: rgba(59, 130, 246, 0.05);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.status-option.current {
  background: var(--ion-card-background);
  border-style: dashed;
}

/* Status Option Icon */
.status-option-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.status-option-icon ion-icon {
  font-size: 32px;
  color: white;
}

.status-option-icon.success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.status-option-icon.warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.status-option-icon.primary {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
}

.status-option-icon.medium {
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
}

.status-option-icon.danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

/* Status Option Content */
.status-option-content {
  flex: 1;
}

.status-option-content h5 {
  margin: 0 0 4px 0;
  font-size: 16px;
  font-weight: 700;
  color: var(--ion-text-color);
}

.status-option-content p {
  margin: 0;
  font-size: 13px;
  color: var(--ion-color-step-600);
  line-height: 1.4;
}

/* Current Badge */
.current-badge {
  position: absolute;
  top: 8px;
  right: 8px;
  font-size: 11px;
  padding: 4px 8px;
  font-weight: 700;
}

/* Selection Indicator */
.selection-indicator {
  position: absolute;
  top: 8px;
  left: 8px;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--ion-color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
  animation: scaleIn 0.2s ease;
}

.selection-indicator ion-icon {
  font-size: 18px;
  color: white;
}

@keyframes scaleIn {
  from {
    transform: scale(0);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

/* Terminal Warning (when selecting alta or obito) */
.terminal-warning {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px;
  background: #fff3cd;
  border: 2px solid #ffc107;
  border-radius: 10px;
  margin-bottom: 16px;
}

.terminal-warning ion-icon {
  font-size: 24px;
  color: #856404;
  flex-shrink: 0;
  margin-top: 2px;
}

.terminal-warning p {
  margin: 0;
  font-size: 14px;
  color: #856404;
  line-height: 1.5;
}

.terminal-warning strong {
  font-weight: 700;
}

/* Status Actions */
.status-actions {
  margin-top: 20px;
}

.status-actions ion-button {
  --border-radius: 12px;
  --padding-top: 16px;
  --padding-bottom: 16px;
  font-weight: 700;
  font-size: 15px;
  text-transform: none;
}

.help-text {
  margin: 12px 0 0 0;
  text-align: center;
  font-size: 13px;
  color: var(--ion-color-step-600);
  line-height: 1.4;
}
</style>