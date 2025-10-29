<template>
  <div
    class="app-card machine-card"
    :class="machineCardClass"
    @click="handleClick"
  >
    <div class="machine-info">
      <div class="machine-number">{{ machine.number }}</div>
      <div class="flex-1">
        <div class="machine-name">{{ machine.name }}</div>
        <div v-if="showDetails" class="machine-details">
          {{ statusText }}
        </div>
      </div>
    </div>

    <div v-if="showBadge" class="ml-auto">
      <div class="status-badge" :class="`status-badge--${machine.status}`">
        {{ statusText }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Machine {
  id: number;
  number: string;
  name: string;
  status: 'available' | 'occupied' | 'maintenance' | 'inactive';
}

interface Props {
  machine: Machine;
  showDetails?: boolean;
  showBadge?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showDetails: true,
  showBadge: false,
});

const emit = defineEmits<{
  (e: 'click', machine: Machine): void;
}>();

const machineCardClass = computed(() => {
  return `machine-card--${props.machine.status}`;
});

const statusText = computed(() => {
  const statusMap = {
    available: 'Disponível',
    occupied: 'Em Uso',
    maintenance: 'Manutenção',
    inactive: 'Inativa',
  };
  return statusMap[props.machine.status] || props.machine.status;
});

const handleClick = () => {
  emit('click', props.machine);
};
</script>

<style scoped>
/*
 * All styling is handled by theme/components/cards.css
 * and theme/app/medical.css
 */
</style>
