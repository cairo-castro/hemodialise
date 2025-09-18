<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\Patient;
use App\Models\SafetyChecklist;
use App\Models\CleaningControl;
use App\Models\ChemicalDisinfection;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Estatísticas do dia
        $stats = [
            'checklists_today' => SafetyChecklist::whereDate('session_date', $today)->count(),
            'cleanings_today' => CleaningControl::whereDate('cleaning_date', $today)->count(),
            'disinfections_today' => ChemicalDisinfection::whereDate('disinfection_date', $today)->count(),
            'total_machines' => Machine::where('active', true)->count(),
            'total_patients' => Patient::where('active', true)->count(),
        ];

        // Atividades recentes
        $recent_activities = collect()
            ->merge(SafetyChecklist::with(['patient', 'machine', 'user'])
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($item) => [
                    'type' => 'checklist',
                    'title' => 'Checklist de Segurança',
                    'description' => "Paciente: {$item->patient->full_name} | Máquina: {$item->machine->name}",
                    'user' => $item->user->name,
                    'created_at' => $item->created_at,
                ]))
            ->merge(CleaningControl::with(['machine', 'user'])
                ->latest()
                ->take(3)
                ->get()
                ->map(fn($item) => [
                    'type' => 'cleaning',
                    'title' => 'Controle de Limpeza',
                    'description' => "Máquina: {$item->machine->name} | Tipo: {$item->cleaning_type}",
                    'user' => $item->user->name,
                    'created_at' => $item->created_at,
                ]))
            ->sortByDesc('created_at')
            ->take(8);

        // Máquinas disponíveis
        $machines = Machine::where('active', true)->get();

        return view('frontend.dashboard', compact('stats', 'recent_activities', 'machines'));
    }
}
