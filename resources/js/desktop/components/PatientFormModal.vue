<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
          <div
            class="relative w-full max-w-2xl bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
            @click.stop
          >
            <!-- Header -->
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-lg bg-primary-600 flex items-center justify-center">
                    <UserPlusIcon class="w-6 h-6 text-white" />
                  </div>
                  <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                      {{ editingPatient ? 'Editar Paciente' : 'Novo Paciente' }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                      Preencha as informações básicas do paciente
                    </p>
                  </div>
                </div>
                <button
                  @click="handleClose"
                  class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                >
                  <XMarkIcon class="w-6 h-6" />
                </button>
              </div>
            </div>

            <!-- Progress Indicators -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center justify-center space-x-3">
                <div class="flex items-center space-x-2">
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold transition-all"
                    :class="formData.full_name ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500'"
                  >
                    <CheckIcon v-if="formData.full_name" class="w-5 h-5" />
                    <span v-else>1</span>
                  </div>
                  <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Nome</span>
                </div>

                <div class="w-12 h-1 rounded-full" :class="formData.birth_date ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"></div>

                <div class="flex items-center space-x-2">
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold transition-all"
                    :class="formData.birth_date ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500'"
                  >
                    <CheckIcon v-if="formData.birth_date" class="w-5 h-5" />
                    <span v-else>2</span>
                  </div>
                  <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Data</span>
                </div>

                <div class="w-12 h-1 rounded-full" :class="formData.blood_group && formData.rh_factor ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"></div>

                <div class="flex items-center space-x-2">
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold transition-all"
                    :class="formData.blood_group && formData.rh_factor ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500'"
                  >
                    <CheckIcon v-if="formData.blood_group && formData.rh_factor" class="w-5 h-5" />
                    <span v-else>3</span>
                  </div>
                  <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Sangue</span>
                </div>
              </div>
            </div>

            <!-- Form Content -->
            <form @submit.prevent="handleSubmit" class="p-6 space-y-5">
              <!-- Nome Completo -->
              <div class="space-y-2">
                <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                  <UserIcon class="w-4 h-4 mr-2 text-primary-600 dark:text-primary-400" />
                  Nome Completo
                  <span class="text-red-500 ml-1">*</span>
                </label>
                <input
                  v-model="formData.full_name"
                  type="text"
                  required
                  placeholder="Digite o nome completo do paciente"
                  class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white placeholder-gray-400"
                />
              </div>

              <!-- Data de Nascimento -->
              <div class="space-y-2">
                <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                  <CalendarIcon class="w-4 h-4 mr-2 text-green-600 dark:text-green-400" />
                  Data de Nascimento
                  <span class="text-red-500 ml-1">*</span>
                </label>
                <input
                  v-model="formData.birth_date"
                  type="date"
                  required
                  class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                />
                <p v-if="age" class="text-xs text-gray-500 dark:text-gray-400">
                  Idade: {{ age }} anos
                </p>
              </div>

              <!-- Tipo Sanguíneo e Fator RH -->
              <div class="grid grid-cols-2 gap-4">
                <!-- Tipo Sanguíneo -->
                <div class="space-y-2">
                  <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                    <BeakerIcon class="w-4 h-4 mr-2 text-orange-600 dark:text-orange-400" />
                    Tipo Sanguíneo
                    <span class="text-gray-400 text-xs ml-1">(opcional)</span>
                  </label>
                  <select
                    v-model="formData.blood_group"
                    class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                  >
                    <option value="">Selecione</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="AB">AB</option>
                    <option value="O">O</option>
                  </select>
                </div>

                <!-- Fator RH -->
                <div class="space-y-2">
                  <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                    <PlusCircleIcon class="w-4 h-4 mr-2 text-green-600 dark:text-green-400" />
                    Fator RH
                    <span class="text-gray-400 text-xs ml-1">(opcional)</span>
                  </label>
                  <select
                    v-model="formData.rh_factor"
                    class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white"
                  >
                    <option value="">Selecione</option>
                    <option value="+">Positivo (+)</option>
                    <option value="-">Negativo (-)</option>
                  </select>
                </div>
              </div>

              <!-- Blood Type Display -->
              <div v-if="formData.blood_group && formData.rh_factor" class="p-4 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg">
                <div class="flex items-center">
                  <BeakerIcon class="w-5 h-5 text-primary-600 dark:text-primary-400 mr-2" />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo Sanguíneo Completo:</span>
                  <span class="ml-2 text-lg font-bold text-primary-700 dark:text-primary-300">
                    {{ formData.blood_group }}{{ formData.rh_factor }}
                  </span>
                </div>
              </div>
            </form>

            <!-- Footer -->
            <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-end space-x-3">
              <button
                type="button"
                @click="handleClose"
                class="px-5 py-2.5 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium"
              >
                Cancelar
              </button>
              <button
                @click="handleSubmit"
                :disabled="!canSubmit || saving"
                class="px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
              >
                <CheckCircleIcon v-if="!saving" class="w-5 h-5 mr-2" />
                <div v-else class="w-5 h-5 mr-2 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                {{ saving ? 'Salvando...' : (editingPatient ? 'Atualizar' : 'Cadastrar') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import {
  XMarkIcon,
  UserPlusIcon,
  UserIcon,
  CalendarIcon,
  BeakerIcon,
  PlusCircleIcon,
  CheckIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  patient: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

const formData = ref({
  full_name: '',
  birth_date: '',
  blood_group: '',
  rh_factor: ''
});

const saving = ref(false);
const editingPatient = computed(() => props.patient !== null);

// Calculate age from birth date
const age = computed(() => {
  if (!formData.value.birth_date) return null;

  const birthDate = new Date(formData.value.birth_date);
  const today = new Date();
  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();

  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }

  return age;
});

