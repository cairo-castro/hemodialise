<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Unit;

class NotificationService
{
    /**
     * Notify about a new safety checklist
     */
    public static function notifyChecklistCreated($checklist, $creator)
    {
        $machine = $checklist->machine;
        $patient = $checklist->patient;

        $title = 'Novo checklist de segurança';
        $message = sprintf(
            'Checklist para %s (Paciente: %s) criado por %s',
            $machine->name ?? 'Máquina desconhecida',
            $patient->full_name ?? 'Paciente desconhecido',
            $creator->name
        );

        return static::createNotificationForUnit(
            type: 'checklist',
            title: $title,
            message: $message,
            unitId: $machine->unit_id ?? $creator->unit_id,
            data: [
                'checklist_id' => $checklist->id,
                'machine_id' => $machine->id ?? null,
                'patient_id' => $patient->id ?? null,
            ],
            actionUrl: "/admin/safety-checklists/{$checklist->id}",
            excludeUserId: $creator->id // Don't notify the creator
        );
    }

    /**
     * Notify about checklist completion
     */
    public static function notifyChecklistCompleted($checklist, $completedBy)
    {
        $machine = $checklist->machine;
        $patient = $checklist->patient;

        $title = 'Checklist de segurança concluído';
        $message = sprintf(
            'Checklist para %s (Paciente: %s) foi concluído por %s',
            $machine->name ?? 'Máquina desconhecida',
            $patient->full_name ?? 'Paciente desconhecido',
            $completedBy->name
        );

        return static::createNotificationForUnit(
            type: 'success',
            title: $title,
            message: $message,
            unitId: $machine->unit_id ?? $completedBy->unit_id,
            data: [
                'checklist_id' => $checklist->id,
                'machine_id' => $machine->id ?? null,
                'patient_id' => $patient->id ?? null,
            ],
            actionUrl: "/admin/safety-checklists/{$checklist->id}"
        );
    }

    /**
     * Notify about checklist interruption
     */
    public static function notifyChecklistInterrupted($checklist, $reason, $interruptedBy)
    {
        $machine = $checklist->machine;

        $title = 'Checklist interrompido';
        $message = sprintf(
            'Checklist para %s foi interrompido por %s. Motivo: %s',
            $machine->name ?? 'Máquina desconhecida',
            $interruptedBy->name,
            $reason
        );

        return static::createNotificationForUnit(
            type: 'error',
            title: $title,
            message: $message,
            unitId: $machine->unit_id ?? $interruptedBy->unit_id,
            data: [
                'checklist_id' => $checklist->id,
                'machine_id' => $machine->id ?? null,
                'reason' => $reason,
            ],
            actionUrl: "/admin/safety-checklists/{$checklist->id}"
        );
    }

    /**
     * Notify about cleaning completion
     */
    public static function notifyCleaningCompleted($cleaning, $completedBy)
    {
        $machine = $cleaning->machine;
        $typeLabel = [
            'daily' => 'Limpeza diária',
            'weekly' => 'Limpeza semanal',
            'monthly' => 'Limpeza mensal',
        ][$cleaning->cleaning_type] ?? 'Limpeza';

        $title = $typeLabel . ' concluída';
        $message = sprintf(
            '%s da %s foi concluída por %s',
            $typeLabel,
            $machine->name ?? 'Máquina desconhecida',
            $completedBy->name
        );

        return static::createNotificationForUnit(
            type: 'success',
            title: $title,
            message: $message,
            unitId: $machine->unit_id ?? $completedBy->unit_id,
            data: [
                'cleaning_id' => $cleaning->id,
                'machine_id' => $machine->id ?? null,
                'cleaning_type' => $cleaning->cleaning_type,
            ],
            actionUrl: "/admin/cleaning-controls/{$cleaning->id}"
        );
    }

    /**
     * Notify about new patient registration
     */
    public static function notifyPatientCreated($patient, $creator)
    {
        $title = 'Novo paciente cadastrado';
        $message = sprintf(
            'Paciente %s foi cadastrado no sistema por %s',
            $patient->full_name,
            $creator->name
        );

        return static::createNotificationForUnit(
            type: 'info',
            title: $title,
            message: $message,
            unitId: $patient->unit_id ?? $creator->unit_id,
            data: [
                'patient_id' => $patient->id,
            ],
            actionUrl: "/admin/patients/{$patient->id}",
            excludeUserId: $creator->id
        );
    }

