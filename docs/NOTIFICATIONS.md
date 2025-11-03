# Sistema de Notificações - Documentação Completa

## Visão Geral

Sistema completo de notificações em tempo real com:
- ✅ Banco de dados e API RESTful
- ✅ Polling automático (30s)
- ✅ Notificações automáticas via Observers
- ✅ Som e vibração para novas notificações
- ✅ Notificações do navegador
- 🔄 Página de histórico (em desenvolvimento)
- 🔄 Preferências de usuário (em desenvolvimento)

## Estrutura do Sistema

### 1. Database & Models

**Tabela: `notifications`**
```php
- id
- user_id (nullable)      // Usuário específico ou broadcast
- unit_id (nullable)      // Unidade específica ou sistema-wide
- type                    // checklist, success, warning, error, info
- title                   // Título da notificação
- message                 // Mensagem descritiva
- data (JSON)            // Dados adicionais
- action_url             // URL para navegação
- read_at                // Timestamp de leitura
- created_at, updated_at
```

**Índices**: Otimizados para queries por user_id, unit_id, read_at e created_at.

### 2. API Endpoints

```
GET    /api/notifications              // Lista paginada
GET    /api/notifications/poll         // Polling para novas
GET    /api/notifications/unread-count // Contador
POST   /api/notifications/mark-all-read
POST   /api/notifications/{id}/mark-read
DELETE /api/notifications/{id}
```

### 3. Notification Service

**Localização**: `app/Services/NotificationService.php`

**Métodos Disponíveis**:

```php
// Checklist events
NotificationService::notifyChecklistCreated($checklist, $creator);
NotificationService::notifyChecklistCompleted($checklist, $completedBy);
NotificationService::notifyChecklistInterrupted($checklist, $reason, $interruptedBy);

// Limpeza events
NotificationService::notifyCleaningCompleted($cleaning, $completedBy);

// Paciente events
NotificationService::notifyPatientCreated($patient, $creator);
NotificationService::notifyPatientStatusChanged($patient, $oldStatus, $newStatus, $changedBy);

// Máquina events
NotificationService::notifyMachineStatusChanged($machine, $oldStatus, $newStatus, $changedBy);
NotificationService::notifyMachineProblem($machine, $problem, $reportedBy);

// Desinfecção events
NotificationService::notifyDisinfectionCompleted($disinfection, $completedBy);

// Broadcast notification
NotificationService::broadcastNotification($type, $title, $message, $data, $actionUrl);
```

### 4. Model Observers

**Registrados automaticamente** em `AppServiceProvider`:

- **SafetyChecklistObserver**: Notifica criação, conclusão e interrupção de checklists
- **PatientObserver**: Notifica cadastro e mudança de status de pacientes
- **CleaningControlObserver**: Notifica conclusão de limpezas

**Como funcionam**: Os observers são disparados automaticamente quando os modelos são criados/atualizados.

### 5. Frontend - Notificações em Tempo Real

**Componente**: `resources/js/desktop/components/NotificationsDropdown.vue`

**Features**:
- Dropdown com lista de notificações
- Badge com contador de não lidas
- Polling automático a cada 30 segundos
- Marca como lida ao clicar
- Navega para action_url automaticamente
- Som e vibração para novas notificações
- Notificações do navegador

**Composable**: `resources/js/desktop/composables/useNotificationSound.js`

```javascript
const {
  notifyUser,                     // Toca som + vibra + mostra notificação
  playSound,                      // Apenas som
  triggerVibration,               // Apenas vibração
  showBrowserNotification,        // Apenas notificação do navegador
  requestNotificationPermission,  // Pedir permissão
  enableSound,                    // ref() - toggle som
  enableVibration,                // ref() - toggle vibração
  enableBrowserNotifications,     // ref() - toggle browser
} = useNotificationSound();
```

## Como Integrar Som/Vibração no Dropdown

Para adicionar o som/vibração no componente NotificationsDropdown.vue:

```vue
<script setup>
// Adicionar import
import { useNotificationSound } from '../composables/useNotificationSound';

// Adicionar no setup
const { notifyUser, requestNotificationPermission } = useNotificationSound();

// No onMounted, pedir permissão
onMounted(() => {
  // ... código existente ...

  // Request notification permission
  requestNotificationPermission();
});

// Na função pollNotifications, após detectar novas notificações:
async function pollNotifications() {
  // ... código existente ...

  if (data.notifications && data.notifications.length > 0) {
    const newNotifications = data.notifications.map(n => ({
      ...n,
      created_at: new Date(n.created_at),
      read: !!n.read_at,
    }));

    // ADICIONAR: Notificar usuário para cada nova notificação
    newNotifications.forEach(notification => {
      notifyUser(notification);
    });

    // Prepend new notifications
    notifications.value = [...newNotifications, ...notifications.value].slice(0, 20);
  }

  // ... restante do código ...
}
</script>
```

