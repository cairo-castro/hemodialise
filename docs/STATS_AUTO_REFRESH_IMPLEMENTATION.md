# Guia de Implementação: Auto-Refresh em Stats Cards

Este guia mostra como aplicar o sistema de auto-refresh em qualquer página com stats cards.

## Páginas Já Implementadas ✅

1. **DashboardPage.vue** - Implementado e testado
2. **ChecklistListPage.vue** - Implementado e testado
3. **CleaningControlsPage.vue** - Implementado e testado
4. **MachinesPage.vue** - Implementado e testado
5. **PatientsPage.vue** - Implementado e testado

## Páginas Pendentes 🔄

6. CleaningChecklistNewPage.vue (se houver stats cards)

## Como Implementar (Passo a Passo)

### 1. Importar o Composable

Adicione o import no `<script setup>`:

```typescript
import { useStatsAutoRefresh } from '@mobile/composables/useStatsAutoRefresh';
```

### 2. Identificar a Função de Carregamento de Stats

Encontre ou crie uma função que carrega os stats da página. Exemplo:

```typescript
const loadStats = async () => {
  try {
    // Suas chamadas de API aqui
    const response = await fetch('/api/stats');
    const data = await response.json();
    stats.value = data;
  } catch (error) {
    console.error('Error loading stats:', error);
  }
};
```

### 3. Adicionar o useStatsAutoRefresh

Logo após a função `loadStats`, adicione:

```typescript
// Auto-refresh dos stats - atualiza automaticamente quando há mudanças
const {
  isRefreshing: isStatsRefreshing,
  forceRefresh: forceStatsRefresh
} = useStatsAutoRefresh(loadStats, {
  loadOnMount: false, // Carregaremos manualmente no onMounted
  interval: 15000, // 15 segundos
  onStatsUpdated: () => {
    console.log('[NomeDaPagina] Stats atualizados automaticamente');
  }
});
```

### 4. Atualizar o handleRefresh (Pull-to-Refresh)

Substitua o código existente do `handleRefresh`:

```typescript
// ANTES:
const handleRefresh = async (event: any) => {
  await loadStats();
  event.target.complete();
};

// DEPOIS:
const handleRefresh = async (event: any) => {
  await forceStatsRefresh(); // Usa forceRefresh para invalidar cache
  event.target.complete();
};
```

### 5. Adicionar Indicador Visual (Opcional)

No template, adicione a classe `updating` nos cards:

```vue
<div class="stat-card" :class="{ 'updating': isStatsRefreshing }">
  <!-- Conteúdo do card -->
</div>
```

E adicione os estilos CSS:

```css
/* Animação de atualização */
.stat-card.updating {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(0.98);
  }
}
```

## Exemplo Completo: CleaningControlsPage.vue

### Script Section

```typescript
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useStatsAutoRefresh } from '@mobile/composables/useStatsAutoRefresh'; // ← ADICIONAR
// ... outros imports

const router = useRouter();

// State
const stats = ref({
  total_today: 0,
  total_this_month: 0
});

// Methods
const loadStats = async () => {
  try {
    const response = await fetch('/api/cleaning-checklists/stats');
    const data = await response.json();
    stats.value = data;
  } catch (error) {
    console.error('Error loading stats:', error);
  }
};

// ← ADICIONAR: Auto-refresh dos stats
const {
  isRefreshing: isStatsRefreshing,
  forceRefresh: forceStatsRefresh
} = useStatsAutoRefresh(loadStats, {
  loadOnMount: false,
  interval: 15000,
  onStatsUpdated: () => {
    console.log('[CleaningControls] Stats atualizados automaticamente');
  }
});

// ← MODIFICAR: Pull-to-refresh
const handleRefresh = async (event: any) => {
  await forceStatsRefresh(); // ← Trocar loadStats() por forceStatsRefresh()
  event.target.complete();
};

// Lifecycle
onMounted(async () => {
  await loadStats();
});
</script>
```

### Template Section (Opcional - Indicador Visual)

