<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click.self="handleCancel"
      >
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-200 dark:border-gray-800">
          <!-- Header -->
          <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800 bg-red-50 dark:bg-red-900/20">
            <div class="flex items-center gap-3">
              <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ title }}
                </h3>
              </div>
            </div>
          </div>

          <!-- Body -->
          <div class="px-6 py-5">
            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
              {{ message }}
            </p>
            <p v-if="itemName" class="mt-3 text-sm font-medium text-gray-900 dark:text-white">
              {{ itemName }}
            </p>
            <p class="mt-3 text-xs text-red-600 dark:text-red-400 font-medium">
              ⚠️ Esta ação não pode ser desfeita.
            </p>
          </div>

          <!-- Footer -->
          <div class="px-6 py-4 bg-gray-50 dark:bg-gray-950 border-t border-gray-200 dark:border-gray-800 flex items-center justify-end gap-3">
            <button
              @click="handleCancel"
              :disabled="isDeleting"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Cancelar
            </button>
            <button
              @click="handleConfirm"
              :disabled="isDeleting"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <svg v-if="isDeleting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ isDeleting ? 'Excluindo...' : confirmButtonText }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    default: 'Confirmar Exclusão'
  },
  message: {
    type: String,
    default: 'Tem certeza que deseja excluir este item?'
  },
  itemName: {
    type: String,
    default: ''
  },
  confirmButtonText: {
    type: String,
    default: 'Excluir'
  }
});

const emit = defineEmits(['close', 'confirm']);

const isDeleting = ref(false);

function handleCancel() {
  if (!isDeleting.value) {
    emit('close');
  }
}

function handleConfirm() {
  if (!isDeleting.value) {
    isDeleting.value = true;
    emit('confirm');
  }
}

// Allow parent to reset deleting state after operation completes
defineExpose({
  resetDeletingState: () => {
    isDeleting.value = false;
  }
});
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
