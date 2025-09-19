<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\PatientController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:field_user,manager,admin')->group(function () {
        Route::apiResource('checklists', ChecklistController::class);

        Route::post('/patients/search', [PatientController::class, 'search']);
        Route::post('/patients', [PatientController::class, 'store']);
    });
});