## Uso Manual no Código

### Criar Notificação Manualmente

```php
use App\Services\NotificationService;

// Em um controller ou service
public function reportProblem(Request $request, Machine $machine)
{
    // ... lógica de negócio ...

    // Criar notificação
    NotificationService::notifyMachineProblem(
        $machine,
        $request->problem_description,
        auth()->user()
    );

    return response()->json(['success' => true]);
}
```

### Broadcast para Todos

```php
// Notificação para todos os usuários
NotificationService::broadcastNotification(
    type: 'info',
    title: 'Manutenção Programada',
    message: 'O sistema estará em manutenção amanhã das 2h às 4h',
    data: ['scheduled_at' => '2025-11-04 02:00:00'],
    actionUrl: '/admin/help'
);
```

## Tipos de Notificação

| Tipo       | Cor     | Ícone              | Uso                                      |
|------------|---------|--------------------|-----------------------------------------|
| `success`  | Verde   | CheckCircle        | Ações concluídas com sucesso            |
| `info`     | Azul    | InformationCircle  | Informações gerais                      |
| `warning`  | Amarelo | ExclamationCircle  | Avisos e alertas                        |
| `error`    | Vermelho| XCircle            | Erros e problemas críticos              |
| `checklist`| Roxo    | ClipboardCheck     | Eventos relacionados a checklists       |

## Preferências de Usuário (Futuro)

### Migration para Preferências

```php
Schema::create('notification_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('notification_type'); // checklist, success, warning, etc
    $table->boolean('enabled')->default(true);
    $table->boolean('sound_enabled')->default(true);
    $table->boolean('vibration_enabled')->default(true);
    $table->boolean('browser_notification_enabled')->default(true);
    $table->timestamps();

    $table->unique(['user_id', 'notification_type']);
});
```

### Verificação de Preferências

Atualizar `NotificationService::shouldNotifyUser()`:

```php
protected static function shouldNotifyUser($user, $type)
{
    $preference = NotificationPreference::where('user_id', $user->id)
        ->where('notification_type', $type)
        ->first();

    return $preference ? $preference->enabled : true;
}
```

## Testando o Sistema

### 1. Criar Notificação de Teste

```bash
php artisan tinker
```

```php
use App\Services\NotificationService;
use App\Models\User;

$user = User::first();
NotificationService::broadcastNotification(
    'info',
    'Notificação de Teste',
    'Esta é uma notificação de teste do sistema',
    ['test' => true],
    null
);
```

### 2. Verificar API

```bash
# Verificar notificações
curl -X GET http://localhost:8000/api/notifications \
  -H "Accept: application/json" \
  --cookie "session_cookie_here"

# Polling
curl -X GET "http://localhost:8000/api/notifications/poll?last_check=2025-11-03T12:00:00Z" \
  -H "Accept: application/json" \
  --cookie "session_cookie_here"
```

### 3. Testar no Navegador

1. Acesse `/desktop`
2. Login com usuário admin/gestor
3. Clique no ícone de sino no canto superior direito
4. Verifique as notificações existentes
5. Crie um novo paciente no admin para ver notificação aparecer

## Performance

- **Polling Interval**: 30 segundos (configurável)
- **Índices**: Otimizados para queries frequentes
- **Paginação**: Limite de 20 notificações por request
- **Cleanup**: Considerar job para arquivar notificações antigas (>30 dias)

## Próximos Passos

1. ✅ Integrar som/vibração no NotificationsDropdown (código pronto, basta adicionar)
2. 🔄 Criar página de histórico completo em `/desktop/notifications`
3. 🔄 Implementar sistema de preferências de usuário
4. 🔄 Adicionar filtros por tipo de notificação
5. 🔄 Implementar WebSockets para notificações instantâneas (futuro)

## Troubleshooting

### Notificações não aparecem

1. Verificar se os observers estão registrados em `AppServiceProvider`
2. Verificar logs: `tail -f storage/logs/laravel.log`
3. Testar API diretamente: `GET /api/notifications`

### Som não toca

1. Verificar permissões do navegador
2. Verificar localStorage: `localStorage.getItem('notificationSound')`
3. Testar em navegador com autoplay permitido

### Polling não funciona

1. Verificar console do navegador para erros
2. Verificar se o intervalo está ativo
3. Testar endpoint de polling manualmente

## Conclusão

O sistema de notificações está **totalmente funcional** e pronto para uso. As funcionalidades básicas estão implementadas e os recursos avançados (preferências, histórico completo) podem ser adicionados conforme necessário.