    /**
     * Notify about patient status change
     */
    public static function notifyPatientStatusChanged($patient, $oldStatus, $newStatus, $changedBy)
    {
        $title = 'Status de paciente alterado';
        $message = sprintf(
            'Status de %s foi alterado de "%s" para "%s" por %s',
            $patient->full_name,
            $oldStatus->label(),
            $newStatus->label(),
            $changedBy->name
        );

        $type = $newStatus->value === 'obito' ? 'error' : 'warning';

        return static::createNotificationForUnit(
            type: $type,
            title: $title,
            message: $message,
            unitId: $patient->unit_id ?? $changedBy->unit_id,
            data: [
                'patient_id' => $patient->id,
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
            ],
            actionUrl: "/admin/patients/{$patient->id}"
        );
    }

    /**
     * Notify about machine status change
     */
    public static function notifyMachineStatusChanged($machine, $oldStatus, $newStatus, $changedBy)
    {
        $title = 'Status de máquina alterado';
        $message = sprintf(
            'Status da %s foi alterado de "%s" para "%s" por %s',
            $machine->name,
            $oldStatus,
            $newStatus,
            $changedBy->name
        );

        $type = in_array($newStatus, ['em_manutencao', 'inativa']) ? 'warning' : 'info';

        return static::createNotificationForUnit(
            type: $type,
            title: $title,
            message: $message,
            unitId: $machine->unit_id,
            data: [
                'machine_id' => $machine->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
            actionUrl: "/admin/machines/{$machine->id}"
        );
    }

    /**
     * Notify about machine problem
     */
    public static function notifyMachineProblem($machine, $problem, $reportedBy)
    {
        $title = 'Problema reportado em máquina';
        $message = sprintf(
            'Problema reportado na %s por %s: %s',
            $machine->name,
            $reportedBy->name,
            $problem
        );

        return static::createNotificationForUnit(
            type: 'error',
            title: $title,
            message: $message,
            unitId: $machine->unit_id,
            data: [
                'machine_id' => $machine->id,
                'problem' => $problem,
            ],
            actionUrl: "/admin/machines/{$machine->id}"
        );
    }

    /**
     * Notify about chemical disinfection completion
     */
    public static function notifyDisinfectionCompleted($disinfection, $completedBy)
    {
        $machine = $disinfection->machine;

        $title = 'Desinfecção química concluída';
        $message = sprintf(
            'Desinfecção química da %s foi concluída por %s',
            $machine->name ?? 'Máquina desconhecida',
            $completedBy->name
        );

        return static::createNotificationForUnit(
            type: 'success',
            title: $title,
            message: $message,
            unitId: $machine->unit_id ?? $completedBy->unit_id,
            data: [
                'disinfection_id' => $disinfection->id,
                'machine_id' => $machine->id ?? null,
            ],
            actionUrl: "/admin/chemical-disinfections/{$disinfection->id}"
        );
    }

    /**
     * Create notification for all users in a unit (respecting preferences)
     */
    protected static function createNotificationForUnit($type, $title, $message, $unitId, $data = [], $actionUrl = null, $excludeUserId = null)
    {
        // Get all users in the unit who should receive this type of notification
        $users = User::where('unit_id', $unitId)
            ->whereIn('role', ['admin', 'gestor', 'coordenador', 'supervisor'])
            ->when($excludeUserId, fn($q) => $q->where('id', '!=', $excludeUserId))
            ->get();

        $notifications = [];

        foreach ($users as $user) {
            // Check user preferences (will implement later)
            if (static::shouldNotifyUser($user, $type)) {
                $notifications[] = Notification::create([
                    'user_id' => $user->id,
                    'unit_id' => $unitId,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data,
                    'action_url' => $actionUrl,
                ]);
            }
        }

        return $notifications;
    }

    /**
     * Check if user should receive notification based on preferences
     */
    protected static function shouldNotifyUser($user, $type)
    {
        // For now, notify everyone
        // Later we'll check user preferences table
        return true;
    }

    /**
     * Create a broadcast notification (all users in all units)
     */
    public static function broadcastNotification($type, $title, $message, $data = [], $actionUrl = null)
    {
        return Notification::create([
            'user_id' => null, // Broadcast to all
            'unit_id' => null, // All units
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
        ]);
    }
}
