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
    const params = new URLSearchParams({
      full_name: criteria.full_name,
      birth_date: criteria.birth_date
    });

    try {
      const response = await this.apiDataSource.get<Patient>(
        `/patients/search?${params.toString()}`,
        token
      );
      return response.data;
    } catch (error: any) {
      if (error.status === 404) {
        return null; // Patient not found
      }
      throw error;
    }
  }

  async create(data: CreatePatientData): Promise<Patient> {
    const token = this.getToken();
    const response = await this.apiDataSource.post<{ success: boolean; patient: Patient }>('/patients', data, token);
    return response.data.patient;
  }

  async getById(id: number): Promise<Patient> {
    const token = this.getToken();
    const response = await this.apiDataSource.get<Patient>(`/patients/${id}`, token);
    return response.data;
  }

  async getAll(): Promise<Patient[]> {
    const token = this.getToken();
    const response = await this.apiDataSource.get<Patient[]>('/patients', token);
    return response.data;
  }

  async update(id: number, data: Partial<CreatePatientData>): Promise<Patient> {
    const token = this.getToken();
    const response = await this.apiDataSource.put<Patient>(`/patients/${id}`, data, token);
    return response.data;
  }
}