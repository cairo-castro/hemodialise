# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sistema de Hemodiálise - A Laravel-based hemodialysis clinic management system optimized for mobile use. The system digitizes manual spreadsheet processes used by the State of Maranhão Health Department, providing safety checklists, equipment management, and procedure tracking for hemodialysis centers.

## Development Commands

### Setup and Installation
```bash
# Quick setup (automated)
./setup.sh

# Manual installation
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build

# Start development server
php artisan serve
# OR use the automated script that handles port conflicts
./run.sh
```

### Development Workflow
```bash
# Install dependencies
composer install && npm install

# Database operations
php artisan migrate:fresh --seed    # Reset DB with sample data
php artisan migrate                  # Run pending migrations

# Asset compilation
npm run dev                         # Development (watch mode)
npm run build                       # Production build

# Testing
composer test                       # Run test suite
php artisan test                    # Alternative test command

# Code quality
vendor/bin/pint                     # Code formatting (Laravel Pint)

# Filament commands
php artisan make:filament-resource ModelName --generate
php artisan make:filament-user      # Create admin user
php artisan filament:upgrade        # Upgrade Filament components
```

### Monitoring and Debugging
```bash
# View logs
tail -f storage/logs/laravel.log
php artisan pail                    # Real-time logs (Laravel Pail)

# Clear caches
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Architecture Overview

### Dual Authentication System
The system implements a sophisticated dual authentication architecture:

1. **Session-based Authentication**: For Filament admin panel (admin/manager users)
2. **JWT Authentication**: For API endpoints and mobile interface (field users)

**Authentication Flow**:
- Users login via centralized `/login` page
- JavaScript determines redirect based on user role:
  - `admin`/`manager` → `/admin-bridge` → Filament admin panel
  - `field_user` → `/mobile` PWA interface

**Bridge System**: `/admin-bridge` converts JWT tokens to Laravel sessions for Filament compatibility, as Filament requires session-based auth.

### User Roles and Access Control
- **admin**: Full system access, user management
- **manager**: Clinic management, reports, oversight
- **field_user**: Mobile interface only, checklist entry

Role-based middleware (`FilamentAccessMiddleware`) prevents field users from accessing admin interface.

### Mobile-First PWA Architecture
- **Primary Interface**: Mobile Progressive Web App for field technicians
- **Admin Interface**: Filament-based admin panel for managers/admins
- **Responsive Design**: Single codebase serves both mobile and desktop
- **Offline Capability**: Service worker for offline functionality
- **Real-Time Sync**: Lightweight polling system for automatic data synchronization (see [Data Sync Documentation](docs/DATA_SYNC.md))

### Core Domain Models
- **Units**: Hemodialysis clinic locations with multiple machines
- **Machines**: Individual hemodialysis equipment units
- **Patients**: Patient records with medical information
- **Safety Checklists**: Pre-dialysis safety verification (8 mandatory checks)
- **Cleaning Controls**: Equipment cleaning procedures (daily/weekly/monthly)
- **Chemical Disinfections**: Chemical disinfection process tracking

**Key Relationships**:
- Unit → hasMany → Users, Machines
- Machine → hasMany → SafetyChecklists, CleaningControls, ChemicalDisinfections
- Patient → hasMany → SafetyChecklists
- User → hasMany → all procedure records (for audit trail)

### Frontend Technology Stack
- **Alpine.js**: Reactive components for mobile interface (`loginApp`, `mobileApp` functions)
- **TailwindCSS 4.0**: Utility-first styling
- **Vite**: Asset bundling and hot reload
- **Blade Templates**: Server-side rendering for Filament

### API Structure
RESTful API with JWT authentication:
- `POST /api/login` - JWT authentication
- `GET /api/me` - User profile validation
- `POST /api/logout` - Token invalidation
- `POST /api/checklists` - Safety checklist submission

Role-based API access control ensures field users can only access appropriate endpoints.

### Database Architecture
- **Development**: SQLite for simplicity
- **Production**: MariaDB/MySQL configured
- **Audit Trail**: All procedures track user, timestamp, machine
- **Soft Deletes**: Implemented for data integrity

### Key Configuration Files
- `config/auth.php`: Dual guard configuration (session + JWT)
- `config/jwt.php`: JWT settings and token management
- `app/Providers/Filament/AdminPanelProvider.php`: Filament panel configuration
- `routes/web.php`: Web routes with authentication logic
- `routes/api.php`: API routes with JWT middleware

### Middleware Architecture
- `FilamentAccessMiddleware`: Role-based Filament access control
- `JwtAuthMiddleware`: JWT token validation for web routes
- `auth:api`: JWT authentication for API routes
- Custom logout handling to prevent redirect loops

### Mobile Interface Implementation
The mobile interface (`/mobile`) uses client-side authentication:
1. Page loads without server-side auth requirements
2. JavaScript `checkAuth()` validates JWT token via API
3. Shows login form if unauthenticated
4. Shows mobile app interface if authenticated

This approach prevents 401 errors during browser redirects while maintaining security through API validation.

### Security Considerations
- JWT tokens stored in localStorage and HTTP-only cookies
- CSRF protection for web routes
- Role-based access control at multiple layers
- Audit trails for all medical procedures
- Logout parameter (`?logout=true`) prevents authentication loops

### Production Deployment Notes
- Configure document root to `public/` directory
- Run `composer install --optimize-autoloader --no-dev`
- Execute caching commands: `config:cache`, `route:cache`, `view:cache`
- Update `.env` for production database credentials
- Ensure proper file permissions for `storage/` and `bootstrap/cache/`

### Production Server Access
**Server Details:**
- Host: `212.85.1.175`
- User: `root`
- Password: `ClinQua-Hosp@2025`
- Deployment: Dokploy (Docker-based)
- Domain: `qualidadehd.direcaoclinica.com.br`

**SSH Access:**
```bash
# Access server via SSH
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175
```

**Docker Container Management:**
```bash
# List running containers
docker ps | grep qualidade

