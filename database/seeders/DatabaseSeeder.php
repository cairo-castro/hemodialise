<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);

        // Criar máquinas de exemplo
        \App\Models\Machine::create(['name' => 'Máquina HD-01', 'identifier' => 'HD-01', 'description' => 'Hemodiálise Turno Manhã']);
        \App\Models\Machine::create(['name' => 'Máquina HD-02', 'identifier' => 'HD-02', 'description' => 'Hemodiálise Turno Tarde']);
        \App\Models\Machine::create(['name' => 'Máquina HD-03', 'identifier' => 'HD-03', 'description' => 'Hemodiálise Turno Noite']);

        // Criar pacientes de exemplo
        \App\Models\Patient::create([
            'full_name' => 'João Silva Santos',
            'birth_date' => '1980-05-15',
            'medical_record' => '2025001',
            'blood_type' => 'O+',
            'allergies' => 'Nenhuma alergia conhecida',
        ]);

        \App\Models\Patient::create([
            'full_name' => 'Maria Oliveira Costa',
            'birth_date' => '1975-08-22',
            'medical_record' => '2025002',
            'blood_type' => 'A+',
            'allergies' => 'Alérgica a penicilina',
        ]);
    }
}