```vue
<template>
  <ion-page>
    <ion-content>
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card" :class="{ 'updating': isStatsRefreshing }">
          <div class="stat-number">{{ stats.total_today }}</div>
          <div class="stat-label">Registros Hoje</div>
        </div>

        <div class="stat-card" :class="{ 'updating': isStatsRefreshing }">
          <div class="stat-number">{{ stats.total_this_month }}</div>
          <div class="stat-label">Total do Mês</div>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<style scoped>
/* Adicionar animação de atualização */
.stat-card.updating {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(0.98);
  }
}
</style>
```

## Exemplo: MachinesPage.vue

```typescript
import { useStatsAutoRefresh } from '@mobile/composables/useStatsAutoRefresh';

const loadStats = async () => {
  try {
    const response = await fetch('/api/machines/availability');
    const data = await response.json();
    availableMachines.value = data.available;
    occupiedMachines.value = data.occupied;
  } catch (error) {
    console.error('Error loading machine stats:', error);
  }
};

const {
  isRefreshing: isStatsRefreshing,
  forceRefresh: forceStatsRefresh
} = useStatsAutoRefresh(loadStats, {
  loadOnMount: false,
  interval: 15000,
  onStatsUpdated: () => console.log('[Machines] Stats updated')
});

const handleRefresh = async (event: any) => {
  await forceStatsRefresh();
  event.target.complete();
};
```

## Exemplo: PatientsPage.vue

```typescript
import { useStatsAutoRefresh } from '@mobile/composables/useStatsAutoRefresh';

const loadPatients = async () => {
  try {
    const response = await fetch('/api/patients');
    const data = await response.json();
    patients.value = data.patients;
    totalPatients.value = data.total;
  } catch (error) {
    console.error('Error loading patients:', error);
  }
};

const {
  isRefreshing: isStatsRefreshing,
  forceRefresh: forceStatsRefresh
} = useStatsAutoRefresh(loadPatients, {
  loadOnMount: false,
  interval: 15000,
  onStatsUpdated: () => console.log('[Patients] List updated')
});

const handleRefresh = async (event: any) => {
  await forceStatsRefresh();
  event.target.complete();
};
```

## Checklist de Implementação

Para cada página:

- [ ] Importar `useStatsAutoRefresh`
- [ ] Identificar função de carregamento de stats (`loadStats`, `loadMachines`, etc.)
- [ ] Adicionar `useStatsAutoRefresh` passando a função de carregamento
- [ ] Modificar `handleRefresh` para usar `forceStatsRefresh()`
- [ ] (Opcional) Adicionar indicador visual `isStatsRefreshing`
- [ ] (Opcional) Adicionar CSS de animação `.updating`
- [ ] Testar a página para verificar auto-refresh funcionando

## Benefícios

✅ **Dados sempre atualizados** - Stats refletem mudanças em até 15 segundos
✅ **Zero configuração manual** - Funciona automaticamente
✅ **Feedback visual** - Usuário vê quando dados estão sendo atualizados
✅ **Pull-to-refresh otimizado** - Invalida cache para atualização imediata
✅ **Performance** - Cache agressivo no backend (10s) minimiza queries

## Debug

Para ver o auto-refresh em ação:

1. Abra o console do navegador (F12)
2. Navegue para a página
3. Você verá logs como:
   ```
   [DataSync] Iniciando polling a cada 15s
   [NomeDaPagina] Stats atualizados automaticamente
   ```

## Troubleshooting

**Stats não atualizam:**
- Verifique se `loadOnMount: false` está definido
- Verifique se `loadStats()` é chamado no `onMounted()`
- Verifique erros no console

**Muitas requisições:**
- Aumente o `interval` para 30000 (30s)
- Verifique se não há múltiplos `useStatsAutoRefresh` na mesma página

**Cache não invalida:**
- Certifique-se de usar `forceStatsRefresh()` no pull-to-refresh
- Verifique se o endpoint `/api/sync/invalidate-cache` está acessível
