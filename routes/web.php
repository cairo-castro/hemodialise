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
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

// Página principal - sempre mostra login
Route::get('/', function () {
    // Não verificar auth aqui, apenas redirecionar para login
    return redirect()->route('login');
});

// Rota mobile PWA - autenticação via JavaScript
Route::get('/mobile', [MobileController::class, 'index']);

// Rota desktop - interface de gestão
Route::get('/desktop', [App\Http\Controllers\DesktopController::class, 'index']);

// Rotas de autenticação
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/auth/check', [JwtAuthController::class, 'check']);
Route::get('/admin-bridge', [AdminController::class, 'bridge']);
Route::post('/admin-login', [AdminController::class, 'login']);
Route::get('/admin-login', function() {
    return redirect('/admin-bridge');
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
    // Logout via GET para Filament
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login?logout=true');
});

// Override Filament logout route
Route::post('/admin/logout', function(Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login?logout=true');
})->name('filament.admin.auth.logout');

// Rotas protegidas (frontend público para operadores)
Route::middleware(['auth'])->group(function () {

    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('frontend.dashboard');

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
