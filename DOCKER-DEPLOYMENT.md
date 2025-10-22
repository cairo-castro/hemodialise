# Hemodialise - Docker Deployment Guide

## 🐳 Docker Production Setup

This guide covers deploying the Hemodialise application using Docker, optimized for Dokploy deployment.

## Prerequisites

- Docker 20.10+
- Docker Compose 2.x+ (for local testing)
- PostgreSQL database (managed by Dokploy or external)
- Domain name configured with SSL (handled by Dokploy/Traefik)

## Quick Start

### Local Development with Docker Compose

1. **Generate Application Key**
```bash
# Generate a Laravel app key
php artisan key:generate --show
```

2. **Create `.env` file** (or set environment variables in Dokploy)
```env
APP_NAME="Hemodialise"
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=hemodialise
DB_USERNAME=hemodialise
DB_PASSWORD=strong-password-here

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

JWT_SECRET=your-jwt-secret-here

# Container initialization
RUN_MIGRATIONS=true
RUN_SEEDERS=false
```

3. **Build and Run**
```bash
# Build the Docker image
docker-compose build

# Start services
docker-compose up -d

# Check logs
docker-compose logs -f app

# Access application at http://localhost:8000
```

4. **Run Database Seeders** (first time only)
```bash
docker-compose exec app su-exec laravel php artisan db:seed --class=RolesAndPermissionsSeeder
docker-compose exec app su-exec laravel php artisan db:seed --class=UserSeeder
```

### Production Deployment on Dokploy

#### 1. Prepare Repository

Push your code to a Git repository (GitHub, GitLab, Bitbucket, or self-hosted).

#### 2. Create Dokploy Application

1. Log into your Dokploy instance
2. Create new application → Choose "Dockerfile"
3. Connect your Git repository
4. Set build context path: `/`
5. Dockerfile path: `Dockerfile`

#### 3. Configure Environment Variables

Add these in Dokploy application settings:

```env
APP_NAME=Hemodialise
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=your-postgres-host
DB_PORT=5432
DB_DATABASE=hemodialise
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

JWT_SECRET=your-jwt-secret-here

RUN_MIGRATIONS=true
RUN_SEEDERS=false
```

#### 4. Configure Database

Create a PostgreSQL database in Dokploy:
- Database name: `hemodialise`
- User: `hemodialise`
- Password: (generate strong password)
- Connection: Use Dokploy's internal network DNS name

#### 5. Configure Domain & SSL

1. Add your domain in Dokploy (e.g., `hemodialise.yourdomain.com`)
2. Dokploy will automatically provision SSL certificate via Let's Encrypt
3. Configure DNS A/CNAME record pointing to Dokploy server IP

#### 6. Deploy

1. Click "Deploy" in Dokploy
2. Monitor build logs
3. Wait for health checks to pass
4. Access your application at configured domain

#### 7. Initial Database Seeding

After first deployment, run seeders:

```bash
# SSH into Dokploy server
ssh user@dokploy-server

# Find container ID
docker ps | grep hemodialise

# Run seeders
docker exec -it <container-id> su-exec laravel php artisan db:seed --class=RolesAndPermissionsSeeder
docker exec -it <container-id> su-exec laravel php artisan db:seed --class=UserSeeder
```

## Architecture

### Multi-Stage Docker Build

1. **node-builder**: Builds frontend assets (Vite + Ionic)
2. **composer-builder**: Installs PHP dependencies
3. **production**: Final Alpine-based runtime image

### Services

The production container runs multiple services via Supervisor:
- **Nginx**: Web server (port 80)
- **PHP-FPM**: PHP processor (Unix socket)
- **Laravel Queue Workers**: Background job processing (2 workers)
- **Laravel Scheduler**: Cron-like task scheduler

### File Structure

```
docker/
├── nginx/
│   ├── nginx.conf          # Main Nginx config
│   └── default.conf        # Site config (Laravel routing)
├── supervisor/
│   └── supervisord.conf    # Process manager config
└── entrypoint.sh           # Startup script
```

## Environment Variables

### Required Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_KEY` | Laravel encryption key | `base64:xxx...` |
| `DB_HOST` | Database hostname | `postgres` or `db.example.com` |
| `DB_DATABASE` | Database name | `hemodialise` |
| `DB_USERNAME` | Database user | `hemodialise` |
| `DB_PASSWORD` | Database password | `strong-password` |

### Optional Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_DEBUG` | `false` | Enable debug mode (never in production!) |
| `RUN_MIGRATIONS` | `true` | Auto-run migrations on startup |
| `RUN_SEEDERS` | `false` | Auto-run seeders on startup |
| `QUEUE_CONNECTION` | `database` | Queue driver (database/redis/sqs) |

## Health Checks

The container exposes a health check endpoint:

