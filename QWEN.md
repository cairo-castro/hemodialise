# Sistema de Hemodiálise - QWEN Context

## 🏥 Project Overview

The Sistema de Hemodiálise is a digital platform developed for modernizing control processes in hemodialysis clinics, replacing manual spreadsheets used by the Maranhão State Health Department. The system is built with a modern web stack featuring Laravel backend, multiple frontend interfaces (Ionic for mobile, Preline/Tailwind for desktop), and JWT-based authentication.

### Key Features
- **Mobile-First**: Interface optimized for smartphones and tablets
- **PWA**: Works offline as a native app
- **Dual Interface**: Admin panel for management + mobile app for field operations
- **Security**: JWT + Session authentication with full audit trail
- **Compliance**: Based on standards from the Maranhão State Health Department

## 🚀 Tech Stack

### Backend
- **Laravel 12**: Main PHP framework
- **Filament 3**: Admin panel framework
- **JWT Authentication**: API authentication via tymon/jwt-auth
- **SQLite/MariaDB**: Database storage

### Frontend
- **Ionic 8 + Vue 3**: Mobile interface and PWA
- **Preline UI**: Desktop interface
- **TailwindCSS 4**: Styling framework
- **Alpine.js**: Reactive components
- **Vite**: Build tool and dev server

### DevOps
- **Composer**: PHP dependency management
- **NPM**: JavaScript dependency management
- **Git**: Version control

## 🏗️ Architecture

The system features a sophisticated multi-interface architecture:

### 1. Smart Routing System
- Automatically detects device type and user role
- Routes users to the most appropriate interface
- Supports mobile (Ionic), desktop (Preline), and admin (Filament) interfaces

### 2. Authentication System
- Dual authentication system (JWT + Session)
- Role-based access control (admin, gestor, coordenador, supervisor, tecnico)
- Unit-scoped access for multi-tenant operations

### 3. Frontend Interfaces
- **Mobile Interface** (Ionic): For field operations, PWA capable
- **Desktop Interface** (Preline): For management tasks
- **Admin Panel** (Filament): Full system administration

## 📁 Directory Structure

```
sistema-hemodialise/
├── app/                    # Laravel application code
│   ├── Http/              # Controllers, middleware, requests
│   ├── Models/            # Eloquent models
│   └── ...
├── ionic-frontend/        # Legacy Ionic frontend (not currently used)
├── resources/             # Frontend assets
│   ├── js/                # JavaScript files
│   │   ├── mobile/        # Mobile-specific code
│   │   ├── desktop/       # Desktop-specific code
│   │   └── shared/        # Shared components
│   └── css/               # CSS files
├── routes/                # Route definitions
├── config/                # Laravel configuration
├── database/              # Migrations, seeders, factories
└── public/                # Public assets and index.php
```

## 🔧 Building and Running

### Installation
```bash
# Clone the repository
git clone https://github.com/cairo-castro/hemodialise.git
cd hemodialise

# Execute the automated installation script
chmod +x setup.sh
./setup.sh

# Or manual installation:
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan migrate --force
php artisan db:seed --force
```

### Development Mode
```bash
# For mobile development
npm run dev:mobile

# For desktop development  
npm run dev:desktop

# For full development with concurrent processes
composer run dev

# For simple development setup
composer run dev-simple
```

### Running the Application
```bash
# Start the development server
php artisan serve

# Or with specific host and port
php artisan serve --host=0.0.0.0 --port=8000
```

## 🎯 Default Credentials

- **Admin**: admin@hemodialise.com / admin123
- **Technical Staff**: tecnico.joao@hemodialise.com / tecnico123

## 📊 Main Modules

### 1. Unit Management
- Clinic/center registration
- Team management
- Unit-specific configurations

### 2. Machine Management
- Equipment registration
- Maintenance control
- Operational status tracking

### 3. Patient Management
- Digital medical records
- Session history
- Medical information tracking

### 4. Safety Checklist
- 8 mandatory safety checks
- Shift-based control
- Compliance calculation

### 5. Cleaning Control
- Daily/weekly/monthly procedures
- Product registration
- Digital signatures

### 6. Chemical Disinfection
- Process control
- Concentration records
- Batch traceability

## 🛣️ Key Routes

### API Routes
- `/api/login` - Authentication endpoint
- `/api/me` - User profile (dual auth)
- `/api/checklists` - Safety checklists
- `/api/patients` - Patient management
- `/api/machines` - Machine management

### Web Routes
- `/` - Smart routing system
- `/login` - Login interface
- `/mobile` - Mobile/PWA interface
- `/desktop` - Desktop interface
- `/admin` - Admin panel (Filament)

## 🔐 Authentication System

The application implements a sophisticated dual authentication system:
- JWT tokens for API endpoints
- Session authentication for web interfaces
- Role-based access control
- Unit-scoped operations


## 📱 Mobile Interface

The mobile interface is built with:
- Ionic 8
- Vue 3
- PWA support for offline functionality
- Touch-optimized interface
- Real-time synchronization when online

## 💻 Desktop Interface

The desktop interface features:
- Preline UI components
- Tailwind CSS styling
- Responsive design
- Management-focused workflows

## 🧰 Development Commands

### Composer Scripts
- `composer run dev` - Full development with concurrent processes
- `composer run dev-simple` - Basic development server
- `composer run dev-reset` - Reset development environment
- `composer install` - Install PHP dependencies
- `composer run dev-setup` - Complete dev setup

### NPM Scripts
- `npm run build` - Build all assets
- `npm run build:mobile` - Build mobile assets
- `npm run build:desktop` - Build desktop assets
- `npm run dev` - Development server
- `npm run dev:mobile` - Mobile development
- `npm run dev:desktop` - Desktop development

## 🧪 Testing

```bash
# Run tests
composer run test

# Or directly with Laravel:
php artisan test
```

## 💾 Database

The system supports multiple database options:
- SQLite (default for development)
- MariaDB/MySQL (production)

## 📈 Environment Configuration

Key environment files:
- `.env.example` - Example environment configuration
- `.env` - Actual environment configuration (not tracked)

Important environment variables:
- `APP_URL` - Application URL
- `DB_*` - Database connection settings
- `JWT_*` - JWT authentication settings

## 🔄 Update Process

To update the system:
1. Pull latest changes: `git pull`
2. Update dependencies: `composer install && npm install`
3. Run migrations: `php artisan migrate`
4. Clear caches: `php artisan config:clear && php artisan route:clear`
5. Rebuild assets: `npm run build`

## 🆘 Support

- **Email**: suporte@hemodialise.com
- **GitHub Issues**: Project repository
- **Documentation**: https://docs.hemodialise.com.br
- **API Docs**: https://api.hemodialise.com.br/docs

## 📝 Development Conventions

- Follow Laravel conventions for backend code
- Use Vue 3 Composition API for frontend components
- Apply Tailwind CSS utility-first methodology
- Utilize Filament conventions for admin panels
- Implement proper error handling and validation
- Write comprehensive tests for new features