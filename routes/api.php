<?php

use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\CleaningChecklistController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\DataSyncController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

// Endpoint for user info - session authentication only
Route::get('/me', function() {
    \Log::info('[API /me] START', [
        'auth_check' => auth()->check(),
        'session_id' => session()->getId(),
        'has_session' => session()->has('login_web_' . sha1(config('auth.guards.web.provider'))),
        'cookie_session' => request()->cookie(config('session.cookie')),
    ]);

    if (auth()->check()) {
        $user = auth()->user();
        $user->load('unit', 'units');

        \Log::info('[API /me] Authentication SUCCESS', ['user' => $user->email]);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'unit_id' => $user->unit_id,
                'unit' => $user->unit ? [
                    'id' => $user->unit->id,
                    'name' => $user->unit->name,
                ] : null,
                'units' => $user->units->map(fn($unit) => [
                    'id' => $unit->id,
                    'name' => $unit->name,
                ]),
                'created_at' => $user->created_at ? $user->created_at->toISOString() : null
            ]
        ]);
    }

    \Log::warning('[API /me] Authentication FAILED - No valid session');
    return response()->json([
        'success' => false,
        'error' => 'Unauthenticated'
    ], 401);
});

// User units routes - session authentication
Route::prefix('user-units')->middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\UserUnitController::class, 'index']);
    Route::post('/switch', [App\Http\Controllers\Api\UserUnitController::class, 'switch']);
    Route::get('/current', [App\Http\Controllers\Api\UserUnitController::class, 'current']);
});

// API routes with session authentication
Route::middleware('auth')->group(function () {

    // Dashboard routes - available to all authenticated users
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/sessions-by-shift', [DashboardController::class, 'sessionsByShift']);
    Route::get('/dashboard/recent-activity', [DashboardController::class, 'recentActivity']);

    // Profile routes - available to all authenticated users
    Route::put('/profile/update', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    // Data sync endpoint - lightweight polling system for real-time updates
    Route::get('/sync/check-updates', [DataSyncController::class, 'checkUpdates']);
    Route::post('/sync/invalidate-cache', [DataSyncController::class, 'invalidateCache']);

    // Notification routes - available to all authenticated users
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/poll', [NotificationController::class, 'poll']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    Route::middleware(['role:tecnico,gestor,coordenador,supervisor,admin', 'unit.scope'])->group(function () {
        // Rotas específicas devem vir ANTES do apiResource para evitar conflitos
        Route::get('/checklists/active', [ChecklistController::class, 'active']);
        Route::get('/checklists/recent', [ChecklistController::class, 'recent']);
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
        Route::patch('/patients/{id}/toggle-active', [PatientController::class, 'toggleActive']);
        Route::patch('/patients/{id}/status', [PatientController::class, 'updateStatus']);

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