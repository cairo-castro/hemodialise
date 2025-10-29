import { PatientRepository } from '../../domain/repositories/PatientRepository';
import { Patient, PatientSearchCriteria, CreatePatientData } from '../../domain/entities/Patient';
import { ApiDataSource } from '../datasources/ApiDataSource';
import { LocalStorageDataSource } from '../datasources/LocalStorageDataSource';
import { API_CONFIG } from '@mobile/config/api';

export class PatientRepositoryImpl implements PatientRepository {
  constructor(
    private apiDataSource: ApiDataSource,
    private localStorageDataSource: LocalStorageDataSource
  ) {}

  async search(criteria: PatientSearchCriteria): Promise<Patient | null> {
    // Session-based auth: não precisa de token, usa cookie de sessão
    try {
      const response = await this.apiDataSource.post<{ found: boolean; patient?: Patient }>(
        API_CONFIG.ENDPOINTS.PATIENTS_SEARCH,
        {
          full_name: criteria.full_name,
          birth_date: criteria.birth_date
        }
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
    const response = await this.apiDataSource.post<{ success: boolean; patient: Patient }>(API_CONFIG.ENDPOINTS.PATIENTS, data);
    return response.data.patient;
  }

  async getById(id: number): Promise<Patient> {
    const response = await this.apiDataSource.get<any>(`${API_CONFIG.ENDPOINTS.PATIENTS}/${id}`);
    
    // API returns { success: true, patient: {...} }
    if (response.data && response.data.patient) {
      return response.data.patient;
    }
    
    throw new Error('Paciente não encontrado');
  }

  async getAll(searchQuery?: string, perPage: number = 100): Promise<Patient[]> {
    // Construir query params
    const params = new URLSearchParams();
    params.append('per_page', perPage.toString());
    if (searchQuery) {
      params.append('search', searchQuery);
    }

    const url = `${API_CONFIG.ENDPOINTS.PATIENTS}?${params.toString()}`;
    const response = await this.apiDataSource.get<any>(url);

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

  async quickSearch(query: string, limit: number = 20): Promise<Patient[]> {
    // Construir query params
    const params = new URLSearchParams();
    params.append('query', query);
    params.append('limit', limit.toString());

    const url = `${API_CONFIG.ENDPOINTS.PATIENTS_QUICK_SEARCH}?${params.toString()}`;
    const response = await this.apiDataSource.get<any>(url);

    // API returns { success: true, patients: [...] }
    if (response.data && response.data.patients) {
      return response.data.patients;
    }

    return [];
  }

  async update(id: number, data: Partial<CreatePatientData>): Promise<Patient> {
    const response = await this.apiDataSource.put<Patient>(`${API_CONFIG.ENDPOINTS.PATIENTS}/${id}`, data);
    return response.data;
  }
}