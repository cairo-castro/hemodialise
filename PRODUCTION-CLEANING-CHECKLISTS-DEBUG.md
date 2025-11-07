# Debug: Cleaning Checklists Vazio em Produção

## Problema
A página `/desktop/cleaning-checklists` está vazia em produção, mesmo com dados no banco.

## Checklist de Debug

### 1. Verificar Dados no Banco (Produção)

```bash
# SSH no servidor
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175

# Acessar container Docker
CONTAINER_ID=$(docker ps --filter "name=qualidade-qualidadehd" --format "{{.Names}}" | head -1)
docker exec -it $CONTAINER_ID sh

# Verificar dados
php artisan tinker
>>> \App\Models\CleaningControl::count()
>>> \App\Models\CleaningControl::with(['machine', 'user'])->first()
```

**Resultado esperado:** Deve retornar registros

---

### 2. Verificar API em Produção

```bash
# Dentro do container ou via SSH
curl -X GET "http://localhost/api/cleaning-controls" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=SEU_COOKIE_AQUI" \
  | jq .
```

**Possíveis problemas:**

#### A) 401 Unauthenticated
```json
{
  "message": "Unauthenticated."
}
```

**Solução:** Usuário não está autenticado. Verificar:
- Cookie de sessão está sendo enviado
- Sessão não expirou
- Middleware de autenticação está correto

#### B) 403 Forbidden
```json
{
  "message": "This action is unauthorized."
}
```

**Solução:** Usuário não tem permissão
- Verificar role do usuário (deve ser admin, manager, ou field_user)
- Verificar middleware `role:` nas rotas

#### C) Retorna vazio mesmo autenticado
```json
{
  "data": [],
  "links": {...},
  "meta": {...}
}
```

**Causa:** Filtro por unidade (`scoped_unit_id`) está impedindo

---

### 3. Verificar Middleware UnitScope

O middleware `UnitScopeMiddleware` adiciona automaticamente `scoped_unit_id` ao request.

**Verificar lógica:**

```php
// app/Http/Middleware/UnitScopeMiddleware.php

if ($user->hasGlobalAccess()) {
    // Usuário global - pode ver todas unidades OU nenhuma
    $selectedUnitId = $request->input('unit_id')
        ?? $request->header('X-Unit-Id')
        ?? $user->current_unit_id;

    if ($selectedUnitId) {
        $request->merge(['scoped_unit_id' => $selectedUnitId]);
    } else {
        // SEM UNIDADE SELECIONADA = RETORNA VAZIO
        $request->merge(['scoped_unit_id' => null, 'has_global_access' => true]);
    }
}
```

**Problema comum:** Usuário admin/manager sem `current_unit_id` definido

**Solução:**

```bash
# No tinker
$user = User::where('email', 'admin@example.com')->first();
$user->current_unit_id = 1; // ID da unidade padrão
$user->save();
```

---

### 4. Verificar Cache em Produção

```bash
# Limpar todos os caches
docker exec $CONTAINER_ID php artisan cache:clear
docker exec $CONTAINER_ID php artisan config:clear
docker exec $CONTAINER_ID php artisan route:clear
docker exec $CONTAINER_ID php artisan view:clear

# Recriar cache otimizado
docker exec $CONTAINER_ID php artisan config:cache
docker exec $CONTAINER_ID php artisan route:cache
docker exec $CONTAINER_ID php artisan view:cache
```

---

### 5. Verificar Assets Compilados

```bash
# Verificar se assets do desktop existem
docker exec $CONTAINER_ID ls -lh /var/www/html/public/desktop-assets/assets/

# Deve conter:
# - CleaningChecklistsView-*.js
# - desktop-*.js
# - vendor-*.js
```

**Se faltando:**

```bash
# Build local e enviar para produção
npm run build:desktop
git add public/desktop-assets/
git commit -m "build: update desktop assets"
git push origin main
```

---

### 6. Debug JavaScript no Browser

Abra DevTools (F12) na página `/desktop/cleaning-checklists`:

#### Console Tab - Procurar por erros:

```
Error loading cleanings: AxiosError {...}
```

**Se erro 401/403:** Problema de autenticação
**Se erro 500:** Problema no servidor (ver logs)
**Se erro CORS:** Configuração CORS incorreta

#### Network Tab - Verificar request:

1. Filtrar por `cleaning-controls`
2. Verificar **Request Headers:**
   - `Cookie: laravel_session=...` (deve existir)
   - `X-CSRF-TOKEN: ...` (deve existir)
3. Verificar **Response:**
   - Status Code: 200 (OK)
   - Response Body: `{data: [...], links: {...}}`

---

### 7. Verificar Logs do Laravel

```bash
# Ver últimos 200 logs
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175 \
  "docker exec \$(docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1) \
  tail -200 storage/logs/laravel-\$(date +%Y-%m-%d).log"

# Procurar por:
# - [API /api/cleaning-controls]
# - ERROR
# - SQLSTATE
# - Unauthenticated
```

---

## Soluções por Cenário

### Cenário 1: Usuário Admin sem current_unit_id

**Sintoma:** API retorna vazio mesmo com dados no banco

**Causa:** `UnitScopeMiddleware` filtra por `scoped_unit_id` que é `null`

**Solução:**

