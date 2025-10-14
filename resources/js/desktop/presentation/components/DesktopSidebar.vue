<template>
  <div class="hs-overlay hs-overlay-open:translate-x-0 -translate-x-full transition-all duration-300 transform fixed top-0 start-0 bottom-0 z-[60] w-64 bg-white border-e border-gray-200 pt-7 pb-10 overflow-y-auto lg:block lg:translate-x-0 lg:end-auto lg:bottom-0">
    
    <!-- Logo and Brand -->
    <div class="px-6">
      <div class="flex items-center mb-8">
        <div class="w-10 h-10 medical-gradient rounded-lg flex items-center justify-center mr-3">
          <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-bold text-gray-800">Hemodiálise</h1>
          <p class="text-sm text-gray-600">Sistema Executivo</p>
        </div>
      </div>

      <!-- User Profile Card -->
      <div v-if="user" class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl mb-6 border border-blue-100">
        <div class="flex items-center">
          <div class="w-12 h-12 medical-gradient rounded-full flex items-center justify-center text-white font-bold text-lg mr-3">
            {{ user.getInitials ? user.getInitials() : user.name?.charAt(0) }}
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-gray-800 truncate">{{ user.name }}</h3>
            
            <!-- Single Unit Display -->
            <div v-if="!availableUnits || availableUnits.length <= 1" class="flex items-center mt-1">
              <svg class="w-3 h-3 text-gray-400 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
              </svg>
              <span class="text-xs text-gray-600 truncate">{{ currentUnit?.name || 'Carregando...' }}</span>
            </div>

            <!-- Multiple Units Selector -->
            <div v-else class="relative mt-2">
              <button 
                @click="showUnitSelector = !showUnitSelector"
                class="w-full flex items-center justify-between px-3 py-2 text-xs bg-white border border-blue-200 rounded-lg hover:border-blue-300 transition-colors"
              >
                <div class="flex items-center min-w-0 flex-1">
                  <svg class="w-3 h-3 text-blue-500 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                  </svg>
                  <span class="text-gray-700 font-medium truncate">{{ currentUnit?.name }}</span>
                </div>
                <svg class="w-4 h-4 text-gray-400 ml-2 flex-shrink-0" :class="{ 'transform rotate-180': showUnitSelector }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <div 
                v-if="showUnitSelector"
                class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto"
              >
                <button
                  v-for="unit in availableUnits"
                  :key="unit.id"
                  @click="selectUnit(unit.id)"
                  class="w-full px-3 py-2 text-left text-xs hover:bg-blue-50 transition-colors flex items-center"
                  :class="{ 'bg-blue-50 text-blue-700 font-medium': unit.id === selectedUnitId }"
                >
                  <svg v-if="unit.id === selectedUnitId" class="w-3 h-3 text-blue-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <span class="flex-1 truncate" :class="{ 'ml-5': unit.id !== selectedUnitId }">{{ unit.name }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Interface Toggle -->
      <div v-if="canToggleInterfaces" class="mb-6">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Alternar Interface</p>
        <div class="space-y-1">
          <button 
            @click="$emit('interface-switch', 'mobile')" 
            class="w-full flex items-center px-3 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
              <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 011 1v9a1 1 0 01-1 1H5a1 1 0 01-1-1V7z"/>
            </svg>
            Interface Mobile
          </button>
          <button 
            @click="$emit('interface-switch', 'admin')" 
            class="w-full flex items-center px-3 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 8a2 2 0 110 4 2 2 0 010-4zM10 14a2 2 0 110 4 2 2 0 010-4z"/>
            </svg>
            Painel Admin
          </button>
        </div>
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="hs-accordion-group p-6 w-full flex flex-col flex-wrap">
      <ul class="space-y-1.5">
        <!-- Dashboard -->
        <li>
          <button 
            @click="$emit('view-change', 'dashboard')"
            :class="currentView === 'dashboard' ? 'bg-blue-100 text-blue-800' : 'text-gray-700 hover:bg-gray-100'"
            class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm font-medium rounded-lg w-full text-left transition-colors"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10.707 2.293a1 1 0 00-1.414 0l-9 9a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l2.293 2.293a1 1 0 001.414-1.414l-9-9z"/>
            </svg>
            Dashboard Executivo
          </button>
        </li>

        <!-- Operations -->
        <li>
          <div class="space-y-1">
            <button class="w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Operações
            </button>
            <div class="ps-2">
              <ul class="space-y-1">
                <li>
                  <button 
                    @click="$emit('view-change', 'safety-control')" 
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100 w-full text-left"
                  >
                    Controle de Segurança
                  </button>
                </li>
                <li>
                  <button 
                    @click="$emit('view-change', 'machines')" 
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100 w-full text-left"
                  >
                    Gestão de Máquinas
                  </button>
                </li>
                <li>
                  <button 
                    @click="$emit('view-change', 'patients')" 
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100 w-full text-left"
                  >
                    Gestão de Pacientes
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </li>

        <!-- Reports -->
        <li>
          <div class="space-y-1">
            <button class="w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414L15 8.414V14a1 1 0 11-2 0V9.414l-1.293 1.293a1 1 0 01-1.414-1.414L12.586 7H8z"/>
              </svg>
              Relatórios
            </button>
            <div class="ps-2">
              <ul class="space-y-1">
                <li>
                  <button 
                    @click="$emit('view-change', 'reports-operational')" 
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100 w-full text-left"
                  >
                    Relatórios Operacionais
                  </button>
                </li>
                <li>
                  <button 
                    @click="$emit('view-change', 'reports-quality')" 
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100 w-full text-left"
                  >
                    Qualidade e Segurança
                  </button>
                </li>
                <li>
                  <button 
                    @click="$emit('view-change', 'analytics')" 
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-700 rounded-lg hover:bg-gray-100 w-full text-left"
                  >
                    Analytics Avançado
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </li>
      </ul>
    </nav>

    <!-- Logout -->
    <div class="px-6 mt-auto">
      <button 
        @click="$emit('logout')" 
        class="w-full flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors"
      >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z"/>
        </svg>
        Sair do Sistema
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  user: Object,
  currentView: String,
  canToggleInterfaces: Boolean
});

