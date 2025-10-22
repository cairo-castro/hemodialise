# ==============================================
# Multi-stage Dockerfile para Laravel + Vue.js
# Otimizado para Produção com Dokploy
# ==============================================

# ==============================================
# Stage 1: Build Node.js Assets
# ==============================================
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copiar apenas package files para aproveitar cache do Docker
COPY package*.json ./

# Instalar dependências Node
RUN npm ci --only=production

# Copiar código fonte necessário
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY tsconfig.json ./
COPY ionic-frontend ./ionic-frontend

# Build dos assets (mobile e desktop)
RUN npm run build:mobile && npm run build

# ==============================================
# Stage 2: Composer Dependencies
# ==============================================
FROM composer:2 AS composer-builder

WORKDIR /app

# Copiar apenas composer files para cache
COPY composer.json composer.lock ./

# Instalar dependências PHP (production only)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

# ==============================================
# Stage 3: Production Runtime
# ==============================================
FROM php:8.2-fpm-alpine AS production

LABEL maintainer="Sistema Hemodiálise - Maranhão"
LABEL version="1.0"
LABEL description="Sistema de Gestão de Hemodiálise"

# Instalar dependências do sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    curl \
    zip \
    unzip \
    git \
    bash

# Instalar extensões PHP necessárias
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    zip \
    gd \
    mbstring \
    opcache \
    bcmath \
    exif

# Configurar OPcache para produção
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.fast_shutdown=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Configuração PHP otimizada
RUN { \
    echo 'memory_limit=512M'; \
    echo 'upload_max_filesize=50M'; \
    echo 'post_max_size=50M'; \
    echo 'max_execution_time=300'; \
    echo 'max_input_time=300'; \
    echo 'expose_php=Off'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# Criar usuário não-root
RUN addgroup -g 1000 laravel \
    && adduser -D -u 1000 -G laravel laravel

# Diretório de trabalho
WORKDIR /var/www/html

# Copiar código do aplicativo
COPY --chown=laravel:laravel . .

# Copiar dependências do Composer
COPY --from=composer-builder --chown=laravel:laravel /app/vendor ./vendor

# Copiar assets buildados do Node
COPY --from=node-builder --chown=laravel:laravel /app/public/build ./public/build
COPY --from=node-builder --chown=laravel:laravel /app/public/mobile-assets ./public/mobile-assets

# Criar diretórios necessários com permissões corretas
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R laravel:laravel storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configurar Nginx
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Configurar Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Configurar PHP-FPM
RUN sed -i 's/user = www-data/user = laravel/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/group = www-data/group = laravel/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/listen = 127.0.0.1:9000/listen = \/var\/run\/php-fpm.sock/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.owner = www-data/listen.owner = laravel/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.group = www-data/listen.group = laravel/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.mode = 0660/listen.mode = 0660/g' /usr/local/etc/php-fpm.d/www.conf

# Script de entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expor porta
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

# Entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Comando padrão
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
