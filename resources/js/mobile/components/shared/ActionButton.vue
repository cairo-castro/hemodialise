<template>
  <button
    class="btn-primary"
    @click="handleClick"
  >
    <ion-icon v-if="icon" :icon="icon" class="btn-icon"></ion-icon>

    <div class="btn-primary-text">
      <div class="btn-primary-title">{{ title }}</div>
      <div v-if="subtitle" class="btn-primary-subtitle">{{ subtitle }}</div>
    </div>

    <ion-badge v-if="badge" class="btn-badge" color="light">
      {{ badge }}
    </ion-badge>
  </button>
</template>

<script setup lang="ts">
import { IonIcon, IonBadge } from '@ionic/vue';

interface Props {
  title: string;
  subtitle?: string;
  icon?: any;
  badge?: number | string;
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
 * All styling is handled by theme/components/buttons.css
 * No additional styles needed
 */
</style>
