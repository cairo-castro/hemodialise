<template>
  <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
    <div class="max-w-md mx-auto">
      <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="currentColor" viewBox="0 0 20 20">
        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ title }}</h3>
      <p class="text-gray-600 mb-4">{{ subtitle }}</p>
      <p class="text-sm text-gray-500">Esta seção está em desenvolvimento.</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Seção em Desenvolvimento'
  },
  subtitle: {
    type: String,
    default: 'Esta funcionalidade estará disponível em breve'
  }
});
</script>