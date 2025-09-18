<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Unit::create([
            'name' => 'Centro de Hemodiálise São Luís',
            'code' => 'SL001',
            'address' => 'Rua das Flores, 123 - Centro, São Luís - MA',
            'phone' => '(98) 3232-1234',
            'manager_name' => 'Dr. João Silva',
            'active' => true,
        ]);

        Unit::create([
            'name' => 'Clínica Renal Imperatriz',
            'code' => 'IMP001',
            'address' => 'Av. Brasil, 456 - Bacuri, Imperatriz - MA',
            'phone' => '(99) 3524-5678',
            'manager_name' => 'Dra. Maria Santos',
            'active' => true,
        ]);

        Unit::create([
            'name' => 'Nefroclínica Caxias',
            'code' => 'CX001',
            'address' => 'Praça Duque de Caxias, 789 - Centro, Caxias - MA',
            'phone' => '(99) 3351-9012',
            'manager_name' => 'Dr. Pedro Costa',
            'active' => true,
        ]);
    }
}
