<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningControl extends Model
{
    protected $fillable = [
        'machine_id',
        'user_id',
        'cleaning_date',
        'shift',
        'cleaning_time',
        'daily_cleaning',
        'weekly_cleaning',
        'monthly_cleaning',
        'special_cleaning',
        'cleaning_products_used',
        'cleaning_procedure',
        'external_cleaning_done',
        'internal_cleaning_done',
        'filter_replacement',
        'system_disinfection',
        'observations',
        'responsible_signature',
    ];

    protected $casts = [
        'cleaning_date' => 'date',
        'cleaning_time' => 'datetime:H:i',
        'daily_cleaning' => 'boolean',
        'weekly_cleaning' => 'boolean',
        'monthly_cleaning' => 'boolean',
        'special_cleaning' => 'boolean',
        'external_cleaning_done' => 'boolean',
        'internal_cleaning_done' => 'boolean',
        'filter_replacement' => 'boolean',
        'system_disinfection' => 'boolean',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCleaningTypeAttribute(): string
    {
        if ($this->special_cleaning) return 'Limpeza Especial';
        if ($this->monthly_cleaning) return 'Limpeza Mensal';
        if ($this->weekly_cleaning) return 'Limpeza Semanal';
        if ($this->daily_cleaning) return 'Limpeza Diária';
        return 'Não especificado';
    }
}
