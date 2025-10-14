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
        $units = [
            [
                'name' => 'Hospital Vila Luizão',
                'code' => 'HVL001',
                'address' => 'A definir',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Municipal em Porto Franco',
                'code' => 'HMPF001',
                'address' => 'Porto Franco - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Serviço de Hemodiálise de São Luís',
                'code' => 'SHSL001',
                'address' => 'São Luís - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Regional de Barreirinhas',
                'code' => 'HRBA001',
                'address' => 'Barreirinhas - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Regional Dr. Rubens Jorge (Lago da Pedra)',
                'code' => 'HRLP001',
                'address' => 'Lago da Pedra - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital de Urgência e Emergência de Presidente Dutra',
                'code' => 'HUEPD001',
                'address' => 'Presidente Dutra - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Centro de Hemodialise',
                'code' => 'CH001',
                'address' => 'A definir',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Genesio Rego',
                'code' => 'HGR001',
                'address' => 'A definir',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Regional de Santa Luzia do Paruá',
                'code' => 'HRSLP001',
                'address' => 'Santa Luzia do Paruá - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Regional de Grajaú - Serviço de hemodiálise Dr. José Jorge',
                'code' => 'HRGR001',
                'address' => 'Grajaú - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Macrorregional Dra. Ruth Noleto',
                'code' => 'HMRN001',
                'address' => 'A definir',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital da Ilha - Hospital Aquiles Lisboa',
                'code' => 'HIAL001',
                'address' => 'São Luís - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Serviço de Hemodiálise do Hospital Dr. José da Costa Almeida (Chapadinha)',
                'code' => 'SHJCA001',
                'address' => 'Chapadinha - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Serviço de hemodiálise do Hospital Regional de Carutapera',
                'code' => 'SHRC001',
                'address' => 'Carutapera - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Presidente Vargas',
                'code' => 'HPV001',
                'address' => 'A definir',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Regional de Barra do Corda',
                'code' => 'HRBC001',
                'address' => 'Barra do Corda - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital de Cuidados Intensivos - HCI',
                'code' => 'HCI001',
                'address' => 'A definir',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Centro de Hemodiálise de Barreirinhas Sr. João Ivo Vale',
                'code' => 'CHBA001',
                'address' => 'Barreirinhas - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'MARI Imperatriz',
                'code' => 'MARI001',
                'address' => 'Imperatriz - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Regional Alarico Nunes',
                'code' => 'HRAN001',
                'address' => 'A definir',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Regional Dr. Kleber Carvalho Branco (Pedreiras)',
                'code' => 'HRKC001',
                'address' => 'Pedreiras - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Macrorregional de Caxias Dr. Everaldo Ferreira Aragão',
                'code' => 'HMCX001',
                'address' => 'Caxias - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
            [
                'name' => 'Hospital Macrorregional Alexandre Mamede Trovão (Coroatá)',
                'code' => 'HMCO001',
                'address' => 'Coroatá - MA',
                'phone' => null,
                'manager_name' => null,
                'active' => true,
            ],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
