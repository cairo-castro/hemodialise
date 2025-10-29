import { ChecklistRepository } from '../../domain/repositories/ChecklistRepository';
import { SafetyChecklist, CreateChecklistData } from '../../domain/entities/SafetyChecklist';
import { ApiDataSource } from '../datasources/ApiDataSource';
import { LocalStorageDataSource } from '../datasources/LocalStorageDataSource';
import { API_CONFIG } from '@mobile/config/api';

export class ChecklistRepositoryImpl implements ChecklistRepository {
  constructor(
    private apiDataSource: ApiDataSource,
    private localStorageDataSource: LocalStorageDataSource
  ) {}

  async create(data: CreateChecklistData): Promise<SafetyChecklist> {
    // Session-based auth: não precisa de token, usa cookie de sessão
    const response = await this.apiDataSource.post<SafetyChecklist>(API_CONFIG.ENDPOINTS.CHECKLISTS, data);
    return response.data;
  }

  async getById(id: number): Promise<SafetyChecklist> {
    const response = await this.apiDataSource.get<SafetyChecklist>(`${API_CONFIG.ENDPOINTS.CHECKLISTS}/${id}`);
    return response.data;
  }

  async getByUser(userId: number, limit?: number): Promise<SafetyChecklist[]> {
    const params = new URLSearchParams();
    if (limit) params.append('limit', limit.toString());

    const endpoint = `${API_CONFIG.ENDPOINTS.CHECKLISTS_BY_USER}/${userId}${params.toString() ? '?' + params.toString() : ''}`;
    const response = await this.apiDataSource.get<SafetyChecklist[]>(endpoint);
    return response.data;
  }

  async getByMachine(machineId: number, date?: string): Promise<SafetyChecklist[]> {
    const params = new URLSearchParams();
    if (date) params.append('date', date);

    const endpoint = `${API_CONFIG.ENDPOINTS.CHECKLISTS_BY_MACHINE}/${machineId}${params.toString() ? '?' + params.toString() : ''}`;
    const response = await this.apiDataSource.get<SafetyChecklist[]>(endpoint);
    return response.data;
  }

  async getTodaysChecklists(): Promise<SafetyChecklist[]> {
    const today = new Date().toISOString().split('T')[0];
    const response = await this.apiDataSource.get<SafetyChecklist[]>(`${API_CONFIG.ENDPOINTS.CHECKLISTS_TODAY}/${today}`);
    return response.data;
  }

  async getPendingChecklists(): Promise<SafetyChecklist[]> {
    const response = await this.apiDataSource.get<SafetyChecklist[]>(API_CONFIG.ENDPOINTS.CHECKLISTS_PENDING);
    return response.data;
  }
}