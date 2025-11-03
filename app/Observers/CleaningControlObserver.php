<?php

namespace App\Observers;

use App\Models\CleaningControl;
use App\Services\NotificationService;

class CleaningControlObserver
{
    /**
     * Handle the CleaningControl "created" event.
     */
    public function created(CleaningControl $cleaningControl): void
    {
        // Notify when cleaning is completed (on creation)
        if ($creator = auth()->user()) {
            NotificationService::notifyCleaningCompleted($cleaningControl, $creator);
        }
    }

    /**
     * Handle the CleaningControl "updated" event.
     */
    public function updated(CleaningControl $cleaningControl): void
    {
        //
    }

    /**
     * Handle the CleaningControl "deleted" event.
     */
    public function deleted(CleaningControl $cleaningControl): void
    {
        //
    }

    /**
     * Handle the CleaningControl "restored" event.
     */
    public function restored(CleaningControl $cleaningControl): void
    {
        //
    }

    /**
     * Handle the CleaningControl "force deleted" event.
     */
    public function forceDeleted(CleaningControl $cleaningControl): void
    {
        //
    }
}
