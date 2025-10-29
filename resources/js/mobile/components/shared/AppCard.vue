<template>
  <div
    class="app-card"
    :class="cardClasses"
    @click="handleClick"
  >
    <slot></slot>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  variant?: 'default' | 'gradient-primary' | 'gradient-secondary' | 'bordered' | 'elevated' | 'flat';
  clickable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  clickable: false,
});

const emit = defineEmits<{
  (e: 'click'): void;
}>();

const cardClasses = computed(() => {
  const classes: string[] = [];

  if (props.variant !== 'default') {
    classes.push(`app-card--${props.variant}`);
  }

  return classes;
});

const handleClick = () => {
  if (props.clickable) {
    emit('click');
  }
};
</script>

<style scoped>
/*
 * All styling is handled by theme/components/cards.css
 * This component is a simple wrapper that applies the right classes
 */
</style>
