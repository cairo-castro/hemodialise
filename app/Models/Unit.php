<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Unit extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'manager_name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'address', 'phone', 'manager_name', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }

    public function safetyChecklists(): HasMany
    {
        return $this->hasMany(SafetyChecklist::class);
    }
}
