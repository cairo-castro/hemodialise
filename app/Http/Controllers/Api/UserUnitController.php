<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserUnitController extends Controller
{
    /**
     * Lista todas as unidades acessíveis pelo usuário autenticado
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        if ($user->hasGlobalAccess()) {
            $units = \App\Models\Unit::where('active', true)
                ->withCount('safetyChecklists')
                ->orderBy('name')
                ->get();
        } else {
            $units = $user->units()
                ->where('active', true)
                ->withCount('safetyChecklists')
                ->orderBy('name')
                ->get();
        }

        return response()->json([
            'success' => true,
            'units' => $units,
            'current_unit_id' => $user->current_unit_id ?? $user->unit_id,
        ]);
    }

    /**
     * Alterna para uma unidade específica
     */
    public function switch(Request $request): JsonResponse
    {
        \Log::info('[UserUnitController::switch] START', [
            'user_id' => auth()->id(),
            'auth_check' => auth()->check(),
            'session_id' => session()->getId(),
            'unit_id' => $request->input('unit_id'),
            'csrf_token' => $request->header('X-CSRF-TOKEN'),
            'xsrf_token' => $request->header('X-XSRF-TOKEN'),
            'has_session_cookie' => $request->hasCookie(config('session.cookie')),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if (!auth()->check()) {
            \Log::error('[UserUnitController::switch] NOT AUTHENTICATED');
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado.',
            ], 401);
        }

        $request->validate([
            'unit_id' => 'required|integer|exists:units,id',
        ]);

        $user = auth()->user();
        $unitId = $request->input('unit_id');

        if (!$user->canAccessUnit($unitId)) {
            \Log::warning('[UserUnitController::switch] Access denied', [
                'user_id' => $user->id,
                'unit_id' => $unitId,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para acessar esta unidade.',
            ], 403);
        }

        $result = $user->switchToUnit($unitId);

        \Log::info('[UserUnitController::switch] SUCCESS', [
            'user_id' => $user->id,
            'unit_id' => $unitId,
            'switch_result' => $result,
            'current_unit_id' => $user->current_unit_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unidade alterada com sucesso.',
            'current_unit' => $user->currentUnit ?? $user->unit,
        ]);
    }

    /**
     * Retorna a unidade atualmente ativa
     */
    public function current(): JsonResponse
    {
        $user = auth()->user();
        $activeUnit = $user->getActiveUnit();

        return response()->json([
            'success' => true,
            'unit' => $activeUnit,
        ]);
    }
}