```
GET /up
Returns: 200 OK
```

Dokploy and Docker both use this for readiness/liveness checks.

## Persistent Storage

Data persisted across container restarts:
- `/var/www/html/storage` - Logs, uploaded files, framework cache
- `/var/www/html/bootstrap/cache` - Optimized Laravel cache files

Map these to Docker volumes for data persistence.

## Performance Optimizations

### Included Optimizations

- **OPcache**: Precompiled PHP bytecode caching
- **Gzip Compression**: Reduced bandwidth usage
- **Static File Caching**: Browser-side caching (1 year)
- **Route/Config/View Caching**: Laravel optimization
- **Composer Autoloader**: Optimized class maps

### Database Optimization

The application uses PostgreSQL with indexes on:
- User lookups by email/identifier
- Machine availability queries
- Unit-scoped data filtering

### Queue Workers

Background jobs are processed by 2 queue workers:
- Max execution time: 1 hour
- Max retries: 3
- Sleep between jobs: 3 seconds

## Monitoring & Logs

### View Logs

```bash
# All logs
docker-compose logs -f

# Specific service
docker-compose logs -f app

# Inside container
docker-compose exec app tail -f /var/www/html/storage/logs/laravel.log
```

### Log Locations

- Application logs: `/var/www/html/storage/logs/laravel.log`
- Queue worker logs: `/var/www/html/storage/logs/worker.log`
- Scheduler logs: `/var/www/html/storage/logs/scheduler.log`
- Nginx access: `/var/log/nginx/access.log`
- Nginx error: `/var/log/nginx/error.log`

## Security

### Implemented Security Measures

1. **Non-root user**: Application runs as `laravel:laravel` (UID 1000)
2. **Security headers**: X-Frame-Options, X-XSS-Protection, X-Content-Type-Options
3. **Sensitive file blocking**: `.env`, `.git`, hidden files denied
4. **Opcache validation**: Disabled in production for security
5. **Read-only root filesystem**: Storage/cache are mounted volumes

### Best Practices

- Use strong `APP_KEY` and `JWT_SECRET`
- Never set `APP_DEBUG=true` in production
- Use environment variables for secrets (never commit `.env`)
- Enable HTTPS/SSL (Dokploy handles via Traefik)
- Regularly update base images (`docker-compose pull`)

## Troubleshooting

### Container won't start

```bash
# Check logs
docker-compose logs app

# Common issues:
# - Database not ready: Increase healthcheck timeout
# - Missing APP_KEY: Generate with `php artisan key:generate`
# - Permission errors: Check storage/bootstrap/cache ownership
```

### Database connection errors

```bash
# Verify database is running
docker-compose ps postgres

# Test connection
docker-compose exec app nc -zv postgres 5432

# Check credentials in .env
docker-compose exec app env | grep DB_
```

### Performance issues

```bash
# Check resource usage
docker stats

# Verify OPcache is enabled
docker-compose exec app php -i | grep opcache

# Clear Laravel caches
docker-compose exec app su-exec laravel php artisan cache:clear
docker-compose exec app su-exec laravel php artisan config:clear
```

### Queue jobs not processing

```bash
# Check worker status
docker-compose exec app supervisorctl status laravel-queue:*

# Restart workers
docker-compose exec app supervisorctl restart laravel-queue:*

# View worker logs
docker-compose exec app tail -f /var/www/html/storage/logs/worker.log
```

## Maintenance

### Update Application

```bash
# Pull latest code (in Dokploy, trigger new deployment)
git pull origin main

# Rebuild image
docker-compose build

# Restart with new image
docker-compose up -d

# Run migrations
docker-compose exec app su-exec laravel php artisan migrate --force
```

### Database Backup

```bash
# Backup database
docker-compose exec postgres pg_dump -U hemodialise hemodialise > backup.sql

# Restore database
docker-compose exec -T postgres psql -U hemodialise hemodialise < backup.sql
```

### Clear Caches

```bash
docker-compose exec app su-exec laravel php artisan optimize:clear
docker-compose exec app su-exec laravel php artisan optimize
```

## Scaling

### Horizontal Scaling (Multiple Containers)

For load balancing across multiple instances:

1. Use external PostgreSQL (not docker-compose)
2. Use Redis for shared cache/sessions
3. Use shared file storage (S3/MinIO) for uploads
4. Deploy multiple app containers behind load balancer

### Vertical Scaling (More Resources)

Adjust in `docker-compose.yml`:

```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          cpus: '1'
          memory: 1G
```

## Support

For issues specific to:
- **Docker setup**: Check this guide and Docker logs
- **Dokploy deployment**: Consult [Dokploy documentation](https://docs.dokploy.com)
- **Application errors**: Check Laravel logs in `storage/logs/`

---

**Built with ❤️ for Healthcare Quality Management**
