<?php

namespace App\Http\Requests;

use App\Models\Machine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreChecklistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'machine_id' => 'required|exists:machines,id',
            'patient_id' => 'required|exists:patients,id',
            'shift' => 'required|in:matutino,vespertino,noturno,madrugada',
            'observations' => 'nullable|string',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateMachineAvailability($validator);
        });
    }

    /**
     * Validar disponibilidade de máquinas
     */
    protected function validateMachineAvailability($validator)
    {
        $machineId = $this->input('machine_id');
        
        if (!$machineId) {
            return; // Será pego pela validação básica
        }

        // 1. Verificar se a máquina específica está disponível
        $machine = Machine::find($machineId);
        
        if (!$machine) {
            return; // Será pego pela validação exists
        }

        // Verificar se máquina está ativa
        if (!$machine->active) {
            $validator->errors()->add(
                'machine_id',
                'A máquina selecionada está desativada e não pode ser usada.'
            );
            return;
        }

        // Verificar status da máquina
        if ($machine->status === 'maintenance') {
            $validator->errors()->add(
                'machine_id',
                'A máquina selecionada está em manutenção. Por favor, escolha outra máquina.'
            );
            return;
        }

        if ($machine->status === 'occupied') {
            $validator->errors()->add(
                'machine_id',
                'A máquina selecionada já está ocupada com outra sessão. Por favor, escolha outra máquina.'
            );
            return;
        }

        if ($machine->status === 'reserved') {
            // Verificar se a reserva é antiga (mais de 30 minutos)
            $currentChecklist = $machine->getCurrentChecklist();
            
            if ($currentChecklist) {
                $reservedMinutes = now()->diffInMinutes($currentChecklist->created_at);
                
                if ($reservedMinutes < 30) {
                    $validator->errors()->add(
                        'machine_id',
                        "A máquina está reservada para outro checklist. Por favor, escolha outra máquina."
                    );
                    return;
                }
            }
        }

        // 2. Verificar se há pelo menos uma máquina disponível na unidade do usuário
        $this->validateUnitHasAvailableMachines($validator);
    }

    /**
     * Validar se a unidade tem máquinas disponíveis
     */
    protected function validateUnitHasAvailableMachines($validator)
    {
        $user = $this->user();
        
        if (!$user) {
            return;
        }

        // Para técnicos, verificar apenas sua unidade
        $query = Machine::where('active', true);
        
        if ($user->isTecnico()) {
            $query->where('unit_id', $user->unit_id);
        }

        // Contar máquinas por status
        $availableCount = (clone $query)->where('status', 'available')->count();
        $totalCount = $query->count();

        // Se não há máquinas disponíveis
        if ($availableCount === 0) {
            $occupiedCount = (clone $query)->where('status', 'occupied')->count();
            $maintenanceCount = (clone $query)->where('status', 'maintenance')->count();
            $reservedCount = (clone $query)->where('status', 'reserved')->count();

            $message = "Não há máquinas disponíveis no momento. ";
            
            if ($occupiedCount > 0) {
                $message .= "Ocupadas: {$occupiedCount}. ";
            }
            if ($maintenanceCount > 0) {
                $message .= "Em manutenção: {$maintenanceCount}. ";
            }
            if ($reservedCount > 0) {
                $message .= "Reservadas: {$reservedCount}. ";
            }
            
            $message .= "Por favor, aguarde até que uma máquina fique disponível.";

            throw ValidationException::withMessages([
                'machine_id' => [$message]
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'machine_id.required' => 'Selecione uma máquina para iniciar o checklist.',
            'machine_id.exists' => 'A máquina selecionada não foi encontrada.',
            'patient_id.required' => 'Selecione um paciente para iniciar o checklist.',
            'patient_id.exists' => 'O paciente selecionado não foi encontrado.',
            'shift.required' => 'Selecione o turno da sessão.',
            'shift.in' => 'O turno selecionado é inválido.',
        ];
    }
}
