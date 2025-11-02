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
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center">
                    <CpuChipIcon class="w-6 h-6 text-white" />
                  </div>
                  <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                      {{ editingMachine ? 'Editar Máquina' : 'Nova Máquina' }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                      Preencha as informações da máquina de hemodiálise
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
                    :class="formData.name ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500'"
                  >
                    <CheckIcon v-if="formData.name" class="w-5 h-5" />
                    <span v-else>1</span>
                  </div>
                  <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Nome</span>
                </div>

                <div class="w-12 h-1 rounded-full" :class="formData.identifier ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'"></div>

                <div class="flex items-center space-x-2">
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold transition-all"
                    :class="formData.identifier ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500'"
                  >
                    <CheckIcon v-if="formData.identifier" class="w-5 h-5" />
                    <span v-else>2</span>
                  </div>
                  <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Identificador</span>
                </div>

                <div class="w-12 h-1 rounded-full" :class="formData.status ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'"></div>

                <div class="flex items-center space-x-2">
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold transition-all"
                    :class="formData.status ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500'"
                  >
                    <CheckIcon v-if="formData.status" class="w-5 h-5" />
                    <span v-else>3</span>
                  </div>
                  <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Status</span>
                </div>
              </div>
            </div>

            <!-- Form Content -->
            <form @submit.prevent="handleSubmit" class="p-6 space-y-5">
              <!-- Nome -->
              <div class="space-y-2">
                <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                  <CpuChipIcon class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-400" />
                  Nome da Máquina
                  <span class="text-red-500 ml-1">*</span>
                </label>
                <input
                  v-model="formData.name"
                  type="text"
                  required
                  placeholder="Ex: Máquina 01, Fresenius 2008K, etc."
                  class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-white placeholder-gray-400"
                />
              </div>

              <!-- Identificador -->
              <div class="space-y-2">
                <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                  <IdentificationIcon class="w-4 h-4 mr-2 text-purple-600 dark:text-purple-400" />
                  Identificador / Código
                  <span class="text-red-500 ml-1">*</span>
                </label>
                <input
                  v-model="formData.identifier"
                  type="text"
                  required
                  placeholder="Ex: HD-001, M01, etc."
                  class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-white placeholder-gray-400"
                />
              </div>

              <!-- Status -->
              <div class="space-y-2">
                <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                  <CheckBadgeIcon class="w-4 h-4 mr-2 text-green-600 dark:text-green-400" />
                  Status
                  <span class="text-red-500 ml-1">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                  <button
                    type="button"
                    v-for="statusOption in statusOptions"
                    :key="statusOption.value"
                    @click="formData.status = statusOption.value"
                    class="flex items-center justify-between p-3 border-2 rounded-lg transition-all"
                    :class="[
                      formData.status === statusOption.value
                        ? `border-${statusOption.color}-500 bg-${statusOption.color}-50 dark:bg-${statusOption.color}-900/20`
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                    ]"
                  >
                    <div class="flex items-center">
                      <component :is="statusOption.icon" class="w-5 h-5 mr-2" :class="`text-${statusOption.color}-600 dark:text-${statusOption.color}-400`" />
                      <span class="font-medium text-gray-900 dark:text-white text-sm">{{ statusOption.label }}</span>
                    </div>
                    <CheckCircleIcon
                      v-if="formData.status === statusOption.value"
                      class="w-5 h-5"
                      :class="`text-${statusOption.color}-600 dark:text-${statusOption.color}-400`"
                    />
                  </button>
                </div>
              </div>

              <!-- Descrição (Opcional) -->
              <div class="space-y-2">
                <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                  <DocumentTextIcon class="w-4 h-4 mr-2 text-gray-600 dark:text-gray-400" />
                  Descrição
                  <span class="text-gray-400 text-xs ml-1">(opcional)</span>
                </label>
                <textarea
                  v-model="formData.description"
                  rows="3"
                  placeholder="Informações adicionais sobre a máquina..."
                  class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-white placeholder-gray-400 resize-none"
                ></textarea>
              </div>

              <!-- Ativo/Inativo -->
              <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                  <component
                    :is="formData.active ? CheckCircleIcon : XCircleIcon"
                    class="w-5 h-5 mr-3"
                    :class="formData.active ? 'text-green-600 dark:text-green-400' : 'text-gray-400'"
                  />
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Máquina Ativa</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ formData.active ? 'Esta máquina está ativa e disponível para uso' : 'Esta máquina está inativa' }}
                    </p>
                  </div>
                </div>
                <button
                  type="button"
                  @click="formData.active = !formData.active"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                  :class="formData.active ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600'"
                >
                  <span
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                    :class="formData.active ? 'translate-x-6' : 'translate-x-1'"
                  ></span>
                </button>
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
                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
              >
                <CheckCircleIcon v-if="!saving" class="w-5 h-5 mr-2" />
                <div v-else class="w-5 h-5 mr-2 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                {{ saving ? 'Salvando...' : (editingMachine ? 'Atualizar' : 'Cadastrar') }}
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
  CpuChipIcon,
  IdentificationIcon,
  CheckBadgeIcon,
  DocumentTextIcon,
  CheckIcon,
  CheckCircleIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline';
