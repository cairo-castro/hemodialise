<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\SafetyChecklistController;
use App\Http\Controllers\Frontend\CleaningControlController;
use App\Http\Controllers\Frontend\ChemicalDisinfectionController;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\DesktopController;
use App\Services\DeviceDetector;
use Illuminate\Http\Request;

// Main entry point - simplified routing based on device and auth
Route::get('/', function (Request $request) {
    $isMobile = DeviceDetector::isMobile($request);

    // If already authenticated, redirect to appropriate interface
    if (auth()->check()) {
        return redirect($isMobile ? '/mobile' : '/desktop');
    }

    // Not authenticated
    if ($isMobile) {
        // Mobile: go to PWA (will show login if needed)
        return redirect('/mobile');
    } else {
        // Desktop: go to centralized login
        return redirect('/login');
    }
})->name('home');

// Mobile/Ionic interface - PWA with session authentication
// Uses @guest/@auth Blade directives to show login or app
Route::get('/mobile/{any?}', function () {
    return view('mobile.app');
})->where('any', '.*')->name('mobile.spa');

// Desktop interface routes - with authentication middleware
Route::prefix('desktop')->name('desktop.')->middleware('auth')->group(function () {
    // Desktop/Preline interface - gestão
    Route::get('/', [DesktopController::class, 'index'])->name('index');
    Route::get('/preline', [DesktopController::class, 'preline'])->name('preline');
    Route::get('/simple', [DesktopController::class, 'simple'])->name('simple');
});


// Rotas de autenticação - centralizadas com Session Laravel
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login')
    ->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.post');

// NOTE: /admin route is handled by Filament automatically
// Filament registers /admin and all sub-routes with its own authentication middleware
// configured in app/Providers/Filament/AdminPanelProvider.php
// Users can access /admin directly and Filament will handle authentication
// Logout - simplified with session-only auth
Route::post('/logout', function(Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Return JSON for AJAX requests or redirect for form submissions
    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Logout realizado com sucesso',
            'csrf_token' => csrf_token() // Return new CSRF token
        ]);
    }

    return redirect('/login')->with('logout_success', true);
})->name('logout');

// CSRF Token refresh endpoint - returns a fresh CSRF token
Route::get('/csrf-token', function() {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
})->name('csrf.token');

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
