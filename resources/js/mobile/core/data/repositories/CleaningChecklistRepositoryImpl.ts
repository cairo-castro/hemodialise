import { CleaningChecklistRepository, CleaningChecklistFilters } from '../../domain/repositories/CleaningChecklistRepository';
import { CleaningChecklist, CleaningChecklistCreate, CleaningChecklistStats } from '../../domain/entities/CleaningChecklist';
import { ApiDataSource } from '../datasources/ApiDataSource';
import { AuthRepository } from '../../domain/repositories/AuthRepository';

export class CleaningChecklistRepositoryImpl implements CleaningChecklistRepository {
  constructor(
    private apiDataSource: ApiDataSource,
    private authRepository: AuthRepository
  ) {}

  private getToken(): string {
    const token = this.authRepository.getStoredToken();
    if (!token) throw new Error('Token não encontrado');
    return token;
  }

  async getAll(filters?: CleaningChecklistFilters): Promise<CleaningChecklist[]> {
    const token = this.getToken();
    const queryParams = new URLSearchParams();

    if (filters) {
      if (filters.start_date) queryParams.append('start_date', filters.start_date);
      if (filters.end_date) queryParams.append('end_date', filters.end_date);
      if (filters.machine_id) queryParams.append('machine_id', filters.machine_id.toString());
      if (filters.shift) queryParams.append('shift', filters.shift);
    }

    const query = queryParams.toString();
    const endpoint = `/cleaning-checklists${query ? `?${query}` : ''}`;

    const response = await this.apiDataSource.get<any>(endpoint, token);

    if (response.data && response.data.checklists) {
      return response.data.checklists;
    }
    if (Array.isArray(response.data)) {
      return response.data;
    }
    return [];
  }

  async getById(id: number): Promise<CleaningChecklist> {
    const token = this.getToken();
    const response = await this.apiDataSource.get<any>(`/cleaning-checklists/${id}`, token);

    if (response.data && response.data.checklist) {
      return response.data.checklist;
    }
    throw new Error('Checklist não encontrado');
  }

  async create(data: CleaningChecklistCreate): Promise<CleaningChecklist> {
    const token = this.getToken();
    const response = await this.apiDataSource.post<any>('/cleaning-checklists', data, token);

    if (response.data && response.data.checklist) {
      return response.data.checklist;
    }
    throw new Error('Erro ao criar checklist');
  }

  async update(id: number, data: Partial<CleaningChecklistCreate>): Promise<CleaningChecklist> {
    const token = this.getToken();
    const response = await this.apiDataSource.put<any>(`/cleaning-checklists/${id}`, data, token);

    if (response.data && response.data.checklist) {
      return response.data.checklist;
    }
    throw new Error('Erro ao atualizar checklist');
  }

  async delete(id: number): Promise<void> {
    const token = this.getToken();
    await this.apiDataSource.delete(`/cleaning-checklists/${id}`, token);
  }

  async getStats(): Promise<CleaningChecklistStats> {
    const token = this.getToken();
    const response = await this.apiDataSource.get<any>('/cleaning-checklists/stats', token);

    if (response.data && response.data.stats) {
      return response.data.stats;
    }
    throw new Error('Erro ao obter estatísticas');
  }
}
