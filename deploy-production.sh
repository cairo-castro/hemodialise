#!/bin/bash

# ============================================
# Deploy Script - Cleaning Controls Updates
# ============================================
#
# Este script automatiza o deploy das melhorias
# de cleaning controls para produção
#
# Uso: ./deploy-production.sh
# ============================================

set -e  # Exit on error

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurações
SSH_HOST="212.85.1.175"
SSH_USER="root"
SSH_PASS="ClinQua-Hosp@2025"
APP_DIR="/var/www/html"

echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   Deploy - Cleaning Controls & Machine Updates   ${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo ""

# Função para executar comandos SSH
run_remote() {
    local cmd="$1"
    local description="$2"

    echo -e "${YELLOW}▶ ${description}...${NC}"
    sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no "$SSH_USER@$SSH_HOST" "$cmd"

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ ${description} - Concluído${NC}"
    else
        echo -e "${RED}✗ ${description} - Falhou${NC}"
        exit 1
    fi
    echo ""
}

# Função para executar comandos Docker
run_docker() {
    local cmd="$1"
    local description="$2"

    local full_cmd="CONTAINER=\$(docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1) && docker exec \$CONTAINER $cmd"
    run_remote "$full_cmd" "$description"
}

echo -e "${YELLOW}🔍 Verificando conexão com servidor...${NC}"
sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no -o ConnectTimeout=5 "$SSH_USER@$SSH_HOST" "echo 'Conexão OK'" > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Conectado ao servidor ${SSH_HOST}${NC}"
else
    echo -e "${RED}✗ Erro ao conectar ao servidor${NC}"
    exit 1
fi
echo ""

# Confirmação do usuário
echo -e "${YELLOW}⚠️  ATENÇÃO: Você está prestes a fazer deploy para PRODUÇÃO!${NC}"
echo -e "${YELLOW}   Servidor: ${SSH_HOST}${NC}"
echo -e "${YELLOW}   Container: qualidade-qualidadehd${NC}"
echo ""
read -p "Deseja continuar? (sim/não): " confirm

if [ "$confirm" != "sim" ]; then
    echo -e "${RED}Deploy cancelado pelo usuário.${NC}"
    exit 0
fi
echo ""

# ============================================
# ETAPA 1: Backup do Banco de Dados
# ============================================
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}  ETAPA 1: Backup do Banco de Dados  ${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

BACKUP_FILE="backup_cleaning_controls_$(date +%Y%m%d_%H%M%S).sql"
run_docker "mysqldump -u root hemodialise > /tmp/$BACKUP_FILE" "Criando backup do banco de dados"

echo -e "${GREEN}✓ Backup salvo como: /tmp/$BACKUP_FILE${NC}"
echo ""

# ============================================
# ETAPA 2: Pull do Código
# ============================================
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}  ETAPA 2: Atualização do Código  ${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Nota: Em produção com Dokploy, o código é atualizado automaticamente via Git webhook
echo -e "${YELLOW}ℹ Dokploy atualiza código automaticamente via Git webhook${NC}"
echo -e "${YELLOW}  Certifique-se de que o commit foi feito para a branch main${NC}"
echo ""

read -p "Código já foi commitado e está na branch main? (sim/não): " code_ready

if [ "$code_ready" != "sim" ]; then
    echo -e "${RED}Por favor, commit e push o código antes de continuar.${NC}"
    exit 1
fi
echo ""

# ============================================
# ETAPA 3: Verificar Container
# ============================================
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}  ETAPA 3: Verificação do Container  ${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

run_remote "docker ps --filter 'name=qualidade-qualidadehd' --format 'table {{.Names}}\t{{.Status}}'" "Listando container ativo"

# ============================================
# ETAPA 4: Executar Migration
# ============================================
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}  ETAPA 4: Executando Migration  ${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

echo -e "${YELLOW}⚠️  Esta migration irá:${NC}"
echo -e "   1. Converter colunas TINYINT → VARCHAR (temporário)"
echo -e "   2. Converter dados: 0/1 → NC/C"
echo -e "   3. Alterar tipo VARCHAR → ENUM('C', 'NC', 'NA')"
echo ""

read -p "Executar migration agora? (sim/não): " run_migration

if [ "$run_migration" = "sim" ]; then
    run_docker "php artisan migrate --force" "Executando migration"

    # Verificar se migration foi aplicada corretamente
    echo -e "${YELLOW}▶ Verificando estrutura da tabela...${NC}"
    run_docker "php artisan tinker --execute=\"print_r(DB::select('SHOW COLUMNS FROM cleaning_controls WHERE Field IN (\\\"hd_machine_cleaning\\\", \\\"osmosis_cleaning\\\", \\\"serum_support_cleaning\\\")'))\""  "Verificando colunas alteradas"
else
    echo -e "${YELLOW}⚠️  Migration não foi executada. Execute manualmente:${NC}"
    echo -e "   docker exec [CONTAINER] php artisan migrate"
fi
echo ""

# ============================================
# ETAPA 5: Limpar Caches
# ============================================
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}  ETAPA 5: Limpeza de Caches  ${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

run_docker "php artisan optimize:clear" "Limpando todos os caches"
run_docker "php artisan config:cache" "Cacheando configurações"
run_docker "php artisan route:cache" "Cacheando rotas"
run_docker "php artisan view:cache" "Cacheando views"

# ============================================
# ETAPA 6: Verificação Final
# ============================================
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}  ETAPA 6: Verificação Final  ${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

echo -e "${GREEN}✓ Deploy concluído com sucesso!${NC}"
echo ""

echo -e "${YELLOW}📋 Checklist de Testes Pós-Deploy:${NC}"
echo -e "   [ ] Acessar https://qualidadehd.direcaoclinica.com.br"
echo -e "   [ ] Criar novo checklist de limpeza (desktop)"
echo -e "   [ ] Criar novo checklist de limpeza (mobile)"
echo -e "   [ ] Verificar filtro de máquinas disponíveis"
echo -e "   [ ] Tentar alterar status de máquina com checklist ativo"
echo -e "   [ ] Verificar logs por erros"
echo ""

echo -e "${YELLOW}📊 Monitoramento:${NC}"
echo -e "   Logs em tempo real:"
echo -e "   ${BLUE}sshpass -p '$SSH_PASS' ssh $SSH_USER@$SSH_HOST \\${NC}"
echo -e "   ${BLUE}  'docker logs \$(docker ps --filter \"name=qualidade\" --format \"{{.Names}}\" | head -1) -f'${NC}"
echo ""

echo -e "${YELLOW}🔄 Rollback (se necessário):${NC}"
echo -e "   ${BLUE}sshpass -p '$SSH_PASS' ssh $SSH_USER@$SSH_HOST \\${NC}"
echo -e "   ${BLUE}  'docker exec \$(docker ps --filter \"name=qualidade\" --format \"{{.Names}}\" | head -1) \\${NC}"
echo -e "   ${BLUE}  php artisan migrate:rollback --step=1'${NC}"
echo ""

echo -e "${GREEN}═══════════════════════════════════════════════════${NC}"
echo -e "${GREEN}         Deploy Finalizado com Sucesso! 🚀        ${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════${NC}"
