import { MachineRepository, UpdateStatusParams, ToggleActiveParams } from '../../domain/repositories/MachineRepository';
import { Machine } from '../../domain/entities/Machine';
import { ApiDataSource } from '../datasources/ApiDataSource';
import { LocalStorageDataSource } from '../datasources/LocalStorageDataSource';

export class MachineRepositoryImpl implements MachineRepository {
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

  async getAll(): Promise<Machine[]> {
    const token = this.getToken();
    const response = await this.apiDataSource.get<any>('/machines', token);

    // API returns { success: true, machines: [...] }
    if (response.data && response.data.machines) {
      return response.data.machines;
    }

    // Fallback if data is already an array
    if (Array.isArray(response.data)) {
      return response.data;
    }

    return [];
  }

  async getById(id: number): Promise<Machine> {
    const token = this.getToken();
    const response = await this.apiDataSource.get<Machine>(`/machines/${id}`, token);
    return response.data;
  }

  async getByUnit(unitId: number): Promise<Machine[]> {
    const token = this.getToken();
    const response = await this.apiDataSource.get<Machine[]>(`/machines/unit/${unitId}`, token);
    return response.data;
  }

  async getAvailable(): Promise<Machine[]> {
    const token = this.getToken();
    const response = await this.apiDataSource.get<{ success: boolean; machines: Machine[] }>('/machines/available', token);

    // Try to access machines from different possible structures
    if ((response as any).data?.machines) {
      return (response as any).data.machines;
    }

    if ((response as any).machines) {
      return (response as any).machines;
    }

    // Fallback for direct array response
    return (response as any).data || response as any;
  }

  async updateStatus(id: number, params: UpdateStatusParams): Promise<Machine> {
    const token = this.getToken();
    const response = await this.apiDataSource.put<{ machine: Machine }>(
      `/machines/${id}/status`,
      params,
      token
    );
    return response.data.machine;
  }

  async toggleActive(id: number, params: ToggleActiveParams): Promise<Machine> {
    const token = this.getToken();
    const response = await this.apiDataSource.put<{ machine: Machine }>(
      `/machines/${id}/toggle-active`,
      params,
      token
    );
    return response.data.machine;
  }
}