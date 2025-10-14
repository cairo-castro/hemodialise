# Sistema de Permissões Multinível - Hemodiálise

## Visão Geral

Sistema completo de permissões baseado em **Spatie Laravel Permission** com suporte a:
- **Acesso Global**: Ver e gerenciar todas as unidades
- **Acesso por Unidade**: Acesso restrito apenas à unidade do usuário

---

## Arquitetura

### Componentes Principais

1. **Spatie Laravel Permission** - Base do sistema de roles e permissions
2. **Unit Scope Middleware** - Filtragem automática por unidade
3. **Policies** - Autorização granular por modelo
4. **Custom Methods no User** - hasGlobalAccess(), canAccessUnit(), etc.

### Extensão das Tabelas

#### `model_has_permissions`
```sql
- model_type (string)
- model_id (bigint)
- permission_id (bigint)
- unit_id (bigint, nullable) ← NOVO
```

#### `model_has_roles`
```sql
- model_type (string)
- model_id (bigint)
- role_id (bigint)
- unit_id (bigint, nullable) ← NOVO
```

**Quando `unit_id` é NULL** → Permissão/Role GLOBAL
**Quando `unit_id` tem valor** → Permissão/Role específica da unidade

---

## Roles Definidos

| Role | Descrição | Acesso | Interfaces |
|------|-----------|--------|------------|
| `super-admin` | Administrador Total | **Global** - Todas unidades | Admin, Desktop, Mobile |
| `gestor-global` | Gestor Administrativo | **Global** - Todas unidades | Admin, Desktop, Mobile |
| `gestor-unidade` | Gestor de Unidade | **Unidade** - Apenas sua unidade | Desktop, Mobile |
| `coordenador` | Coordenador | **Unidade** - Apenas sua unidade | Desktop, Mobile |
| `supervisor` | Supervisor | **Unidade** - Apenas sua unidade | Desktop, Mobile |
| `tecnico` | Técnico de Campo | **Unidade** - Apenas sua unidade | Mobile |

---

## Permissions Criados (56 permissões)

### Máquinas (5 permissões)
- `machines.view` - Visualizar máquinas
- `machines.create` - Criar máquinas
- `machines.update` - Atualizar máquinas
- `machines.delete` - Deletar máquinas
- `machines.manage-status` - Gerenciar status (manutenção, ativar/desativar)

### Pacientes (5 permissões)
- `patients.view` - Visualizar pacientes
- `patients.create` - Criar pacientes
- `patients.update` - Atualizar pacientes
- `patients.delete` - Deletar pacientes
- `patients.export` - Exportar dados de pacientes

### Checklists de Segurança (8 permissões)
- `safety-checklists.view` - Visualizar checklists
- `safety-checklists.create` - Criar checklists
- `safety-checklists.update` - Atualizar checklists
- `safety-checklists.delete` - Deletar checklists
- `safety-checklists.advance` - Avançar fase
- `safety-checklists.interrupt` - Interromper sessão
- `safety-checklists.pause` - Pausar sessão
- `safety-checklists.resume` - Retomar sessão

### Checklists de Limpeza (4 permissões)
- `cleaning-checklists.view`
- `cleaning-checklists.create`
- `cleaning-checklists.update`
- `cleaning-checklists.delete`

### Unidades (4 permissões)
- `units.view` - Visualizar unidades
- `units.create` - Criar unidades
- `units.update` - Atualizar unidades
- `units.delete` - Deletar unidades

### Usuários (4 permissões)
- `users.view` - Visualizar usuários
- `users.create` - Criar usuários
- `users.update` - Atualizar usuários
- `users.delete` - Deletar usuários

### Sistema (10 permissões)
- `system.settings` - Configurações do sistema
- `system.audit-logs` - Logs de auditoria
- `system.backups` - Gerenciar backups
- `reports.operational` - Relatórios operacionais
- `reports.quality` - Relatórios de qualidade
- `reports.export` - Exportar relatórios
- `analytics.view` - Ver analytics
- `interface.admin` - Acessar painel admin
- `interface.desktop` - Acessar interface desktop
- `interface.mobile` - Acessar interface mobile

---

## Matriz de Permissões por Role

### Super-Admin
✅ **TODAS AS PERMISSÕES** (56/56)
- Acesso Global (todas as unidades)
- Gate::before retorna true automaticamente

