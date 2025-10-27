<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\SafetyChecklistController;
use App\Http\Controllers\Frontend\CleaningControlController;
use App\Http\Controllers\Frontend\ChemicalDisinfectionController;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\JwtAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DesktopController;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

// Main entry point - redirect based on authentication and device
Route::get('/', function (Request $request) {
    // Se não estiver autenticado, vai para login
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    
    // Se estiver autenticado, detecta dispositivo e redireciona
    $userAgent = $request->header('User-Agent', '');
    $isMobile = stripos($userAgent, 'Mobile') !== false || 
               stripos($userAgent, 'Android') !== false || 
               stripos($userAgent, 'iPhone') !== false;
    
    return redirect($isMobile ? '/mobile' : '/desktop');
})->name('home');

// Mobile/Ionic interface - única interface mobile
// NOTE: No auth middleware here - the mobile SPA handles authentication internally via JWT
Route::prefix('mobile')->name('mobile.')->group(function () {
    // Catch-all route for SPA navigation (Vue Router handles all routes)
    // This must be a single route that serves the SPA for ALL paths
    // The mobile app checks authentication on load and shows login if needed
    Route::get('/{any?}', function () {
        return view('mobile.app');
    })->where('any', '.*')->name('spa');
});

// Desktop interface routes - with authentication middleware
Route::prefix('desktop')->name('desktop.')->middleware('auth')->group(function () {
    // Desktop/Preline interface - gestão
    Route::get('/', [DesktopController::class, 'index'])->name('index');
    Route::get('/preline', [DesktopController::class, 'preline'])->name('preline');
    Route::get('/simple', [DesktopController::class, 'simple'])->name('simple');
});


// Rotas de autenticação
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/auth/check', [JwtAuthController::class, 'check']);
Route::get('/admin-bridge', [AdminController::class, 'bridge']);
Route::post('/admin-login', [AdminController::class, 'login']);
Route::get('/admin-login', function() {
    return redirect('/admin-bridge');
});
// Handle direct access to admin route
Route::get('/admin', function() {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    
    // If user is logged in but doesn't have admin access, redirect appropriately
    $user = auth()->user();
    if (!$user->canAccessAdmin()) {
        $userAgent = request()->header('User-Agent', '');
        $isMobile = stripos($userAgent, 'Mobile') !== false || 
                   stripos($userAgent, 'Android') !== false || 
                   stripos($userAgent, 'iPhone') !== false;

        $redirectUrl = $isMobile ? '/mobile' : '/desktop';
        return redirect($redirectUrl)->with('error', 'Acesso negado. Apenas usuários globais podem acessar o painel administrativo.');
    }
    
    // If user has admin access, they should use the bridge mechanism
    return redirect('/admin-bridge');
})->middleware('auth');

// Custom admin login route with specific POST handling
Route::match(['get', 'post'], '/admin/login', function() {
    // If already logged in and have admin access, redirect to bridge
    if (auth()->check() && auth()->user()->canAccessAdmin()) {
        return redirect('/admin-bridge');
    }
    
    // Otherwise, redirect to main login
    return redirect('/login');
});
Route::post('/logout', function(Request $request) {
    try {
        // Invalidar token JWT se existir
        $token = $request->bearerToken() ?: $request->header('Authorization');
        if ($token) {
            JWTAuth::setToken(str_replace('Bearer ', '', $token))->invalidate();
        }
    } catch (\Exception $e) {
        // Ignore token errors on logout
    }

    // Limpar sessão Laravel
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json(['message' => 'Logout realizado com sucesso']);
})->name('logout');

Route::get('/logout', function(Request $request) {
    // Logout via GET - limpar completamente a sessão
    auth()->logout();
    $request->session()->flush(); // Remove todos os dados da sessão
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Hard redirect sem parâmetros para forçar reload completo
    return redirect('/login')->with('logout_success', true);
});

// Override Filament logout route
Route::post('/admin/logout', function(Request $request) {
    auth()->logout();
    $request->session()->flush(); // Remove todos os dados da sessão
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Hard redirect sem parâmetros para forçar reload completo
    return redirect('/login')->with('logout_success', true);
})->name('filament.admin.auth.logout');

// Interface performance testing route
Route::get('/performance-test', function() {
    return view('test.performance', [
        'timestamp' => microtime(true),
        'memory' => memory_get_usage(true),
        'peak_memory' => memory_get_peak_usage(true)
    ]);
})->name('performance.test');

// Offline fallback page
Route::get('/offline', function() {
    return view('offline', [
        'title' => 'Sistema Offline'
    ]);
})->name('offline');

// Rotas protegidas (frontend público para operadores)
Route::middleware(['auth'])->group(function () {

    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/frontend/dashboard', [DashboardController::class, 'index'])->name('frontend.dashboard');

    // Checklist de Segurança
    Route::prefix('safety')->name('frontend.safety.')->group(function () {
        Route::get('/', [SafetyChecklistController::class, 'index'])->name('index');
        Route::get('/create', [SafetyChecklistController::class, 'create'])->name('create');
        Route::post('/', [SafetyChecklistController::class, 'store'])->name('store');
        Route::get('/{checklist}', [SafetyChecklistController::class, 'show'])->name('show');
        Route::get('/{checklist}/edit', [SafetyChecklistController::class, 'edit'])->name('edit');
        Route::put('/{checklist}', [SafetyChecklistController::class, 'update'])->name('update');
    });

    // Controle de Limpeza
    Route::prefix('cleaning')->name('frontend.cleaning.')->group(function () {
        Route::get('/', [CleaningControlController::class, 'index'])->name('index');
        Route::get('/create', [CleaningControlController::class, 'create'])->name('create');
        Route::post('/', [CleaningControlController::class, 'store'])->name('store');
        Route::get('/{cleaning}', [CleaningControlController::class, 'show'])->name('show');
        Route::get('/{cleaning}/edit', [CleaningControlController::class, 'edit'])->name('edit');
        Route::put('/{cleaning}', [CleaningControlController::class, 'update'])->name('update');
    });

    // Desinfecção Química
    Route::prefix('disinfection')->name('frontend.disinfection.')->group(function () {
        Route::get('/', [ChemicalDisinfectionController::class, 'index'])->name('index');
        Route::get('/create', [ChemicalDisinfectionController::class, 'create'])->name('create');
        Route::post('/', [ChemicalDisinfectionController::class, 'store'])->name('store');
        Route::get('/{disinfection}', [ChemicalDisinfectionController::class, 'show'])->name('show');
        Route::get('/{disinfection}/edit', [ChemicalDisinfectionController::class, 'edit'])->name('edit');
        Route::put('/{disinfection}', [ChemicalDisinfectionController::class, 'update'])->name('update');
    });
});