# Current container name pattern: qualidade-qualidadehd-bue1bg.1.[HASH]
# Find current container:
CONTAINER_ID=$(docker ps --filter "name=qualidade-qualidadehd" --format "{{.Names}}" | head -1)

# Access container shell
docker exec -it $CONTAINER_ID sh

# View Laravel logs
docker exec $CONTAINER_ID tail -100 storage/logs/laravel-$(date +%Y-%m-%d).log

# View Nginx access logs
docker exec $CONTAINER_ID tail -100 /var/log/nginx/access.log

# View Nginx error logs
docker exec $CONTAINER_ID tail -100 /var/log/nginx/error.log

# Check container health
docker inspect $CONTAINER_ID | grep -A 10 Health

# View container logs
docker logs $CONTAINER_ID --tail 100 -f
```

**Useful Production Commands:**
```bash
# One-liner to access latest Laravel log
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175 \
  "docker exec \$(docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1) \
  tail -200 storage/logs/laravel-\$(date +%Y-%m-%d).log"

# Check application status
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175 \
  "docker ps --filter 'name=qualidade-qualidadehd'"

# View real-time logs
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175 \
  "docker logs \$(docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1) -f"
```

**Deployment Process:**
1. Push code to `main` branch on GitHub
2. Dokploy detects changes automatically
3. Triggers Docker rebuild using `Dockerfile.production`
4. New container is created and deployed
5. Old container is stopped and removed
6. Traffic is routed to new container via Traefik

**Database Access:**
```bash
# MariaDB container
docker exec -it qualidade-productionqualidade-l2xbgb.1.yglsvypy0m21nmg1d0w7bhbdc mysql -u root -p

# Database credentials are stored in Dokploy environment variables
```

### Testing Strategy
- PHPUnit configured for backend testing
- Filament resources auto-generated with standard CRUD operations
- Database seeders provide realistic test data
- Factory classes for model generation

This system prioritizes mobile usability while maintaining comprehensive administrative capabilities through its dual-interface architecture.