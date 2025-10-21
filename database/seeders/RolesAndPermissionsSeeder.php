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
        // Nomenclatura: resource.action (ex: machines.view, patients.create)
        // ====================================
        $permissions = [
            // Máquinas
            'machines.view',
            'machines.create',
            'machines.update',
            'machines.delete',
            'machines.manage-status', // colocar em manutenção, ativar/desativar

            // Pacientes
            'patients.view',
            'patients.create',
            'patients.update',
            'patients.delete',
            'patients.export',

            // Checklists de Segurança
            'safety-checklists.view',
            'safety-checklists.create',
            'safety-checklists.update',
            'safety-checklists.delete',
            'safety-checklists.advance-phase',
            'safety-checklists.interrupt',
            'safety-checklists.pause',
            'safety-checklists.resume',

            // Checklists de Limpeza
            'cleaning-checklists.view',
            'cleaning-checklists.create',
            'cleaning-checklists.update',
            'cleaning-checklists.delete',

            // Unidades
            'units.view',
            'units.create',
            'units.update',
            'units.delete',
            'units.manage-access', // atribuir usuários a unidades

            // Usuários
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.assign-roles',
            'users.assign-permissions',

            // Relatórios
            'reports.view',
            'reports.export',
            'reports.analytics',

            // Configurações
            'settings.manage',
            'audit-logs.view',
            'backups.manage',

            // Acesso às interfaces
            'access.mobile',
            'access.desktop',
            'access.admin',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ====================================
        // ROLES - Definir níveis de acesso
        // ====================================

        // NÍVEL GLOBAL - Acesso a todas as unidades

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions(Permission::all()); // Todas as permissões

        $gestorGlobal = Role::firstOrCreate(['name' => 'gestor-global']);
        $gestorGlobal->syncPermissions([
            // Máquinas
            'machines.view',
            'machines.create',
            'machines.update',
            'machines.manage-status',

            // Pacientes
            'patients.view',
            'patients.create',
            'patients.update',
            'patients.export',

            // Checklists
            'safety-checklists.view',
            'safety-checklists.create',
            'safety-checklists.update',
            'safety-checklists.advance-phase',
            'safety-checklists.interrupt',
            'safety-checklists.pause',
            'safety-checklists.resume',
            'cleaning-checklists.view',
            'cleaning-checklists.create',
            'cleaning-checklists.update',

            // Unidades
            'units.view',

            // Usuários
            'users.view',

            // Relatórios
            'reports.view',
            'reports.export',
            'reports.analytics',

            // Interfaces
            'access.mobile',
            'access.desktop',
            'access.admin',
        ]);

        // NÍVEL UNIDADE - Acesso apenas à sua unidade

        $gestorUnidade = Role::firstOrCreate(['name' => 'gestor-unidade']);
        $gestorUnidade->syncPermissions([
            // Máquinas
            'machines.view',
            'machines.manage-status',

            // Pacientes
            'patients.view',
            'patients.create',
            'patients.update',

            // Checklists
            'safety-checklists.view',
            'safety-checklists.create',
            'safety-checklists.update',
            'safety-checklists.advance-phase',
            'safety-checklists.interrupt',
            'safety-checklists.pause',
            'safety-checklists.resume',
            'cleaning-checklists.view',
            'cleaning-checklists.create',
            'cleaning-checklists.update',

            // Relatórios
            'reports.view',
            'reports.export',

            // Interfaces
            'access.mobile',
            'access.desktop',
            'access.admin',
        ]);

        $coordenador = Role::firstOrCreate(['name' => 'coordenador']);
        $coordenador->syncPermissions([
            // Máquinas
            'machines.view',
            'machines.manage-status',

            // Pacientes
            'patients.view',
            'patients.create',
            'patients.update',

            // Checklists
            'safety-checklists.view',
            'safety-checklists.create',
            'safety-checklists.update',
            'safety-checklists.advance-phase',
            'safety-checklists.interrupt',
            'safety-checklists.pause',
            'safety-checklists.resume',
            'cleaning-checklists.view',
            'cleaning-checklists.create',
            'cleaning-checklists.update',

            // Relatórios
            'reports.view',

            // Interfaces
            'access.mobile',
            'access.desktop',
        ]);

        $supervisor = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisor->syncPermissions([
            // Máquinas
            'machines.view',
            'machines.manage-status',

            // Pacientes
            'patients.view',

            // Checklists
            'safety-checklists.view',
            'safety-checklists.create',
            'safety-checklists.update',
            'safety-checklists.advance-phase',
            'safety-checklists.pause',
            'safety-checklists.resume',
            'cleaning-checklists.view',
            'cleaning-checklists.create',
            'cleaning-checklists.update',

            // Relatórios
            'reports.view',

            // Interfaces
            'access.mobile',
            'access.desktop',
        ]);

        $tecnico = Role::firstOrCreate(['name' => 'tecnico']);
        $tecnico->syncPermissions([
            // Máquinas
            'machines.view',

            // Pacientes
            'patients.view',

            // Checklists
            'safety-checklists.view',
            'safety-checklists.create',
            'safety-checklists.update',
            'safety-checklists.advance-phase',
            'cleaning-checklists.view',
            'cleaning-checklists.create',

            // Interfaces
            'access.mobile',
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
