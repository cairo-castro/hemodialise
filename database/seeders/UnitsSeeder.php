<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Dados extraídos do Excel: sistema hemodialise(respostas) (1).xlsx
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Hospital Vila Luizão', 'code' => 'HVL'],
            ['name' => 'Hospital Municipal em Porto Franco', 'code' => 'HMPF'],
            ['name' => 'Serviço de Hemodiálise de São Luís', 'code' => 'SHSL'],
            ['name' => 'Hospital Regional de Barreirinhas', 'code' => 'HRB'],
            ['name' => 'Hospital Regional Dr. Rubens Jorge (Lago da Pedra)', 'code' => 'HRDRJ'],
            ['name' => 'Hospital de Urgência e Emergência de Presidente Dutra', 'code' => 'HUEPD'],
            ['name' => 'Centro de Hemodialise', 'code' => 'CH'],
            ['name' => 'Hospital Genesio Rego', 'code' => 'HGR'],
            ['name' => 'Hospital Regional de Santa Luzia do Paruá', 'code' => 'HRSLP'],
            ['name' => 'Hospital Regional de Grajaú,Serviço de hemodiálise do hospital regional de Grajaú Dr. José Jorge', 'code' => 'HRG'],
            ['name' => 'Hospital Macrorregional Dra. Ruth Noleto', 'code' => 'HMRN'],
            ['name' => 'Hospital da Ilha,Hospital Aquiles Lisboa', 'code' => 'HI'],
            ['name' => 'Serviço de Hemodiálise do hospital Dr. José da Costa Almeida(Chapadinha)', 'code' => 'SHJCA'],
            ['name' => 'Serviço de hemodiálise do Hospital Regiona de Carutapera', 'code' => 'SHRC'],
            ['name' => 'Hospital Presidente Vargas', 'code' => 'HPV'],
            ['name' => 'Hospital Regional de Barra do Corda', 'code' => 'HRBC'],
            ['name' => 'Hospital de Cuidados Intensivos - HCI', 'code' => 'HCI'],
            ['name' => 'Centro de Hemodiálise de Barreirinhas Sr. João Ivo Vale', 'code' => 'CHBJIV'],
            ['name' => 'MARI Imperatriz', 'code' => 'MARI'],
            ['name' => 'Hospital Regional Alarico Nunes', 'code' => 'HRAN'],
            ['name' => 'Hospital Regional Dr. Kleber Carvalho Branco (Pedreiras)', 'code' => 'HRDKCB'],
            ['name' => 'Hospital Macrorregional de Caxias Dr. Everaldo Ferreira Aragão', 'code' => 'HMCEFA'],
            ['name' => 'Hospital Macrorregional Alexandre Mamede Trovão (Coroatá)', 'code' => 'HMAMT'],
        ];

        foreach ($units as $index => $unitData) {
            Unit::updateOrCreate(
                ['code' => $unitData['code']],
                [
                    'name' => $unitData['name'],
                    'code' => $unitData['code'],
                    'address' => 'Endereço não cadastrado - ' . $unitData['name'],
                    'phone' => '(98) 0000-0000',
                    'manager_name' => 'A definir',
                    'active' => true,
                ]
            );
        }

        $this->command->info('Units created successfully from Excel data!');
        $this->command->info('Total units: ' . count($units));
    }
}
