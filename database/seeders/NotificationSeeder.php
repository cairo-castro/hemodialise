<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Unit;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first admin/manager user
        $user = User::whereIn('role', ['admin', 'gestor', 'coordenador'])->first();
        $unit = Unit::first();

        if (!$user || !$unit) {
            $this->command->warn('No user or unit found. Skipping notification seeding.');
            return;
        }

        $this->command->info('Creating sample notifications...');

        // Create various types of notifications
        $notifications = [
            [
                'user_id' => $user->id,
                'unit_id' => $unit->id,
                'type' => 'checklist',
                'title' => 'Novo checklist criado',
                'message' => 'Checklist de segurança para Máquina 5 foi criado por ' . $user->name,
                'data' => ['machine_id' => 1, 'checklist_id' => 1],
                'action_url' => '/admin/safety-checklists/1',
                'read_at' => null,
                'created_at' => Carbon::now()->subMinutes(5),
            ],
            [
                'user_id' => $user->id,
                'unit_id' => $unit->id,
                'type' => 'success',
                'title' => 'Limpeza concluída',
                'message' => 'Limpeza diária da Máquina 3 foi concluída com sucesso',
                'data' => ['machine_id' => 3, 'cleaning_id' => 2],
                'action_url' => '/admin/cleaning-controls',
                'read_at' => null,
                'created_at' => Carbon::now()->subMinutes(30),
            ],
            [
                'user_id' => $user->id,
                'unit_id' => $unit->id,
                'type' => 'warning',
                'title' => 'Manutenção agendada',
                'message' => 'Máquina 2 tem manutenção preventiva agendada para amanhã',
                'data' => ['machine_id' => 2],
                'action_url' => '/admin/machines/2',
                'read_at' => null,
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'user_id' => $user->id,
                'unit_id' => $unit->id,
                'type' => 'info',
                'title' => 'Novo paciente cadastrado',
                'message' => 'Paciente Maria Santos foi cadastrado no sistema',
                'data' => ['patient_id' => 1],
                'action_url' => '/admin/patients/1',
                'read_at' => Carbon::now()->subHours(1),
                'created_at' => Carbon::now()->subHours(4),
            ],
            [
                'user_id' => $user->id,
                'unit_id' => $unit->id,
                'type' => 'error',
                'title' => 'Checklist incompleto',
                'message' => 'Checklist da Máquina 1 foi interrompido e precisa ser revisado',
                'data' => ['machine_id' => 1, 'checklist_id' => 2],
                'action_url' => '/admin/safety-checklists/2',
                'read_at' => Carbon::now()->subHours(12),
                'created_at' => Carbon::now()->subDay(),
            ],
            [
                'user_id' => null, // Broadcast notification to all users
                'unit_id' => $unit->id,
                'type' => 'info',
                'title' => 'Atualização do sistema',
                'message' => 'O sistema foi atualizado com novas funcionalidades. Confira as novidades!',
                'data' => [],
                'action_url' => '/admin/help',
                'read_at' => null,
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'user_id' => $user->id,
                'unit_id' => $unit->id,
                'type' => 'checklist',
                'title' => 'Checklist em andamento',
                'message' => 'Checklist para Máquina 4 está aguardando fase 2 - Dialisador',
                'data' => ['machine_id' => 4, 'checklist_id' => 3],
                'action_url' => '/admin/safety-checklists/3',
                'read_at' => null,
                'created_at' => Carbon::now()->subMinutes(15),
            ],
            [
                'user_id' => $user->id,
                'unit_id' => $unit->id,
                'type' => 'success',
                'title' => 'Desinfecção química completa',
                'message' => 'Desinfecção química da Máquina 6 foi concluída com sucesso',
                'data' => ['machine_id' => 6],
                'action_url' => '/admin/chemical-disinfections',
                'read_at' => null,
                'created_at' => Carbon::now()->subMinutes(45),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }

        $this->command->info('Created ' . count($notifications) . ' sample notifications.');
    }
}
