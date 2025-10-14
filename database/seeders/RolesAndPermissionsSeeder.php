<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ====================================
        // PERMISSIONS - Definir todas as permissões do sistema
        // ====================================
        $permissions = [
            // Máquinas
            'view machines',
            'create machines',
            'update machines',
            'delete machines',
            'manage machine status', // colocar em manutenção, ativar/desativar
            
            // Pacientes
            'view patients',
            'create patients',
            'update patients',
            'delete patients',
            'export patients',
            
            // Checklists de Segurança
            'view safety checklists',
            'create safety checklists',
            'update safety checklists',
            'delete safety checklists',
            'advance checklist phase',
            'interrupt checklist',
            'pause checklist',
            'resume checklist',
            
            // Checklists de Limpeza
            'view cleaning checklists',
            'create cleaning checklists',
            'update cleaning checklists',
            'delete cleaning checklists',
            
            // Unidades
            'view units',
            'create units',
            'update units',
            'delete units',
            'manage unit access', // atribuir usuários a unidades
            
            // Usuários
            'view users',
            'create users',
            'update users',
            'delete users',
            'assign roles',
            'assign permissions',
            
            // Relatórios
            'view reports',
            'export reports',
            'view analytics',
            
            // Configurações
            'manage system settings',
            'view audit logs',
            'manage backups',
            
            // Acesso às interfaces
            'access mobile',
            'access desktop',
            'access admin',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // ====================================
        // ROLES - Definir níveis de acesso
        // ====================================

        // NÍVEL GLOBAL - Acesso a todas as unidades
        
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all()); // Todas as permissões

        $gestorGlobal = Role::create(['name' => 'gestor-global']);
        $gestorGlobal->givePermissionTo([
            // Máquinas
            'view machines',
            'create machines',
            'update machines',
            'manage machine status',
            
            // Pacientes
            'view patients',
            'create patients',
            'update patients',
            'export patients',
            
            // Checklists
            'view safety checklists',
            'create safety checklists',
            'update safety checklists',
            'advance checklist phase',
            'interrupt checklist',
            'pause checklist',
            'resume checklist',
            'view cleaning checklists',
            'create cleaning checklists',
            'update cleaning checklists',
            
            // Unidades
            'view units',
            
            // Usuários
            'view users',
            
            // Relatórios
            'view reports',
            'export reports',
            'view analytics',
            
            // Interfaces
            'access mobile',
            'access desktop',
            'access admin',
        ]);

        // NÍVEL UNIDADE - Acesso apenas à sua unidade
        
        $gestorUnidade = Role::create(['name' => 'gestor-unidade']);
        $gestorUnidade->givePermissionTo([
            // Máquinas
            'view machines',
            'manage machine status',
            
            // Pacientes
            'view patients',
            'create patients',
            'update patients',
            
            // Checklists
            'view safety checklists',
            'create safety checklists',
            'update safety checklists',
            'advance checklist phase',
            'interrupt checklist',
            'pause checklist',
            'resume checklist',
            'view cleaning checklists',
            'create cleaning checklists',
            'update cleaning checklists',
            
            // Relatórios
            'view reports',
            'export reports',
            
            // Interfaces
            'access mobile',
            'access desktop',
            'access admin',
        ]);

        $coordenador = Role::create(['name' => 'coordenador']);
        $coordenador->givePermissionTo([
            // Máquinas
            'view machines',
            'manage machine status',
            
            // Pacientes
            'view patients',
            'create patients',
            'update patients',
            
            // Checklists
            'view safety checklists',
            'create safety checklists',
            'update safety checklists',
            'advance checklist phase',
            'interrupt checklist',
            'pause checklist',
            'resume checklist',
            'view cleaning checklists',
            'create cleaning checklists',
            'update cleaning checklists',
            
            // Relatórios
            'view reports',
            
            // Interfaces
            'access mobile',
            'access desktop',
        ]);

        $supervisor = Role::create(['name' => 'supervisor']);
        $supervisor->givePermissionTo([
            // Máquinas
            'view machines',
            'manage machine status',
            
            // Pacientes
            'view patients',
            
            // Checklists
            'view safety checklists',
            'create safety checklists',
            'update safety checklists',
            'advance checklist phase',
            'pause checklist',
            'resume checklist',
            'view cleaning checklists',
            'create cleaning checklists',
            'update cleaning checklists',
            
            // Relatórios
            'view reports',
            
            // Interfaces
            'access mobile',
            'access desktop',
        ]);

        $tecnico = Role::create(['name' => 'tecnico']);
        $tecnico->givePermissionTo([
            // Máquinas
            'view machines',
            
            // Pacientes
            'view patients',
            
            // Checklists
            'view safety checklists',
            'create safety checklists',
            'update safety checklists',
            'advance checklist phase',
            'view cleaning checklists',
            'create cleaning checklists',
            
            // Interfaces
            'access mobile',
        ]);

        $this->command->info('Roles and Permissions created successfully!');
        $this->command->info('');
        $this->command->info('Roles criados:');
        $this->command->info('  - super-admin (Acesso Global Total)');
        $this->command->info('  - gestor-global (Acesso Global Administrativo)');
        $this->command->info('  - gestor-unidade (Gestão de Unidade)');
        $this->command->info('  - coordenador (Coordenação de Unidade)');
        $this->command->info('  - supervisor (Supervisão de Unidade)');
        $this->command->info('  - tecnico (Técnico de Campo)');
    }
}
