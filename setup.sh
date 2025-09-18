#!/bin/bash

# 🏥 Setup do Sistema de Hemodiálise
# Script de instalação e configuração automatizada

echo "🏥 ================================="
echo "   SISTEMA DE HEMODIÁLISE - SETUP"
echo "================================="
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir mensagens coloridas
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Verificar se está no diretório correto
if [ ! -f "artisan" ]; then
    print_error "Execute este script no diretório raiz do projeto Laravel!"
    exit 1
fi

# 1. Verificar dependências
print_info "Verificando dependências..."

# Verificar PHP
if ! command -v php &> /dev/null; then
    print_error "PHP não encontrado! Instale PHP 8.3+ primeiro."
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
if [[ $(echo "$PHP_VERSION < 8.3" | bc -l) ]]; then
    print_error "PHP 8.3+ é necessário. Versão atual: $PHP_VERSION"
    exit 1
fi

print_status "PHP $PHP_VERSION encontrado"

# Verificar Composer
if ! command -v composer &> /dev/null; then
    print_error "Composer não encontrado! Instale o Composer primeiro."
    exit 1
fi

print_status "Composer encontrado"

# Verificar Node.js
if ! command -v node &> /dev/null; then
    print_error "Node.js não encontrado! Instale Node.js primeiro."
    exit 1
fi

print_status "Node.js $(node --version) encontrado"

# Verificar NPM
if ! command -v npm &> /dev/null; then
    print_error "NPM não encontrado!"
    exit 1
fi

print_status "NPM $(npm --version) encontrado"

# 2. Configurar banco de dados
print_info "Configurando banco de dados..."

# Verificar se MySQL/MariaDB está disponível
if ! command -v mysql &> /dev/null; then
    print_error "MySQL/MariaDB não encontrado!"
    exit 1
fi

# Testar conexão com o banco
if ! mysql -u root -pqualidade123 -e "SELECT 1;" &> /dev/null; then
    print_error "Não foi possível conectar ao MariaDB com as credenciais fornecidas!"
    print_info "Verifique se o MariaDB está rodando e as credenciais estão corretas."
    exit 1
fi

print_status "Conexão com MariaDB estabelecida"

# Criar banco de dados se não existir
mysql -u root -pqualidade123 -e "CREATE DATABASE IF NOT EXISTS hemodialise CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

if [ $? -eq 0 ]; then
    print_status "Banco de dados 'hemodialise' configurado"
else
    print_error "Erro ao criar banco de dados"
    exit 1
fi

# 3. Instalar dependências PHP
print_info "Instalando dependências PHP..."
composer install --optimize-autoloader

if [ $? -eq 0 ]; then
    print_status "Dependências PHP instaladas"
else
    print_error "Erro ao instalar dependências PHP"
    exit 1
fi

# 4. Instalar dependências Node.js
print_info "Instalando dependências Node.js..."
npm install

if [ $? -eq 0 ]; then
    print_status "Dependências Node.js instaladas"
else
    print_error "Erro ao instalar dependências Node.js"
    exit 1
fi

# 5. Configurar arquivo .env
print_info "Configurando arquivo .env..."

if [ ! -f ".env" ]; then
    cp .env.example .env
    print_status "Arquivo .env criado a partir do .env.example"
fi

# Gerar chave da aplicação
php artisan key:generate --force

if [ $? -eq 0 ]; then
    print_status "Chave da aplicação gerada"
else
    print_error "Erro ao gerar chave da aplicação"
    exit 1
fi

# 6. Executar migrations e seeders
print_info "Configurando banco de dados..."

# Limpar cache de configuração
php artisan config:clear

# Executar migrations
php artisan migrate --force

if [ $? -eq 0 ]; then
    print_status "Migrations executadas com sucesso"
else
    print_error "Erro ao executar migrations"
    exit 1
fi

# Executar seeders
php artisan db:seed --force

if [ $? -eq 0 ]; then
    print_status "Dados de exemplo inseridos"
else
    print_error "Erro ao inserir dados de exemplo"
    exit 1
fi

# 7. Compilar assets
print_info "Compilando assets frontend..."
npm run build

if [ $? -eq 0 ]; then
    print_status "Assets compilados"
else
    print_error "Erro ao compilar assets"
    exit 1
fi

# 8. Configurar permissões
print_info "Configurando permissões..."
chmod -R 775 storage bootstrap/cache

if [ $? -eq 0 ]; then
    print_status "Permissões configuradas"
else
    print_warning "Aviso: Erro ao configurar permissões - pode ser necessário ajustar manualmente"
fi

# 9. Otimizar aplicação
print_info "Otimizando aplicação..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_status "Aplicação otimizada"

echo ""
echo "🎉 ================================="
echo "   INSTALAÇÃO CONCLUÍDA COM SUCESSO!"
echo "================================="
echo ""
print_info "Credenciais de acesso:"
echo "  📧 Email: admin@hemodialise.com"
echo "  🔐 Senha: admin123"
echo ""
print_info "Para iniciar o servidor:"
echo "  php artisan serve"
echo ""
print_info "Acesse o sistema em:"
echo "  http://localhost:8000/admin"
echo ""
print_status "Sistema pronto para uso! 🚀"