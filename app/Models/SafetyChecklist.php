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
        'current_phase',
        'pre_dialysis_started_at',
        'pre_dialysis_completed_at',
        'during_session_started_at',
        'during_session_completed_at',
        'post_dialysis_started_at',
        'post_dialysis_completed_at',
        'is_interrupted',
        'interrupted_at',
        'interruption_reason',
        // Pré-diálise
        'machine_disinfected',
        'capillary_lines_identified',
        'patient_identification_confirmed',
        'vascular_access_evaluated',
        'vital_signs_checked',
        'medications_reviewed',
        'dialyzer_membrane_checked',
        'equipment_functioning_verified',
        // Durante a sessão
        'dialysis_parameters_verified',
        'patient_comfort_assessed',
        'fluid_balance_monitored',
        'alarms_responded',
        // Pós-diálise
        'session_completed_safely',
        'vascular_access_secured',
        'patient_vital_signs_stable',
        'equipment_cleaned',
        'observations',
        'incidents',
    ];

    protected $casts = [
        'session_date' => 'date',
        'pre_dialysis_started_at' => 'datetime',
        'pre_dialysis_completed_at' => 'datetime',
        'during_session_started_at' => 'datetime',
        'during_session_completed_at' => 'datetime',
        'post_dialysis_started_at' => 'datetime',
        'post_dialysis_completed_at' => 'datetime',
        'is_interrupted' => 'boolean',
        'interrupted_at' => 'datetime',
        // Pré-diálise
        'machine_disinfected' => 'boolean',
        'capillary_lines_identified' => 'boolean',
        'patient_identification_confirmed' => 'boolean',
        'vascular_access_evaluated' => 'boolean',
        'vital_signs_checked' => 'boolean',
        'medications_reviewed' => 'boolean',
        'dialyzer_membrane_checked' => 'boolean',
        'equipment_functioning_verified' => 'boolean',
        // Durante a sessão
        'dialysis_parameters_verified' => 'boolean',
        'patient_comfort_assessed' => 'boolean',
        'fluid_balance_monitored' => 'boolean',
        'alarms_responded' => 'boolean',
        // Pós-diálise
        'session_completed_safely' => 'boolean',
        'vascular_access_secured' => 'boolean',
        'patient_vital_signs_stable' => 'boolean',
        'equipment_cleaned' => 'boolean',
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
        $allItems = array_merge(
            $this->getPreDialysisItems(),
            $this->getDuringSessionItems(),
            $this->getPostDialysisItems()
        );

        $completedItems = collect($allItems)
            ->filter(fn($item) => $this->{$item})
            ->count();

        return ($completedItems / count($allItems)) * 100;
    }

    public function getPreDialysisItems(): array
    {
        return [
            'machine_disinfected',
            'capillary_lines_identified',
            'patient_identification_confirmed',
            'vascular_access_evaluated',
            'vital_signs_checked',
            'medications_reviewed',
            'dialyzer_membrane_checked',
            'equipment_functioning_verified',
        ];
    }

    public function getDuringSessionItems(): array
    {
        return [
            'dialysis_parameters_verified',
            'patient_comfort_assessed',
            'fluid_balance_monitored',
            'alarms_responded',
        ];
    }

    public function getPostDialysisItems(): array
    {
        return [
            'session_completed_safely',
            'vascular_access_secured',
            'patient_vital_signs_stable',
            'equipment_cleaned',
        ];
    }

    public function getPhaseCompletionPercentage(string $phase): float
    {
        $methodName = 'get' . ucfirst(str_replace('_', '', $phase)) . 'Items';
        if (!method_exists($this, $methodName)) {
            return 0;
        }

        $items = $this->{$methodName}();
        $completedItems = collect($items)
            ->filter(fn($item) => $this->{$item})
            ->count();

        return count($items) > 0 ? ($completedItems / count($items)) * 100 : 0;
    }

    public function canAdvanceToNextPhase(): bool
    {
        switch ($this->current_phase) {
            case 'pre_dialysis':
                return $this->getPhaseCompletionPercentage('pre_dialysis') === 100.0;
            case 'during_session':
                return $this->getPhaseCompletionPercentage('during_session') === 100.0;
            case 'post_dialysis':
                return $this->getPhaseCompletionPercentage('post_dialysis') === 100.0;
            default:
                return false;
        }
    }

    public function advanceToNextPhase(): void
    {
        $now = now();

        switch ($this->current_phase) {
            case 'pre_dialysis':
                $this->pre_dialysis_completed_at = $now;
                $this->during_session_started_at = $now;
                $this->current_phase = 'during_session';
                break;
            case 'during_session':
                $this->during_session_completed_at = $now;
                $this->post_dialysis_started_at = $now;
                $this->current_phase = 'post_dialysis';
                break;
            case 'post_dialysis':
                $this->post_dialysis_completed_at = $now;
                $this->current_phase = 'completed';
                break;
        }

        $this->save();
    }

    public function interruptSession(string $reason): void
    {
        $this->is_interrupted = true;
        $this->interrupted_at = now();
        $this->interruption_reason = $reason;
        $this->current_phase = 'interrupted';
        $this->save();
    }
}
