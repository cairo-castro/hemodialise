<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@hemodialise.com'],
            [
                'name' => 'Administrador do Sistema',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'default_view' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'gestor@hemodialise.com'],
            [
                'name' => 'Gestor Regional',
                'password' => bcrypt('gestor123'),
                'role' => 'gestor',
                'default_view' => 'desktop',
                'unit_id' => 1,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'coordenador@hemodialise.com'],
            [
                'name' => 'Coordenador Operacional',
                'password' => bcrypt('coord123'),
                'role' => 'coordenador',
                'default_view' => 'desktop',
                'unit_id' => 1,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'supervisor@hemodialise.com'],
            [
                'name' => 'Supervisor Técnico',
                'password' => bcrypt('super123'),
                'role' => 'supervisor',
                'default_view' => 'desktop',
                'unit_id' => 1,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'tecnico.joao@hemodialise.com'],
            [
                'name' => 'Técnico João Silva',
                'password' => bcrypt('tecnico123'),
                'role' => 'tecnico',
                'default_view' => 'mobile',
                'unit_id' => 1,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'tecnica.maria@hemodialise.com'],
            [
                'name' => 'Técnica Maria Santos',
                'password' => bcrypt('tecnico123'),
                'role' => 'tecnico',
                'default_view' => 'mobile',
                'unit_id' => 2,
                'email_verified_at' => now(),
            ]
        );
    }
}