<?php

namespace App\Policies;

use App\Models\Machine;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MachinePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('machines.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Machine $machine): bool
    {
        // Acesso global pode ver qualquer máquina
        if ($user->hasGlobalAccess()) {
            return $user->hasPermissionTo('machines.view');
        }

        // Usuário de unidade só pode ver máquinas da sua unidade
        return $user->hasPermissionTo('machines.view') 
            && $user->canAccessUnit($machine->unit_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('machines.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Machine $machine): bool
    {
        // Acesso global pode atualizar qualquer máquina
        if ($user->hasGlobalAccess()) {
            return $user->hasPermissionTo('machines.update');
        }

        // Usuário de unidade só pode atualizar máquinas da sua unidade
        return $user->hasPermissionTo('machines.update') 
            && $user->canAccessUnit($machine->unit_id);
    }

    /**
     * Determine whether the user can manage machine status.
     */
    public function manageStatus(User $user, Machine $machine): bool
    {
        // Acesso global pode gerenciar status de qualquer máquina
        if ($user->hasGlobalAccess()) {
            return $user->hasPermissionTo('machines.manage-status');
        }

        // Usuário de unidade só pode gerenciar máquinas da sua unidade
        return $user->hasPermissionTo('machines.manage-status') 
            && $user->canAccessUnit($machine->unit_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Machine $machine): bool
    {
        // Acesso global pode deletar qualquer máquina
        if ($user->hasGlobalAccess()) {
            return $user->hasPermissionTo('machines.delete');
        }

        // Usuário de unidade só pode deletar máquinas da sua unidade
        return $user->hasPermissionTo('machines.delete') 
            && $user->canAccessUnit($machine->unit_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Machine $machine): bool
    {
        return $this->update($user, $machine);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Machine $machine): bool
    {
        return $this->delete($user, $machine);
    }
}
