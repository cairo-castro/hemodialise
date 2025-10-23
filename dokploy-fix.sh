#!/bin/bash
# Script para corrigir a configuração do Dokploy via SSH

SERVER="212.85.1.175"
PASSWORD="ClinQua-Hosp@2025"

echo "=========================================="
echo "Dokploy - Correção do qualidadeHD"
echo "=========================================="
echo ""

# Verificar se sshpass está instalado
if ! command -v sshpass &> /dev/null; then
    echo "Instalando sshpass..."
    sudo apt-get update && sudo apt-get install -y sshpass
fi

echo "Conectando ao servidor $SERVER..."
echo ""

# Mostrar status atual do container
echo "1. Status atual do container qualidadeHD:"
sshpass -p "$PASSWORD" ssh -o StrictHostKeyChecking=no root@$SERVER "docker ps -a | grep qualidade | head -3"

echo ""
echo "=========================================="
echo "PRÓXIMOS PASSOS:"
echo "=========================================="
echo ""
echo "O problema foi identificado: o container está usando SQLite ao invés de MariaDB."
echo ""
echo "SOLUÇÃO:"
echo "1. Acesse o painel Dokploy: http://$SERVER:3000"
echo "2. Faça login no Dokploy"
echo "3. Encontre o projeto 'qualidadeHD' (qualidade-qualidadehd-bue1bg)"
echo "4. Vá para 'Settings' ou 'Environment Variables'"
echo "5. Adicione/atualize estas variáveis CRÍTICAS:"
echo ""
echo "   DB_CONNECTION=mariadb"
echo "   DB_HOST=qualidade-productionqualidade-l2xbgb"
echo "   DB_PORT=3306"
echo "   DB_DATABASE=hemodialise_gqa"
echo "   DB_USERNAME=Usr_QltGest@2025"
echo "   DB_PASSWORD=Qlt!H0sp#2025"
echo "   APP_ENV=production"
echo "   APP_KEY=base64:H2UOVPoDW9emzGrn1Rx2EV15YOsosTwPtvPUPmsVph0="
echo ""
echo "6. IMPORTANTE: Copie TODAS as variáveis do arquivo DOKPLOY_ENV.md"
echo "7. Salve as alterações"
echo "8. Faça o REDEPLOY do projeto"
echo ""
echo "Arquivo completo com todas as variáveis: DOKPLOY_ENV.md"
echo ""
echo "=========================================="
