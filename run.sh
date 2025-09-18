#!/bin/bash

# 🏥 Script para executar o Sistema de Hemodiálise
# Execução rápida do servidor de desenvolvimento

echo "🏥 Sistema de Hemodiálise - Iniciando servidor..."

# Parar processos existentes na porta 8000
echo "🔄 Verificando processos na porta 8000..."
EXISTING_PID=$(lsof -ti:8000)
if [ ! -z "$EXISTING_PID" ]; then
    echo "⚠️  Parando processo existente (PID: $EXISTING_PID)"
    kill -9 $EXISTING_PID 2>/dev/null
    sleep 2
fi

# Verificar se está no diretório correto
if [ ! -f "artisan" ]; then
    echo "❌ Execute este script no diretório raiz do projeto!"
    exit 1
fi

# Verificar se o .env existe
if [ ! -f ".env" ]; then
    echo "⚠️  Arquivo .env não encontrado. Execute ./setup.sh primeiro!"
    exit 1
fi

# Limpar cache se necessário
php artisan config:clear

# Verificar conexão com banco
echo "🔍 Verificando conexão com banco de dados..."
php artisan migrate:status > /dev/null 2>&1

if [ $? -ne 0 ]; then
    echo "❌ Erro de conexão com banco. Verifique as configurações!"
    exit 1
fi

echo "✅ Conexão com banco estabelecida"

# Iniciar servidor
echo "🚀 Iniciando servidor em http://localhost:8000"
echo "📱 Acesse o admin em: http://localhost:8000/admin"
echo ""
echo "Credenciais:"
echo "📧 Email: admin@hemodialise.com"
echo "🔐 Senha: admin123"
echo ""
echo "Pressione Ctrl+C para parar o servidor"
echo "================================="

php artisan serve --host=0.0.0.0 --port=8000