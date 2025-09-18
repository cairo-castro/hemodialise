<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChecklistController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:field_user,manager,admin')->group(function () {
        Route::apiResource('checklists', ChecklistController::class);
    });
});