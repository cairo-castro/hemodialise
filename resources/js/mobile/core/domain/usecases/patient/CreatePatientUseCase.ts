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

    if (!data.medical_record?.trim()) {
      throw new Error('Prontuário médico é obrigatório');
    }

    // Validate date
    if (!this.isValidDate(data.birth_date)) {
      throw new Error('Data de nascimento deve estar no formato válido');
    }

    // Validate blood type if provided
    if (data.blood_type && !this.isValidBloodType(data.blood_type)) {
      throw new Error('Tipo sanguíneo inválido');
    }

    // Normalize data
    const normalizedData: CreatePatientData = {
      full_name: data.full_name.trim(),
      birth_date: data.birth_date,
      medical_record: data.medical_record.trim(),
      blood_type: data.blood_type?.trim(),
      allergies: data.allergies?.trim(),
      observations: data.observations?.trim()
    };

    return await this.patientRepository.create(normalizedData);
  }

  private isValidDate(dateString: string): boolean {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date.getTime());
  }

  private isValidBloodType(bloodType: string): boolean {
    const validTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    return validTypes.includes(bloodType);
  }
}