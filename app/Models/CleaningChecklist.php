<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningChecklist extends Model
{
    protected $fillable = [
        'machine_id',
        'user_id',
        'checklist_date',
        'shift',
        'chemical_disinfection_time',
        'chemical_disinfection_completed',
        'hd_machine_cleaning',
        'osmosis_cleaning',
        'serum_support_cleaning',
        'observations',
    ];

    protected $casts = [
        'checklist_date' => 'date',
        'chemical_disinfection_time' => 'datetime:H:i',
        'chemical_disinfection_completed' => 'boolean',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
