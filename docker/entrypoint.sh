#!/bin/sh
set -e

echo "Starting Hemodialise application..."

# Wait for database to be ready
if [ ! -z "$DB_HOST" ]; then
    echo "Waiting for database at $DB_HOST:${DB_PORT:-5432}..."
    timeout=60
    while ! nc -z $DB_HOST ${DB_PORT:-5432}; do
        timeout=$((timeout - 1))
        if [ $timeout -le 0 ]; then
            echo "Error: Database connection timeout"
            exit 1
        fi
        echo "Waiting for database... ($timeout seconds remaining)"
        sleep 1
    done
    echo "Database is ready!"
fi

# Set proper permissions
echo "Setting permissions..."
chown -R laravel:laravel /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run as laravel user for artisan commands
echo "Running Laravel setup..."
su-exec laravel php artisan config:cache
su-exec laravel php artisan route:cache
su-exec laravel php artisan view:cache

# Run migrations
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Running database migrations..."
    su-exec laravel php artisan migrate --force --no-interaction
fi

# Run seeders (only if explicitly enabled)
if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo "Running database seeders..."
    su-exec laravel php artisan db:seed --force --no-interaction
fi

# Create storage link if not exists
if [ ! -L /var/www/html/public/storage ]; then
    echo "Creating storage link..."
    su-exec laravel php artisan storage:link
fi

# Optimize autoloader
echo "Optimizing autoloader..."
su-exec laravel composer dump-autoload --optimize --no-dev --classmap-authoritative

echo "Starting services..."
exec supervisord -c /etc/supervisor/supervisord.conf
