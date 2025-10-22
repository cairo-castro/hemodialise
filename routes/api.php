<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\CleaningChecklistController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\MachineController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');

// Rota para debug de token (remover em produção)
Route::get('/debug-token', function() {
    $token = request()->bearerToken();

    if (!$token) {
        return response()->json(['error' => 'Token não fornecido'], 400);
    }

    try {
        $user = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->authenticate();
        return response()->json([
            'valid' => true,
            'user' => $user,
            'token_payload' => \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->getPayload()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'valid' => false,
            'error' => $e->getMessage(),
            'error_class' => get_class($e)
        ]);
    }
});

// Endpoint that works with both JWT and session authentication
Route::get('/me', function() {
    // Try JWT first
    try {
        $token = request()->bearerToken();
        if ($token) {
            $user = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->authenticate();
            if ($user) {
                return response()->json(['user' => $user]);
            }
        }
    } catch (\Exception $e) {
        // JWT failed, try session
    }

    // Try session authentication
    if (auth()->check()) {
        return response()->json(['user' => auth()->user()]);
    }

    return response()->json(['error' => 'Unauthenticated'], 401);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rotas para gerenciamento de unidades do usuário
    Route::prefix('user-units')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\UserUnitController::class, 'index']);
        Route::post('/switch', [App\Http\Controllers\Api\UserUnitController::class, 'switch']);
        Route::get('/current', [App\Http\Controllers\Api\UserUnitController::class, 'current']);
    });

    Route::middleware(['role:tecnico,gestor,coordenador,supervisor,admin', 'unit.scope'])->group(function () {
        // Rotas específicas devem vir ANTES do apiResource para evitar conflitos
        Route::get('/checklists/active', [ChecklistController::class, 'active']);
        Route::patch('/checklists/{checklist}/phase', [ChecklistController::class, 'updatePhase']);
        Route::post('/checklists/{checklist}/advance', [ChecklistController::class, 'advancePhase']);
        Route::post('/checklists/{checklist}/interrupt', [ChecklistController::class, 'interrupt']);
        Route::post('/checklists/{checklist}/pause', [ChecklistController::class, 'pause']);
        Route::post('/checklists/{checklist}/resume', [ChecklistController::class, 'resume']);

        Route::apiResource('checklists', ChecklistController::class);

        Route::get('/patients', [PatientController::class, 'index']);
        Route::get('/patients/quick-search', [PatientController::class, 'quickSearch']);
        Route::post('/patients/search', [PatientController::class, 'search']);
        Route::get('/patients/{id}', [PatientController::class, 'show']);
        Route::post('/patients', [PatientController::class, 'store']);

        Route::get('/machines', [MachineController::class, 'index']);
        Route::get('/machines/available', [MachineController::class, 'available']);
        Route::get('/machines/availability', [MachineController::class, 'availability']);
        Route::post('/machines', [MachineController::class, 'store']);
        Route::get('/machines/{machine}', [MachineController::class, 'show']);
        Route::put('/machines/{machine}', [MachineController::class, 'update']);
        Route::delete('/machines/{machine}', [MachineController::class, 'destroy']);
        Route::put('/machines/{machine}/status', [MachineController::class, 'updateStatus']);
        Route::put('/machines/{machine}/toggle-active', [MachineController::class, 'toggleActive']);
        Route::put('/machines/{machine}/toggle-active', [MachineController::class, 'toggleActive']);

        // Cleaning Checklist routes
        Route::get('/cleaning-checklists/stats', [CleaningChecklistController::class, 'stats']);
        Route::apiResource('cleaning-checklists', CleaningChecklistController::class);
    });
});