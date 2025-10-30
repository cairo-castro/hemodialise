#!/bin/sh
set -e

echo "=============================================="
echo "Starting Hemodialise Production Application"
echo "Domain: qualidadehd.direcaoclinica.com.br"
echo "=============================================="
echo ""

# Validate environment variables first
echo "Step 1: Validating environment variables..."
if [ -f "/var/www/html/docker/validate-env.sh" ]; then
    chmod +x /var/www/html/docker/validate-env.sh
    /var/www/html/docker/validate-env.sh
    if [ $? -ne 0 ]; then
        echo ""
        echo "❌ Environment validation failed!"
        echo "See DOKPLOY-ENV-GUIDE.md for setup instructions"
        exit 1
    fi
else
    echo "⚠️  Warning: validate-env.sh not found, skipping validation"
fi

echo ""
echo "Step 2: Checking database connection..."

# Function to wait for MariaDB
wait_for_database() {
    echo "Waiting for MariaDB at $DB_HOST:${DB_PORT}..."

    max_attempts=30
    attempt=0

    while [ $attempt -lt $max_attempts ]; do
        if nc -z "$DB_HOST" "${DB_PORT}" 2>/dev/null; then
            echo "✓ Database connection established!"
            return 0
        fi

        attempt=$((attempt + 1))
        echo "  Waiting for database... (attempt $attempt/$max_attempts)"
        sleep 2
    done

    echo "✗ ERROR: Database connection timeout after $max_attempts attempts"
    exit 1
}

# Wait for database
wait_for_database

echo ""
echo "Setting up application permissions..."
chown -R laravel:laravel /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo ""
echo "Step 3: Running Composer post-install scripts..."

# Run composer scripts that were skipped during build (package:discover, filament:upgrade)
echo "  → Running package discovery..."
su-exec laravel php artisan package:discover --ansi || echo "⚠️ Package discovery failed (non-fatal)"

echo "  → Running Filament upgrade..."
su-exec laravel php artisan filament:upgrade --ansi || echo "⚠️ Filament upgrade failed (non-fatal)"

echo ""
echo "Step 4: Verifying Composer autoload..."

# Verify autoload files exist
if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "❌ ERROR: Composer autoload not found!"
    echo "Running composer dump-autoload..."
    su-exec laravel composer dump-autoload --optimize --classmap-authoritative --no-dev
fi

# Verify autoload is working by checking for a core Laravel class
echo "  → Verifying autoload integrity..."
su-exec laravel php -r "
require '/var/www/html/vendor/autoload.php';
if (class_exists('Illuminate\Foundation\Application')) {
    echo '✓ Autoload is working correctly\n';
    exit(0);
} else {
    echo '❌ ERROR: Autoload verification failed!\n';
    exit(1);
}
"

if [ $? -ne 0 ]; then
    echo "❌ Autoload verification failed! Regenerating..."
    su-exec laravel composer dump-autoload --optimize --classmap-authoritative --no-dev
    echo "✓ Autoload regenerated successfully"
fi

echo ""
echo "Optimizing Laravel for production..."

# Clear all caches first
echo "  → Clearing existing caches..."
su-exec laravel php artisan cache:clear --no-interaction || true
su-exec laravel php artisan config:clear --no-interaction || true
su-exec laravel php artisan route:clear --no-interaction || true
su-exec laravel php artisan view:clear --no-interaction || true

# Only cache configuration, routes, and views if not already cached
echo "  → Caching configuration..."
su-exec laravel php artisan config:cache --no-interaction

echo "  → Caching routes..."
su-exec laravel php artisan route:cache --no-interaction

echo "  → Caching views..."
su-exec laravel php artisan view:cache --no-interaction

echo "  → Caching events..."
su-exec laravel php artisan event:cache --no-interaction || true

# Check if migrations have already been run using a marker file
MIGRATION_MARKER="/var/www/html/storage/migrations_completed"
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    # Only run migrations if marker file doesn't exist
    if [ ! -f "$MIGRATION_MARKER" ]; then
        echo ""
        echo "Running database migrations for the first time..."
        su-exec laravel php artisan migrate --force --no-interaction
        
        if [ $? -eq 0 ]; then
            echo "✓ Migrations completed successfully"
            # Create marker file to prevent running migrations again
            touch "$MIGRATION_MARKER"
            chown laravel:laravel "$MIGRATION_MARKER"
        else
            echo "✗ Migration failed!"
            exit 1
        fi
    else
        echo ""
        echo "⚠ Skipping migrations (already completed)"
    fi
else
    echo ""
    echo "⚠ Skipping migrations (RUN_MIGRATIONS=false)"
fi

# Run seeders (only if explicitly enabled and never run before)
SEEDER_MARKER="/var/www/html/storage/seeders_completed"
if [ "${RUN_SEEDERS:-false}" = "true" ] && [ ! -f "$SEEDER_MARKER" ]; then
    echo ""
    echo "Running database seeders for the first time..."
    su-exec laravel php artisan db:seed --force --no-interaction
    
    if [ $? -eq 0 ]; then
        echo "✓ Seeders completed successfully"
        # Create marker file to prevent running seeders again
        touch "$SEEDER_MARKER"
        chown laravel:laravel "$SEEDER_MARKER"
    else
        echo "⚠ Seeder execution had issues (this may be normal)"
    fi
elif [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo ""
    echo "⚠ Skipping seeders (already completed)"
else
    echo ""
    echo "Skipping seeders (RUN_SEEDERS=false)"
fi

# Create storage link
if [ ! -L /var/www/html/public/storage ]; then
    echo ""
    echo "Creating storage symlink..."
    su-exec laravel php artisan storage:link --no-interaction
    echo "✓ Storage link created"
fi

# Optimize icons and assets
echo ""
echo "Optimizing Filament resources..."
su-exec laravel php artisan filament:optimize --no-interaction || true
su-exec laravel php artisan icons:cache --no-interaction || true

# Clear any remaining application cache
echo ""
echo "Final cache optimization..."
su-exec laravel php artisan optimize --no-interaction

echo ""
echo "=============================================="
echo "✓ Application setup completed successfully!"
echo "=============================================="
echo ""

# Ensure /var/run exists with correct permissions for PHP-FPM socket
echo "Preparing PHP-FPM socket directory..."
mkdir -p /var/run
chmod 755 /var/run

# Verify PHP-FPM configuration for socket
if grep -q "listen = /var/run/php-fpm.sock" /usr/local/etc/php-fpm.d/www.conf; then
    echo "✓ PHP-FPM configured to use Unix socket"

    # Ensure socket permissions are configured
    if ! grep -q "^listen.owner" /usr/local/etc/php-fpm.d/www.conf; then
        echo "⚠️  Adding missing socket permissions..."
        echo "listen.owner = nginx" >> /usr/local/etc/php-fpm.d/www.conf
        echo "listen.group = nginx" >> /usr/local/etc/php-fpm.d/www.conf
        echo "listen.mode = 0660" >> /usr/local/etc/php-fpm.d/www.conf
    fi
else
    echo "✓ PHP-FPM configured to use TCP"
fi

echo ""
echo "Starting services via Supervisor..."
echo "  - Nginx (HTTP Server)"
echo "  - PHP-FPM (Application Server)"
echo "  - Laravel Queue Workers (2 workers)"
echo "  - Laravel Scheduler (Cron Tasks)"
echo ""

# Start supervisor (this will run in foreground)
exec /usr/bin/supervisord -c /etc/supervisord.conf
