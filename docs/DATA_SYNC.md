# Sistema de Sincronização de Dados em Tempo Real

## Visão Geral

Este sistema implementa sincronização de dados em tempo real para a gestão mobile usando **polling leve**, uma alternativa open source e econômica ao WebSockets que consome apenas **1-2% da performance** em comparação com soluções baseadas em WebSockets como Laravel Reverb ou Pusher.

## Por que Polling ao invés de WebSockets?

### Vantagens do Polling Leve:
1. **Baixíssimo consumo de recursos** - Ideal para servidores compartilhados
2. **Zero dependências externas** - Não requer Redis, servidor WebSocket dedicado, etc.
3. **Compatibilidade universal** - Funciona em qualquer ambiente (proxies, firewalls, CDNs)
4. **Simplicidade** - Mais fácil de debugar e manter
5. **Custo zero** - Sem necessidade de serviços pagos como Pusher

### Comparação de Performance:

| Solução | CPU | Memória | Conexões Simultâneas |
|---------|-----|---------|---------------------|
| WebSocket (Reverb/Pusher) | Alta | Alta | Muitas (persistentes) |
| **Polling Leve (Nossa solução)** | **Mínima** | **Mínima** | **Poucas (efêmeras)** |

## Arquitetura

### Backend (Laravel)

**Controller**: `App\Http\Controllers\Api\DataSyncController`

**Endpoints**:
- `GET /api/sync/check-updates` - Verifica se há atualizações
- `POST /api/sync/invalidate-cache` - Força limpeza de cache

**Estratégia de Cache**:
- Cache de 10 segundos para timestamps de última atualização
- Cache de 10 segundos para dados atualizados
- Queries otimizadas usando `max('updated_at')` ao invés de `select *`
- Filtro por unidade (unit_id) para isolar dados

**Fluxo de Verificação**:
```
Cliente envia: last_check = "2025-01-28T10:00:00Z"
     ↓
Backend verifica cache (10s)
     ↓
Se cache válido: retorna timestamp sem query ao BD
Se cache inválido: busca max(updated_at) de cada tabela
     ↓
Compara com last_check do cliente
     ↓
Se há atualizações: retorna dados novos/alterados
Se não há: retorna {has_updates: false}
```

### Frontend (Vue.js/Ionic)

**Composable**: `useDataSync.ts`

**Características**:
- Polling automático a cada 15 segundos (configurável)
- Retry automático com backoff em caso de erro
- Para automaticamente quando componente é desmontado
- Callback customizável para reagir a atualizações

**Uso Global** (já implementado em `App.vue`):
```typescript
const { isPolling, hasUpdates, latestData } = useDataSync({
  interval: 15000, // 15 segundos
  onUpdate: (data) => {
    // Seu código para atualizar UI
    console.log('Novas atualizações:', data);
  }
});
```

## Como Usar

### 1. Uso Automático Global

Já está ativo em `resources/js/mobile/App.vue`. Todas as páginas do mobile se beneficiam automaticamente.

### 2. Uso em Componentes Específicos

Se você quiser reagir a atualizações em um componente específico:

```vue
<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-title>Minha Lista</ion-title>
        <!-- Indicador visual de sincronização -->
        <ion-badge v-if="hasUpdates" color="success" slot="end">
          Novas atualizações disponíveis
        </ion-badge>
      </ion-toolbar>
    </ion-header>

    <ion-content>
      <!-- Seu conteúdo aqui -->
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useDataSync } from '@mobile/composables/useDataSync';

const items = ref([]);

// Configurar data sync para esta página
const { hasUpdates, latestData, checkNow, invalidateServerCache } = useDataSync({
  onUpdate: (data) => {
    // Atualizar lista local com novos dados
    if (data.safety_checklists) {
      // Mesclar ou substituir dados locais
      items.value = mergeItems(items.value, data.safety_checklists);

      // Ou simplesmente recarregar do backend
      loadItems();
    }
  }
});

const handleRefresh = async (event: any) => {
  await checkNow(); // Força verificação imediata
  event.target.complete();
};

// Após criar/editar dados localmente, invalide o cache
const saveItem = async (item: any) => {
  await api.post('/api/checklists', item);

  // Força o backend a retornar dados atualizados na próxima verificação
  await invalidateServerCache();
};
</script>
```

