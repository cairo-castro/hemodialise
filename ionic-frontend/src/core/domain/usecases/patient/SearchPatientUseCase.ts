import { PatientRepository } from '../../repositories/PatientRepository';
import { Patient, PatientSearchCriteria } from '../../entities/Patient';

export class SearchPatientUseCase {
  constructor(private patientRepository: PatientRepository) {}

  async execute(criteria: PatientSearchCriteria): Promise<Patient | null> {
    // Validate input
    if (!criteria.full_name?.trim()) {
      throw new Error('Nome completo é obrigatório');
    }

    if (!criteria.birth_date) {
      throw new Error('Data de nascimento é obrigatória');
    }

    // Validate date format
    if (!this.isValidDate(criteria.birth_date)) {
      throw new Error('Data de nascimento deve estar no formato válido');
    }

    // Normalize search data
    const normalizedCriteria: PatientSearchCriteria = {
      full_name: criteria.full_name.trim().toLowerCase(),
      birth_date: criteria.birth_date
    };

    return await this.patientRepository.search(normalizedCriteria);
  }

  private isValidDate(dateString: string): boolean {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date.getTime());
  }
}