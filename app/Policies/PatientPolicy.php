<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PatientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('patients.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Patient $patient): bool
    {
        // Acesso global pode ver qualquer paciente
        if ($user->hasGlobalAccess()) {
            return $user->hasPermissionTo('patients.view');
        }

        // Usuário de unidade só pode ver pacientes da sua unidade
        return $user->hasPermissionTo('patients.view') 
            && $user->canAccessUnit($patient->unit_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('patients.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Patient $patient): bool
    {
        // Acesso global pode atualizar qualquer paciente
        if ($user->hasGlobalAccess()) {
            return $user->hasPermissionTo('patients.update');
        }

        // Usuário de unidade só pode atualizar pacientes da sua unidade
        return $user->hasPermissionTo('patients.update') 
            && $user->canAccessUnit($patient->unit_id);
    }

    /**
     * Determine whether the user can export patient data.
     */
    public function export(User $user): bool
    {
        return $user->hasPermissionTo('patients.export');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Patient $patient): bool
    {
        // Acesso global pode deletar qualquer paciente
        if ($user->hasGlobalAccess()) {
            return $user->hasPermissionTo('patients.delete');
        }

        // Usuário de unidade só pode deletar pacientes da sua unidade
        return $user->hasPermissionTo('patients.delete') 
            && $user->canAccessUnit($patient->unit_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Patient $patient): bool
    {
        return $this->update($user, $patient);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Patient $patient): bool
    {
        return $this->delete($user, $patient);
    }
}