### Gestor-Global
✅ 50 permissões (exclui apenas gerenciamento de sistema crítico)
- Máquinas: view, create, update, manage-status
- Pacientes: view, create, update, export
- Checklists: view, create, update, advance, pause, resume
- Unidades: view
- Usuários: view, create, update
- Relatórios: view, export
- Analytics: view
- Interfaces: admin, desktop, mobile

### Gestor-Unidade
✅ 35 permissões
- Máquinas: view, create, update, manage-status
- Pacientes: view, create, update, export
- Checklists: view, create, update, advance, interrupt, pause, resume
- Unidades: view (apenas sua)
- Usuários: view (apenas sua unidade)
- Relatórios: view, export
- Analytics: view
- Interfaces: desktop, mobile

### Coordenador
✅ 28 permissões
- Máquinas: view, update, manage-status
- Pacientes: view, create, update
- Checklists: view, create, update, advance, pause, resume
- Unidades: view (apenas sua)
- Usuários: view (apenas sua unidade)
- Relatórios: view
- Interfaces: desktop, mobile

### Supervisor
✅ 22 permissões
- Máquinas: view, manage-status
- Pacientes: view, update
- Checklists: view, create, update, advance, pause, resume
- Unidades: view (apenas sua)
- Relatórios: view
- Interfaces: desktop, mobile

### Técnico
✅ 12 permissões
- Máquinas: view
- Pacientes: view
- Checklists: view, create, update, advance, pause, resume
- Interfaces: mobile

---

## Middleware UnitScopeMiddleware

### Funcionamento

```php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();

    if ($user->hasGlobalAccess()) {
        // Usuário global - pode filtrar por unidade específica ou ver todas
        $selectedUnitId = $request->input('unit_id') ?? $request->header('X-Unit-Id');
        
        if ($selectedUnitId) {
            $request->merge(['scoped_unit_id' => $selectedUnitId]);
        } else {
            $request->merge(['scoped_unit_id' => null, 'has_global_access' => true]);
        }
    } else {
        // Usuário de unidade - forçar filtro por sua unidade
        $request->merge(['scoped_unit_id' => $user->unit_id]);
    }

    return $next($request);
}
```

### Uso nos Controllers

```php
// MachineController
public function index(Request $request): JsonResponse
{
    $query = Machine::orderBy('name');
    
    // Aplicar filtro de unidade
    $scopedUnitId = $request->get('scoped_unit_id');
    if ($scopedUnitId !== null) {
        $query->where('unit_id', $scopedUnitId);
    }

    $machines = $query->get();
    // ...
}
```

---

## Métodos do Modelo User

### hasGlobalAccess()
```php
/**
 * Verifica se o usuário tem acesso global (pode ver todas as unidades)
 */
public function hasGlobalAccess(): bool
{
    return $this->hasRole(['super-admin', 'gestor-global']) || $this->unit_id === null;
}
```

### canAccessUnit($unitId)
```php
/**
 * Verifica se o usuário pode acessar uma unidade específica
 */
public function canAccessUnit(int $unitId): bool
{
    // Acesso global pode acessar qualquer unidade
    if ($this->hasGlobalAccess()) {
        return true;
    }

    // Usuários de unidade só acessam sua própria unidade
    return $this->unit_id === $unitId;
}
```

### accessibleUnits()
```php
/**
 * Retorna as unidades que o usuário pode acessar
 */
public function accessibleUnits()
{
    if ($this->hasGlobalAccess()) {
        return Unit::all();
    }

    return Unit::where('id', $this->unit_id)->get();
}
```

### assignRoleWithUnit($roleName, $unitId)
```php
/**
 * Atribui role com contexto de unidade
 */
public function assignRoleWithUnit(string $roleName, ?int $unitId = null): self
{
    $role = Role::findByName($roleName);
    
    if ($unitId) {
        $this->roles()->attach($role->id, ['unit_id' => $unitId]);
    } else {
        $this->assignRole($roleName);
    }

    return $this;
}
```

### hasPermissionInUnit($permission, $unitId)
```php
/**
 * Verifica se possui permissão em uma unidade específica
 */
public function hasPermissionInUnit(string $permission, int $unitId): bool
{
    // Acesso global tem todas as permissões
    if ($this->hasGlobalAccess()) {
        return $this->hasPermissionTo($permission);
    }

    // Verificar se tem a permissão globalmente
    if ($this->hasPermissionTo($permission)) {
        return true;
    }

    // Verificar se tem a permissão específica da unidade
    return $this->permissions()
        ->where('name', $permission)
        ->wherePivot('unit_id', $unitId)
        ->exists();
}
```

