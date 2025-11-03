<?php

namespace App\Console\Commands;

use App\Mail\NewChecklistNotification;
use App\Mail\MaintenanceAlertNotification;
use App\Mail\WeeklyReportNotification;
use App\Mail\SystemUpdateNotification;
use App\Models\SafetyChecklist;
use App\Models\Machine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test {email} {type=all : Type of notification to test (all, checklist, maintenance, report, update)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email notifications by sending samples';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $type = $this->argument('type');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address!');
            return 1;
        }

        $this->info('Testing email notifications...');
        $this->newLine();

        $success = true;

        if ($type === 'all' || $type === 'checklist') {
            $success = $this->testChecklistNotification($email) && $success;
        }

        if ($type === 'all' || $type === 'maintenance') {
            $success = $this->testMaintenanceNotification($email) && $success;
        }

        if ($type === 'all' || $type === 'report') {
            $success = $this->testWeeklyReportNotification($email) && $success;
        }

        if ($type === 'all' || $type === 'update') {
            $success = $this->testSystemUpdateNotification($email) && $success;
        }

        $this->newLine();
        if ($success) {
            $this->info('✅ All notifications sent successfully!');
            return 0;
        } else {
            $this->error('❌ Some notifications failed to send.');
            return 1;
        }
    }

    private function testChecklistNotification($email)
    {
        try {
            $this->info('📋 Sending checklist notification...');

            // Get the first checklist or create a mock one
            $checklist = SafetyChecklist::with(['patient', 'machine', 'user'])->first();

            if (!$checklist) {
                $this->warn('No checklist found in database. Skipping checklist notification.');
                return true;
            }

            Mail::to($email)->send(new NewChecklistNotification($checklist));
            $this->line('   ✓ Checklist notification sent');
            return true;
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
            return false;
        }
    }

    private function testMaintenanceNotification($email)
    {
        try {
            $this->info('🔧 Sending maintenance alert...');

            // Get the first machine or create a mock one
            $machine = Machine::with('unit')->first();

            if (!$machine) {
                $this->warn('No machine found in database. Skipping maintenance notification.');
                return true;
            }

            Mail::to($email)->send(new MaintenanceAlertNotification(
                $machine,
                'Manutenção Preventiva Trimestral',
                now()->addDays(7)->format('d/m/Y')
            ));
            $this->line('   ✓ Maintenance alert sent');
            return true;
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
            return false;
        }
    }

    private function testWeeklyReportNotification($email)
    {
        try {
            $this->info('📊 Sending weekly report...');

            $reportData = [
                'period' => 'Semana de ' . now()->subDays(7)->format('d/m') . ' a ' . now()->format('d/m/Y'),
                'total_checklists' => 45,
                'total_cleanings' => 32,
                'active_machines' => 8,
                'total_sessions' => 120,
                'highlights' => [
                    'Todos os checklists foram realizados no prazo',
                    '100% de conformidade nas limpezas diárias',
                    'Zero incidentes reportados'
                ],
                'alerts' => [
                    'Máquina HD-03 próxima da manutenção preventiva',
                    'Estoque de filtros precisa ser reposto'
                ]
            ];

            Mail::to($email)->send(new WeeklyReportNotification($reportData));
            $this->line('   ✓ Weekly report sent');
            return true;
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
            return false;
        }
    }

    private function testSystemUpdateNotification($email)
    {
        try {
            $this->info('🚀 Sending system update notification...');

            Mail::to($email)->send(new SystemUpdateNotification(
                'Sistema de Notificações por Email',
                'Implementamos um novo sistema completo de notificações por email para manter você informado sobre todas as atividades importantes do sistema.',
                [
                    'Notificações de novos checklists criados',
                    'Alertas de manutenção preventiva',
                    'Relatórios semanais automáticos',
                    'Avisos de atualizações do sistema',
                    'Configuração personalizada de preferências'
                ]
            ));
            $this->line('   ✓ System update notification sent');
            return true;
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
            return false;
        }
    }
}
