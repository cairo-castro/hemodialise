<template>
  <div class="h-full bg-gray-50">
    <div class="p-8">
      <h1 class="text-2xl font-bold text-gray-900">Sistema Hemodiálise - Desktop</h1>
      <p class="text-gray-600 mt-2">Vue.js funcionando corretamente!</p>
      
      <div class="mt-8 bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Informações do Sistema</h2>
        <ul class="space-y-2">
          <li><strong>Framework:</strong> Vue.js 3</li>
          <li><strong>Status:</strong> <span class="text-green-600">Ativo</span></li>
          <li><strong>Timestamp:</strong> {{ currentTime }}</li>
        </ul>
        
        <button 
          @click="updateTime" 
          class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
          Atualizar Hora
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const currentTime = ref('');

const updateTime = () => {
  currentTime.value = new Date().toLocaleString('pt-BR');
};

onMounted(() => {
  updateTime();
  console.log('DesktopAppSimple montado com sucesso!');
});
</script>