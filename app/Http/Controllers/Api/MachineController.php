<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\JsonResponse;

class MachineController extends Controller
{
    public function available(): JsonResponse
    {
        try {
            $machines = Machine::where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'identifier', 'description']);

            return response()->json([
                'success' => true,
                'machines' => $machines
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar máquinas disponíveis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}