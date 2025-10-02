#!/bin/bash

# 🏥 Script para executar o Sistema de Hemodiálise
# Execução rápida do servidor de desenvolvimento

echo "🏥 Sistema de Hemodiálise - Iniciando servidor..."

# Função para limpar processos ao sair
cleanup() {
    echo ""
    echo "🛑 Parando servidores..."
    kill $PHP_PID $VITE_PID 2>/dev/null
    exit
}

trap cleanup SIGINT SIGTERM

# Parar processos existentes nas portas
echo "🔄 Verificando processos existentes..."
for PORT in 8000 5173 5174; do
    EXISTING_PID=$(lsof -ti:$PORT)
    if [ ! -z "$EXISTING_PID" ]; then
        echo "⚠️  Parando processo na porta $PORT (PID: $EXISTING_PID)"
        kill -9 $EXISTING_PID 2>/dev/null
    fi
done
sleep 2

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

# Verificar se node_modules existe
if [ ! -d "node_modules" ]; then
    echo "⚠️  Dependências não instaladas. Instalando..."
    npm install
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

# Iniciar servidores
echo "🚀 Iniciando servidores..."
echo ""
echo "📱 Sistema: http://localhost:8000"
echo "📱 Admin: http://localhost:8000/admin"
echo ""
echo "Credenciais:"
echo "📧 Email: admin@hemodialise.com"
echo "🔐 Senha: admin123"
echo ""
echo "Pressione Ctrl+C para parar os servidores"
echo "================================="
echo ""

# Iniciar Vite em background
npm run dev > /dev/null 2>&1 &
VITE_PID=$!

# Aguardar Vite iniciar
sleep 3

# Iniciar servidor Laravel
php artisan serve --host=0.0.0.0 --port=8000 &
PHP_PID=$!

# Aguardar ambos os processos
wait