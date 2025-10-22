# Correção: Carregamento de Unidades no Dashboard Mobile

## Problema Identificado

O Dashboard mobile não conseguia carregar a unidade do usuário, mostrando o seguinte log:

```
Unidades carregadas (mobile): {total: 0, current: undefined, currentId: 6}
```

Isso indicava que:
- A API estava retornando `total: 0` unidades
- O `currentId` estava definido (6) mas sem dados da unidade
- A unidade atual estava `undefined`

## Causa Raiz

O `UserSeeder` estava populando apenas o campo `unit_id` na tabela `users`, mas **não estava populando a tabela pivot `user_unit`** necessária para o sistema de múltiplas unidades.

### Relacionamento no Modelo User

```php
public function units()
{
    return $this->belongsToMany(Unit::class, 'user_unit')
        ->withPivot('is_primary')
        ->withTimestamps();
}
```

Este relacionamento `belongsToMany` depende da tabela pivot `user_unit` para funcionar.

### Controller API

O `UserUnitController::index()` estava tentando buscar as unidades via relacionamento:

```php
public function index(): JsonResponse
{
    $user = auth()->user();

    if ($user->hasGlobalAccess()) {
        $units = \App\Models\Unit::where('active', true)->get();
    } else {
        $units = $user->units()->where('active', true)->get(); // ← Retornava vazio
    }

    return response()->json([
        'success' => true,
        'units' => $units, // ← Array vazio
        'current_unit_id' => $user->current_unit_id ?? $user->unit_id,
    ]);
}
```

## Solução Implementada

### 1. Atualização do UserSeeder

Modificado para popular tanto `unit_id` quanto a tabela pivot `user_unit`:

```php
// Criar usuário
$user = \App\Models\User::updateOrCreate(
    ['email' => strtolower($userData['email'])],
    [
        'name' => $userData['name'],
        'password' => bcrypt('senha123'),
        'role' => $userData['role'],
        'default_view' => $userData['role'] === 'tecnico' ? 'mobile' : 'desktop',
        'unit_id' => $unit->id,
        'current_unit_id' => $unit->id, // ← NOVO: Define unidade atual
        'email_verified_at' => now(),
    ]
);

// ← NOVO: Associar usuário à unidade na tabela pivot
if (!$user->units()->where('unit_id', $unit->id)->exists()) {
    $user->units()->attach($unit->id, [
        'is_primary' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}
```

### 2. Campos Populados

Agora cada usuário tem:

| Campo | Descrição | Exemplo |
|-------|-----------|---------|
| `unit_id` | Unidade principal (compatibilidade) | `16` |
| `current_unit_id` | Unidade atualmente ativa | `16` |
| Tabela `user_unit` | Relacionamento many-to-many | `[{user_id: 1, unit_id: 16, is_primary: true}]` |

## Resultados

### Antes
```
Unidades carregadas (mobile): {total: 0, current: undefined, currentId: 6}
```

### Depois
```
Unidades carregadas (mobile): {
  total: 1,
  current: "Hospital Regional de Barra do Corda",
  currentId: 16
}
```

### Verificação no Banco

```bash
# Total de relações criadas
SELECT COUNT(*) FROM user_unit;
# Resultado: 25 registros

# Verificar usuário específico
SELECT u.name, u.unit_id, u.current_unit_id, un.name as unit_name
FROM users u
LEFT JOIN user_unit uu ON u.id = uu.user_id
LEFT JOIN units un ON uu.unit_id = un.id
WHERE u.email = 'haylineguimaraes87@gmail.com';

# Resultado:
# Hayline Sousa Guimarães | 16 | 16 | Hospital Regional de Barra do Corda
```

## Arquivos Modificados

1. **`database/seeders/UserSeeder.php`**
   - Adicionado `current_unit_id` ao criar usuário
   - Adicionada lógica para popular tabela pivot `user_unit`
   - Marca a unidade como `is_primary: true`

## Como Testar

### 1. Re-executar o seeder
```bash
php artisan db:seed --class=UserSeeder
```

### 2. Testar no mobile
1. Acesse `/mobile`
2. Faça login com qualquer usuário (ex: `haylineguimaraes87@gmail.com` / `senha123`)
3. O dashboard deve exibir corretamente a unidade do usuário

### 3. Verificar no console do navegador
O log deve mostrar:
```
Unidades carregadas (mobile): {total: 1, current: "Nome da Unidade", currentId: X}
```

## Benefícios

1. **Sistema de Múltiplas Unidades**: Preparado para usuários com acesso a múltiplas unidades
2. **Compatibilidade**: Mantém `unit_id` para código legado
3. **Flexibilidade**: Usuário pode alternar entre unidades (se tiver acesso a mais de uma)
4. **Consistência**: Dados corretos em todas as interfaces (mobile, desktop, admin)

## Próximos Passos (Opcional)

Para adicionar suporte a múltiplas unidades para usuários específicos:

```php
// Exemplo: Adicionar mais uma unidade para um gestor
$user = User::find(1);
$outraUnidade = Unit::find(2);

$user->units()->attach($outraUnidade->id, [
    'is_primary' => false,
    'created_at' => now(),
    'updated_at' => now()
]);
```

## Data da Correção

**21 de Outubro de 2025**

## Comandos Úteis

```bash
# Limpar e re-popular todas as seeds
php artisan migrate:fresh --seed

# Apenas re-executar UserSeeder
php artisan db:seed --class=UserSeeder

# Verificar unidades de um usuário
php artisan tinker
>>> $user = User::where('email', 'email@exemplo.com')->first();
>>> $user->units()->get();
```
