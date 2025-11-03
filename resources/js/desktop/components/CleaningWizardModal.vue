<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="handleClose"
      >
        <div
          class="bg-white dark:bg-gray-950 rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden border border-gray-200 dark:border-gray-800 flex flex-col"
          @click.stop
        >
          <!-- Header -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-800 flex-shrink-0">
            <div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                Novo Checklist de Limpeza
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Passo {{ currentStep }} de 4
              </p>
            </div>
            <button
              @click="handleClose"
              class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Progress Bar -->
          <div class="w-full h-2 bg-gray-200 dark:bg-gray-800">
            <div
              class="h-full bg-primary-600 transition-all duration-300"
              :style="{ width: `${(currentStep / 4) * 100}%` }"
            ></div>
          </div>

          <!-- Content -->
          <div class="p-6 overflow-y-auto flex-1">
            <!-- Step 1: Máquina, Turno e Data -->
            <div v-show="currentStep === 1" class="space-y-6">
              <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Máquina, Turno e Data</h4>

              <!-- Machine Selection -->
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Selecione a Máquina
                </label>
                <div v-if="loadingMachines" class="flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
                  <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Carregando máquinas...</span>
                </div>
                <div v-else-if="machines.length === 0" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                  <p class="text-sm text-yellow-800 dark:text-yellow-400">
                    ⚠️ Nenhuma máquina disponível. Verifique se há máquinas cadastradas para sua unidade.
                  </p>
                </div>
                <select
                  v-else
                  v-model="formData.machine_id"
                  class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white"
                >
                  <option value="">Selecione uma máquina...</option>
                  <option
                    v-for="machine in machines"
                    :key="machine.id"
                    :value="machine.id"
                  >
                    {{ machine.name }}
                  </option>
                </select>
              </div>

              <!-- Shift Selection -->
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Selecione o Turno
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                  <button
                    type="button"
                    @click="formData.shift = 'matutino'"
                    :class="[
                      'p-4 rounded-lg border-2 transition-all flex flex-col items-center gap-2',
                      formData.shift === 'matutino'
                        ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20'
                        : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'
                    ]"
                  >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <div class="text-center">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">Matutino</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">06:00-12:00</div>
                    </div>
                  </button>

                  <button
                    type="button"
                    @click="formData.shift = 'vespertino'"
                    :class="[
                      'p-4 rounded-lg border-2 transition-all flex flex-col items-center gap-2',
                      formData.shift === 'vespertino'
                        ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20'
                        : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'
                    ]"
                  >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <div class="text-center">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">Vespertino</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">12:00-18:00</div>
                    </div>
                  </button>

                  <button
                    type="button"
                    @click="formData.shift = 'noturno'"
                    :class="[
                      'p-4 rounded-lg border-2 transition-all flex flex-col items-center gap-2',
                      formData.shift === 'noturno'
                        ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20'
                        : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'
                    ]"
                  >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <div class="text-center">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">Noturno</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">18:00-00:00</div>
                    </div>
                  </button>
                </div>
              </div>

              <!-- Date and Time Selection -->
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Data da Limpeza
                  </label>
                  <input
                    type="date"
                    v-model="formData.cleaning_date"
                    :max="today"
                    class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white"
                  />
                </div>

                <div class="space-y-2">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Horário da Limpeza
                  </label>
                  <input
                    type="time"
                    v-model="formData.cleaning_time"
                    class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white"
                  />
                </div>
              </div>
            </div>

            <!-- Step 2: Tipo de Limpeza -->
            <div v-show="currentStep === 2" class="space-y-6">
              <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Tipo de Limpeza</h4>

              <div class="grid grid-cols-2 gap-4">
                <label
                  :class="[
                    'relative p-4 rounded-lg border-2 transition-all cursor-pointer',
                    formData.daily_cleaning
                      ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                      : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'
                  ]"
                >
                  <input
                    type="checkbox"
                    v-model="formData.daily_cleaning"
                    class="sr-only"
                  />
                  <div class="flex items-center gap-3">
                    <div :class="[
                      'w-5 h-5 rounded border-2 flex items-center justify-center',
                      formData.daily_cleaning ? 'border-blue-600 bg-blue-600' : 'border-gray-300 dark:border-gray-600'
                    ]">
                      <svg v-if="formData.daily_cleaning" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">Limpeza Diária</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Limpeza rotineira</div>
                    </div>
                  </div>
                </label>

                <label
                  :class="[
                    'relative p-4 rounded-lg border-2 transition-all cursor-pointer',
                    formData.weekly_cleaning
                      ? 'border-yellow-600 bg-yellow-50 dark:bg-yellow-900/20'
                      : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'
                  ]"
                >
                  <input
                    type="checkbox"
                    v-model="formData.weekly_cleaning"
                    class="sr-only"
                  />
                  <div class="flex items-center gap-3">
                    <div :class="[
                      'w-5 h-5 rounded border-2 flex items-center justify-center',
                      formData.weekly_cleaning ? 'border-yellow-600 bg-yellow-600' : 'border-gray-300 dark:border-gray-600'
                    ]">
                      <svg v-if="formData.weekly_cleaning" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">Limpeza Semanal</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Limpeza profunda</div>
                    </div>
                  </div>
                </label>

                <label
                  :class="[
                    'relative p-4 rounded-lg border-2 transition-all cursor-pointer',
                    formData.monthly_cleaning
                      ? 'border-purple-600 bg-purple-50 dark:bg-purple-900/20'
                      : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'
                  ]"
                >
                  <input
                    type="checkbox"
                    v-model="formData.monthly_cleaning"
                    class="sr-only"
                  />
                  <div class="flex items-center gap-3">
                    <div :class="[
                      'w-5 h-5 rounded border-2 flex items-center justify-center',
                      formData.monthly_cleaning ? 'border-purple-600 bg-purple-600' : 'border-gray-300 dark:border-gray-600'
                    ]">
                      <svg v-if="formData.monthly_cleaning" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">Limpeza Mensal</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Manutenção preventiva</div>
                    </div>
                  </div>
                </label>

                <label
                  :class="[
                    'relative p-4 rounded-lg border-2 transition-all cursor-pointer',
                    formData.special_cleaning
                      ? 'border-red-600 bg-red-50 dark:bg-red-900/20'
                      : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'
                  ]"
                >
                  <input
                    type="checkbox"
                    v-model="formData.special_cleaning"
                    class="sr-only"
                  />
                  <div class="flex items-center gap-3">
                    <div :class="[
                      'w-5 h-5 rounded border-2 flex items-center justify-center',
                      formData.special_cleaning ? 'border-red-600 bg-red-600' : 'border-gray-300 dark:border-gray-600'
                    ]">
                      <svg v-if="formData.special_cleaning" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">Limpeza Especial</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Situação excepcional</div>
                    </div>
                  </div>
                </label>
              </div>
            </div>

            <!-- Step 3: Itens de Limpeza -->
            <div v-show="currentStep === 3" class="space-y-6">
              <div>
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Itens de Limpeza</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Marque a conformidade de cada item</p>
              </div>

              <!-- Legend -->
              <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full bg-green-500"></div>
                  <span class="text-xs text-gray-600 dark:text-gray-400">Conforme</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full bg-red-500"></div>
                  <span class="text-xs text-gray-600 dark:text-gray-400">Não Conforme</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                  <span class="text-xs text-gray-600 dark:text-gray-400">N/A</span>
                </div>
              </div>

              <!-- Cleaning Items -->
              <div class="space-y-4">
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <p class="text-sm font-medium text-gray-900 dark:text-white mb-3">Máquina de HD</p>
                  <div class="flex gap-3">
                    <button
                      type="button"
                      @click="formData.hd_machine_cleaning = true"
                      :class="[
                        'flex-1 px-4 py-2 rounded-lg border-2 transition-all text-sm font-medium',
                        formData.hd_machine_cleaning === true
                          ? 'border-green-600 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400'
                          : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                    >
                      Conforme
                    </button>
                    <button
                      type="button"
                      @click="formData.hd_machine_cleaning = false"
                      :class="[
                        'flex-1 px-4 py-2 rounded-lg border-2 transition-all text-sm font-medium',
                        formData.hd_machine_cleaning === false
                          ? 'border-red-600 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400'
                          : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                    >
                      Não Conforme
                    </button>
                    <button
                      type="button"
                      @click="formData.hd_machine_cleaning = null"
                      :class="[
                        'flex-1 px-4 py-2 rounded-lg border-2 transition-all text-sm font-medium',
                        formData.hd_machine_cleaning === null
                          ? 'border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                          : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                    >
                      N/A
                    </button>
                  </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <p class="text-sm font-medium text-gray-900 dark:text-white mb-3">Osmose</p>
                  <div class="flex gap-3">
                    <button
                      type="button"
                      @click="formData.osmosis_cleaning = true"
                      :class="[
                        'flex-1 px-4 py-2 rounded-lg border-2 transition-all text-sm font-medium',
                        formData.osmosis_cleaning === true
                          ? 'border-green-600 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400'
                          : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                    >
                      Conforme
                    </button>
                    <button
                      type="button"
                      @click="formData.osmosis_cleaning = false"
                      :class="[
                        'flex-1 px-4 py-2 rounded-lg border-2 transition-all text-sm font-medium',
                        formData.osmosis_cleaning === false
                          ? 'border-red-600 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400'
                          : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                    >
                      Não Conforme
                    </button>
                    <button
                      type="button"
                      @click="formData.osmosis_cleaning = null"
                      :class="[
                        'flex-1 px-4 py-2 rounded-lg border-2 transition-all text-sm font-medium',
                        formData.osmosis_cleaning === null
                          ? 'border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                          : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                    >
                      N/A
                    </button>
                  </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <p class="text-sm font-medium text-gray-900 dark:text-white mb-3">Suporte de Soro</p>
                  <div class="flex gap-3">
                    <button
                      type="button"
                      @click="formData.serum_support_cleaning = true"
                      :class="[
                        'flex-1 px-4 py-2 rounded-lg border-2 transition-all text-sm font-medium',
                        formData.serum_support_cleaning === true
                          ? 'border-green-600 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400'
                          : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                    >
                      Conforme
                    </button>
                    <button
                      type="button"
                      @click="formData.serum_support_cleaning = false"
                      :class="[
                        'flex-1 px-4 py-2 rounded-lg border-2 transition-all text-sm font-medium',
                        formData.serum_support_cleaning === false
                          ? 'border-red-600 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400'
                          : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                    >
                      Não Conforme
                    </button>
                    <button
                      type="button"
                      @click="formData.serum_support_cleaning = null"
                      :class="[
                        'flex-1 px-4 py-2 rounded-lg border-2 transition-all text-sm font-medium',
                        formData.serum_support_cleaning === null
                          ? 'border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                          : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                    >
                      N/A
                    </button>
                  </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <p class="text-sm font-medium text-gray-900 dark:text-white mb-3">Desinfecção Química</p>
                  <div class="flex gap-3">
                    <button
                      type="button"
                      @click="formData.chemical_disinfection = true"
                      :class="[
                        'flex-1 px-4 py-2 rounded-lg border-2 transition-all text-sm font-medium',
                        formData.chemical_disinfection === true
                          ? 'border-green-600 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400'
                          : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                    >
                      Conforme
                    </button>
                    <button
                      type="button"
                      @click="formData.chemical_disinfection = false"
                      :class="[
                        'flex-1 px-4 py-2 rounded-lg border-2 transition-all text-sm font-medium',
                        formData.chemical_disinfection === false
                          ? 'border-red-600 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400'
                          : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                    >
                      Não Conforme
                    </button>
                    <button
                      type="button"
                      @click="formData.chemical_disinfection = null"
                      :class="[
                        'flex-1 px-4 py-2 rounded-lg border-2 transition-all text-sm font-medium',
                        formData.chemical_disinfection === null
                          ? 'border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                          : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                    >
                      N/A
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 4: Produtos, Procedimentos e Observações -->
            <div v-show="currentStep === 4" class="space-y-6">
              <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Produtos, Procedimentos e Observações</h4>

              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Produtos Utilizados
                  </label>
                  <textarea
                    v-model="formData.cleaning_products_used"
                    rows="3"
                    placeholder="Liste os produtos utilizados na limpeza..."
                    class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Procedimento de Limpeza
                  </label>
                  <textarea
                    v-model="formData.cleaning_procedure"
                    rows="4"
                    placeholder="Descreva o procedimento de limpeza realizado..."
                    class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Observações <span class="text-gray-500">(opcional)</span>
                  </label>
                  <textarea
                    v-model="formData.observations"
                    rows="3"
                    placeholder="Adicione observações sobre a limpeza (opcional)..."
                    class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-gray-800 flex-shrink-0">
            <button
              v-if="currentStep > 1"
              @click="previousStep"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
            >
              Voltar
            </button>
            <div v-else></div>

            <div class="flex items-center gap-2">
              <button
                @click="handleClose"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
              >
                Cancelar
              </button>
              <button
                v-if="currentStep < 4"
                @click="nextStep"
                :disabled="!isCurrentStepValid"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Próximo
              </button>
              <button
                v-else
                @click="submit"
                :disabled="!isFormValid || isSubmitting"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ isSubmitting ? 'Salvando...' : 'Concluir Limpeza' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['close', 'created']);

