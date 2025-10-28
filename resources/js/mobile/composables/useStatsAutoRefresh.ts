import { ref, onMounted, onUnmounted, watch } from 'vue';
import { useDataSync } from './useDataSync';

/**
 * useStatsAutoRefresh - Composable para auto-refresh de stats cards
 *
 * Integra-se com o sistema de data sync global para atualizar
 * automaticamente os stats cards quando houver mudanças nos dados.
 *
 * @param loadStatsFunction - Função para carregar/atualizar os stats
 * @param options - Opções de configuração
 */
export function useStatsAutoRefresh(
  loadStatsFunction: () => Promise<void>,
  options: {
    /**
     * Se true, carrega stats imediatamente ao montar
     * @default true
     */
    loadOnMount?: boolean;
    /**
     * Intervalo de polling em ms (passa para useDataSync)
     * @default 15000 (15 segundos)
     */
    interval?: number;
    /**
     * Callback chamado após stats serem atualizados
     */
    onStatsUpdated?: () => void;
    /**
     * Se true, exibe logs de debug no console
     * @default false
     */
    debug?: boolean;
  } = {}
) {
  const {
    loadOnMount = true,
    interval = 15000,
    onStatsUpdated,
    debug = false
  } = options;

  const isRefreshing = ref(false);
  const lastRefreshTime = ref<Date | null>(null);
  const refreshCount = ref(0);

  /**
   * Atualiza os stats
   */
  const refreshStats = async () => {
    if (isRefreshing.value) {
      if (debug) console.log('[StatsAutoRefresh] Já está atualizando, ignorando...');
      return;
    }

    try {
      isRefreshing.value = true;

      if (debug) console.log('[StatsAutoRefresh] Atualizando stats...');

      await loadStatsFunction();

      lastRefreshTime.value = new Date();
      refreshCount.value++;

      if (debug) {
        console.log('[StatsAutoRefresh] Stats atualizados com sucesso', {
          time: lastRefreshTime.value.toLocaleTimeString(),
          count: refreshCount.value
        });
      }

      // Chama callback se fornecido
      if (onStatsUpdated) {
        onStatsUpdated();
      }

    } catch (error) {
      console.error('[StatsAutoRefresh] Erro ao atualizar stats:', error);
    } finally {
      isRefreshing.value = false;
    }
  };

  // Integra com o sistema de data sync global
  const {
    hasUpdates,
    latestData,
    checkNow,
    invalidateServerCache
  } = useDataSync({
    interval,
    onUpdate: (data) => {
      if (debug) {
        console.log('[StatsAutoRefresh] Data sync detectou atualizações:', {
          safety_checklists: data.safety_checklists?.length || 0,
          cleaning_controls: data.cleaning_controls?.length || 0,
          chemical_disinfections: data.chemical_disinfections?.length || 0
        });
      }

      // Atualiza stats automaticamente quando há novas atualizações
      refreshStats();
    }
  });

  // Observa mudanças em hasUpdates para atualizar stats
  watch(hasUpdates, (newValue) => {
    if (newValue && debug) {
      console.log('[StatsAutoRefresh] Novas atualizações detectadas, recarregando stats...');
    }
  });

  /**
   * Força uma atualização imediata dos stats
   * Útil para pull-to-refresh ou após criar/editar dados
   */
  const forceRefresh = async () => {
    if (debug) console.log('[StatsAutoRefresh] Refresh forçado solicitado');

    // Invalida cache do servidor para garantir dados atualizados
    await invalidateServerCache();

    // Verifica atualizações imediatamente
    await checkNow();

    // Atualiza stats
    await refreshStats();
  };

  // Carrega stats ao montar
  onMounted(async () => {
    if (loadOnMount) {
      if (debug) console.log('[StatsAutoRefresh] Componente montado, carregando stats iniciais...');
      await refreshStats();
    }
  });

  return {
    // Estado
    isRefreshing,
    hasUpdates,
    latestData,
    lastRefreshTime,
    refreshCount,

    // Métodos
    refreshStats,
    forceRefresh,
    checkNow,
    invalidateServerCache
  };
}

/**
 * Exemplo de uso em DashboardPage.vue:
 *
 * ```typescript
 * import { useStatsAutoRefresh } from '@mobile/composables/useStatsAutoRefresh';
 *
 * export default {
 *   setup() {
 *     const loadStats = async () => {
 *       // Sua lógica de carregamento de stats
 *       await Promise.all([
 *         loadActiveChecklists(),
 *         loadAvailableMachines(),
 *         loadMachines()
 *       ]);
 *     };
 *
 *     const {
 *       isRefreshing,
 *       hasUpdates,
 *       forceRefresh,
 *       lastRefreshTime
 *     } = useStatsAutoRefresh(loadStats, {
 *       loadOnMount: true,
 *       interval: 15000,
 *       onStatsUpdated: () => {
 *         console.log('Stats atualizados!');
 *       },
 *       debug: false
 *     });
 *
 *     // Para pull-to-refresh
 *     const handleRefresh = async (event: any) => {
 *       await forceRefresh();
 *       event.target.complete();
 *     };
 *
 *     return {
 *       isRefreshing,
 *       hasUpdates,
 *       forceRefresh,
 *       handleRefresh,
 *       lastRefreshTime
 *     };
 *   }
 * }
 * ```
 */
