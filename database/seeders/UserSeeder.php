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
                'name' => 'Administrador',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'gerente.sl@hemodialise.com'],
            [
                'name' => 'Gerente São Luís',
                'password' => bcrypt('gerente123'),
                'role' => 'manager',
                'unit_id' => 1,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'tecnico.joao@hemodialise.com'],
            [
                'name' => 'Técnico João',
                'password' => bcrypt('tecnico123'),
                'role' => 'field_user',
                'unit_id' => 1,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'tecnica.maria@hemodialise.com'],
            [
                'name' => 'Técnica Maria',
                'password' => bcrypt('tecnico123'),
                'role' => 'field_user',
                'unit_id' => 2,
                'email_verified_at' => now(),
            ]
        );
    }
}