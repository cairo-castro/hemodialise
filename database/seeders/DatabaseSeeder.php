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
        // Ordem: Roles/Permissões → Unidades → Usuários
        $this->call([
            RolesAndPermissionsSeeder::class,
            UnitsSeeder::class,
            UserSeeder::class,
        ]);

        // Criar máquinas de exemplo (não criar mais, apenas se necessário)
        // As unidades já criarão suas próprias máquinas

        // Criar pacientes de exemplo (não criar mais, apenas se necessário)
        // Os usuários das unidades cadastrarão seus próprios pacientes
    }
}
