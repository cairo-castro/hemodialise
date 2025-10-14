import { PatientRepository } from '../../repositories/PatientRepository';
import { Patient, CreatePatientData } from '../../entities/Patient';

export class CreatePatientUseCase {
  constructor(private patientRepository: PatientRepository) {}

  async execute(data: CreatePatientData): Promise<Patient> {
    // Validate required fields
    if (!data.full_name?.trim()) {
      throw new Error('Nome completo é obrigatório');
    }

    if (!data.birth_date) {
      throw new Error('Data de nascimento é obrigatória');
    }

    // Validate date
    if (!this.isValidDate(data.birth_date)) {
      throw new Error('Data de nascimento deve estar no formato válido');
    }

    // Validate blood group if provided
    if (data.blood_group && !this.isValidBloodGroup(data.blood_group)) {
      throw new Error('Tipo sanguíneo inválido');
    }

    // Validate RH factor if provided
    if (data.rh_factor && !this.isValidRhFactor(data.rh_factor)) {
      throw new Error('Fator RH inválido');
    }

    // Normalize data
    const normalizedData: CreatePatientData = {
      full_name: data.full_name.trim(),
      birth_date: data.birth_date,
      blood_group: data.blood_group?.trim(),
      rh_factor: data.rh_factor?.trim(),
      allergies: data.allergies?.trim(),
      observations: data.observations?.trim()
    };

    return await this.patientRepository.create(normalizedData);
  }

  private isValidDate(dateString: string): boolean {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date.getTime());
  }

  private isValidBloodGroup(bloodGroup: string): boolean {
    const validGroups = ['A', 'B', 'AB', 'O'];
    return validGroups.includes(bloodGroup);
  }

  private isValidRhFactor(rhFactor: string): boolean {
    const validFactors = ['+', '-'];
    return validFactors.includes(rhFactor);
  }
}