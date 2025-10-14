#!/bin/bash

# Script de teste para validar o sistema de múltiplas unidades

echo "==================================="
echo "Teste do Sistema de Múltiplas Unidades"
echo "==================================="
echo ""

# Teste 1: Verificar usuário com múltiplas unidades
echo "1. Testando usuário com múltiplas unidades (ID: 3)"
echo "---------------------------------------------------"
php artisan tinker --execute="
\$user = App\Models\User::with('units', 'currentUnit')->find(3);
echo 'Usuário: ' . \$user->name . PHP_EOL;
echo 'Acesso Global: ' . (\$user->hasGlobalAccess() ? 'SIM' : 'NÃO') . PHP_EOL;
echo 'Total de Unidades: ' . \$user->units->count() . PHP_EOL;
echo 'Unidades:' . PHP_EOL;
foreach (\$user->units as \$u) {
    echo '  [' . \$u->id . '] ' . \$u->name . PHP_EOL;
}
echo 'Unidade Atual: ' . (\$user->getActiveUnit()->name ?? 'NENHUMA') . ' (ID: ' . (\$user->current_unit_id ?? 'NULL') . ')' . PHP_EOL;
"

echo ""
echo "---------------------------------------------------"
echo ""

# Teste 2: Simular endpoint /api/user-units
echo "2. Simulando endpoint GET /api/user-units"
echo "---------------------------------------------------"
php artisan tinker --execute="
\$user = App\Models\User::find(3);
if (\$user->hasGlobalAccess()) {
    \$units = App\Models\Unit::where('active', true)->get();
    echo 'Retornando TODAS as unidades (acesso global)' . PHP_EOL;
} else {
    \$units = \$user->units()->where('active', true)->get();
    echo 'Retornando apenas unidades do usuário' . PHP_EOL;
}
echo 'Total retornado: ' . \$units->count() . PHP_EOL;
echo 'current_unit_id: ' . (\$user->current_unit_id ?? \$user->unit_id ?? 'NULL') . PHP_EOL;
echo 'Unidades na resposta:' . PHP_EOL;
foreach (\$units as \$u) {
    echo '  [' . \$u->id . '] ' . \$u->name . PHP_EOL;
}
"

echo ""
echo "---------------------------------------------------"
echo ""

# Teste 3: Verificar usuário com acesso global
echo "3. Testando usuário com acesso global (ID: 1 - Admin)"
echo "---------------------------------------------------"
php artisan tinker --execute="
\$user = App\Models\User::with('units', 'currentUnit')->find(1);
if (\$user) {
    echo 'Usuário: ' . \$user->name . PHP_EOL;
    echo 'Acesso Global: ' . (\$user->hasGlobalAccess() ? 'SIM' : 'NÃO') . PHP_EOL;
    echo 'Total de Unidades Associadas: ' . \$user->units->count() . PHP_EOL;
    
    if (\$user->hasGlobalAccess()) {
        \$allUnits = App\Models\Unit::where('active', true)->count();
        echo 'Total de Unidades no Sistema: ' . \$allUnits . PHP_EOL;
        echo 'Deve retornar: TODAS as unidades (' . \$allUnits . ')' . PHP_EOL;
    }
} else {
    echo 'Usuário não encontrado' . PHP_EOL;
}
"

echo ""
echo "---------------------------------------------------"
echo ""

# Teste 4: Verificar estrutura da tabela user_unit
echo "4. Verificando tabela user_unit (pivot)"
echo "---------------------------------------------------"
php artisan tinker --execute="
\$pivots = DB::table('user_unit')->get();
echo 'Total de associações user-unit: ' . \$pivots->count() . PHP_EOL;
echo 'Associações:' . PHP_EOL;
foreach (\$pivots as \$p) {
    \$user = App\Models\User::find(\$p->user_id);
    \$unit = App\Models\Unit::find(\$p->unit_id);
    \$isPrimary = \$p->is_primary ? '[PRIMARY]' : '';
    echo '  User ' . \$p->user_id . ' (' . \$user->name . ') → Unit ' . \$p->unit_id . ' (' . \$unit->name . ') ' . \$isPrimary . PHP_EOL;
}
"

echo ""
echo "==================================="
echo "Testes concluídos!"
echo "==================================="
