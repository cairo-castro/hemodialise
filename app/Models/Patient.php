<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Patient extends Model
{
    use LogsActivity;

    protected $fillable = [
        'full_name',
        'birth_date',
        'blood_group',
        'rh_factor',
        'allergies',
        'observations',
        'active',
        'unit_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['full_name', 'birth_date', 'blood_group', 'rh_factor', 'allergies', 'active', 'unit_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $appends = [
        'name',
        'age',
        'blood_type'
    ];

    public function safetyChecklists(): HasMany
    {
        return $this->hasMany(SafetyChecklist::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    public function getAgeAttribute(): int
    {
        return Carbon::parse($this->birth_date)->age;
    }

    public function getBloodTypeAttribute(): ?string
    {
        if ($this->blood_group && $this->rh_factor) {
            return $this->blood_group . $this->rh_factor;
        }
        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }
}
