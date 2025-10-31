# Sistema de Polling em Tempo Real - Dashboard Desktop

## Visão Geral

Implementamos um sistema de polling eficiente para atualizar os dados do dashboard em tempo real, garantindo performance e economia de recursos.

## Arquitetura

### Frontend (Vue 3)

#### Composable `usePolling`
Localização: `resources/js/desktop/composables/usePolling.js`

**Características:**
- Polling automático com intervalo configurável
- Pausa automática quando a aba perde o foco (economia de recursos)
- Retoma automática quando a aba volta ao foco
- Controle manual de start/stop/refresh
- Gerenciamento de estados (loading, error, data)
- Cleanup automático no unmount

**Uso:**
```javascript
const polling = usePolling(fetchFunction, {
  interval: 30000,  // 30 segundos
  immediate: true,  // Inicia imediatamente
  enabled: true,    // Habilita polling
});

// Acessar dados
polling.data.value
polling.loading.value
polling.error.value

// Controles
polling.refresh()      // Atualização manual
polling.startPolling() // Iniciar polling
polling.stopPolling()  // Parar polling
```

### Backend (Laravel)

#### Cache Strategy
Implementado no `DashboardController`:

1. **Stats Endpoint** (`/api/dashboard/stats`)
   - Cache: 60 segundos
   - Polling Frontend: 10 segundos
   - Motivo: Dados mais críticos, atualizações frequentes

2. **Sessions by Shift** (`/api/dashboard/sessions-by-shift`)
   - Cache: 300 segundos (5 minutos)
   - Polling Frontend: 30 segundos
   - Motivo: Dados históricos, menos voláteis

3. **Recent Activity** (`/api/dashboard/recent-activity`)
   - Cache: 60 segundos
   - Polling Frontend: 15 segundos
   - Motivo: Atividades recentes, equilíbrio entre atualização e performance

## Otimizações de Performance

### 1. Cache por Unidade e Tempo
```php
$cacheKey = "dashboard_stats_unit_{$unitId}_" . now()->format('Y-m-d_H:i');
```
- Cada unidade tem seu próprio cache
- Cache renovado a cada minuto
- Evita consultas desnecessárias ao banco

### 2. Visibilidade da Página
- Polling pausa quando o usuário muda de aba
- Polling retoma quando volta à aba do dashboard
- Economiza recursos do navegador e servidor

### 3. Prevenção de Requisições Concorrentes
- Flag `loading` previne múltiplas requisições simultâneas
- Garante que apenas uma requisição por tipo rode por vez

### 4. Indicadores Visuais
- Indicador "LIVE" com animação pulse/ping
- Botão "Atualizar Agora" com ícone de loading
- Ring azul nos cards durante atualização

## Intervalos de Polling

| Endpoint | Frontend Interval | Backend Cache | Motivo |
|----------|------------------|---------------|--------|
| Stats | 10s | 60s | Métricas críticas |
| Sessions | 30s | 300s | Dados históricos |
| Activity | 15s | 60s | Atividades recentes |

## Benefícios

1. **Performance**
   - Reduz carga no banco de dados com cache
   - Pausa automática quando não está em uso
   - Previne requisições duplicadas

2. **UX**
   - Dados sempre atualizados
   - Feedback visual de atualizações
   - Botão de refresh manual

3. **Escalabilidade**
   - Cache por unidade permite muitos usuários simultâneos
   - Sistema de polling não sobrecarrega o servidor
   - Fácil ajuste de intervalos conforme necessidade

## Configuração

### Ajustar Intervalos de Polling

Em `DashboardView.vue`:

```javascript
// Alterar intervalo de stats (padrão: 10000ms = 10s)
const statsPolling = usePolling(loadStats, {
  interval: 15000, // 15 segundos
  immediate: true,
});
```

### Ajustar Cache do Backend

Em `DashboardController.php`:

```php
// Alterar duração do cache (padrão: 60 segundos)
return Cache::remember($cacheKey, 120, function () use ($unitId) {
    return $this->calculateStats($unitId);
});
```

## Monitoramento

### Logs do Console
O sistema automaticamente loga:
- Erros de polling
- Status de carregamento
- Dados recebidos (em desenvolvimento)

### Indicadores Visuais
- Bolinha verde pulsando = Polling ativo
- Ring azul nos cards = Atualizando
- Ícone girando = Carregando

## Desabilitar Polling (se necessário)

Para desabilitar temporariamente:

```javascript
const polling = usePolling(fetchFunction, {
  enabled: false, // Desabilita polling
});
```

## Troubleshooting

### Polling muito frequente
- Aumentar `interval` no frontend
- Aumentar duração do cache no backend

### Dados não atualizam
- Verificar se `enabled: true`
- Verificar console para erros
- Confirmar que as APIs estão respondendo

### Performance lenta
- Reduzir frequência de polling
- Aumentar cache duration
- Verificar queries do banco (índices)

## Próximas Melhorias

1. WebSockets para atualizações push (elimina polling)
2. Service Worker para cache offline
3. Compressão de respostas (gzip)
4. GraphQL para otimizar queries
5. Redis para cache distribuído
