#!/bin/bash

# ============================================
# Script para Gerar Chaves de Produção
# Sistema Hemodiálise - Dokploy
# ============================================

echo "============================================"
echo "Gerador de Chaves para Produção"
echo "Sistema Hemodiálise - Qualidade HD"
echo "============================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if Laravel is installed
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erro: arquivo 'artisan' não encontrado.${NC}"
    echo "Este script deve ser executado na raiz do projeto Laravel."
    exit 1
fi

echo -e "${BLUE}📝 Gerando chaves necessárias para produção...${NC}"
echo ""

# Generate APP_KEY
echo -e "${YELLOW}1. Gerando APP_KEY...${NC}"
APP_KEY=$(php artisan key:generate --show)

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ APP_KEY gerado com sucesso!${NC}"
    echo -e "   ${APP_KEY}"
else
    echo -e "${RED}✗ Erro ao gerar APP_KEY${NC}"
    exit 1
fi

echo ""

# Generate JWT_SECRET
echo -e "${YELLOW}2. Gerando JWT_SECRET...${NC}"

# Try using artisan command first
JWT_SECRET=$(php artisan jwt:secret --show 2>/dev/null)

if [ $? -eq 0 ] && [ ! -z "$JWT_SECRET" ]; then
    echo -e "${GREEN}✓ JWT_SECRET gerado com sucesso!${NC}"
    echo -e "   ${JWT_SECRET}"
else
    # Fallback: generate using openssl
    echo -e "${BLUE}ℹ Usando openssl para gerar JWT_SECRET...${NC}"
    JWT_SECRET=$(openssl rand -base64 64 | tr -d '\n')
    
    if [ ! -z "$JWT_SECRET" ]; then
        echo -e "${GREEN}✓ JWT_SECRET gerado com sucesso!${NC}"
        echo -e "   ${JWT_SECRET}"
    else
        echo -e "${RED}✗ Erro ao gerar JWT_SECRET${NC}"
        exit 1
    fi
fi

echo ""
echo "============================================"
echo -e "${GREEN}✅ Todas as chaves foram geradas!${NC}"
echo "============================================"
echo ""
echo -e "${YELLOW}📋 COPIE E GUARDE ESTAS CHAVES EM LOCAL SEGURO:${NC}"
echo ""
echo "┌─────────────────────────────────────────────────────────"
echo "│"
echo "│ APP_KEY=${APP_KEY}"
echo "│"
echo "│ JWT_SECRET=${JWT_SECRET}"
echo "│"
echo "└─────────────────────────────────────────────────────────"
echo ""
echo -e "${BLUE}📖 Próximos passos:${NC}"
echo ""
echo "1. No painel do Dokploy, vá para sua aplicação"
echo "2. Clique na aba 'Environment' ou 'Environment Variables'"
echo "3. Adicione as seguintes variáveis:"
echo ""
echo -e "   ${YELLOW}APP_KEY${NC}=${APP_KEY}"
echo -e "   ${YELLOW}JWT_SECRET${NC}=${JWT_SECRET}"
echo ""
echo "4. Salve as alterações"
echo "5. Faça o deploy da aplicação"
echo ""
echo -e "${RED}⚠️  IMPORTANTE:${NC}"
echo "   - NÃO commite estas chaves no Git"
echo "   - NÃO compartilhe em canais públicos"
echo "   - Guarde em um gerenciador de senhas"
echo ""
echo -e "${GREEN}✨ Configuração de produção pronta!${NC}"
echo ""
