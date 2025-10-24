<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

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
        'current_unit_id',
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

    /**
     * Unidade atualmente selecionada pelo usuário
     */
    public function currentUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'current_unit_id');
    }

    /**
     * Todas as unidades às quais o usuário tem acesso
     */
    public function units()
    {
        return $this->belongsToMany(Unit::class, 'user_unit')
            ->withPivot('is_primary')
            ->withTimestamps();
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
        // Apenas usuários GLOBAIS podem acessar o admin
        // super-admin, gestor-global e coordenadores globais
        $canAccess = $this->hasRole(['super-admin', 'gestor-global']) || 
                     ($this->role === 'coordenador' && $this->unit_id === null);
        
        \Log::info('User canAccessAdmin check', [
            'user_id' => $this->id,
            'role' => $this->role,
            'unit_id' => $this->unit_id,
            'can_access' => $canAccess
        ]);
        
        return $canAccess;
    }

    /**
     * Verifica se o usuário tem acesso global (pode ver todas as unidades)
     */
    public function hasGlobalAccess(): bool
    {
        return $this->hasRole(['super-admin', 'gestor-global']) || $this->unit_id === null;
    }

    /**
     * Verifica se o usuário pode acessar uma unidade específica
     */
    public function canAccessUnit(int $unitId): bool
    {
        // Acesso global pode acessar qualquer unidade
        if ($this->hasGlobalAccess()) {
            return true;
        }

        // Verifica se o usuário está associado a esta unidade
        return $this->units()->where('unit_id', $unitId)->exists();
    }

    /**
     * Retorna as unidades que o usuário pode acessar
     */
    public function accessibleUnits()
    {
        if ($this->hasGlobalAccess()) {
            return Unit::all();
        }

        // Retorna todas as unidades associadas ao usuário
        return $this->units;
    }

    /**
     * Retorna a unidade ativa para filtragem de dados
     * Prioridade: current_unit_id > unit_id (principal) > primeira unidade associada
     */
    public function getActiveUnit()
    {
        if ($this->current_unit_id) {
            return $this->currentUnit;
        }

        if ($this->unit_id) {
            return $this->unit;
        }

        return $this->units()->first();
    }

    /**
     * Define a unidade atualmente ativa
     */
    public function switchToUnit(int $unitId): bool
    {
        // Verifica se o usuário tem acesso a esta unidade
        if (!$this->canAccessUnit($unitId)) {
            return false;
        }

        $this->current_unit_id = $unitId;
        $this->save();

        return true;
    }

    /**
     * Atribui role com contexto de unidade
     */
    public function assignRoleWithUnit(string $roleName, ?int $unitId = null): self
    {
        $role = \Spatie\Permission\Models\Role::findByName($roleName);
        
        // Se unit_id é fornecido, atribuir role no contexto da unidade
        if ($unitId) {
            $this->roles()->attach($role->id, ['unit_id' => $unitId]);
        } else {
            $this->assignRole($roleName);
        }

        return $this;
    }

    /**
     * Atribui permissão com contexto de unidade
     */
    public function givePermissionWithUnit(string $permissionName, ?int $unitId = null): self
    {
        $permission = \Spatie\Permission\Models\Permission::findByName($permissionName);
        
        // Se unit_id é fornecido, atribuir permissão no contexto da unidade
        if ($unitId) {
            $this->permissions()->attach($permission->id, ['unit_id' => $unitId]);
        } else {
            $this->givePermissionTo($permissionName);
        }

        return $this;
    }

    /**
     * Verifica se possui permissão em uma unidade específica
     */
    public function hasPermissionInUnit(string $permission, int $unitId): bool
    {
        // Acesso global tem todas as permissões
        if ($this->hasGlobalAccess()) {
            return $this->hasPermissionTo($permission);
        }

        // Verificar se tem a permissão globalmente
        if ($this->hasPermissionTo($permission)) {
            return true;
        }

        // Verificar se tem a permissão específica da unidade
        return $this->permissions()
            ->where('name', $permission)
            ->wherePivot('unit_id', $unitId)
            ->exists();
    }
}
