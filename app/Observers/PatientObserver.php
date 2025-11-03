<?php

namespace App\Observers;

use App\Models\Patient;
use App\Services\NotificationService;

class PatientObserver
{
    /**
     * Handle the Patient "created" event.
     */
    public function created(Patient $patient): void
    {
        // Notify about new patient registration
        if ($creator = auth()->user()) {
            NotificationService::notifyPatientCreated($patient, $creator);
        }
    }

    /**
     * Handle the Patient "updated" event.
     */
    public function updated(Patient $patient): void
    {
        // Check if status changed
        if ($patient->wasChanged('status')) {
            $oldStatus = $patient->getOriginal('status');
            $newStatus = $patient->status;

            if ($changedBy = auth()->user()) {
                NotificationService::notifyPatientStatusChanged(
                    $patient,
                    $oldStatus,
                    $newStatus,
                    $changedBy
                );
            }
        }
    }

    /**
     * Handle the Patient "deleted" event.
     */
    public function deleted(Patient $patient): void
    {
        //
    }

    /**
     * Handle the Patient "restored" event.
     */
    public function restored(Patient $patient): void
    {
        //
    }

    /**
     * Handle the Patient "force deleted" event.
     */
    public function forceDeleted(Patient $patient): void
    {
        //
    }
}
