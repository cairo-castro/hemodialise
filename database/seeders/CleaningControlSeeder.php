<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CleaningControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $machines = \App\Models\Machine::with('unit')->take(20)->get();
        $users = \App\Models\User::whereIn('role', ['tecnico', 'coordenador', 'supervisor'])->get();

        if ($machines->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No machines or users found. Skipping CleaningControl seeding.');
            return;
        }

        $shifts = ['matutino', 'vespertino', 'noturno'];
        $cleaningTypes = ['daily', 'weekly', 'monthly', 'special'];

        // Create records for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays($i);

            // Create 2-5 cleanings per day
            $dailyCount = rand(2, 5);

            for ($j = 0; $j < $dailyCount; $j++) {
                $machine = $machines->random();
                $user = $users->random();
                $shift = $shifts[array_rand($shifts)];
                $type = $cleaningTypes[array_rand($cleaningTypes)];

                \App\Models\CleaningControl::create([
                    'machine_id' => $machine->id,
                    'unit_id' => $machine->unit_id,
                    'user_id' => $user->id,
                    'cleaning_date' => $date->toDateString(),
                    'cleaning_time' => $date->setTime(rand(6, 22), rand(0, 59))->format('H:i'),
                    'shift' => $shift,
                    'daily_cleaning' => $type === 'daily',
                    'weekly_cleaning' => $type === 'weekly',
                    'monthly_cleaning' => $type === 'monthly',
                    'special_cleaning' => $type === 'special',
                    'hd_machine_cleaning' => rand(0, 1),
                    'osmosis_cleaning' => rand(0, 1),
                    'serum_support_cleaning' => rand(0, 1),
                    'chemical_disinfection' => rand(0, 1),
                    'external_cleaning_done' => rand(0, 1),
                    'internal_cleaning_done' => rand(0, 1),
                    'filter_replacement' => $type === 'monthly' ? rand(0, 1) : false,
                    'system_disinfection' => $type !== 'daily' ? rand(0, 1) : false,
                    'cleaning_products_used' => 'Álcool 70%, Hipoclorito de Sódio',
                    'cleaning_procedure' => 'Limpeza conforme protocolo padrão da unidade',
                    'observations' => rand(0, 1) ? 'Procedimento realizado sem intercorrências.' : null,
                ]);
            }
        }

        $this->command->info('CleaningControl records created successfully!');
    }
}