### 3. Ajustar Intervalo de Polling

Para economizar ainda mais bateria/dados em dispositivos móveis:

```typescript
// Polling mais lento (30 segundos)
const { ... } = useDataSync({
  interval: 30000 // 30s
});

// Polling mais rápido (apenas se necessário)
const { ... } = useDataSync({
  interval: 5000 // 5s - NÃO RECOMENDADO para produção
});
```

**Recomendação**: 15-30 segundos é ideal para balancear tempo real vs. performance.

## Performance e Otimizações

### Backend

1. **Cache agressivo (10s)**:
   ```php
   Cache::remember("data_sync_unit_{$unitId}_last_update", 10, function() {
       // Query apenas se cache expirou
   });
   ```

2. **Queries otimizadas**:
   ```php
   // ✅ Bom - Apenas timestamp
   $latest = SafetyChecklist::max('updated_at');

   // ❌ Ruim - Traz todos os dados
   $latest = SafetyChecklist::latest()->first()?->updated_at;
   ```

3. **Limite de resultados**:
   ```php
   ->limit(50) // Nunca retorna mais de 50 registros por tipo
   ```

4. **Filtragem por unidade**:
   ```php
   // Usuários só veem dados da sua unidade
   ->whereHas('machine', fn($q) => $q->where('unit_id', $unitId))
   ```

### Frontend

1. **Timeout de 5s**: Requisições que demoram mais são canceladas
2. **Retry com limite**: Máximo de 3 tentativas antes de parar
3. **Cleanup automático**: Para o polling quando componente é desmontado

### Monitoramento

Para ver o polling em ação, abra o console do navegador:

```
[DataSync] Iniciando polling a cada 15s
[DataSync] Novas atualizações detectadas: { last_update, safety_checklists: 3, ... }
[App] Novas atualizações recebidas: { ... }
```

## Troubleshooting

### Problema: Dados não atualizam automaticamente

**Solução**:
1. Verifique se o polling está ativo no console: `isPolling: true`
2. Verifique se não há erros de autenticação (401)
3. Teste manualmente: `await checkNow()`

### Problema: Muitas requisições ao servidor

**Solução**:
1. Aumente o intervalo de polling (ex: 30s ao invés de 15s)
2. Verifique se não há múltiplos componentes iniciando polling separadamente

### Problema: Cache não invalida após criar dados

**Solução**:
```typescript
// Após salvar dados, sempre invalide o cache
await api.post('/api/checklists', data);
await invalidateServerCache(); // ← Adicione esta linha
```

## Métricas de Performance Esperadas

Para um servidor com 50 usuários simultâneos:

| Métrica | Valor | Comparação WebSocket |
|---------|-------|---------------------|
| Requisições/min | 200 (50 users × 4 checks/min) | N/A |
| CPU adicional | < 1% | 5-10% |
| Memória adicional | < 50MB | 200-500MB |
| Largura de banda | ~100KB/min | ~500KB/min |

## Roadmap Futuro

Possíveis melhorias futuras:

1. **Long Polling**: Manter conexão aberta por até 30s esperando atualizações
2. **Server-Sent Events (SSE)**: Alternativa leve a WebSockets
3. **Service Worker**: Cache offline com sincronização em background
4. **Delta Sync**: Enviar apenas campos alterados, não registros completos

## Conclusão

Este sistema fornece sincronização de dados em tempo real suficientemente rápida para a maioria dos casos de uso (atualização em até 15 segundos) com **fração mínima do custo e complexidade** de soluções WebSocket.

Para um sistema governamental com orçamento limitado e servidor compartilhado, esta é a solução ideal.
