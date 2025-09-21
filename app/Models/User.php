<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'unit_id',
        'default_view',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'unit_id' => $this->unit_id,
            'default_view' => $this->default_view,
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGestor(): bool
    {
        return $this->role === 'gestor';
    }

    public function isCoordenador(): bool
    {
        return $this->role === 'coordenador';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isTecnico(): bool
    {
        return $this->role === 'tecnico';
    }

    public function canToggleViews(): bool
    {
        return in_array($this->role, ['gestor', 'coordenador', 'supervisor', 'admin']);
    }

    public function canAccessMobile(): bool
    {
        return in_array($this->role, ['tecnico', 'gestor', 'coordenador', 'supervisor']);
    }

    public function canAccessDesktop(): bool
    {
        return in_array($this->role, ['gestor', 'coordenador', 'supervisor', 'admin']);
    }

    public function canAccessAdmin(): bool
    {
        $canAccess = in_array($this->role, ['gestor', 'coordenador', 'supervisor', 'admin']);
        \Log::info('User canAccessAdmin check', [
            'user_id' => $this->id,
            'role' => $this->role,
            'can_access' => $canAccess
        ]);
        return $canAccess;
    }
}
