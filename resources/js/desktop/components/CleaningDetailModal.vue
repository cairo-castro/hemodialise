<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="close"
      >
        <div
          class="bg-white dark:bg-gray-950 rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden border border-gray-200 dark:border-gray-800 flex flex-col"
          @click.stop
        >
          <!-- Header -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-800 flex-shrink-0">
            <div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                Detalhes da Limpeza #{{ cleaning?.id }}
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ formatDate(cleaning?.cleaning_date) }}
              </p>
            </div>
            <button
              @click="close"
              class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Content -->
          <div class="p-6 overflow-y-auto flex-1">
            <div v-if="loading" class="flex items-center justify-center py-12">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
            </div>

            <div v-else-if="cleaning" class="space-y-6">
              <!-- Type Badge -->
              <div class="flex items-center gap-3">
                <span :class="getCleaningTypeBadgeClass(cleaning)">
                  {{ getCleaningTypeLabel(cleaning) }}
                </span>
              </div>

              <!-- Basic Info -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                  <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Máquina</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ cleaning.machine?.name || 'N/A' }}
                  </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                  <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Data da Limpeza</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ formatDate(cleaning.cleaning_date) }}
                  </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                  <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Turno</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ getShiftLabel(cleaning.shift) }}
                  </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                  <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Responsável</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ cleaning.user?.name || 'N/A' }}
                  </p>
                </div>
              </div>

              <!-- Cleaning Items -->
              <div class="space-y-4">
                <h4 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
                  Itens da Limpeza
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Editable mode with Conforme/Não Conforme/Não se Aplica -->
                  <template v-if="editMode">
                    <!-- Máquina de HD -->
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg space-y-2">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">Máquina de HD</p>
                      <div class="flex gap-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                            v-model="editableData.hd_machine_cleaning"
                            type="radio"
                            :value="true"
                            class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">Conforme</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                            v-model="editableData.hd_machine_cleaning"
                            type="radio"
                            :value="false"
                            class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">Não Conforme</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                            v-model="editableData.hd_machine_cleaning"
                            type="radio"
                            :value="null"
                            class="w-4 h-4 text-gray-600 border-gray-300 focus:ring-gray-500"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">N/A</span>
                        </label>
                      </div>
                    </div>

                    <!-- Osmose -->
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg space-y-2">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">Osmose</p>
                      <div class="flex gap-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                            v-model="editableData.osmosis_cleaning"
                            type="radio"
                            :value="true"
                            class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">Conforme</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                            v-model="editableData.osmosis_cleaning"
                            type="radio"
                            :value="false"
                            class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">Não Conforme</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                            v-model="editableData.osmosis_cleaning"
                            type="radio"
                            :value="null"
                            class="w-4 h-4 text-gray-600 border-gray-300 focus:ring-gray-500"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">N/A</span>
                        </label>
                      </div>
                    </div>

                    <!-- Suporte de Soro -->
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg space-y-2">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">Suporte de Soro</p>
                      <div class="flex gap-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                            v-model="editableData.serum_support_cleaning"
                            type="radio"
                            :value="true"
                            class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">Conforme</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                            v-model="editableData.serum_support_cleaning"
                            type="radio"
                            :value="false"
                            class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">Não Conforme</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                            v-model="editableData.serum_support_cleaning"
                            type="radio"
                            :value="null"
                            class="w-4 h-4 text-gray-600 border-gray-300 focus:ring-gray-500"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">N/A</span>
                        </label>
                      </div>
                    </div>

                    <!-- Desinfecção Química -->
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg space-y-2">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">Desinfecção Química</p>
                      <div class="flex gap-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                            v-model="editableData.chemical_disinfection"
                            type="radio"
                            :value="true"
                            class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">Conforme</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                            v-model="editableData.chemical_disinfection"
                            type="radio"
                            :value="false"
                            class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">Não Conforme</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                            v-model="editableData.chemical_disinfection"
                            type="radio"
                            :value="null"
                            class="w-4 h-4 text-gray-600 border-gray-300 focus:ring-gray-500"
                          />
                          <span class="text-sm text-gray-700 dark:text-gray-300">N/A</span>
                        </label>
                      </div>
                    </div>
                  </template>

                  <!-- Read-only view with badges -->
                  <template v-else>
                    <div class="flex items-start justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                      <span class="text-sm text-gray-900 dark:text-white">Máquina de HD</span>
                      <span :class="getComplianceStatusClass(cleaning.hd_machine_cleaning)">
                        {{ getComplianceStatusLabel(cleaning.hd_machine_cleaning) }}
                      </span>
                    </div>

                    <div class="flex items-start justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                      <span class="text-sm text-gray-900 dark:text-white">Osmose</span>
                      <span :class="getComplianceStatusClass(cleaning.osmosis_cleaning)">
                        {{ getComplianceStatusLabel(cleaning.osmosis_cleaning) }}
                      </span>
                    </div>

                    <div class="flex items-start justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                      <span class="text-sm text-gray-900 dark:text-white">Suporte de Soro</span>
                      <span :class="getComplianceStatusClass(cleaning.serum_support_cleaning)">
                        {{ getComplianceStatusLabel(cleaning.serum_support_cleaning) }}
                      </span>
                    </div>

                    <div class="flex items-start justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                      <span class="text-sm text-gray-900 dark:text-white">Desinfecção Química</span>
                      <span :class="getComplianceStatusClass(cleaning.chemical_disinfection)">
                        {{ getComplianceStatusLabel(cleaning.chemical_disinfection) }}
                      </span>
                    </div>
                  </template>
                </div>
              </div>

              <!-- Products and Procedure -->
              <div class="space-y-4">
                <h4 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
                  Produtos e Procedimentos
                </h4>

                <div class="grid grid-cols-1 gap-4">
                  <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Produtos Utilizados</p>
                    <p v-if="!editMode" class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">
                      {{ cleaning.cleaning_products_used || 'Não informado' }}
                    </p>
                    <textarea
                      v-else
                      v-model="editableData.cleaning_products_used"
                      rows="3"
                      class="w-full text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-2 focus:ring-2 focus:ring-primary-500"
                      placeholder="Produtos utilizados na limpeza..."
                    ></textarea>
                  </div>

                  <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Procedimento de Limpeza</p>
                    <p v-if="!editMode" class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">
                      {{ cleaning.cleaning_procedure || 'Não informado' }}
                    </p>
                    <textarea
                      v-else
                      v-model="editableData.cleaning_procedure"
                      rows="4"
                      class="w-full text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-2 focus:ring-2 focus:ring-primary-500"
                      placeholder="Descreva o procedimento..."
                    ></textarea>
                  </div>
                </div>
              </div>

              <!-- Observations -->
              <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Observações</p>
                <p v-if="!editMode" class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">
                  {{ cleaning.observations || 'Sem observações' }}
                </p>
                <textarea
                  v-else
                  v-model="editableData.observations"
                  rows="3"
                  class="w-full text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-2 focus:ring-2 focus:ring-primary-500"
                  placeholder="Observações gerais..."
                ></textarea>
              </div>
            </div>

            <div v-else class="text-center py-12">
              <p class="text-gray-500 dark:text-gray-400">Erro ao carregar detalhes da limpeza</p>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-gray-800 flex-shrink-0">
            <!-- Left side: Delete button -->
            <div>
              <button
                v-if="cleaning"
                @click="showDeleteConfirm = true"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
              >
                Excluir
              </button>
            </div>

            <!-- Right side: Edit/Save/Cancel buttons -->
            <div class="flex items-center gap-2">
              <template v-if="editMode">
                <button
                  @click="cancelEdit"
                  class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                >
                  Cancelar
                </button>
                <button
                  @click="saveCleaning"
                  :disabled="saving"
                  class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="saving">Salvando...</span>
                  <span v-else>Salvar</span>
                </button>
              </template>
              <template v-else>
                <button
                  v-if="cleaning"
                  @click="toggleEditMode"
                  class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors"
                >
                  Editar
                </button>
                <button
                  @click="close"
                  class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                >
                  Fechar
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Delete Confirmation Modal -->
    <ConfirmDeleteModal
      :is-open="showDeleteConfirm"
      title="Confirmar Exclusão da Limpeza"
      message="Tem certeza que deseja excluir este registro de limpeza?"
      :item-name="`Limpeza #${cleaning?.id} - ${cleaning?.machine?.name}`"
      @close="showDeleteConfirm = false"
      @confirm="handleDeleteConfirm"
      ref="deleteModalRef"
    />
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import ConfirmDeleteModal from './ConfirmDeleteModal.vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  cleaningId: {
    type: Number,
    default: null
  }
});

