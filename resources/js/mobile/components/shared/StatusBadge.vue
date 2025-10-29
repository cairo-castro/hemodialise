<template>
  <div class="status-badge" :class="`status-badge--${variant}`">
    <div v-if="showDot" class="status-dot" :class="`status-dot--${variant}`"></div>
    <slot>{{ label }}</slot>
  </div>
</template>

<script setup lang="ts">
interface Props {
  variant: 'available' | 'occupied' | 'active' | 'maintenance' | 'inactive' | 'online' | 'offline';
  label?: string;
  showDot?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showDot: false,
});
</script>

<style scoped>
/*
 * All styling is handled by theme/components/badges.css
 */
</style>
