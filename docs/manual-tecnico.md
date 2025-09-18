# 🔧 Manual Técnico - Sistema de Hemodiálise

## 🏗️ Arquitetura do Sistema

### Stack Tecnológico
- **Backend**: Laravel 12.x + PHP 8.3
- **Frontend**: Filament 3.0 + Alpine.js + TailwindCSS 4.0
- **Base de Dados**: SQLite (dev) / MariaDB (prod)
- **Autenticação**: JWT + Session híbrido
- **Assets**: Vite + NPM

### Estrutura de Diretórios
```
sistema-hemodialise/
├── app/
│   ├── Filament/Resources/     # Recursos do painel admin
│   ├── Http/Controllers/       # Controladores web e API
│   ├── Http/Middleware/        # Middlewares customizados
│   ├── Models/                 # Modelos Eloquent
│   └── Providers/              # Service Providers
├── database/
│   ├── migrations/             # Migrações do banco
│   ├── seeders/               # Seeders para dados teste
│   └── factories/             # Factory classes
├── resources/
│   ├── views/                 # Templates Blade
│   ├── js/                    # JavaScript (Alpine.js)
│   └── css/                   # Estilos CSS
├── routes/
│   ├── web.php                # Rotas web
│   └── api.php                # Rotas API
└── docs/                      # Documentação
```

## 🔐 Sistema de Autenticação

### Arquitetura Dual
O sistema implementa autenticação híbrida:

1. **Session-based**: Para Filament admin panel
2. **JWT-based**: Para API e interface móvel

### Fluxo de Login
```
Login Centralizado (/login)
├── JavaScript detecta tipo de usuário
├── admin/manager → /admin-bridge → Laravel Session → Filament
└── field_user → /mobile → JWT validation → PWA
```

### Bridge System
O `/admin-bridge` converte tokens JWT em sessões Laravel:
```php
// AdminController@bridge
$user = JWTAuth::setToken($token)->authenticate();
auth()->login($user);
return redirect('/admin');
```

### Guards Configurados
```php
// config/auth.php
'guards' => [
    'web' => ['driver' => 'session'],
    'api' => ['driver' => 'jwt'],
],
```

## 🏗️ Modelos de Dados

### Modelo de Domínio
```
Unit (Unidade)
├── hasMany → Users
├── hasMany → Machines
└── hasMany → Patients

Machine (Máquina)
├── belongsTo → Unit
├── hasMany → SafetyChecklists
├── hasMany → CleaningControls
└── hasMany → ChemicalDisinfections

Patient (Paciente)
├── belongsTo → Unit
└── hasMany → SafetyChecklists

User (Usuário)
├── belongsTo → Unit
├── hasMany → SafetyChecklists (created)
├── hasMany → CleaningControls (created)
└── hasMany → ChemicalDisinfections (created)
```

### Principais Entidades

#### User
```php
// Roles: admin, manager, field_user
protected $fillable = [
    'name', 'email', 'password', 'role', 'unit_id'
];

// Métodos de conveniência
public function isAdmin(): bool
public function isManager(): bool
public function isFieldUser(): bool
```

#### SafetyChecklist
```php
// 8 verificações obrigatórias pré-diálise
protected $casts = [
    'checklist_data' => 'array', // JSON dos 8 itens
    'session_date' => 'date',
];
```

## 🌐 Rotas e Endpoints

### Rotas Web (web.php)
```php
// Autenticação centralizada
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin']);

// Bridge para Filament
Route::get('/admin-bridge', [AdminController::class, 'bridge']);
Route::post('/admin-login', [AdminController::class, 'login']);

// Interface móvel (sem middleware inicial)
Route::get('/mobile', [MobileController::class, 'index']);

// Logout personalizado para Filament
Route::post('/admin/logout', function(Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    return redirect('/login?logout=true');
})->name('filament.admin.auth.logout');
```

### API Endpoints (api.php)
```php
// Autenticação
POST /api/login              # Login JWT
GET  /api/me                 # Validação token
POST /api/logout             # Invalidar token

// Recursos (auth:api middleware)
POST   /api/checklists       # Criar checklist
GET    /api/checklists       # Listar checklists
GET    /api/checklists/{id}  # Ver checklist
PUT    /api/checklists/{id}  # Atualizar checklist
DELETE /api/checklists/{id}  # Deletar checklist
```

## 🛠️ Middlewares Customizados

### JwtAuthMiddleware
Valida tokens JWT para rotas web:
```php
public function handle(Request $request, Closure $next)
{
    // Para API: Bearer token no header
    if ($request->expectsJson()) {
        return $this->handleApiRequest($request, $next);
    }

    // Para Web: token no cookie jwt_token
    $token = $request->cookie('jwt_token');
    if (!$token) return redirect('/login');

    $user = JWTAuth::setToken($token)->authenticate();
    auth()->setUser($user);

    return $next($request);
}
```

### FilamentAccessMiddleware
Controla acesso ao painel Filament:
```php
public function handle(Request $request, Closure $next)
{
    $user = $request->user();

    if (!$user) return redirect('/login');

    // Apenas admin e manager no Filament
    if ($user->isFieldUser()) {
        return redirect('/mobile');
    }

    return $next($request);
}
```

## 📱 Interface Móvel (PWA)

