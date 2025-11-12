<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SafetyChecklist;

class CompleteAllChecklists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checklists:complete-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Completa todos os checklists pendentes (útil para ambiente de desenvolvimento)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando checklists ativos...');

        $activeChecklists = SafetyChecklist::whereIn('current_phase', [
            'pre_dialysis',
            'during_session',
            'post_dialysis'
        ])
        ->where('is_interrupted', false)
        ->whereNull('interrupted_at')
        ->get();

        if ($activeChecklists->count() === 0) {
            $this->info('✓ Não há checklists ativos para completar.');
            return 0;
        }

        $this->info("Encontrados {$activeChecklists->count()} checklists ativos.");

        if (!$this->confirm('Deseja completar todos estes checklists?', true)) {
            $this->info('Operação cancelada.');
            return 1;
        }

        $completed = 0;
        $now = now();

        foreach ($activeChecklists as $checklist) {
            // Completar todas as fases pendentes
            if (!$checklist->pre_dialysis_completed_at) {
                $checklist->pre_dialysis_completed_at = $now;
            }
            if (!$checklist->during_session_completed_at) {
                $checklist->during_session_completed_at = $now;
            }
            if (!$checklist->post_dialysis_completed_at) {
                $checklist->post_dialysis_completed_at = $now;
            }

            // Marcar como completo
            $checklist->current_phase = 'completed';
            $checklist->save();

            // Liberar a máquina
            if ($checklist->machine) {
                $checklist->machine->markAsAvailable();
                $this->line("✓ Checklist #{$checklist->id} (Máquina: {$checklist->machine->name}) completado e máquina liberada");
            } else {
                $this->line("✓ Checklist #{$checklist->id} completado");
            }

            $completed++;
        }

        $this->info("\n✅ {$completed} checklists completados com sucesso!");
        $this->info('Todas as máquinas foram liberadas e estão disponíveis para uso.');

        return 0;
    }
}
