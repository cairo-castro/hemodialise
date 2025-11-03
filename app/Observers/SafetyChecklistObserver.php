<?php

namespace App\Observers;

use App\Models\SafetyChecklist;
use App\Services\NotificationService;

class SafetyChecklistObserver
{
    /**
     * Handle the SafetyChecklist "created" event.
     */
    public function created(SafetyChecklist $safetyChecklist): void
    {
        // Notify about new checklist creation
        if ($creator = auth()->user()) {
            NotificationService::notifyChecklistCreated($safetyChecklist, $creator);
        }
    }

    /**
     * Handle the SafetyChecklist "updated" event.
     */
    public function updated(SafetyChecklist $safetyChecklist): void
    {
        // Check if status changed to completed
        if ($safetyChecklist->wasChanged('status') && $safetyChecklist->status === 'completed') {
            if ($completedBy = auth()->user()) {
                NotificationService::notifyChecklistCompleted($safetyChecklist, $completedBy);
            }
        }

        // Check if checklist was interrupted
        if ($safetyChecklist->wasChanged('status') && $safetyChecklist->status === 'interrupted') {
            if ($interruptedBy = auth()->user()) {
                $reason = $safetyChecklist->interruption_reason ?? 'Motivo não especificado';
                NotificationService::notifyChecklistInterrupted($safetyChecklist, $reason, $interruptedBy);
            }
        }
    }

    /**
     * Handle the SafetyChecklist "deleted" event.
     */
    public function deleted(SafetyChecklist $safetyChecklist): void
    {
        //
    }

    /**
     * Handle the SafetyChecklist "restored" event.
     */
    public function restored(SafetyChecklist $safetyChecklist): void
    {
        //
    }

    /**
     * Handle the SafetyChecklist "force deleted" event.
     */
    public function forceDeleted(SafetyChecklist $safetyChecklist): void
    {
        //
    }
}
