# 🏥 Sistema de Múltiplas Unidades por Usuário

## 📋 Visão Geral

O sistema foi atualizado para permitir que usuários possam estar associados a múltiplas unidades e alternar entre elas durante sua sessão de trabalho.

## 🗄️ Estrutura de Banco de Dados

### Tabela `user_unit` (Pivot)
```sql
CREATE TABLE user_unit (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    unit_id BIGINT NOT NULL,
    is_primary BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(user_id, unit_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
);
```

### Campo `current_unit_id` na tabela `users`
```sql
ALTER TABLE users ADD COLUMN current_unit_id BIGINT NULL;
ALTER TABLE users ADD FOREIGN KEY (current_unit_id) REFERENCES units(id) ON DELETE SET NULL;
```

**Função:**
- `user_unit`: Relacionamento muitos-para-muitos entre usuários e unidades
- `current_unit_id`: Armazena a unidade atualmente selecionada pelo usuário
- `is_primary`: Marca a unidade principal do usuário

## 🔧 Modelo User

### Novos Relacionamentos

```php
/**
 * Unidade atualmente selecionada pelo usuário
 */
public function currentUnit(): BelongsTo
{
    return $this->belongsTo(Unit::class, 'current_unit_id');
}

/**
 * Todas as unidades às quais o usuário tem acesso
 */
public function units()
{
    return $this->belongsToMany(Unit::class, 'user_unit')
        ->withPivot('is_primary')
        ->withTimestamps();
}
```

### Novos Métodos

```php
/**
 * Retorna a unidade ativa para filtragem de dados
 * Prioridade: current_unit_id > unit_id (principal) > primeira unidade associada
 */
public function getActiveUnit()
{
    if ($this->current_unit_id) {
        return $this->currentUnit;
    }

    if ($this->unit_id) {
        return $this->unit;
    }

    return $this->units()->first();
}

/**
 * Define a unidade atualmente ativa
 */
public function switchToUnit(int $unitId): bool
{
    // Verifica se o usuário tem acesso a esta unidade
    if (!$this->canAccessUnit($unitId)) {
        return false;
    }

    $this->current_unit_id = $unitId;
    $this->save();

    return true;
}
```

### Métodos Atualizados

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

    // Verifica se o usuário está associado a esta unidade
    return $this->units()->where('unit_id', $unitId)->exists();
}

/**
 * Retorna as unidades que o usuário pode acessar
 */
public function accessibleUnits()
{
    if ($this->hasGlobalAccess()) {
        return Unit::all();
    }

    // Retorna todas as unidades associadas ao usuário
    return $this->units;
}
```

## 🛣️ Rotas API

### GET `/api/user-units`
**Descrição:** Lista todas as unidades acessíveis pelo usuário autenticado

**Response:**
```json
{
    "success": true,
    "units": [
        {
            "id": 1,
            "name": "Centro de Hemodiálise São Luís",
            "code": "CH001",
            "active": true
        },
        {
            "id": 2,
            "name": "Hospital Regional de Barreirinhas",
            "code": "HRBA001",
            "active": true
        }
    ],
    "current_unit_id": 1
}
```

### POST `/api/user-units/switch`
**Descrição:** Alterna para uma unidade específica

**Request:**
```json
{
    "unit_id": 2
}
```

**Response:**
```json
{
    "success": true,
    "message": "Unidade alterada com sucesso.",
    "current_unit": {
        "id": 2,
        "name": "Hospital Regional de Barreirinhas",
        "code": "HRBA001",
        "active": true
    }
}
```

**Erro (403):**
```json
{
    "success": false,
    "message": "Você não tem permissão para acessar esta unidade."
}
```

### GET `/api/user-units/current`
**Descrição:** Retorna a unidade atualmente ativa

**Response:**
```json
{
    "success": true,
    "unit": {
        "id": 1,
        "name": "Centro de Hemodiálise São Luís",
        "code": "CH001",
        "active": true
    }
}
```

## 🎨 Interface do Usuário

### Dashboard Desktop

**Componente:** `DesktopSidebar.vue`

**Exibição:**
```vue
<!-- User Profile Card -->
<div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl mb-6">
  <div class="flex items-center mb-3">
    <div class="w-12 h-12 rounded-full bg-gradient flex items-center justify-center text-white font-bold text-lg mr-3">
      {{ user.getInitials() }}
    </div>
    <div class="flex-1">
      <h3 class="font-semibold text-gray-800">{{ user.name }}</h3>
      <p class="text-xs text-gray-500 mt-1">{{ currentUnit?.name }}</p>
    </div>
  </div>

  <!-- Unit Selector -->
  <div v-if="availableUnits.length > 1" class="mt-3">
    <select 
      v-model="selectedUnitId" 
      @change="handleUnitChange"
      class="w-full px-3 py-2 text-sm border border-blue-200 rounded-lg"
    >
      <option v-for="unit in availableUnits" :key="unit.id" :value="unit.id">
        {{ unit.name }}
      </option>
    </select>
  </div>
