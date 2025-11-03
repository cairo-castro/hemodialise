# Sistema de Notificações por Email

Este documento descreve o sistema completo de notificações por email implementado no Sistema de Hemodiálise.

## 📋 Visão Geral

O sistema permite que usuários recebam notificações por email sobre eventos importantes, com controle total sobre suas preferências através da interface de configurações.

## ✨ Funcionalidades

### Tipos de Notificações Disponíveis

1. **Novos Checklists** (`email_new_checklists`)
   - Notifica quando um novo checklist de segurança é criado
   - Inclui detalhes do paciente, máquina, turno e responsável
   - Ativado por padrão

2. **Manutenção de Máquinas** (`email_maintenance`)
   - Alertas de manutenção preventiva programada
   - Informa máquina, tipo de manutenção e data prevista
   - Ativado por padrão

3. **Relatórios Semanais** (`email_weekly_reports`)
   - Resumo semanal de atividades da unidade
   - Estatísticas de checklists, limpezas, sessões
   - Destaques e alertas importantes
   - Desativado por padrão

4. **Atualizações do Sistema** (`email_system_updates`)
   - Notificações sobre novas funcionalidades
   - Melhorias e correções implementadas
   - Ativado por padrão

## 🗄️ Estrutura do Banco de Dados

### Tabela: `notification_preferences`

```sql
CREATE TABLE notification_preferences (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    email_new_checklists BOOLEAN DEFAULT TRUE,
    email_maintenance BOOLEAN DEFAULT TRUE,
    email_weekly_reports BOOLEAN DEFAULT FALSE,
    email_system_updates BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## 🎨 Interface do Usuário

### Página de Configurações

Localização: `Configurações > Notificações`

Os usuários podem:
- Visualizar todas as opções de notificação disponíveis
- Ativar/desativar cada tipo individualmente
- Salvar preferências com feedback visual
- Ver descrições detalhadas de cada notificação

Arquivo: `resources/js/desktop/views/SettingsView.vue`

## 🔌 API Endpoints

### GET `/api/notification-preferences`

Retorna as preferências de notificação do usuário autenticado.

**Response:**
```json
{
  "success": true,
  "preferences": {
    "id": 1,
    "user_id": 5,
    "email_new_checklists": true,
    "email_maintenance": true,
    "email_weekly_reports": false,
    "email_system_updates": true,
    "created_at": "2025-11-03T17:45:00.000000Z",
    "updated_at": "2025-11-03T17:45:00.000000Z"
  }
}
```

### PUT `/api/notification-preferences`

Atualiza as preferências de notificação do usuário.

**Request:**
```json
{
  "email_new_checklists": true,
  "email_maintenance": true,
  "email_weekly_reports": false,
  "email_system_updates": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Preferências atualizadas com sucesso",
  "preferences": { /* updated preferences */ }
}
```

## 📧 Classes de Email (Mailables)

### 1. NewChecklistNotification

**Arquivo:** `app/Mail/NewChecklistNotification.php`

**Template:** `resources/views/emails/notifications/new-checklist.blade.php`

**Uso:**
```php
use App\Mail\NewChecklistNotification;
use Illuminate\Support\Facades\Mail;

$checklist = SafetyChecklist::find($id);
Mail::to($user->email)->send(new NewChecklistNotification($checklist));
```

### 2. MaintenanceAlertNotification

**Arquivo:** `app/Mail/MaintenanceAlertNotification.php`

**Template:** `resources/views/emails/notifications/maintenance-alert.blade.php`

**Uso:**
```php
use App\Mail\MaintenanceAlertNotification;

$machine = Machine::find($id);
Mail::to($user->email)->send(new MaintenanceAlertNotification(
    $machine,
    'Manutenção Preventiva Trimestral',
    '15/11/2025'
));
```

### 3. WeeklyReportNotification

**Arquivo:** `app/Mail/WeeklyReportNotification.php`

**Template:** `resources/views/emails/notifications/weekly-report.blade.php`

**Uso:**
```php
use App\Mail\WeeklyReportNotification;

$reportData = [
    'period' => 'Semana de 28/10 a 03/11/2025',
    'total_checklists' => 45,
    'total_cleanings' => 32,
    'active_machines' => 8,
    'total_sessions' => 120,
    'highlights' => [
        'Todos os checklists foram realizados no prazo',
        '100% de conformidade nas limpezas diárias'
    ],
    'alerts' => [
        'Máquina HD-03 próxima da manutenção preventiva'
    ]
];

Mail::to($user->email)->send(new WeeklyReportNotification($reportData));
```

### 4. SystemUpdateNotification

**Arquivo:** `app/Mail/SystemUpdateNotification.php`

**Template:** `resources/views/emails/notifications/system-update.blade.php`

**Uso:**
```php
use App\Mail\SystemUpdateNotification;

Mail::to($user->email)->send(new SystemUpdateNotification(
    'Nova Funcionalidade de Relatórios',
    'Implementamos gráficos interativos e exportação em PDF',
    [
        'Gráficos de desempenho',
        'Exportação em PDF',
        'Filtros avançados'
    ]
));
```

## 🧪 Testando as Notificações

### Comando de Teste

```bash
# Testar todas as notificações
php artisan notifications:test seuemail@exemplo.com

# Testar notificação específica
php artisan notifications:test seuemail@exemplo.com checklist
php artisan notifications:test seuemail@exemplo.com maintenance
php artisan notifications:test seuemail@exemplo.com report
php artisan notifications:test seuemail@exemplo.com update
```

**Arquivo:** `app/Console/Commands/TestNotifications.php`

### Email de Teste Básico

```bash
php artisan mail:test seuemail@exemplo.com
```

## 🔄 Integração com Eventos do Sistema

### Exemplo: Enviar notificação ao criar checklist

```php
// No ChecklistController ou usando Events/Listeners

