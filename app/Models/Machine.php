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
}
