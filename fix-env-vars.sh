#!/bin/bash
# Script para configurar variáveis de ambiente no serviço Docker do Dokploy

echo "🔧 Configurando variáveis de ambiente no serviço..."
echo ""

# Atualizar serviço com TODAS as variáveis de ambiente necessárias
docker service update \
  --env-add "APP_NAME=Sistema Hemodiálise - Qualidade" \
  --env-add "APP_ENV=production" \
  --env-add "APP_KEY=base64:+vZ3zPQKzXvnwJGVYTz5hN8yR6mL4kW2xB7fC9dE0aA=" \
  --env-add "APP_DEBUG=false" \
  --env-add "APP_URL=https://qualidadehd.direcaoclinica.com.br" \
  --env-add "APP_TIMEZONE=America/Sao_Paulo" \
  --env-add "APP_LOCALE=pt_BR" \
  --env-add "APP_FALLBACK_LOCALE=pt_BR" \
  --env-add "LOG_CHANNEL=stack" \
  --env-add "LOG_LEVEL=warning" \
  --env-add "DB_CONNECTION=mysql" \
  --env-add "DB_HOST=qualidade-productionqualidade-l2xbgb" \
  --env-add "DB_PORT=3306" \
  --env-add "DB_DATABASE=hemodialise_gqa" \
  --env-add "DB_USERNAME=Usr_QltGest@2025" \
  --env-add "DB_PASSWORD=Qlt!H0sp#2025" \
  --env-add "DB_CHARSET=utf8mb4" \
  --env-add "DB_COLLATION=utf8mb4_unicode_ci" \
  --env-add "SESSION_DRIVER=database" \
  --env-add "SESSION_LIFETIME=480" \
  --env-add "SESSION_ENCRYPT=true" \
  --env-add "SESSION_SECURE_COOKIE=true" \
  --env-add "CACHE_STORE=database" \
  --env-add "QUEUE_CONNECTION=database" \
  --env-add "BROADCAST_CONNECTION=log" \
  --env-add "FILESYSTEM_DISK=local" \
  --env-add "TRUSTED_PROXIES=*" \
  --env-add "RUN_MIGRATIONS=true" \
  --env-add "RUN_SEEDERS=false" \
  --env-add "JWT_SECRET=xK8mN9pQ2rT5vW7yZ1aC4eG6hJ0kL3nP" \
  qualidade-qualidadehd-bue1bg

echo ""
echo "✅ Variáveis de ambiente aplicadas!"
echo ""
echo "⏳ Aguardando o serviço reiniciar (60 segundos)..."
sleep 60

echo ""
echo "📊 Status do serviço:"
docker service ps qualidade-qualidadehd-bue1bg --no-trunc | head -5

echo ""
echo "📋 Logs do serviço (últimas 30 linhas):"
docker service logs qualidade-qualidadehd-bue1bg --tail 30

echo ""
echo "🔍 Verificando variáveis de ambiente no container:"
CONTAINER_ID=$(docker ps --filter name=qualidade-qualidadehd --format '{{.ID}}' | head -1)
if [ ! -z "$CONTAINER_ID" ]; then
    echo "Container: $CONTAINER_ID"
    docker exec $CONTAINER_ID env | grep -E "DB_|APP_" | sort
else
    echo "⚠️ Container não está rodando ainda"
fi