const emit = defineEmits(['close', 'deleted']);

const cleaning = ref(null);
const loading = ref(false);
const editMode = ref(false);
const saving = ref(false);
const showDeleteConfirm = ref(false);
const deleteModalRef = ref(null);

const editableData = ref({
  hd_machine_cleaning: null,
  osmosis_cleaning: null,
  serum_support_cleaning: null,
  chemical_disinfection: null,
  cleaning_products_used: '',
  cleaning_procedure: '',
  observations: ''
});

watch(() => props.cleaningId, (newId) => {
  if (newId && props.isOpen) {
    loadCleaning();
  }
}, { immediate: true });

watch(() => props.isOpen, (isOpen) => {
  if (isOpen && props.cleaningId) {
    loadCleaning();
  } else {
    editMode.value = false;
  }
});

async function loadCleaning() {
  if (!props.cleaningId) return;

  try {
    loading.value = true;
    const response = await axios.get(`/api/cleaning-controls/${props.cleaningId}`);
    cleaning.value = response.data;
  } catch (error) {
    console.error('Error loading cleaning:', error);
  } finally {
    loading.value = false;
  }
}

function toggleEditMode() {
  editMode.value = !editMode.value;
  if (editMode.value) {
    // Populate editable data
    editableData.value = {
      hd_machine_cleaning: cleaning.value.hd_machine_cleaning,
      osmosis_cleaning: cleaning.value.osmosis_cleaning,
      serum_support_cleaning: cleaning.value.serum_support_cleaning,
      chemical_disinfection: cleaning.value.chemical_disinfection,
      cleaning_products_used: cleaning.value.cleaning_products_used || '',
      cleaning_procedure: cleaning.value.cleaning_procedure || '',
      observations: cleaning.value.observations || ''
    };
  }
}