const canSubmit = computed(() => {
  return formData.value.full_name.trim().length > 0 &&
         formData.value.birth_date.length > 0;
});

// Watch for patient prop changes (for editing)
watch(() => props.patient, (newPatient) => {
  if (newPatient) {
    formData.value = {
      full_name: newPatient.full_name || '',
      birth_date: newPatient.birth_date || '',
      blood_group: newPatient.blood_group || '',
      rh_factor: newPatient.rh_factor || ''
    };
  }
}, { immediate: true });

// Watch for modal open/close
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && !props.patient) {
    // Reset form when opening for new patient
    resetForm();
  }
});

function resetForm() {
  formData.value = {
    full_name: '',
    birth_date: '',
    blood_group: '',
    rh_factor: ''
  };
}

async function handleSubmit() {
  if (!canSubmit.value || saving.value) return;

  saving.value = true;

  try {
    // Prepare patient data
    const patientData = {
      full_name: formData.value.full_name.trim(),
      birth_date: formData.value.birth_date,
      blood_group: formData.value.blood_group || null,
      rh_factor: formData.value.rh_factor || null
    };

    // TODO: Replace with actual API call
    // const url = editingPatient.value
    //   ? `/api/patients/${props.patient.id}`
    //   : '/api/patients';
    // const method = editingPatient.value ? 'PUT' : 'POST';
    //
    // const response = await fetch(url, {
    //   method,
    //   headers: { 'Content-Type': 'application/json' },
    //   credentials: 'include',
    //   body: JSON.stringify(patientData)
    // });
    //
    // const result = await response.json();

    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));

    emit('saved', {
      id: editingPatient.value ? props.patient.id : Date.now(),
      ...patientData
    });

    resetForm();
    emit('close');
  } catch (error) {
    console.error('Error saving patient:', error);
    alert('Erro ao salvar paciente. Tente novamente.');
  } finally {
    saving.value = false;
  }
}

function handleBackdropClick() {
  if (formData.value.full_name || formData.value.birth_date) {
    if (confirm('Deseja realmente sair? Os dados não salvos serão perdidos.')) {
      handleClose();
    }
  } else {
    handleClose();
  }
}

function handleClose() {
  if (!saving.value) {
    resetForm();
    emit('close');
  }
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

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
  opacity: 0;
}
</style>