const emit = defineEmits(['view-change', 'interface-switch', 'logout', 'unit-changed']);

const availableUnits = ref([]);
const currentUnit = ref(null);
const selectedUnitId = ref(null);
const showUnitSelector = ref(false);

// Carrega as unidades disponíveis
const loadAvailableUnits = async () => {
  try {
    const response = await axios.get('/api/user-units');
    availableUnits.value = response.data.units || [];
    
    // Encontra a unidade atual
    const currentUnitId = response.data.current_unit_id;
    if (currentUnitId) {
      currentUnit.value = response.data.units.find(u => u.id === currentUnitId);
      selectedUnitId.value = currentUnitId;
    } else if (response.data.units.length > 0) {
      // Fallback: usa a primeira unidade se não houver current_unit_id
      currentUnit.value = response.data.units[0];
      selectedUnitId.value = response.data.units[0].id;
    }
    
    console.log('Unidades carregadas:', {
      total: availableUnits.value.length,
      current: currentUnit.value?.name,
      currentId: selectedUnitId.value
    });
  } catch (error) {
    console.error('Erro ao carregar unidades:', error);
    // Fallback: usa os dados do usuário prop
    if (props.user?.unit) {
      currentUnit.value = props.user.unit;
      selectedUnitId.value = props.user.unit.id;
      availableUnits.value = [props.user.unit];
    }
  }
};

// Seleciona uma unidade
const selectUnit = async (unitId) => {
  showUnitSelector.value = false;
  
  if (unitId === selectedUnitId.value) {
    return; // Já está na unidade selecionada
  }

  try {
    const response = await axios.post('/api/user-units/switch', {
      unit_id: unitId
    });

    if (response.data.success) {
      currentUnit.value = response.data.current_unit;
      selectedUnitId.value = unitId;
      emit('unit-changed', response.data.current_unit);
      
      // Recarrega a página para aplicar o novo filtro de unidade
      window.location.reload();
    }
  } catch (error) {
    console.error('Erro ao trocar unidade:', error);
    // Reverte a seleção em caso de erro
    selectedUnitId.value = currentUnit.value?.id;
  }
};

// Fecha o dropdown ao clicar fora
const handleClickOutside = (event) => {
  if (showUnitSelector.value && !event.target.closest('.relative')) {
    showUnitSelector.value = false;
  }
};

// Atualiza a unidade atual quando o usuário muda
watch(() => props.user, (newUser, oldUser) => {
  // Só atualiza se o usuário mudou de verdade (exemplo: após login)
  if (newUser && (!oldUser || newUser.id !== oldUser.id)) {
    console.log('User changed, updating unit info');
    currentUnit.value = newUser.current_unit || newUser.unit;
    selectedUnitId.value = currentUnit.value?.id;
    loadAvailableUnits();
  }
});

onMounted(() => {
  console.log('DesktopSidebar mounted with user:', props.user);
  
  if (props.user) {
    // Define valores iniciais imediatamente dos props
    if (props.user.current_unit || props.user.unit) {
      currentUnit.value = props.user.current_unit || props.user.unit;
      selectedUnitId.value = currentUnit.value?.id;
      console.log('Unidade inicial definida:', currentUnit.value?.name);
    }
    
    // Carrega lista completa de unidades
    loadAvailableUnits();
  }
  
  // Adiciona listener para fechar dropdown ao clicar fora
  document.addEventListener('click', handleClickOutside);
});

// Remove listener ao desmontar
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>