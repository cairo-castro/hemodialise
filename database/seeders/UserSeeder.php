<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Dados extraídos do Excel: sistema hemodialise(respostas) (1).xlsx
     */
    public function run(): void
    {
        // ADMIN GLOBAL - Acesso total ao sistema
        $admin = \App\Models\User::updateOrCreate(
            ['email' => 'admin@hemodialise.ma.gov.br'],
            [
                'name' => 'Administrador do Sistema',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'default_view' => 'admin',
                'unit_id' => null, // Usuário global - sem unidade específica
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['super-admin']);

        // GERENTE GLOBAL - Gestão em todas as unidades
        $gerente = \App\Models\User::updateOrCreate(
            ['email' => 'joenvilly.azevedo@emserh.ma.gov.br'],
            [
                'name' => 'Joenvilly Azevedo',
                'password' => bcrypt('A123456*'),
                'role' => 'gestor',
                'default_view' => 'desktop',
                'unit_id' => null, // Usuário global - sem unidade específica
                'email_verified_at' => now(),
            ]
        );
        $gerente->syncRoles(['gestor-unidade']);

        // COORDENADOR GLOBAL - Coordenação em todas as unidades
        $coordenador = \App\Models\User::updateOrCreate(
            ['email' => 'andre.campos@emserh.ma.gov.br'],
            [
                'name' => 'André Campos',
                'password' => bcrypt('A123456*'),
                'role' => 'coordenador',
                'default_view' => 'desktop',
                'unit_id' => null, // Usuário global - sem unidade específica
                'email_verified_at' => now(),
            ]
        );
        $coordenador->syncRoles(['coordenador']);

        // USUÁRIOS DAS UNIDADES - Dados do Excel
        $users = [
            [
                'name' => 'Ana Lucia Dos Santo Araujo',
                'cpf' => '819.552.003-00',
                'email' => 'dialisevilaluizao@cenefrologiama.com.br',
                'cargo' => 'Gestão Asistencial',
                'empresa' => 'Clinica de Rim',
                'unidade' => 'Hospital Vila Luizão',
                'role' => 'gestor',
            ],
            [
                'name' => 'Kayro Cesar Maciel Leal',
                'cpf' => '611.164.183-20',
                'email' => 'kayro2112@hotmail.com',
                'cargo' => 'Enfermeiro Nefrologista',
                'empresa' => 'Med Service',
                'unidade' => 'Hospital Municipal em Porto Franco',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Suzana Farias Brasil Nepomuceno',
                'cpf' => '061.175.763-07',
                'email' => 'suzanafbn@gmail.com',
                'cargo' => 'Coordenação de enfermagem',
                'empresa' => 'Medservice/Emserh',
                'unidade' => 'Serviço de Hemodiálise de São Luís',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Giovane Prudêncio do Nascimento',
                'cpf' => '053.689.903-75',
                'email' => 'giovane.nefro@gmail.com',
                'cargo' => 'Responsável Técnico',
                'empresa' => 'Nefromais',
                'unidade' => 'Hospital Regional de Barreirinhas',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Gladstony Barros Mesquita',
                'cpf' => '739.334.573-00',
                'email' => 'gladstony.mesquita@gmail.com',
                'cargo' => 'Enfermeiro RT',
                'empresa' => 'Mearim Serviços nefrológicos',
                'unidade' => 'Hospital Regional Dr. Rubens Jorge (Lago da Pedra)',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Michely de Oliveira Silva',
                'cpf' => '600.210.613-86',
                'email' => 'enf.michely@hotmail.com',
                'cargo' => 'Responsável Técnica do setor Nefrologia',
                'empresa' => 'IADVH',
                'unidade' => 'Hospital de Urgência e Emergência de Presidente Dutra',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Juliana Fonseca Soares',
                'cpf' => '053.610.853-68',
                'email' => 'juliana.fonsecas@hotmail.com',
                'cargo' => 'Supervisora de Enfermagem - NEP',
                'empresa' => 'Emeserh',
                'unidade' => 'Centro de Hemodialise',
                'role' => 'supervisor',
            ],
            [
                'name' => 'Michelle Cabral da Silva Moraes',
                'cpf' => '656.944.573-16',
                'email' => 'dialisegenesiorego@gmail.com',
                'cargo' => 'Enfermeira Nefrologista',
                'empresa' => 'Centro de Nefrologia do Maranhão - CENEFRON',
                'unidade' => 'Hospital Genesio Rego',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Danielly Pereira Gonçalves',
                'cpf' => '044.664.353-08',
                'email' => 'danielly524@gmail.com',
                'cargo' => 'Enfermeira',
                'empresa' => 'Med Service',
                'unidade' => 'Hospital Regional de Santa Luzia do Paruá',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Jordana Angelica da Silveira Silva do Lago',
                'cpf' => '602.731.483-46',
                'email' => 'jordana.angelica@hotmail.com',
                'cargo' => 'RT ENFERMAGEM',
                'empresa' => 'NEFROCLIN',
                'unidade' => 'Hospital Regional de Grajaú,Serviço de hemodiálise do hospital regional de Grajaú Dr. José Jorge',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Eliete da Silva Carvalho',
                'cpf' => '030.263.233-65',
                'email' => 'nefro.macro21@outlook.com',
                'cargo' => 'Enfermeira',
                'empresa' => 'IADVH',
                'unidade' => 'Hospital Macrorregional Dra. Ruth Noleto',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Ana Cleudes Alves dos Santos Gomes',
                'cpf' => '027.260.593-09',
                'email' => 'ana_cleudes@hotmail.com',
                'cargo' => 'Enfermeira',
                'empresa' => 'Nefroclin',
                'unidade' => 'Hospital da Ilha,Hospital Aquiles Lisboa',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Ednólia Costa Moreira',
                'cpf' => '605.219.413-85',
                'email' => 'dialisechapadinha2024@hotmail.com',
                'cargo' => 'Enfermeira/Coordenadora',
                'empresa' => 'Emserh',
                'unidade' => 'Serviço de Hemodiálise do hospital Dr. José da Costa Almeida(Chapadinha)',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Sayara Teixeira Potter da Rosa',
                'cpf' => '018.545.062-85',
                'email' => 'sayarapotter02@gmail.com',
                'cargo' => 'Enfermeira nefrologista',
                'empresa' => 'Cenefrom',
                'unidade' => 'Serviço de hemodiálise do Hospital Regiona de Carutapera',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Leda Barros de Castro',
                'cpf' => '003.980.383-09',
                'email' => 'ledabarrosdecastro@yahoo.com.br',
                'cargo' => 'Enfermeira / supervisao',
                'empresa' => 'Emserh',
                'unidade' => 'Hospital Presidente Vargas',
                'role' => 'supervisor',
            ],
            [
                'name' => 'Hayline Sousa Guimarães',
                'cpf' => '032.864.383-16',
                'email' => 'haylineguimaraes87@gmail.com',
                'cargo' => 'Técnico de enfermagem',
                'empresa' => 'Cenefrom',
                'unidade' => 'Hospital Regional de Barra do Corda',
                'role' => 'tecnico',
            ],
            [
                'name' => 'Dilza Martins Cantanhede',
                'cpf' => '799.032.743-00',
                'email' => 'dilzacantanhede@hotmail.com',
                'cargo' => 'Enfermeira',
                'empresa' => 'Cenefron',
                'unidade' => 'Hospital de Cuidados Intensivos - HCI',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Silanilson Batista Silva',
                'cpf' => '706.094.973-91',
                'email' => 'silanilsonbatista@hotmail.com',
                'cargo' => 'COORDENADOR ENFERMAGEM',
                'empresa' => 'EVIDENCE',
                'unidade' => 'Centro de Hemodiálise de Barreirinhas Sr. João Ivo Vale',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Tuane Souza Guimarães',
                'cpf' => '029.555.273-51',
                'email' => 'tuany_sg@hotmail.com',
                'cargo' => 'Enfermeira',
                'empresa' => 'Pronto Atendimento Nefrológico',
                'unidade' => 'MARI Imperatriz',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Thaliane Gonçalves',
                'cpf' => '024.598.233-71',
                'email' => 'dialisebarradocorda@gmail.com',
                'cargo' => 'Gerente do Serviço de Diálise',
                'empresa' => 'Hospital Regional de Barra do Corda',
                'unidade' => 'Hospital Regional de Barra do Corda',
                'role' => 'gestor',
            ],
            [
                'name' => 'Alan Danilo Teixeira Carvalho',
                'cpf' => '018.080.433-24',
                'email' => 'enfssm@gmail.com',
                'cargo' => 'ENFERMEIRO',
                'empresa' => 'Soluções Serviços Médicos',
                'unidade' => 'Hospital Regional Alarico Nunes',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Dayany de Arêa Leão Coutinho',
                'cpf' => '816.870.953-53',
                'email' => 'coordregionalcaxias@gmail.com',
                'cargo' => 'Gerente de Enfermagem- RT unidade',
                'empresa' => 'Emserh',
                'unidade' => 'Hospital Macrorregional de Caxias Dr. Everaldo Ferreira Aragão',
                'role' => 'gestor',
            ],
            [
                'name' => 'Régilla Fernanda Alves Pinheiro',
                'cpf' => '010.900.643-73',
                'email' => 'coord.enfermagemmacrocta@gmail.com',
                'cargo' => 'Coordenação',
                'empresa' => 'Emserh',
                'unidade' => 'Hospital Macrorregional Alexandre Mamede Trovão (Coroatá)',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Taanac Melo de Almeida',
                'cpf' => '001.486.183-67',
                'email' => 'taanac_mello@hotmail.com',
                'cargo' => 'Enfermeiro',
                'empresa' => 'Hospital Alexandre Mamede Trovão',
                'unidade' => 'Hospital Macrorregional Alexandre Mamede Trovão (Coroatá)',
                'role' => 'coordenador',
            ],
            [
                'name' => 'Rayenna Almeida Araújo',
                'cpf' => '026.589.123-07',
                'email' => 'rayennahh@gmail.com',
                'cargo' => 'Supervisão de Enfermagem-UTI',
                'empresa' => 'EMSERH',
                'unidade' => 'Hospital Macrorregional de Caxias Dr. Everaldo Ferreira Aragão',
                'role' => 'supervisor',
            ],
        ];

        foreach ($users as $userData) {
            // Buscar unidade pelo nome
            $unit = Unit::where('name', $userData['unidade'])->first();

            if (!$unit) {
                $this->command->warn("Unidade não encontrada: {$userData['unidade']} para {$userData['name']}");
                continue;
            }

            // Criar usuário
            $user = \App\Models\User::updateOrCreate(
                ['email' => strtolower($userData['email'])],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('senha123'), // Senha padrão
                    'role' => $userData['role'],
                    'default_view' => $userData['role'] === 'tecnico' ? 'mobile' : 'desktop',
                    'unit_id' => $unit->id,
                    'current_unit_id' => $unit->id, // Define também a unidade atual
                    'email_verified_at' => now(),
                ]
            );

            // Associar usuário à unidade na tabela pivot (user_unit)
            // Isso é necessário para o sistema de múltiplas unidades
            if (!$user->units()->where('unit_id', $unit->id)->exists()) {
                $user->units()->attach($unit->id, [
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Atribuir role do Spatie Permission
            $spatieRole = match($userData['role']) {
                'gestor' => 'gestor-unidade',
                'coordenador' => 'coordenador',
                'supervisor' => 'supervisor',
                'tecnico' => 'tecnico',
                default => 'tecnico',
            };

            $user->syncRoles([$spatieRole]);

            $this->command->info("✓ {$userData['name']} - {$userData['unidade']}");
        }

        $this->command->info('');
        $this->command->info('Users created successfully from Excel data!');
        $this->command->info('Total users: ' . (count($users) + 3) . ' (including admin, gerente and coordenador)');
        $this->command->info('');
        $this->command->info('CREDENCIAIS USUÁRIOS GLOBAIS:');
        $this->command->info('┌────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ Admin Global:       admin@hemodialise.ma.gov.br / admin123         │');
        $this->command->info('│ Gerente Global:     joenvilly.azevedo@emserh.ma.gov.br / A123456*  │');
        $this->command->info('│ Coordenador Global: andre.campos@emserh.ma.gov.br / A123456*      │');
        $this->command->info('└────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');
        $this->command->info('Usuários das unidades: [email] / senha123');
    }
}
