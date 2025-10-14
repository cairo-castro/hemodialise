<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MigrateUsersToMultipleUnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::whereNotNull('unit_id')->get();

        foreach ($users as $user) {
            // Associa o usuário à sua unidade atual na tabela pivot
            $user->units()->syncWithoutDetaching([
                $user->unit_id => ['is_primary' => true]
            ]);

            // Define a unidade atual como a unidade principal
            $user->current_unit_id = $user->unit_id;
            $user->save();

            $this->command->info("Usuário {$user->name} associado à unidade {$user->unit->name}");
        }

        $this->command->info('Migração concluída!');
    }
}
