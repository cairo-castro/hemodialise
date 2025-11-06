# Correção de Expiração de Sessão do Filament

## Problema
O painel administrativo Filament estava mantendo sessões ativas por dias, causando o alerta "would you like to refresh the page" devido a tentativas de polling em sessões expiradas.

## Soluções Implementadas

### 1. Configuração de Sessão (.env)
```env
SESSION_LIFETIME=120                # Reduzido de 480min (8h) para 120min (2h)
SESSION_EXPIRE_ON_CLOSE=true        # Adicionado: expira ao fechar navegador
```

### 2. Configuração do Filament (AdminPanelProvider.php)
Adicionadas as seguintes configurações:
- `->spa(false)` - Desabilita modo SPA para evitar problemas com sessões obsoletas
- `->unsavedChangesAlerts(false)` - Desabilita alertas de mudanças não salvas em sessões longas

### 3. Middleware de Verificação de Sessão
Criado `App\Http\Middleware\CheckSessionExpiration` que:
- Verifica se a sessão expirou com base na última atividade
- Força logout automático se a sessão estiver expirada
- Atualiza o timestamp de última atividade em cada requisição
- Retorna resposta JSON apropriada para requisições AJAX

### 4. Limpeza Automática de Sessões
Criado comando `sessions:clean` que:
- Remove sessões expiradas do banco de dados
- Executa automaticamente a cada hora via Laravel Scheduler
- Pode ser executado manualmente: `php artisan sessions:clean`

### 5. Tratamento de CSRF Token Mismatch
Melhorado o tratamento de erros 419 (CSRF Token Mismatch) no `bootstrap/app.php`

## Como Aplicar na Produção

### 1. Atualizar .env de Produção
```bash
SESSION_LIFETIME=120
SESSION_EXPIRE_ON_CLOSE=true
```

### 2. Limpar Cache
```bash
php artisan optimize:clear
php artisan config:cache
```

### 3. Limpar Sessões Antigas
```bash
php artisan sessions:clean
```

## Arquivos Modificados

1. `.env` - Configurações de sessão
2. `app/Providers/Filament/AdminPanelProvider.php` - Configuração do painel
3. `app/Http/Middleware/CheckSessionExpiration.php` - Novo middleware
4. `app/Console/Commands/CleanExpiredSessions.php` - Novo comando
5. `bootstrap/app.php` - Agendamento de tarefas