```php
// No tinker (produção)
$admins = User::whereIn('role', ['admin', 'manager'])->get();
foreach ($admins as $admin) {
    if (!$admin->current_unit_id) {
        $admin->current_unit_id = Unit::first()->id;
        $admin->save();
        echo "Updated {$admin->email}\n";
    }
}
```

---

### Cenário 2: Assets não compilados

**Sintoma:** Página carrega mas JavaScript não executa

**Causa:** Build desktop não foi executado antes do deploy

**Solução:**

```bash
# Local
npm run build:desktop
git add public/desktop-assets/
git commit -m "build: compile desktop assets"
git push origin main

# Aguardar Dokploy rebuild automático
```

---

### Cenário 3: Cache desatualizado

**Sintoma:** Código atualizado mas comportamento antigo

**Causa:** Cache de configuração/rotas desatualizado

**Solução:**

```bash
# SSH no servidor
docker exec $CONTAINER_ID php artisan optimize:clear
docker exec $CONTAINER_ID php artisan config:cache
docker exec $CONTAINER_ID php artisan route:cache
```

---

### Cenário 4: Sessão expirada

**Sintoma:** API retorna 401 Unauthenticated

**Causa:** Cookie de sessão expirou ou não está sendo enviado

**Solução Frontend:**

```javascript
// Verificar se axios está configurado para enviar cookies
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

**Verificar em:** `resources/js/desktop.js`

---

## Script de Debug Rápido

Crie este arquivo em produção para debug rápido:

```php
<?php
// debug-cleaning.php (na raiz do projeto)

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CLEANING CONTROLS DEBUG ===\n\n";

// 1. Total de registros
$total = \App\Models\CleaningControl::count();
echo "Total records: {$total}\n\n";

// 2. Primeiro registro
$first = \App\Models\CleaningControl::with(['machine', 'user'])->first();
if ($first) {
    echo "First record:\n";
    echo "  ID: {$first->id}\n";
    echo "  Machine: {$first->machine->name}\n";
    echo "  User: {$first->user->name}\n";
    echo "  Date: {$first->cleaning_date}\n";
    echo "  Unit ID: {$first->unit_id}\n\n";
}

// 3. Usuários admin sem current_unit_id
$admins = \App\Models\User::whereIn('role', ['admin', 'manager'])
    ->whereNull('current_unit_id')
    ->get();

if ($admins->count() > 0) {
    echo "WARNING: Users without current_unit_id:\n";
    foreach ($admins as $admin) {
        echo "  - {$admin->email} (role: {$admin->role})\n";
    }
    echo "\n";
}

// 4. Teste de query com filtro
$withFilter = \App\Models\CleaningControl::where('unit_id', 1)->count();
$withoutFilter = \App\Models\CleaningControl::count();
echo "Records with unit_id=1: {$withFilter}\n";
echo "Records total: {$withoutFilter}\n\n";

echo "=== END DEBUG ===\n";
```

**Executar:**

```bash
docker exec $CONTAINER_ID php debug-cleaning.php
```

---

## Checklist de Deploy (Preventivo)

Antes de fazer deploy, sempre executar:

```bash
# 1. Build assets
npm run build:desktop
npm run build:mobile

# 2. Verificar arquivos gerados
ls -lh public/desktop-assets/assets/ | grep CleaningChecklistsView

# 3. Commit e push
git add public/
git commit -m "build: update compiled assets"
git push origin main

# 4. Após deploy, limpar cache
# (automatizar via CI/CD se possível)
docker exec $CONTAINER_ID php artisan optimize:clear
docker exec $CONTAINER_ID php artisan config:cache
```

---

## Teste Final

Após aplicar correções, testar:

1. **Login no sistema** (produção)
2. **Acessar** `/desktop/cleaning-checklists`
3. **DevTools F12** > Network tab
4. **Verificar** request `/api/cleaning-controls`
   - Status: 200 ✅
   - Response: `{data: [...]}`  ✅
   - Dados exibidos na tela ✅

---

## Contato para Suporte

Se o problema persistir após seguir este guia:

1. Coletar logs:
   ```bash
   # Laravel logs
   docker exec $CONTAINER_ID tail -200 storage/logs/laravel-$(date +%Y-%m-%d).log > logs-laravel.txt

   # Nginx logs
   docker exec $CONTAINER_ID tail -200 /var/log/nginx/error.log > logs-nginx.txt
   ```

2. Browser DevTools:
   - Console errors (screenshot)
   - Network tab (screenshot do request/response)

3. Debug script output:
   ```bash
   docker exec $CONTAINER_ID php debug-cleaning.php > debug-output.txt
   ```

4. Enviar arquivos para análise

---

## Resumo Rápido

**Causa mais provável:** Usuário admin sem `current_unit_id` definido

**Solução rápida:**
```bash
# SSH no servidor
docker exec -it $(docker ps --filter "name=qualidade-qualidadehd" --format "{{.Names}}" | head -1) sh

# Tinker
php artisan tinker

# Corrigir
$user = User::where('email', 'SEU_EMAIL_ADMIN')->first();
$user->current_unit_id = 1;
$user->save();
```

**Limpar cache:**
```bash
php artisan optimize:clear
php artisan config:cache
```

**Reload página** e testar!
