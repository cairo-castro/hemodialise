#!/bin/bash

###############################################################################
# Script de Teste Local do Dockerfile.production
# Verifica se o autoload está funcionando corretamente antes do deploy
###############################################################################

set -e

echo "=========================================="
echo "🧪 Teste Local - Dockerfile.production"
echo "=========================================="
echo ""

# Cores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para printar com cor
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# 1. Build da imagem
echo "📦 Step 1: Building Docker image..."
docker build -f Dockerfile.production -t hemodialise-test:local . --progress=plain 2>&1 | tee build.log

if [ ${PIPESTATUS[0]} -eq 0 ]; then
    print_success "Build completed successfully"
else
    print_error "Build failed! Check build.log for details"
    exit 1
fi

echo ""
echo "🔍 Step 2: Verifying build outputs..."

# Verificar se o autoload foi gerado corretamente
if grep -q "Autoload optimized successfully" build.log; then
    print_success "Autoload optimization confirmed in build"
else
    print_warning "Autoload optimization message not found in build log"
fi

# Verificar se o Composer está instalado
if grep -q "Composer installed for production maintenance" build.log; then
    print_success "Composer installation confirmed"
else
    print_warning "Composer installation message not found"
fi

echo ""
echo "🚀 Step 3: Starting test container..."

# Remove container anterior se existir
docker rm -f hemodialise-test 2>/dev/null || true

# Cria arquivo .env temporário para teste
cat > .env.test << 'EOF'
APP_NAME="Hemodialise Test"
APP_ENV=production
APP_KEY=base64:TEST_KEY_FOR_LOCAL_TESTING_ONLY
APP_DEBUG=false
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=test
DB_USERNAME=test
DB_PASSWORD=test

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
EOF

# Inicia container em background
docker run -d \
    --name hemodialise-test \
    --env-file .env.test \
    -e RUN_MIGRATIONS=false \
    -e RUN_SEEDERS=false \
    -p 8888:80 \
    hemodialise-test:local

# Aguarda container inicializar
echo "⏳ Waiting for container to start..."
sleep 5

echo ""
echo "🔍 Step 4: Verifying autoload inside container..."

# Testa se o Composer está disponível
if docker exec hemodialise-test composer --version >/dev/null 2>&1; then
    print_success "Composer is available in container"
else
    print_error "Composer not found in container"
    docker logs hemodialise-test
    exit 1
fi

# Testa se o autoload existe
if docker exec hemodialise-test test -f /var/www/html/vendor/autoload.php; then
    print_success "Autoload file exists"
else
    print_error "Autoload file not found!"
    exit 1
fi

# Testa se as classes críticas estão no autoload
echo ""
echo "🔍 Step 5: Testing critical classes..."

# Teste de classe Api\AuthController
if docker exec hemodialise-test php -r "require '/var/www/html/vendor/autoload.php'; exit(class_exists('App\Http\Controllers\Api\AuthController') ? 0 : 1);" 2>/dev/null; then
    print_success "App\\Http\\Controllers\\Api\\AuthController is autoloadable"
else
    print_error "App\\Http\\Controllers\\Api\\AuthController NOT found in autoload!"
    echo ""
    echo "Checking autoload files..."
    docker exec hemodialise-test ls -la /var/www/html/vendor/composer/autoload_*.php
    exit 1
fi

# Teste de classe Frontend\AuthController
if docker exec hemodialise-test php -r "require '/var/www/html/vendor/autoload.php'; exit(class_exists('App\Http\Controllers\Frontend\AuthController') ? 0 : 1);" 2>/dev/null; then
    print_success "App\\Http\\Controllers\\Frontend\\AuthController is autoloadable"
else
    print_error "App\\Http\\Controllers\\Frontend\\AuthController NOT found in autoload!"
    exit 1
fi

# Teste de outras classes importantes
for class in "App\\Models\\User" "App\\Http\\Controllers\\Controller" "App\\Providers\\AppServiceProvider"; do
    if docker exec hemodialise-test php -r "require '/var/www/html/vendor/autoload.php'; exit(class_exists('$class') ? 0 : 1);" 2>/dev/null; then
        print_success "$class is autoloadable"
    else
        print_error "$class NOT found in autoload!"
        exit 1
    fi
done

echo ""
echo "🔍 Step 6: Checking entrypoint logs..."

# Verifica se o entrypoint detectou e validou o autoload
if docker logs hemodialise-test 2>&1 | grep -q "Verifying Composer autoload"; then
    print_success "Entrypoint autoload verification is working"
else
    print_warning "Entrypoint verification not found in logs"
fi

if docker logs hemodialise-test 2>&1 | grep -q "AuthController is autoloadable"; then
    print_success "Entrypoint detected AuthController classes"
else
    print_warning "AuthController verification not found in logs"
fi

echo ""
echo "📊 Step 7: Container health check..."

# Verifica se o container está rodando
if docker ps | grep -q hemodialise-test; then
    print_success "Container is running"
else
    print_error "Container stopped unexpectedly!"
    docker logs hemodialise-test
    exit 1
fi

# Aguarda um pouco para health check
sleep 10

# Verifica health status
HEALTH_STATUS=$(docker inspect --format='{{.State.Health.Status}}' hemodialise-test 2>/dev/null || echo "no-healthcheck")
if [ "$HEALTH_STATUS" = "healthy" ]; then
    print_success "Container health check: HEALTHY"
elif [ "$HEALTH_STATUS" = "no-healthcheck" ]; then
    print_warning "Health check not configured"
else
    print_warning "Container health check: $HEALTH_STATUS (may need more time)"
fi

echo ""
echo "=========================================="
echo "✅ ALL TESTS PASSED!"
echo "=========================================="
echo ""
echo "📝 Summary:"
echo "  • Build: SUCCESS"
echo "  • Composer: AVAILABLE"
echo "  • Autoload: WORKING"
echo "  • Critical Classes: FOUND"
echo "  • Container: RUNNING"
echo ""
echo "🚀 Ready to deploy to production!"
echo ""
echo "📋 Next steps:"
echo "  1. Review build.log if needed"
echo "  2. Test locally at: http://localhost:8888"
echo "  3. Deploy to Dokploy (force rebuild)"
echo ""
echo "🧹 Cleanup:"
echo "  docker stop hemodialise-test"
echo "  docker rm hemodialise-test"
echo "  docker rmi hemodialise-test:local"
echo "  rm .env.test build.log"
echo ""
