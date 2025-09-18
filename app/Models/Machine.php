<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    protected $fillable = [
        'name',
        'identifier',
        'description',
        'active',
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

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