function cancelEdit() {
  editMode.value = false;
}

async function saveCleaning() {
  try {
    saving.value = true;
    const response = await axios.put(`/api/cleaning-controls/${props.cleaningId}`, editableData.value);

    if (response.data.success) {
      cleaning.value = response.data.cleaning;
      editMode.value = false;
    }
  } catch (error) {
    console.error('Error saving cleaning:', error);
    alert('Erro ao salvar limpeza');
  } finally {
    saving.value = false;
  }
}

async function handleDeleteConfirm() {
  try {
    const response = await axios.delete(`/api/cleaning-controls/${props.cleaningId}`);

    if (response.data.success) {
      // Reset the delete modal state
      deleteModalRef.value?.resetDeletingState();

      // Close the delete confirmation modal
      showDeleteConfirm.value = false;

      // Show success toast
      showSuccessToast('Limpeza excluída com sucesso!');

      // Emit deleted event and close detail modal
      emit('deleted');
      close();
    }
  } catch (error) {
    console.error('Error deleting cleaning:', error);

    // Reset the delete modal state
    deleteModalRef.value?.resetDeletingState();

    // Show error toast
    const errorMessage = error.response?.data?.message || 'Erro ao excluir limpeza';
    showErrorToast(errorMessage);
  }
}

function showSuccessToast(message) {
  const toast = document.createElement('div');
  toast.className = 'fixed top-4 right-4 z-[100] px-6 py-4 bg-green-600 text-white rounded-lg shadow-lg flex items-center gap-3 animate-slide-in-right';
  toast.innerHTML = `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>
    <span class="font-medium">${message}</span>
  `;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'slide-out-right 0.3s ease-out forwards';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

function showErrorToast(message) {
  const toast = document.createElement('div');
  toast.className = 'fixed top-4 right-4 z-[100] px-6 py-4 bg-red-600 text-white rounded-lg shadow-lg flex items-center gap-3 animate-slide-in-right';
  toast.innerHTML = `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
    </svg>
    <span class="font-medium">${message}</span>
  `;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'slide-out-right 0.3s ease-out forwards';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

function close() {
  emit('close');
}

function getCleaningTypeLabel(cleaning) {
  if (cleaning.special_cleaning) return 'Especial';
  if (cleaning.monthly_cleaning) return 'Mensal';
  if (cleaning.weekly_cleaning) return 'Semanal';
  if (cleaning.daily_cleaning) return 'Diária';
  return 'N/A';
}

function getCleaningTypeBadgeClass(cleaning) {
  if (cleaning.special_cleaning) {
    return 'inline-flex items-center px-3 py-1 text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 rounded-full';
  }
  if (cleaning.monthly_cleaning) {
    return 'inline-flex items-center px-3 py-1 text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded-full';
  }
  if (cleaning.weekly_cleaning) {
    return 'inline-flex items-center px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 rounded-full';
  }
  if (cleaning.daily_cleaning) {
    return 'inline-flex items-center px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded-full';
  }
  return 'inline-flex items-center px-3 py-1 text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 rounded-full';
}

function getShiftLabel(shift) {
  const labels = {
    matutino: 'Matutino',
    vespertino: 'Vespertino',
    noturno: 'Noturno',
    morning: 'Matutino',
    afternoon: 'Vespertino',
    night: 'Noturno'
  };
  return labels[shift] || shift;
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function getComplianceStatusLabel(status) {
  if (status === true) return 'Conforme';
  if (status === false) return 'Não Conforme';
  return 'N/A';
}

function getComplianceStatusClass(status) {
  if (status === true) {
    return 'px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 rounded-full';
  }
  if (status === false) {
    return 'px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 rounded-full';
  }
  return 'px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400 rounded-full';
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active > div,
.modal-leave-active > div {
  transition: transform 0.2s ease;
}

.modal-enter-from > div,
.modal-leave-to > div {
  transform: scale(0.95);
}
</style>
