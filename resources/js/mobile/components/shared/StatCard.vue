<template>
  <div
    class="stat-card"
    :class="`stat-card--${variant}`"
    @click="handleClick"
  >
    <div class="stat-icon">
      <ion-icon :icon="icon"></ion-icon>
    </div>
    <div class="stat-content">
      <div class="stat-number">{{ value }}</div>
      <div class="stat-label">{{ label }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { IonIcon } from '@ionic/vue';

interface Props {
  variant: 'available' | 'occupied' | 'active' | 'total' | 'maintenance';
  value: number | string;
  label: string;
  icon: any;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: 'click'): void;
}>();

const handleClick = () => {
  emit('click');
};
</script>

<style scoped>
/*
 * All styling is handled by theme/components/cards.css
 * No additional styles needed - demonstrates the power of centralized theme
 */
</style>