import {
  CheckCircleIcon as CheckCircleSolidIcon,
  ClockIcon,
  WrenchScrewdriverIcon,
  ShieldExclamationIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  machine: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

const formData = ref({
  name: '',
  identifier: '',
  description: '',
  status: 'available',
  active: true
});

const saving = ref(false);
const editingMachine = computed(() => props.machine !== null);

const statusOptions = [
  { value: 'available', label: 'Disponível', icon: CheckCircleSolidIcon, color: 'green' },
  { value: 'occupied', label: 'Em Uso', icon: ClockIcon, color: 'blue' },
  { value: 'maintenance', label: 'Manutenção', icon: WrenchScrewdriverIcon, color: 'orange' },
  { value: 'reserved', label: 'Reservada', icon: ShieldExclamationIcon, color: 'purple' },
];

const canSubmit = computed(() => {
  return formData.value.name.trim().length > 0 &&
         formData.value.identifier.trim().length > 0 &&
         formData.value.status.length > 0;
});

// Watch for machine prop changes (for editing)
watch(() => props.machine, (newMachine) => {
  if (newMachine) {
    formData.value = {
      name: newMachine.name || '',
      identifier: newMachine.identifier || '',
      description: newMachine.description || '',
      status: newMachine.status || 'available',
      active: newMachine.active !== undefined ? newMachine.active : true
    };
  }
}, { immediate: true });

// Watch for modal open/close
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && !props.machine) {
    resetForm();
  }
});

function resetForm() {
  formData.value = {
    name: '',
    identifier: '',
    description: '',
    status: 'available',
    active: true
  };
}

async function handleSubmit() {
  if (!canSubmit.value || saving.value) return;

  saving.value = true;

  try {
    const machineData = {
      name: formData.value.name.trim(),
      identifier: formData.value.identifier.trim(),
      description: formData.value.description.trim() || null,
      status: formData.value.status,
      active: formData.value.active
    };

    // TODO: Replace with actual API call
    // const url = editingMachine.value
    //   ? `/api/machines/${props.machine.id}`
    //   : '/api/machines';
    // const method = editingMachine.value ? 'PUT' : 'POST';
    //
    // const response = await fetch(url, {
    //   method,
    //   headers: { 'Content-Type': 'application/json' },
    //   credentials: 'include',
    //   body: JSON.stringify(machineData)
    // });

    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));

    emit('saved', {
      id: editingMachine.value ? props.machine.id : Date.now(),
      ...machineData
    });

    resetForm();
    emit('close');
  } catch (error) {
    console.error('Error saving machine:', error);
    alert('Erro ao salvar máquina. Tente novamente.');
  } finally {
    saving.value = false;
  }
}

function handleBackdropClick() {
  if (formData.value.name || formData.value.identifier) {
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
