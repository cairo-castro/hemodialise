<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SafetyChecklist extends Model
{
    protected $fillable = [
        'patient_id',
        'machine_id',
        'user_id',
        'session_date',
        'shift',
        'machine_disinfected',
        'capillary_lines_identified',
        'patient_identification_confirmed',
        'vascular_access_evaluated',
        'vital_signs_checked',
        'medications_reviewed',
        'dialyzer_membrane_checked',
        'equipment_functioning_verified',
        'observations',
        'incidents',
    ];

    protected $casts = [
        'session_date' => 'date',
        'machine_disinfected' => 'boolean',
        'capillary_lines_identified' => 'boolean',
        'patient_identification_confirmed' => 'boolean',
        'vascular_access_evaluated' => 'boolean',
        'vital_signs_checked' => 'boolean',
        'medications_reviewed' => 'boolean',
        'dialyzer_membrane_checked' => 'boolean',
        'equipment_functioning_verified' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCompletionPercentageAttribute(): float
    {
        $checklistItems = [
            'machine_disinfected',
            'capillary_lines_identified',
            'patient_identification_confirmed',
            'vascular_access_evaluated',
            'vital_signs_checked',
            'medications_reviewed',
            'dialyzer_membrane_checked',
            'equipment_functioning_verified',
        ];

        $completedItems = collect($checklistItems)
            ->filter(fn($item) => $this->{$item})
            ->count();

        return ($completedItems / count($checklistItems)) * 100;
    }
}
