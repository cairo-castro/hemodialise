import { CleaningChecklistRepository, CleaningChecklistFilters } from '../../domain/repositories/CleaningChecklistRepository';
import { CleaningChecklist, CleaningChecklistCreate, CleaningChecklistStats } from '../../domain/entities/CleaningChecklist';
import { ApiDataSource } from '../datasources/ApiDataSource';
import { AuthRepository } from '../../domain/repositories/AuthRepository';
import { API_CONFIG } from '@mobile/config/api';

export class CleaningChecklistRepositoryImpl implements CleaningChecklistRepository {
  constructor(
    private apiDataSource: ApiDataSource,
    private authRepository: AuthRepository
  ) {}

  async getAll(filters?: CleaningChecklistFilters): Promise<CleaningChecklist[]> {
    // Session-based auth: não precisa de token, usa cookie de sessão
    const queryParams = new URLSearchParams();

    if (filters) {
      if (filters.start_date) queryParams.append('start_date', filters.start_date);
      if (filters.end_date) queryParams.append('end_date', filters.end_date);
      if (filters.machine_id) queryParams.append('machine_id', filters.machine_id.toString());
      if (filters.shift) queryParams.append('shift', filters.shift);
    }

    const query = queryParams.toString();
    const endpoint = `${API_CONFIG.ENDPOINTS.CLEANING_CHECKLISTS}${query ? `?${query}` : ''}`;

    const response = await this.apiDataSource.get<any>(endpoint);

    if (response.data && response.data.checklists) {
      return response.data.checklists;
    }
    if (Array.isArray(response.data)) {
      return response.data;
    }
    return [];
  }

  async getById(id: number): Promise<CleaningChecklist> {
    const response = await this.apiDataSource.get<any>(`${API_CONFIG.ENDPOINTS.CLEANING_CHECKLISTS}/${id}`);

    if (response.data && response.data.checklist) {
      return response.data.checklist;
    }
    throw new Error('Checklist não encontrado');
  }

  async create(data: CleaningChecklistCreate): Promise<CleaningChecklist> {
    const response = await this.apiDataSource.post<any>(API_CONFIG.ENDPOINTS.CLEANING_CHECKLISTS, data);

    if (response.data && response.data.checklist) {
      return response.data.checklist;
    }
    throw new Error('Erro ao criar checklist');
  }

  async update(id: number, data: Partial<CleaningChecklistCreate>): Promise<CleaningChecklist> {
    const response = await this.apiDataSource.put<any>(`${API_CONFIG.ENDPOINTS.CLEANING_CHECKLISTS}/${id}`, data);

    if (response.data && response.data.checklist) {
      return response.data.checklist;
    }
    throw new Error('Erro ao atualizar checklist');
  }

  async delete(id: number): Promise<void> {
    await this.apiDataSource.delete(`${API_CONFIG.ENDPOINTS.CLEANING_CHECKLISTS}/${id}`);
  }

  async getStats(): Promise<CleaningChecklistStats> {
    const response = await this.apiDataSource.get<any>(API_CONFIG.ENDPOINTS.CLEANING_CHECKLISTS_STATS);

    if (response.data && response.data.stats) {
      return response.data.stats;
    }
    throw new Error('Erro ao obter estatísticas');
  }
}