const currentStep = ref(1);
const machines = ref([]);
const loadingMachines = ref(false);
const isSubmitting = ref(false);

const today = new Date().toISOString().split('T')[0];

const formData = ref({
  machine_id: '',
  shift: '',
  cleaning_date: today,
  cleaning_time: new Date().toTimeString().slice(0, 5),
  daily_cleaning: false,
  weekly_cleaning: false,
  monthly_cleaning: false,
  special_cleaning: false,
  hd_machine_cleaning: null,
  osmosis_cleaning: null,
  serum_support_cleaning: null,
  chemical_disinfection: null,
  cleaning_products_used: '',
  cleaning_procedure: '',
  observations: ''
});

const isCurrentStepValid = computed(() => {
  if (currentStep.value === 1) {
    return formData.value.machine_id && formData.value.shift && formData.value.cleaning_date && formData.value.cleaning_time;
  }
  if (currentStep.value === 2) {
    return formData.value.daily_cleaning || formData.value.weekly_cleaning || formData.value.monthly_cleaning || formData.value.special_cleaning;
  }
  return true;
});

const isFormValid = computed(() => {
  return (
    formData.value.machine_id &&
    formData.value.shift &&
    formData.value.cleaning_date &&
    formData.value.cleaning_time &&
    (formData.value.daily_cleaning || formData.value.weekly_cleaning || formData.value.monthly_cleaning || formData.value.special_cleaning)
  );
});

watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    currentStep.value = 1;
    loadMachines();
    resetForm();
  }
});

onMounted(() => {
  if (props.isOpen) {
    loadMachines();
  }
});

async function loadMachines() {
  try {
    loadingMachines.value = true;
    // Get user's current unit first
    const userResponse = await axios.get('/api/me');
    const currentUnitId = userResponse.data.current_unit_id || userResponse.data.unit_id;

    // Load machines filtered by user's unit
    const params = currentUnitId ? { scoped_unit_id: currentUnitId } : {};
    const response = await axios.get('/api/machines', { params });
    machines.value = response.data.machines || response.data.data || [];
  } catch (error) {
    console.error('Error loading machines:', error);
  } finally {
    loadingMachines.value = false;
  }
}

function resetForm() {
  formData.value = {
    machine_id: '',
    shift: '',
    cleaning_date: today,
    cleaning_time: new Date().toTimeString().slice(0, 5),
    daily_cleaning: false,
    weekly_cleaning: false,
    monthly_cleaning: false,
    special_cleaning: false,
    hd_machine_cleaning: null,
    osmosis_cleaning: null,
    serum_support_cleaning: null,
    chemical_disinfection: null,
    cleaning_products_used: '',
    cleaning_procedure: '',
    observations: ''
  };
}

