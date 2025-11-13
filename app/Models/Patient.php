<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Enums\PatientStatus;

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
        'active',      // Mantido para compatibilidade
        'status',      // Novo campo
        'unit_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'active' => 'boolean',
        'status' => PatientStatus::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['full_name', 'birth_date', 'blood_group', 'rh_factor', 'allergies', 'status', 'unit_id'])
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

    /**
     * Scope: Pacientes ativos (apenas com status 'ativo')
     */
    public function scopeActive($query)
    {
        return $query->where('status', PatientStatus::ATIVO->value);
    }

    /**
     * Scope: Pacientes que podem ter sessões (status: ativo)
     */
    public function scopeCanHaveSessions($query)
    {
        return $query->where('status', PatientStatus::ATIVO->value);
    }

    /**
     * Scope: Pacientes por unidade
     */
    public function scopeForUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * Scope: Pacientes por status
     */
    public function scopeByStatus($query, PatientStatus $status)
    {
        return $query->where('status', $status->value);
    }

    /**
     * Scope: Excluir pacientes com status terminal (alta, óbito)
     */
    public function scopeExcludeTerminal($query)
    {
        return $query->whereNotIn('status', [PatientStatus::ALTA->value, PatientStatus::OBITO->value]);
    }

    /**
     * Check if patient can have new sessions
     */
    public function canHaveSessions(): bool
    {
        return $this->status === PatientStatus::ATIVO;
    }

    /**
     * Check if patient status is terminal (only óbito is truly terminal)
     * Alta is no longer considered terminal as patients can return
     */
    public function isTerminal(): bool
    {
        return $this->status === PatientStatus::OBITO;
    }

    /**
     * Get status label for display
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? 'Desconhecido';
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status?->color() ?? 'secondary';
    }

    protected static function booted()
    {
        static::created(function ($patient) {
            // Notificar novo paciente cadastrado
            if ($creator = auth()->user()) {
                \App\Services\NotificationService::notifyPatientCreated($patient, $creator);
            }
        });

        static::updated(function ($patient) {
            // Notificar mudança de status
            if ($patient->isDirty('status')) {
                $oldStatus = $patient->getOriginal('status');
                $newStatus = $patient->status;

                if ($changedBy = auth()->user()) {
                    \App\Services\NotificationService::notifyPatientStatusChanged(
                        $patient,
                        $oldStatus,
                        $newStatus,
                        $changedBy
                    );
                }
            }
        });
    }
}
