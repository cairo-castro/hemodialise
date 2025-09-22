import { ChecklistRepository } from '../../repositories/ChecklistRepository';
import { SafetyChecklist, CreateChecklistData } from '../../entities/SafetyChecklist';

export class CreateChecklistUseCase {
  constructor(private checklistRepository: ChecklistRepository) {}

  async execute(data: CreateChecklistData): Promise<SafetyChecklist> {
    // Validate required fields
    if (!data.patient_id) {
      throw new Error('ID do paciente é obrigatório');
    }

    if (!data.machine_id) {
      throw new Error('ID da máquina é obrigatório');
    }

    if (!data.shift) {
      throw new Error('Turno é obrigatório');
    }

    // Validate shift
    if (!this.isValidShift(data.shift)) {
      throw new Error('Turno deve ser: matutino, vespertino ou noturno');
    }

    // Validate all safety checks are completed
    const allChecksCompleted = [
      data.patient_identification,
      data.access_patency,
      data.vital_signs,
      data.weight_measurement,
      data.machine_parameters,
      data.anticoagulation,
      data.alarm_tests,
      data.emergency_procedures
    ].every(check => check === true);

    if (!allChecksCompleted) {
      throw new Error('Todos os 8 itens de segurança devem ser verificados');
    }

    return await this.checklistRepository.create(data);
  }

  private isValidShift(shift: string): shift is 'matutino' | 'vespertino' | 'noturno' {
    return ['matutino', 'vespertino', 'noturno'].includes(shift);
  }
}