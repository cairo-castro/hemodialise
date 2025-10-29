<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnitScopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Middleware que filtra dados automaticamente por unidade,
     * exceto para usuários com acesso global.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Se usuário tem acesso global, usa a unidade selecionada no dashboard
        if ($user->hasGlobalAccess()) {
            // Prioridade: unit_id na query > X-Unit-Id no header > current_unit_id do usuário
            $selectedUnitId = $request->input('unit_id')
                ?? $request->header('X-Unit-Id')
                ?? $user->current_unit_id;

            if ($selectedUnitId) {
                // Usuário global selecionou uma unidade específica
                $request->merge(['scoped_unit_id' => $selectedUnitId]);
            } else {
                // Acesso global sem unidade selecionada - retorna vazio
                // Força seleção de unidade no dashboard
                $request->merge(['scoped_unit_id' => null, 'has_global_access' => true]);
            }
        } else {
            // Usuário de unidade - usar unidade atualmente selecionada
            $activeUnit = $user->getActiveUnit();
            $scopedUnitId = $activeUnit ? $activeUnit->id : $user->unit_id;
            
            $request->merge(['scoped_unit_id' => $scopedUnitId]);
        }

        return $next($request);
    }
}
