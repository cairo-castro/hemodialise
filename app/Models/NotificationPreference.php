<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'email_new_checklists',
        'email_maintenance',
        'email_weekly_reports',
        'email_system_updates',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_new_checklists' => 'boolean',
        'email_maintenance' => 'boolean',
        'email_weekly_reports' => 'boolean',
        'email_system_updates' => 'boolean',
    ];

    /**
     * Get the user that owns the notification preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create notification preferences for a user.
     */
    public static function forUser(User $user): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email_new_checklists' => true,
                'email_maintenance' => true,
                'email_weekly_reports' => false,
                'email_system_updates' => true,
            ]
        );
    }
}
