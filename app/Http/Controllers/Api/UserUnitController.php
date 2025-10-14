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
            $units = \App\Models\Unit::where('active', true)->get();
        } else {
            $units = $user->units()->where('active', true)->get();
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
        $request->validate([
            'unit_id' => 'required|integer|exists:units,id',
        ]);

        $user = auth()->user();
        $unitId = $request->input('unit_id');

        if (!$user->canAccessUnit($unitId)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para acessar esta unidade.',
            ], 403);
        }

        $user->switchToUnit($unitId);

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
