<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChemicalDisinfection extends Model
{
    protected $fillable = [
        'machine_id',
        'unit_id',
        'user_id',
        'disinfection_date',
        'shift',
        'start_time',
        'end_time',
        'chemical_product',
        'concentration',
        'concentration_unit',
        'contact_time_minutes',
        'initial_temperature',
        'final_temperature',
        'circulation_verified',
        'contact_time_completed',
        'rinse_performed',
        'system_tested',
        'batch_number',
        'expiry_date',
        'effectiveness_verified',
        'observations',
        'responsible_signature',
    ];

    protected $casts = [
        'disinfection_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'expiry_date' => 'date',
        'concentration' => 'decimal:2',
        'initial_temperature' => 'decimal:1',
        'final_temperature' => 'decimal:1',
        'circulation_verified' => 'boolean',
        'contact_time_completed' => 'boolean',
        'rinse_performed' => 'boolean',
        'system_tested' => 'boolean',
        'effectiveness_verified' => 'boolean',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar por unidade (usa campo direto unit_id)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $unitId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUnit($query, $unitId)
    {
        if ($unitId) {
            return $query->where('unit_id', $unitId);
        }

        return $query;
    }

    public function getDurationInMinutesAttribute(): ?int
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        return $this->end_time->diffInMinutes($this->start_time);
    }

    public function getProcessCompletionAttribute(): float
    {
        $steps = [
            'circulation_verified',
            'contact_time_completed',
            'rinse_performed',
            'system_tested',
            'effectiveness_verified',
        ];

        $completedSteps = collect($steps)
            ->filter(fn($step) => $this->{$step})
            ->count();

        return ($completedSteps / count($steps)) * 100;
    }
}
