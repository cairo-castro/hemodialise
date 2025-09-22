<template>
  <div v-if="shouldShowSwitcher" class="relative">
    <select
      v-model="selectedInterface"
      @change="handleInterfaceChange"
      class="appearance-none bg-white border border-gray-200 rounded-lg px-3 py-2 pr-8 text-sm text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer"
      :disabled="loading"
    >
      <option
        v-for="iface in availableInterfaces"
        :key="iface.value"
        :value="iface.value"
        :class="currentInterface === iface.value ? 'font-semibold' : ''"
      >
        {{ iface.label }}
      </option>
    </select>

    <!-- Custom dropdown arrow -->
    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>

    <!-- Loading indicator -->
    <div v-if="loading" class="absolute inset-y-0 right-6 flex items-center pr-2">
      <div class="animate-spin rounded-full h-3 w-3 border border-gray-300 border-t-blue-600"></div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'InterfaceSwitcher',
  props: {
    user: {
      type: Object,
      default: null
    }
  },
  emits: ['interface-switch'],
  setup(props, { emit }) {
    const loading = ref(false)
    const selectedInterface = ref('desktop')

    // Detectar interface atual baseada na URL
    const currentInterface = computed(() => {
      const path = window.location.pathname
      if (path.startsWith('/admin')) return 'admin'
      if (path.startsWith('/mobile')) return 'mobile'
      if (path.startsWith('/desktop')) return 'desktop'
      return 'desktop' // fallback
    })

    // Verificar se o usuário pode acessar interface específica
    const canAccessInterface = (interfaceType) => {
      // Se não há usuário ou é guest, só pode acessar mobile
      if (!props.user || props.user.role === 'guest') {
        return interfaceType === 'mobile'
      }

      // Verificar role do usuário
      const userRole = props.user.role

      switch (interfaceType) {
        case 'admin':
          return ['admin', 'gestor', 'coordenador', 'supervisor'].includes(userRole)
        case 'desktop':
          return ['admin', 'gestor', 'coordenador', 'supervisor'].includes(userRole)
        case 'mobile':
          return true // Todos podem acessar mobile
        default:
          return false
      }
    }

    // Verificar se deve mostrar o switcher
    const shouldShowSwitcher = computed(() => {
      // Só mostra se o usuário tem permissão para alternar interfaces
      if (!props.user) return false

      // Se é guest, não mostra
      if (props.user.role === 'guest') return false

      // Se tem o método canToggleInterfaces, usar ele
      if (typeof props.user.canToggleInterfaces === 'function') {
        return props.user.canToggleInterfaces()
      }

      // Fallback: verificar se é admin, gestor, coordenador ou supervisor
      return ['admin', 'gestor', 'coordenador', 'supervisor'].includes(props.user.role)
    })
    
    // Filtrar apenas interfaces disponíveis para o usuário
    const availableInterfaces = computed(() => {
      const interfaces = [
        { value: 'admin', label: '🛠️ Admin', url: '/admin' },
        { value: 'desktop', label: '🖥️ Desktop', url: '/desktop/preline' },
        { value: 'mobile', label: '📱 Mobile', url: '/mobile/ionic' }
      ]
      
      return interfaces.filter(iface => canAccessInterface(iface.value))
    })
    
    const handleInterfaceChange = async () => {
      console.log('Interface change triggered:', {
        selected: selectedInterface.value,
        current: currentInterface.value,
        available: availableInterfaces.value
      })

      if (selectedInterface.value === currentInterface.value) {
        console.log('Same interface selected, skipping')
        return
      }

      loading.value = true

      try {
        const targetInterface = availableInterfaces.value.find(
          iface => iface.value === selectedInterface.value
        )

        console.log('Target interface found:', targetInterface)

        if (targetInterface) {
          // Emit evento para componente pai lidar com a mudança
          emit('interface-switch', {
            interface: selectedInterface.value,
            url: targetInterface.url
          })

          console.log('Redirecting to:', targetInterface.url)

          // Aguardar um pouco antes de redirecionar para mostrar loading
          setTimeout(() => {
            window.location.href = targetInterface.url
          }, 300)
        } else {
          console.error('Target interface not found in available interfaces')
          selectedInterface.value = currentInterface.value
        }
      } catch (error) {
        console.error('Erro ao alternar interface:', error)
        // Reverter seleção em caso de erro
        selectedInterface.value = currentInterface.value
      } finally {
        setTimeout(() => {
          loading.value = false
        }, 500)
      }
    }
    
    // Inicializar com interface atual
    onMounted(() => {
      selectedInterface.value = currentInterface.value
      console.log('InterfaceSwitcher mounted:', {
        user: props.user,
        currentInterface: currentInterface.value,
        shouldShow: shouldShowSwitcher.value,
        availableInterfaces: availableInterfaces.value
      })
    })
    
    return {
      loading,
      selectedInterface,
      currentInterface,
      availableInterfaces,
      shouldShowSwitcher,
      handleInterfaceChange,
      canAccessInterface
    }
  }
}
</script>

<style scoped>
/* Remove default select styling */
select {
  background-image: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

/* Custom focus styles */
select:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Disabled state */
select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Hover effect */
select:hover:not(:disabled) {
  border-color: #d1d5db;
  background-color: #f9fafb;
}

/* Current interface highlighting */
option {
  padding: 8px 12px;
}
</style>