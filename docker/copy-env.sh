#!/bin/sh
# Script para copiar o arquivo .env.production para .env se .env não existir
# Isso garante que o Laravel tenha acesso às variáveis de ambiente necessárias

set -e

ENV_FILE="/var/www/html/.env"
ENV_PROD_FILE="/var/www/html/.env.production"

if [ ! -f "$ENV_FILE" ] && [ -f "$ENV_PROD_FILE" ]; then
    echo "Copying .env.production to .env..."
    cp "$ENV_PROD_FILE" "$ENV_FILE"
    chown laravel:laravel "$ENV_FILE"
    echo "✓ .env file created from .env.production"
elif [ -f "$ENV_FILE" ]; then
    echo "✓ .env file already exists"
else
    echo "⚠ Warning: Neither .env nor .env.production found in the container"
fi