</div>
```

**Mudanças:**
- ✅ **Removido:** Exibição do nível/role do usuário
- ✅ **Mantido:** Nome do usuário e unidade atual
- ✅ **Adicionado:** Seletor dropdown para alternar entre unidades (apenas se tiver múltiplas)

### Dashboard Mobile

**Componente:** `DashboardPage.vue`

**Exibição:**
```vue
<!-- Welcome Section -->
<div class="welcome-section" v-if="user">
  <div class="welcome-header">
    <div class="welcome-text">
      <h1>Olá, {{ user.name.split(' ')[0] }}! 👋</h1>
      <p class="unit-name">{{ currentUnit?.name }}</p>
    </div>
  </div>

  <!-- Unit Selector -->
  <div v-if="availableUnits.length > 1" class="unit-selector">
    <ion-select 
      v-model="selectedUnitId" 
      @ionChange="handleUnitChange"
      interface="action-sheet"
      placeholder="Selecionar Unidade"
    >
      <ion-select-option v-for="unit in availableUnits" :key="unit.id" :value="unit.id">
        {{ unit.name }}
      </ion-select-option>
    </ion-select>
  </div>
</div>
```

**Mudanças:**
- ✅ **Removido:** Badge com nível/role do usuário
- ✅ **Mantido:** Nome do usuário e unidade atual
- ✅ **Adicionado:** Seletor Ionic para alternar entre unidades

## 🔄 Fluxo de Troca de Unidade

### 1. Usuário seleciona nova unidade no dropdown

### 2. Frontend chama `POST /api/user-units/switch`
```javascript
const handleUnitChange = async () => {
  try {
    const response = await axios.post('/api/user-units/switch', {
      unit_id: selectedUnitId.value
    });

    if (response.data.success) {
      currentUnit.value = response.data.current_unit;
      // Recarrega a página para aplicar novo filtro
      window.location.reload();
    }
  } catch (error) {
    console.error('Erro ao trocar unidade:', error);
    // Reverte seleção
    selectedUnitId.value = currentUnit.value?.id;
  }
};
```

### 3. Backend atualiza `current_unit_id`
```php
public function switch(Request $request): JsonResponse
{
    $user = auth()->user();
    $unitId = $request->input('unit_id');

    if (!$user->canAccessUnit($unitId)) {
        return response()->json([
            'success' => false,
            'message' => 'Você não tem permissão para acessar esta unidade.',
        ], 403);
    }

    $user->switchToUnit($unitId);

    return response()->json([
        'success' => true,
        'message' => 'Unidade alterada com sucesso.',
        'current_unit' => $user->currentUnit,
    ]);
}
```

### 4. UnitScopeMiddleware aplica novo filtro
```php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();

    if ($user->hasGlobalAccess()) {
        // Usuário global pode ver todas unidades ou filtrar por uma específica
        $selectedUnitId = $request->input('unit_id') ?? $request->header('X-Unit-Id');
        $request->merge(['scoped_unit_id' => $selectedUnitId]);
    } else {
        // Usuário de unidade - usar unidade atualmente selecionada
        $activeUnit = $user->getActiveUnit();
        $scopedUnitId = $activeUnit ? $activeUnit->id : $user->unit_id;
        
        $request->merge(['scoped_unit_id' => $scopedUnitId]);
    }

    return $next($request);
}
```

### 5. Controllers filtram dados pela unidade ativa
```php
public function index(Request $request)
{
    $scopedUnitId = $request->get('scoped_unit_id');
    
    $query = Machine::query();
    
    if ($scopedUnitId !== null) {
        $query->where('unit_id', $scopedUnitId);
    }
    
    return response()->json($query->get());
}
```

## 📝 Exemplos de Uso

### Adicionar múltiplas unidades a um usuário

```php
$user = User::find(1);
$units = Unit::whereIn('id', [1, 2, 3])->get();

