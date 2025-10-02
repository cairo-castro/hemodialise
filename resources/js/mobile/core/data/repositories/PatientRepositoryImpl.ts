import { PatientRepository } from '../../domain/repositories/PatientRepository';
import { Patient, PatientSearchCriteria, CreatePatientData } from '../../domain/entities/Patient';
import { ApiDataSource } from '../datasources/ApiDataSource';
import { LocalStorageDataSource } from '../datasources/LocalStorageDataSource';

export class PatientRepositoryImpl implements PatientRepository {
  constructor(
    private apiDataSource: ApiDataSource,
    private localStorageDataSource: LocalStorageDataSource
  ) {}

  private getToken(): string {
    const token = this.localStorageDataSource.get('auth_token');
    if (!token) {
      throw new Error('Token de autenticação não encontrado');
    }
    return token;
  }

  async search(criteria: PatientSearchCriteria): Promise<Patient | null> {
    const token = this.getToken();

    try {
      const response = await this.apiDataSource.post<{ found: boolean; patient?: Patient }>(
        '/patients/search',
        {
          full_name: criteria.full_name,
          birth_date: criteria.birth_date
        },
        token
      );

      if (response.data.found && response.data.patient) {
        return response.data.patient;
      }

      return null; // Patient not found
    } catch (error: any) {
      if (error.status === 404 || error.status === 422) {
        return null; // Patient not found or validation error
      }
      throw error;
    }
  }

  async create(data: CreatePatientData): Promise<Patient> {
    const token = this.getToken();
    const response = await this.apiDataSource.post<Patient>('/patients', data, token);
    return response.data;
  }

  async getById(id: number): Promise<Patient> {
    const token = this.getToken();
    const response = await this.apiDataSource.get<Patient>(`/patients/${id}`, token);
    return response.data;
  }

  async getAll(): Promise<Patient[]> {
    const token = this.getToken();
    const response = await this.apiDataSource.get<any>('/patients', token);

    // API returns { success: true, patients: [...] }
    if (response.data && response.data.patients) {
      return response.data.patients;
    }

    // Fallback if data is already an array
    if (Array.isArray(response.data)) {
      return response.data;
    }

    return [];
  }

  async update(id: number, data: Partial<CreatePatientData>): Promise<Patient> {
    const token = this.getToken();
    const response = await this.apiDataSource.put<Patient>(`/patients/${id}`, data, token);
    return response.data;
  }
}