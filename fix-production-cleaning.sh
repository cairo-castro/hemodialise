#!/bin/bash

# Script para corrigir problema de cleaning checklists vazio em produção
# Autor: Claude Code
# Data: 2025-11-07

set -e

echo "======================================"
echo "  Fix Cleaning Checklists - Produção"
echo "======================================"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Servidor de produção
SERVER="212.85.1.175"
PASSWORD="ClinQua-Hosp@2025"

echo -e "${YELLOW}1. Conectando ao servidor...${NC}"
CONTAINER_ID=$(sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no root@$SERVER \
  "docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1")

if [ -z "$CONTAINER_ID" ]; then
    echo -e "${RED}ERRO: Container não encontrado!${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Container encontrado: $CONTAINER_ID${NC}"
echo ""

echo -e "${YELLOW}2. Verificando dados no banco...${NC}"
COUNT=$(sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no root@$SERVER \
  "docker exec $CONTAINER_ID php artisan tinker --execute='echo \App\Models\CleaningControl::count();'")

echo -e "${GREEN}✓ Total de registros: $COUNT${NC}"

if [ "$COUNT" -eq "0" ]; then
    echo -e "${RED}AVISO: Nenhum registro encontrado! Execute o seeder:${NC}"
    echo "  php artisan db:seed --class=CleaningControlSeeder"
    echo ""
fi

echo ""
echo -e "${YELLOW}3. Verificando usuários sem current_unit_id...${NC}"

sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no root@$SERVER << 'ENDSSH'
CONTAINER_ID=$(docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1)

docker exec $CONTAINER_ID php artisan tinker << 'ENDPHP'
$admins = \App\Models\User::whereIn('role', ['admin', 'manager', 'gestor', 'coordenador'])
    ->whereNull('current_unit_id')
    ->get();

if ($admins->count() > 0) {
    echo "\n⚠️  Usuários sem current_unit_id encontrados:\n";
    foreach ($admins as $admin) {
        echo "  - {$admin->email} (role: {$admin->role})\n";
    }

    echo "\n🔧 Corrigindo automaticamente...\n";
    $firstUnit = \App\Models\Unit::first();
    if ($firstUnit) {
        foreach ($admins as $admin) {
            $admin->current_unit_id = $firstUnit->id;
            $admin->save();
            echo "  ✓ {$admin->email} -> unit_id: {$firstUnit->id}\n";
        }
        echo "\n✅ Correção concluída!\n";
    } else {
        echo "\n❌ ERRO: Nenhuma unidade encontrada no banco!\n";
    }
} else {
    echo "\n✓ Todos os usuários têm current_unit_id definido\n";
}
ENDPHP
ENDSSH

echo ""
echo -e "${YELLOW}4. Limpando cache...${NC}"

sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no root@$SERVER \
  "docker exec $CONTAINER_ID php artisan optimize:clear" > /dev/null 2>&1

echo -e "${GREEN}✓ Cache limpo${NC}"

echo ""
echo -e "${YELLOW}5. Recriando cache otimizado...${NC}"

sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no root@$SERVER << 'ENDSSH'
CONTAINER_ID=$(docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1)
docker exec $CONTAINER_ID php artisan config:cache > /dev/null 2>&1
docker exec $CONTAINER_ID php artisan route:cache > /dev/null 2>&1
docker exec $CONTAINER_ID php artisan view:cache > /dev/null 2>&1
ENDSSH

echo -e "${GREEN}✓ Cache recriado${NC}"

echo ""
echo -e "${YELLOW}6. Testando API...${NC}"

API_TEST=$(sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no root@$SERVER \
  "docker exec $CONTAINER_ID curl -s http://localhost/api/cleaning-controls -H 'Accept: application/json' | grep -o '\"data\"' | wc -l")

if [ "$API_TEST" -gt "0" ]; then
    echo -e "${GREEN}✓ API respondendo corretamente${NC}"
else
    echo -e "${RED}⚠ API não retornou dados (pode ser problema de autenticação)${NC}"
fi

echo ""
echo "======================================"
echo -e "${GREEN}  Correção concluída!${NC}"
echo "======================================"
echo ""
echo "Próximos passos:"
echo "1. Fazer login no sistema: https://qualidadehd.direcaoclinica.com.br"
echo "2. Acessar: /desktop/cleaning-checklists"
echo "3. Abrir DevTools (F12) > Network tab"
echo "4. Verificar request /api/cleaning-controls"
echo "   - Status deve ser 200"
echo "   - Response deve conter {data: [...]}"
echo ""
echo "Se ainda estiver vazio, verificar:"
echo "- Usuário está autenticado?"
echo "- Cookie de sessão está sendo enviado?"
echo "- Ver console do browser para erros JavaScript"
echo ""