use App\Mail\NewChecklistNotification;
use App\Models\NotificationPreference;
use Illuminate\Support\Facades\Mail;

// Após criar o checklist
$checklist = SafetyChecklist::create($data);

// Buscar usuários que querem receber esta notificação
$users = User::whereHas('notificationPreferences', function ($query) {
    $query->where('email_new_checklists', true);
})->get();

// Enviar emails
foreach ($users as $user) {
    Mail::to($user->email)->send(new NewChecklistNotification($checklist));
}
```

### Exemplo: Usando Jobs para envio assíncrono

```php
use Illuminate\Support\Facades\Mail;

// Criar um job
php artisan make:job SendChecklistNotification

// No job
namespace App\Jobs;

use App\Mail\NewChecklistNotification;
use App\Models\SafetyChecklist;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendChecklistNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public SafetyChecklist $checklist
    ) {}

    public function handle(): void
    {
        $users = User::whereHas('notificationPreferences', function ($query) {
            $query->where('email_new_checklists', true);
        })->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new NewChecklistNotification($this->checklist));
        }
    }
}

// Usar o job
SendChecklistNotification::dispatch($checklist);
```

## 🎨 Design dos Emails

Todos os emails seguem um design consistente:

### Estrutura Visual
- **Header**: Gradiente colorido com ícone e título
- **Content**: Fundo cinza claro com cards brancos
- **Info boxes**: Bordas coloridas à esquerda
- **Botões**: Call-to-action para acessar o sistema
- **Footer**: Informações do sistema e aviso de email automático

### Cores por Tipo
- **Checklist**: Roxo (`#667eea` → `#764ba2`)
- **Manutenção**: Laranja/Vermelho (`#f59e0b` → `#dc2626`)
- **Relatório**: Verde (`#10b981` → `#059669`)
- **Atualização**: Azul (`#3b82f6` → `#1d4ed8`)

### Responsividade
- Largura máxima: 600px
- Design otimizado para mobile e desktop
- Compatível com principais clientes de email

## 📝 Boas Práticas

### 1. Verificar Preferências Antes de Enviar

```php
$user = User::find($userId);
$preferences = NotificationPreference::forUser($user);

if ($preferences->email_new_checklists) {
    Mail::to($user->email)->send(new NewChecklistNotification($checklist));
}
```

### 2. Usar Filas para Envio em Massa

Configure filas no `.env`:
```env
QUEUE_CONNECTION=database
```

Execute migrations de filas:
```bash
php artisan queue:table
php artisan migrate
```

Processar filas:
```bash
php artisan queue:work
```

### 3. Tratamento de Erros

```php
try {
    Mail::to($user->email)->send(new NewChecklistNotification($checklist));
} catch (\Exception $e) {
    \Log::error('Failed to send notification', [
        'user_id' => $user->id,
        'error' => $e->getMessage()
    ]);
}
```

### 4. Rate Limiting

Lembre-se dos limites do Gmail SMTP:
- **500 emails/dia** (conta Gmail gratuita)
- **2000 emails/dia** (Google Workspace)

Para grandes volumes, considere usar serviços como:
- Amazon SES
- SendGrid
- Mailgun
- Postmark

## 🔐 Segurança

### Verificações Implementadas

1. **Autenticação**: Apenas usuários autenticados podem gerenciar preferências
2. **Autorização**: Usuários só podem editar suas próprias preferências
3. **Validação**: Todos os dados são validados antes de salvar
4. **Sanitização**: Emails são validados antes do envio
5. **CSRF Protection**: Rotas web protegidas contra CSRF

### Dados Sensíveis

- Nunca inclua senhas ou tokens em emails
- Use links com tokens temporários para ações sensíveis
- Não exponha IDs internos desnecessariamente

## 🚀 Próximos Passos (Futuras Melhorias)

1. **Notificações Push**: Implementar notificações push no PWA
2. **Notificações In-App**: Sistema de notificações dentro do aplicativo
3. **Agendamento**: Permitir usuários escolherem horários preferidos
4. **Digest**: Agrupar múltiplas notificações em um único email
5. **Templates Personalizáveis**: Permitir personalização dos templates
6. **Estatísticas**: Rastrear taxa de abertura e cliques
7. **Notificações SMS**: Adicionar suporte para SMS críticos
8. **Webhook Support**: Integração com sistemas externos

## 📚 Referências

- [Laravel Mail Documentation](https://laravel.com/docs/11.x/mail)
- [Gmail SMTP Settings](https://support.google.com/mail/answer/7126229)
- [Google App Passwords](https://support.google.com/accounts/answer/185833)
- [Email Design Best Practices](https://www.campaignmonitor.com/resources/guides/email-design/)

## 🆘 Troubleshooting

### Emails não chegam

1. Verifique as configurações do `.env` (veja `CONFIGURACAO_EMAIL.md`)
2. Teste com: `php artisan mail:test seu@email.com`
3. Verifique pasta de SPAM
4. Confira logs: `tail -f storage/logs/laravel.log`

### Erro ao carregar preferências

1. Verifique se a migration foi executada: `php artisan migrate:status`
2. Confira se a API está autenticada
3. Verifique logs do navegador (DevTools > Console)

### Template não renderiza corretamente

1. Limpe cache de views: `php artisan view:clear`
2. Verifique sintaxe Blade no template
3. Teste localmente com: `php artisan notifications:test`

## 📞 Suporte

Para problemas ou dúvidas:
1. Verifique este documento e `CONFIGURACAO_EMAIL.md`
2. Consulte logs em `storage/logs/laravel.log`
3. Use os comandos de teste para diagnosticar
4. Entre em contato com o desenvolvedor responsável
