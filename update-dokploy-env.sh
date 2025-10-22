#!/bin/bash

# Script para atualizar as variáveis de ambiente no Dokploy via SSH
# Este script deve ser executado NO SERVIDOR de produção

echo "🔧 Atualizando variáveis de ambiente do serviço Dokploy..."
echo ""

# Definir as variáveis de ambiente
ENV_VARS=(
    "APP_NAME=Sistema Hemodiálise - Qualidade"
    "APP_ENV=production"
    "APP_DEBUG=false"
    "APP_URL=https://qualidadehd.direcaoclinica.com.br"
    "APP_TIMEZONE=America/Sao_Paulo"
    "APP_LOCALE=pt_BR"
    "APP_FALLBACK_LOCALE=pt_BR"
    "LOG_CHANNEL=stack"
    "LOG_LEVEL=warning"
    "DB_CONNECTION=mysql"
    "DB_HOST=qualidade-productionqualidade-l2xbgb"
    "DB_PORT=3306"
    "DB_DATABASE=hemodialise_gqa"
    "DB_USERNAME=Usr_QltGest@2025"
    "DB_PASSWORD=Qlt!H0sp#2025"
    "DB_CHARSET=utf8mb4"
    "DB_COLLATION=utf8mb4_unicode_ci"
    "SESSION_DRIVER=database"
    "SESSION_LIFETIME=480"
    "CACHE_STORE=database"
    "QUEUE_CONNECTION=database"
    "BROADCAST_CONNECTION=log"
    "FILESYSTEM_DISK=local"
    "TRUSTED_PROXIES=*"
    "RUN_MIGRATIONS=true"
    "RUN_SEEDERS=false"
)

# Construir o comando de atualização do serviço
ENV_FLAGS=""
for env in "${ENV_VARS[@]}"; do
    ENV_FLAGS="$ENV_FLAGS --env-add \"$env\""
done

# Atualizar o serviço
echo "📝 Aplicando variáveis de ambiente ao serviço qualidade-qualidadehd-bue1bg..."
eval docker service update $ENV_FLAGS qualidade-qualidadehd-bue1bg

echo ""
echo "✅ Variáveis de ambiente atualizadas!"
echo ""
echo "⏳ Aguardando o serviço reiniciar (30 segundos)..."
sleep 30

echo ""
echo "📊 Status do serviço:"
docker service ps qualidade-qualidadehd-bue1bg --no-trunc

echo ""
echo "📋 Logs do serviço (últimas 20 linhas):"
docker service logs qualidade-qualidadehd-bue1bg --tail 20

echo ""
echo "✨ Concluído! Verifique os logs acima para confirmar que o serviço iniciou corretamente."
