<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'unit_id',
        'type',
        'title',
        'message',
        'data',
        'action_url',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the unit that the notification belongs to
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Scope: Only unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: Only read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope: For a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id'); // Include broadcast notifications
        });
    }

    /**
     * Scope: For a specific unit
     */
    public function scopeForUnit($query, $unitId)
    {
        return $query->where(function($q) use ($unitId) {
            $q->where('unit_id', $unitId)
              ->orWhereNull('unit_id'); // Include system-wide notifications
        });
    }

    /**
     * Scope: Recent notifications (last 30 days)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays(30));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if (!$this->read_at) {
            $this->read_at = Carbon::now();
            $this->save();
        }
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Create a notification for a checklist event
     */
    public static function createChecklistNotification($title, $message, $data = [], $unitId = null, $userId = null)
    {
        return self::create([
            'type' => 'checklist',
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'unit_id' => $unitId,
            'user_id' => $userId,
            'action_url' => $data['checklist_id'] ?? null ? "/admin/safety-checklists/{$data['checklist_id']}" : null,
        ]);
    }

    /**
     * Create a notification for a machine event
     */
    public static function createMachineNotification($title, $message, $data = [], $unitId = null, $userId = null)
    {
        return self::create([
            'type' => 'info',
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'unit_id' => $unitId,
            'user_id' => $userId,
            'action_url' => $data['machine_id'] ?? null ? "/admin/machines/{$data['machine_id']}" : null,
        ]);
    }

    /**
     * Create a notification for a patient event
     */
    public static function createPatientNotification($title, $message, $data = [], $unitId = null, $userId = null)
    {
        return self::create([
            'type' => 'info',
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'unit_id' => $unitId,
            'user_id' => $userId,
            'action_url' => $data['patient_id'] ?? null ? "/admin/patients/{$data['patient_id']}" : null,
        ]);
    }
}
