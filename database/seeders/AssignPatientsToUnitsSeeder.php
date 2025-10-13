<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Unit;

class AssignPatientsToUnitsSeeder extends Seeder
{
    /**
     * Associa pacientes existentes sem unit_id a uma unidade padrão
     * 
     * Execute apenas uma vez após adicionar o campo unit_id:
     * php artisan db:seed --class=AssignPatientsToUnitsSeeder
     */
    public function run(): void
    {
        $this->command->info('Iniciando atribuição de pacientes a unidades...');

        // Busca a primeira unidade como padrão
        $defaultUnit = Unit::first();

        if (!$defaultUnit) {
            $this->command->error('Nenhuma unidade encontrada! Crie uma unidade primeiro.');
            return;
        }

        // Conta pacientes sem unidade
        $patientsWithoutUnit = Patient::whereNull('unit_id')->count();

        if ($patientsWithoutUnit === 0) {
            $this->command->info('Todos os pacientes já possuem unidade associada!');
            return;
        }

        $this->command->warn("Encontrados {$patientsWithoutUnit} pacientes sem unidade.");
        $this->command->info("Associando à unidade padrão: {$defaultUnit->name} (ID: {$defaultUnit->id})");

        // Atualiza em lotes de 100 para performance
        $updated = 0;
        Patient::whereNull('unit_id')->chunk(100, function ($patients) use ($defaultUnit, &$updated) {
            foreach ($patients as $patient) {
                $patient->update(['unit_id' => $defaultUnit->id]);
                $updated++;
                
                if ($updated % 100 === 0) {
                    $this->command->info("Processados: {$updated} pacientes...");
                }
            }
        });

        $this->command->info("✅ Concluído! {$updated} pacientes associados à unidade {$defaultUnit->name}");
        $this->command->warn("⚠️  IMPORTANTE: Revise os pacientes e ajuste manualmente se necessário!");
    }
}
