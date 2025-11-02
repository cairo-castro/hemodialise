<template>
  <div class="checklists-view space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Checklists de Segurança</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Verificações de segurança para hemodiálise</p>
      </div>
      <button
        @click="showWizard = true"
        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium"
      >
        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Novo Checklist
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Hoje</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">12</p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Em Andamento</p>
        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">3</p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Concluídos</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">8</p>
      </div>
      <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Interrompidos</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">1</p>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Paciente</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Máquina</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Turno</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fase</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">#1234</td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">Maria Silva Santos</td>
            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">Máquina 05</td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded-full">
                Matutino
              </span>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 rounded-full">
                Em Sessão
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <button class="text-primary-600 dark:text-primary-400 hover:underline text-sm font-medium">
                Ver Detalhes
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Checklist Wizard -->
    <ChecklistWizard
      :is-open="showWizard"
      @close="showWizard = false"
      @saved="handleChecklistSaved"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ChecklistWizard from '../components/ChecklistWizard.vue';

const showWizard = ref(false);

function handleChecklistSaved(checklist) {
  console.log('Checklist saved:', checklist);
  // TODO: Refresh checklist list or add new checklist to table
  // For now, just show a success message
  alert('Checklist criado com sucesso!');
}
</script>

<style scoped>
.checklists-view {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