---

## Policies

### MachinePolicy

```php
public function view(User $user, Machine $machine): bool
{
    // Acesso global pode ver qualquer máquina
    if ($user->hasGlobalAccess()) {
        return $user->hasPermissionTo('machines.view');
    }

    // Usuário de unidade só pode ver máquinas da sua unidade
    return $user->hasPermissionTo('machines.view') 
        && $user->canAccessUnit($machine->unit_id);
}

public function update(User $user, Machine $machine): bool
{
    // Acesso global pode atualizar qualquer máquina
    if ($user->hasGlobalAccess()) {
        return $user->hasPermissionTo('machines.update');
    }

    // Usuário de unidade só pode atualizar máquinas da sua unidade
    return $user->hasPermissionTo('machines.update') 
        && $user->canAccessUnit($machine->unit_id);
}
```

### PatientPolicy

```php
public function view(User $user, Patient $patient): bool
{
    // Acesso global pode ver qualquer paciente
    if ($user->hasGlobalAccess()) {
        return $user->hasPermissionTo('patients.view');
    }

    // Usuário de unidade só pode ver pacientes da sua unidade
    return $user->hasPermissionTo('patients.view') 
        && $user->canAccessUnit($patient->unit_id);
}
```

---

## Frontend - Seletor de Unidade

### Para Usuários Globais

```typescript
// Se usuário tem acesso global, mostrar dropdown de seleção de unidade
const user = await authRepository.getCurrentUser();

if (user.hasGlobalAccess()) {
  // Buscar unidades disponíveis
  const units = await unitRepository.getAll();
  
  // Salvar seleção no localStorage
  const selectedUnitId = localStorage.getItem('selected_unit_id');
  
  // Enviar unit_id nas requisições
  const machines = await machineRepository.getAll({
    unit_id: selectedUnitId || null
  });
}
```

### Header HTTP

```typescript
// Adicionar unit_id como header em todas as requisições
axios.defaults.headers.common['X-Unit-Id'] = selectedUnitId;
```

---

## Exemplos de Uso

### Cenário 1: Super-Admin vê todas as máquinas

```http
GET /api/machines
Authorization: Bearer {token}
# Sem X-Unit-Id → Retorna máquinas de TODAS as unidades
```

**Response:**
```json
{
  "success": true,
  "machines": [
    {"id": 1, "name": "Máquina 01", "unit_id": 1},
    {"id": 2, "name": "Máquina 02", "unit_id": 1},
    {"id": 3, "name": "Máquina 03", "unit_id": 2},
    {"id": 4, "name": "Máquina 04", "unit_id": 2}
  ],
  "total": 4
}
```

### Cenário 2: Gestor-Global filtra por unidade específica

```http
GET /api/machines?unit_id=1
Authorization: Bearer {token}
# Com unit_id=1 → Retorna apenas máquinas da unidade 1
```

**Response:**
```json
{
  "success": true,
  "machines": [
    {"id": 1, "name": "Máquina 01", "unit_id": 1},
    {"id": 2, "name": "Máquina 02", "unit_id": 1}
  ],
  "total": 2
}
```

### Cenário 3: Técnico de Unidade 2

```http
GET /api/machines
Authorization: Bearer {token}
# Middleware força scoped_unit_id = 2 automaticamente
```

**Response:**
```json
{
  "success": true,
  "machines": [
    {"id": 3, "name": "Máquina 03", "unit_id": 2},
    {"id": 4, "name": "Máquina 04", "unit_id": 2}
  ],
  "total": 2
}
```

### Cenário 4: Tentativa de acesso não autorizado

```php
// Técnico da Unidade 2 tentando atualizar máquina da Unidade 1
$this->authorize('update', $machine); // unit_id = 1

// Policy verifica:
// 1. User tem permissão machines.update? ✅ Sim
// 2. User pode acessar unit_id 1? ❌ Não (user->unit_id = 2)
// Resultado: AuthorizationException
```

---

## Testes Recomendados

### 1. Teste de Acesso Global

```php
public function test_super_admin_can_view_all_units()
{
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');
    
    $unit1 = Unit::factory()->create();
    $unit2 = Unit::factory()->create();
    
    $machine1 = Machine::factory()->for($unit1)->create();
    $machine2 = Machine::factory()->for($unit2)->create();
    
    $response = $this->actingAs($superAdmin)->get('/api/machines');
    
    $response->assertStatus(200);
    $response->assertJsonCount(2, 'machines');
}
```