function nextStep() {
  if (isCurrentStepValid.value && currentStep.value < 4) {
    currentStep.value++;
  }
}

function previousStep() {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
}

async function submit() {
  if (!isFormValid.value || isSubmitting.value) return;

  try {
    isSubmitting.value = true;
    const response = await axios.post('/api/cleaning-controls', formData.value);

    if (response.data.success) {
      // Show success message
      showSuccessToast('Checklist de limpeza salvo com sucesso!');

      // Emit created event to refresh parent list
      emit('created');

      // Reset submitting state before closing
      isSubmitting.value = false;

      // Close modal - emit directly since we know submission was successful
      emit('close');
    }
  } catch (error) {
    console.error('Error creating cleaning control:', error);
    const errorMessage = error.response?.data?.message || 'Erro ao salvar checklist de limpeza';
    showErrorToast(errorMessage);
  } finally {
    isSubmitting.value = false;
  }
}

function showSuccessToast(message) {
  // Create toast element
  const toast = document.createElement('div');
  toast.className = 'fixed top-4 right-4 z-[100] px-6 py-4 bg-green-600 text-white rounded-lg shadow-lg flex items-center gap-3 animate-slide-in-right';
  toast.innerHTML = `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>
    <span class="font-medium">${message}</span>
  `;
  document.body.appendChild(toast);

  // Remove after 3 seconds
  setTimeout(() => {
    toast.style.animation = 'slide-out-right 0.3s ease-out forwards';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

function showErrorToast(message) {
  // Create toast element
  const toast = document.createElement('div');
  toast.className = 'fixed top-4 right-4 z-[100] px-6 py-4 bg-red-600 text-white rounded-lg shadow-lg flex items-center gap-3 animate-slide-in-right';
  toast.innerHTML = `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
    </svg>
    <span class="font-medium">${message}</span>
  `;
  document.body.appendChild(toast);

  // Remove after 4 seconds
  setTimeout(() => {
    toast.style.animation = 'slide-out-right 0.3s ease-out forwards';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

function handleClose() {
  if (!isSubmitting.value) {
    emit('close');
  }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active > div,
.modal-leave-active > div {
  transition: transform 0.2s ease;
}

.modal-enter-from > div,
.modal-leave-to > div {
  transform: scale(0.95);
}
</style>

<style>
/* Toast animations - must be global to work with dynamically created elements */
@keyframes slide-in-right {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slide-out-right {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(100%);
    opacity: 0;
  }
}

.animate-slide-in-right {
  animation: slide-in-right 0.3s ease-out forwards;
}
</style>
