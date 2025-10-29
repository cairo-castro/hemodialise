<template>
  <div class="action-card" @click="handleClick">
    <div class="action-card-icon" :class="`action-card-icon--${iconVariant}`">
      <ion-icon :icon="icon"></ion-icon>
    </div>

    <div class="action-card-content">
      <div class="action-card-title">{{ title }}</div>
      <div v-if="subtitle" class="action-card-subtitle">{{ subtitle }}</div>
    </div>

    <ion-icon
      :icon="chevronForward"
      class="action-card-chevron"
    ></ion-icon>
  </div>
</template>

<script setup lang="ts">
import { IonIcon } from '@ionic/vue';
import { chevronForward } from 'ionicons/icons';

interface Props {
  title: string;
  subtitle?: string;
  icon: any;
  iconVariant?: 'primary' | 'success' | 'warning' | 'danger' | 'cleaning' | 'tertiary';
}

const props = withDefaults(defineProps<Props>(), {
  iconVariant: 'primary',
});

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
 * No additional styles needed
 */
</style>