### 2. Teste de Filtro por Unidade

```php
public function test_unit_user_only_sees_own_unit()
{
    $unit1 = Unit::factory()->create();
    $unit2 = Unit::factory()->create();
    
    $tecnico = User::factory()->create(['unit_id' => $unit1->id]);
    $tecnico->assignRole('tecnico');
    
    $machine1 = Machine::factory()->for($unit1)->create();
    $machine2 = Machine::factory()->for($unit2)->create();
    
    $response = $this->actingAs($tecnico)->get('/api/machines');
    
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'machines');
    $response->assertJson([
        'machines' => [
            ['unit_id' => $unit1->id]
        ]
    ]);
}
```

### 3. Teste de Policy

```php
public function test_user_cannot_update_machine_from_other_unit()
{
    $unit1 = Unit::factory()->create();
    $unit2 = Unit::factory()->create();
    
    $coordenador = User::factory()->create(['unit_id' => $unit1->id]);
    $coordenador->assignRole('coordenador');
    
    $machine = Machine::factory()->for($unit2)->create();
    
    $this->expectException(AuthorizationException::class);
    
    Gate::authorize('update', $machine);
}
```

---

## Comandos Úteis

### Atribuir Role com Unidade

```php
$user->assignRoleWithUnit('coordenador', $unitId);
```

### Atribuir Permissão com Unidade

```php
$user->givePermissionWithUnit('machines.update', $unitId);
```

### Verificar Permissão em Unidade

```php
if ($user->hasPermissionInUnit('patients.view', $unitId)) {
    // Usuário tem permissão nesta unidade
}
```

### Listar Unidades Acessíveis

```php
$units = $user->accessibleUnits();
```

---

## Migrações de Dados

### Atualizar Usuários Existentes

```php
// Atribuir roles aos usuários existentes
$users = User::all();

foreach ($users as $user) {
    switch ($user->role) {
        case 'admin':
            $user->assignRole('super-admin');
            break;
        case 'gestor':
            if ($user->unit_id === null) {
                $user->assignRole('gestor-global');
            } else {
                $user->assignRole('gestor-unidade');
            }
            break;
        case 'coordenador':
            $user->assignRole('coordenador');
            break;
        case 'supervisor':
            $user->assignRole('supervisor');
            break;
        case 'tecnico':
            $user->assignRole('tecnico');
            break;
    }
}
```

---

## Segurança

### Gate::before para Super-Admin

```php
// Em AuthServiceProvider
Gate::before(function ($user, $ability) {
    return $user->hasRole('super-admin') ? true : null;
});
```

Isso garante que super-admin sempre passa em todas as verificações de Gate/Policy.

### Proteção contra Escalação de Privilégios

```php
// Usuários não podem atribuir roles superiores às suas
public function canAssignRole(User $user, string $roleName): bool
{
    $role = Role::findByName($roleName);
    
    // Super-admin pode atribuir qualquer role
    if ($user->hasRole('super-admin')) {
        return true;
    }
    
    // Gestor-global pode atribuir até gestor-unidade
    if ($user->hasRole('gestor-global')) {
        return in_array($roleName, [
            'gestor-unidade', 'coordenador', 'supervisor', 'tecnico'
        ]);
    }
    
    // Gestor-unidade pode atribuir até supervisor
    if ($user->hasRole('gestor-unidade')) {
        return in_array($roleName, ['coordenador', 'supervisor', 'tecnico']);
    }
    
    return false;
}
```

---

## Conclusão

O sistema de permissões multinível fornece:

✅ **Flexibilidade** - Acesso global ou por unidade conforme necessário
✅ **Segurança** - Validação em múltiplas camadas (Middleware, Policy, Permission)
✅ **Granularidade** - 56 permissões específicas para cada operação
✅ **Escalabilidade** - Fácil adicionar novas roles/permissions
✅ **Auditoria** - Logs automáticos via Spatie
✅ **Manutenibilidade** - Código limpo e organizado

**Hierarquia de Acesso:**
```
Super-Admin (Global)
    ↓
Gestor-Global (Global)
    ↓
Gestor-Unidade (Unidade)
    ↓
Coordenador (Unidade)
    ↓
Supervisor (Unidade)
    ↓
Técnico (Unidade)
```

**Próximas Melhorias:**
- Dashboard de auditoria de permissões
- Logs detalhados de acessos por unidade
- Relatórios de atividades por role
- Interface visual para gerenciar permissions
