<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Machine extends Model
{
    protected $fillable = [
        'name',
        'identifier',
        'description',
        'active',
        'unit_id',
        'status',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function safetyChecklists(): HasMany
    {
        return $this->hasMany(SafetyChecklist::class);
    }

    public function cleaningControls(): HasMany
    {
        return $this->hasMany(CleaningControl::class);
    }

    public function chemicalDisinfections(): HasMany
    {
        return $this->hasMany(ChemicalDisinfection::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('active', true);
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->active;
    }

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    public function isReserved(): bool
    {
        return $this->status === 'reserved';
    }

    public function markAsReserved(): void
    {
        $this->update(['status' => 'reserved']);
    }

    public function markAsOccupied(): void
    {
        $this->update(['status' => 'occupied']);
    }

    public function markAsAvailable(): void
    {
        $this->update(['status' => 'available']);
    }

    public function markAsMaintenance(): void
    {
        $this->update(['status' => 'maintenance']);
    }

    public function getCurrentChecklist()
    {
        return $this->safetyChecklists()
                   ->whereIn('current_phase', ['pre_dialysis', 'during_session', 'post_dialysis'])
                   ->where('is_interrupted', false)
                   ->whereNull('interrupted_at')
                   ->latest()
                   ->first();
    }
}
