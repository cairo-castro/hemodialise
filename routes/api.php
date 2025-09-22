<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\PatientController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

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

    // Rotas para toggle de views
    Route::prefix('view-toggle')->group(function () {
        Route::get('/user-views', [App\Http\Controllers\ViewToggleController::class, 'getUserViews']);
        Route::post('/switch-to-mobile', [App\Http\Controllers\ViewToggleController::class, 'switchToMobile']);
        Route::post('/switch-to-desktop', [App\Http\Controllers\ViewToggleController::class, 'switchToDesktop']);
        Route::post('/switch-to-admin', [App\Http\Controllers\ViewToggleController::class, 'switchToAdmin']);
        Route::post('/set-default', [App\Http\Controllers\ViewToggleController::class, 'setDefaultView']);
    });

    Route::middleware('role:tecnico,gestor,coordenador,supervisor,admin')->group(function () {
        Route::apiResource('checklists', ChecklistController::class);

        Route::post('/patients/search', [PatientController::class, 'search']);
        Route::post('/patients', [PatientController::class, 'store']);
    });
});