// Sincroniza unidades (remove antigas e adiciona novas)
$user->units()->sync([
    1 => ['is_primary' => true],
    2 => ['is_primary' => false],
    3 => ['is_primary' => false],
]);

// Define unidade atual
$user->current_unit_id = 1;
$user->save();
```

### Verificar se usuário pode acessar unidade

```php
$user = User::find(1);

if ($user->canAccessUnit(2)) {
    echo "Usuário pode acessar a unidade 2";
}
```

### Alternar unidade do usuário

```php
$user = User::find(1);

if ($user->switchToUnit(2)) {
    echo "Unidade alterada com sucesso";
} else {
    echo "Usuário não tem acesso a esta unidade";
}
```

### Listar unidades do usuário

```php
$user = User::find(1);
$units = $user->accessibleUnits();

foreach ($units as $unit) {
    echo $unit->name . PHP_EOL;
}
```

## 🔐 Segurança

### Validações Implementadas

1. **Verificação de Acesso:**
   - Usuário só pode alternar para unidades às quais tem acesso
   - Método `canAccessUnit()` verifica relacionamento na tabela `user_unit`

2. **Usuários Globais:**
   - Usuários com `hasGlobalAccess()` podem acessar todas as unidades
   - Super-admin e gestor-global têm acesso irrestrito

3. **Filtro Automático:**
   - `UnitScopeMiddleware` aplica filtro baseado em `current_unit_id`
   - Dados sempre filtrados pela unidade ativa do usuário

4. **Controllers Protegidos:**
   - Todos os endpoints respeitam `scoped_unit_id`
   - Policies verificam permissões e unidade antes de autorizar ações

## 🎯 Benefícios

1. **Flexibilidade:** Usuários podem trabalhar em múltiplas unidades sem precisar fazer logout/login
2. **Segurança:** Filtros automáticos garantem isolamento de dados entre unidades
3. **UX Melhorada:** Troca de unidade é simples e rápida via dropdown
4. **Auditoria:** Sistema registra qual unidade estava ativa durante cada ação
5. **Escalabilidade:** Suporta crescimento do sistema com novas unidades

## 🚀 Próximos Passos

- [ ] Adicionar histórico de trocas de unidades para auditoria
- [ ] Implementar permissões específicas por unidade (já preparado com Spatie)
- [ ] Criar relatórios comparativos entre unidades
- [ ] Adicionar configuração de unidade padrão no perfil do usuário
- [ ] Implementar notificações quando trocar de unidade

## 📊 Estatísticas

- **Migrations:** 2 novas (user_unit, current_unit_id)
- **Rotas API:** 3 novos endpoints
- **Métodos no User:** 5 novos métodos
- **Componentes Atualizados:** 2 (Desktop, Mobile)
- **Middleware Atualizado:** UnitScopeMiddleware
