import { ChecklistRepository } from '../../domain/repositories/ChecklistRepository';
import { SafetyChecklist, CreateChecklistData } from '../../domain/entities/SafetyChecklist';
import { ApiDataSource } from '../datasources/ApiDataSource';
import { LocalStorageDataSource } from '../datasources/LocalStorageDataSource';

export class ChecklistRepositoryImpl implements ChecklistRepository {
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

  async create(data: CreateChecklistData): Promise<SafetyChecklist> {
    const token = this.getToken();
    const response = await this.apiDataSource.post<SafetyChecklist>('/checklists', data, token);
    return response.data;
  }

  async getById(id: number): Promise<SafetyChecklist> {
    const token = this.getToken();
    const response = await this.apiDataSource.get<SafetyChecklist>(`/checklists/${id}`, token);
    return response.data;
  }

  async getByUser(userId: number, limit?: number): Promise<SafetyChecklist[]> {
    const token = this.getToken();
    const params = new URLSearchParams();
    if (limit) params.append('limit', limit.toString());

    const endpoint = `/checklists/user/${userId}${params.toString() ? '?' + params.toString() : ''}`;
    const response = await this.apiDataSource.get<SafetyChecklist[]>(endpoint, token);
    return response.data;
  }

  async getByMachine(machineId: number, date?: string): Promise<SafetyChecklist[]> {
    const token = this.getToken();
    const params = new URLSearchParams();
    if (date) params.append('date', date);

    const endpoint = `/checklists/machine/${machineId}${params.toString() ? '?' + params.toString() : ''}`;
    const response = await this.apiDataSource.get<SafetyChecklist[]>(endpoint, token);
    return response.data;
  }

  async getTodaysChecklists(): Promise<SafetyChecklist[]> {
    const token = this.getToken();
    const today = new Date().toISOString().split('T')[0];
    const response = await this.apiDataSource.get<SafetyChecklist[]>(`/checklists/today/${today}`, token);
    return response.data;
  }

  async getPendingChecklists(): Promise<SafetyChecklist[]> {
    const token = this.getToken();
    const response = await this.apiDataSource.get<SafetyChecklist[]>('/checklists/pending', token);
    return response.data;
  }
}