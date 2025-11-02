<template>
  <div class="space-y-4">
    <div
      v-for="(item, index) in items"
      :key="item.id"
      class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-gray-300 dark:hover:border-gray-600 transition-all"
      :class="{ 'bg-gray-50 dark:bg-gray-800/50': getItemData(item.id)?.status }"
    >
      <div class="flex items-start justify-between mb-3">
        <div class="flex-1">
          <div class="flex items-center">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-300 mr-3">
              {{ index + 1 }}
            </span>
            <label class="text-sm font-medium text-gray-900 dark:text-white">
              {{ item.label }}
              <span v-if="item.required" class="text-red-500 ml-1">*</span>
            </label>
          </div>
        </div>

        <!-- Status Badge -->
        <div v-if="getItemData(item.id)?.status" class="ml-4">
          <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
            :class="getStatusBadgeClass(getItemData(item.id).status)"
          >
            {{ getStatusLabel(getItemData(item.id).status) }}
          </span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center space-x-2 mb-3">
        <button
          @click="setStatus(item.id, 'compliant')"
          class="flex-1 px-4 py-2.5 rounded-lg font-medium transition-all"
          :class="[
            getItemData(item.id)?.status === 'compliant'
              ? 'bg-green-600 text-white shadow-sm ring-2 ring-green-600 ring-offset-2 dark:ring-offset-gray-900'
              : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-700 dark:hover:text-green-400'
          ]"
        >
          <CheckCircleIcon class="w-5 h-5 inline-block mr-2" />
          Conforme
        </button>

        <button
          @click="setStatus(item.id, 'non_compliant')"
          class="flex-1 px-4 py-2.5 rounded-lg font-medium transition-all"
          :class="[
            getItemData(item.id)?.status === 'non_compliant'
              ? 'bg-red-600 text-white shadow-sm ring-2 ring-red-600 ring-offset-2 dark:ring-offset-gray-900'
              : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400'
          ]"
        >
          <XCircleIcon class="w-5 h-5 inline-block mr-2" />
          Não Conforme
        </button>

        <button
          @click="setStatus(item.id, 'not_applicable')"
          class="flex-1 px-4 py-2.5 rounded-lg font-medium transition-all"
          :class="[
            getItemData(item.id)?.status === 'not_applicable'
              ? 'bg-gray-600 text-white shadow-sm ring-2 ring-gray-600 ring-offset-2 dark:ring-offset-gray-900'
              : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'
          ]"
        >
          <MinusCircleIcon class="w-5 h-5 inline-block mr-2" />
          N/A
        </button>
      </div>

      <!-- Observation Field -->
      <Transition name="slide-fade">
        <div v-if="getItemData(item.id)?.status" class="mt-3">
          <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
            Observações
            <span v-if="getItemData(item.id)?.status === 'non_compliant'" class="text-red-500 ml-1">
              (Obrigatório para não conformidades)
            </span>
          </label>
          <textarea
            :value="getItemData(item.id)?.observation || ''"
            @input="setObservation(item.id, $event.target.value)"
            rows="2"
            class="w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-none"
            :placeholder="getItemData(item.id)?.status === 'non_compliant' ? 'Descreva o problema encontrado...' : 'Adicione observações relevantes (opcional)...'"
            :required="getItemData(item.id)?.status === 'non_compliant'"
          ></textarea>
        </div>
      </Transition>

      <!-- Image Upload (Optional) -->
      <Transition name="slide-fade">
        <div v-if="getItemData(item.id)?.status === 'non_compliant'" class="mt-3">
          <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">
            Adicionar Foto (Opcional)
          </label>
          <div class="flex items-center space-x-2">
            <label
              class="flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors cursor-pointer text-sm font-medium"
            >
              <CameraIcon class="w-4 h-4 mr-2" />
              Escolher Foto
              <input
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleImageUpload(item.id, $event)"
              />
            </label>
            <span v-if="getItemData(item.id)?.image" class="text-xs text-gray-500 dark:text-gray-400">
              Foto anexada
            </span>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Summary -->
    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between text-sm">
        <span class="text-gray-600 dark:text-gray-400">Progresso</span>
        <span class="font-medium text-gray-900 dark:text-white">
          {{ completedItems }}/{{ requiredItems }} itens obrigatórios
        </span>
      </div>
      <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
        <div
          class="bg-primary-600 h-2 rounded-full transition-all duration-300"
          :style="{ width: `${progressPercentage}%` }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  CheckCircleIcon,
  XCircleIcon,
  MinusCircleIcon,
  CameraIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  modelValue: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:modelValue']);

// Computed
const completedItems = computed(() => {
  return props.items.filter(item =>
    item.required && props.modelValue[item.id]?.status
  ).length;
});

const requiredItems = computed(() => {
  return props.items.filter(item => item.required).length;
});

const progressPercentage = computed(() => {
  if (requiredItems.value === 0) return 0;
  return Math.round((completedItems.value / requiredItems.value) * 100);
});

// Methods
function getItemData(itemId) {
  return props.modelValue[itemId] || {};
}

function setStatus(itemId, status) {
  const newValue = {
    ...props.modelValue,
    [itemId]: {
      ...getItemData(itemId),
      status,
      observation: getItemData(itemId).observation || ''
    }
  };
  emit('update:modelValue', newValue);
}

function setObservation(itemId, observation) {
  const newValue = {
    ...props.modelValue,
    [itemId]: {
      ...getItemData(itemId),
      observation
    }
  };
  emit('update:modelValue', newValue);
}

function handleImageUpload(itemId, event) {
  const file = event.target.files[0];
  if (file) {
    // TODO: Implement image upload to server
    const reader = new FileReader();
    reader.onload = (e) => {
      const newValue = {
        ...props.modelValue,
        [itemId]: {
          ...getItemData(itemId),
          image: e.target.result
        }
      };
      emit('update:modelValue', newValue);
    };
    reader.readAsDataURL(file);
  }
}

function getStatusLabel(status) {
  const labels = {
    compliant: 'Conforme',
    non_compliant: 'Não Conforme',
    not_applicable: 'N/A'
  };
  return labels[status] || '';
}

function getStatusBadgeClass(status) {
  const classes = {
    compliant: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    non_compliant: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    not_applicable: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
  };
  return classes[status] || '';
}
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.2s ease;
}

.slide-fade-leave-active {
  transition: all 0.2s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}
</style>