### Arquitetura Frontend
- **Alpine.js**: Reatividade e componentes
- **TailwindCSS**: Estilização responsiva
- **Service Worker**: Funcionalidade offline

### Componentes JavaScript

#### loginApp()
Gerencia autenticação na página de login:
```javascript
window.loginApp = function() {
    return {
        async login() {
            const response = await fetch('/api/login', {
                method: 'POST',
                body: JSON.stringify(this.loginForm)
            });

            if (response.ok) {
                const data = await response.json();
                localStorage.setItem('token', data.token);
                document.cookie = `jwt_token=${data.token}`;
                this.redirectUser(data.user);
            }
        },

        redirectUser(user) {
            if (user.role === 'field_user') {
                window.location.href = '/mobile';
            } else {
                window.location.href = '/admin-bridge';
            }
        }
    }
}
```

#### mobileApp()
Gerencia interface móvel:
```javascript
window.mobileApp = function() {
    return {
        async checkAuth() {
            const token = localStorage.getItem('token');
            if (token) {
                const response = await fetch('/api/me', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                if (response.ok) {
                    this.user = await response.json();
                }
            }
        },

        async submitChecklist() {
            await fetch('/api/checklists', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.checklistForm)
            });
        }
    }
}
```

## 🗄️ Base de Dados

### Migrações Principais

#### users
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->enum('role', ['admin', 'manager', 'field_user']);
    $table->foreignId('unit_id')->nullable();
    $table->timestamps();
});
```

#### safety_checklists
```php
Schema::create('safety_checklists', function (Blueprint $table) {
    $table->id();
    $table->foreignId('machine_id')->constrained();
    $table->foreignId('patient_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->date('session_date');
    $table->enum('shift', ['morning', 'afternoon', 'night']);
    $table->json('checklist_data'); // 8 itens obrigatórios
    $table->text('observations')->nullable();
    $table->decimal('completion_percentage', 5, 2);
    $table->timestamps();
});
```

### Seeders
```php
// DatabaseSeeder.php
$this->call([
    UnitSeeder::class,      // Unidades base
    UserSeeder::class,      // Usuários padrão
    MachineSeeder::class,   // Máquinas exemplo
    PatientSeeder::class,   // Pacientes teste
]);
```

## 🔧 Configurações Importantes

### JWT Configuration
```php
// config/jwt.php
'ttl' => 60 * 24,           // Token válido por 24 horas
'refresh_ttl' => 60 * 24 * 7, // Refresh por 7 dias
'algo' => 'HS256',
'secret' => env('JWT_SECRET'),
```

### Filament Configuration
```php
// AdminPanelProvider.php
return $panel
    ->default()
    ->id('admin')
    ->path('admin')
    ->login(false)          // Login customizado
    ->brandName('Sistema Hemodiálise')
    ->colors(['primary' => Color::Blue])
    ->sidebarCollapsibleOnDesktop()
    ->spa()
    ->authMiddleware([
        Authenticate::class,
        FilamentAccessMiddleware::class,
    ]);
```

## 🚀 Deploy e Produção

### Comandos de Build
```bash
# Otimização para produção
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build

# Configuração de banco
php artisan migrate --force
php artisan db:seed --force
```

### Variáveis de Ambiente (.env)
```env
# Aplicação
APP_NAME="Sistema Hemodiálise"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hemodialise.com.br

# Banco de Dados (Produção)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hemodialise
DB_USERNAME=hemodialise_user
DB_PASSWORD=senha_segura

# JWT
JWT_SECRET=chave_secreta_jwt_muito_longa_e_segura

# Sessão
SESSION_DRIVER=database
SESSION_LIFETIME=1440

# Cache
CACHE_DRIVER=database
```

### Servidor Web (Nginx)
```nginx
server {
    listen 80;
    server_name hemodialise.com.br;
    root /var/www/hemodialise/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🧪 Testes

### Estrutura de Testes
```php
// tests/Feature/AuthTest.php
public function test_jwt_login_returns_token()
{
    $user = User::factory()->create();

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['token', 'user']);
}
```

### Executar Testes
```bash
php artisan test                    # Todos os testes
php artisan test --filter=AuthTest  # Teste específico
php artisan test --coverage        # Com cobertura
```

## 🐛 Debug e Monitoramento

### Logs
```bash
# Logs em tempo real
tail -f storage/logs/laravel.log

# Laravel Pail (logs estruturados)
php artisan pail

# Logs específicos
php artisan log:show --name=daily
```

### Debugging
```php
// Debug de queries
DB::enableQueryLog();
// ... código ...
dd(DB::getQueryLog());

// Debug JWT
try {
    $user = JWTAuth::parseToken()->authenticate();
} catch (JWTException $e) {
    Log::error('JWT Error: ' . $e->getMessage());
}
```

## 🔒 Segurança

### Verificações Importantes
1. **CSRF Protection**: Ativo para rotas web
2. **Rate Limiting**: Configurado para APIs
3. **XSS Protection**: Headers de segurança
4. **SQL Injection**: Eloquent ORM previne
5. **Authentication**: JWT com expiração

### Auditoria
Todas as ações são logadas:
```php
// Automático via Eloquent events
User::created(function ($user) {
    Log::info('User created', ['user_id' => $user->id]);
});
```

---

**Sistema de Hemodiálise - Documentação Técnica v1.0**
*Atualizado em: Janeiro 2025*