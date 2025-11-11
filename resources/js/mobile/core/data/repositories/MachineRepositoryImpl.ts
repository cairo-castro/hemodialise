import { MachineRepository, UpdateStatusParams, ToggleActiveParams, CreateMachineParams, UpdateMachineParams } from '../../domain/repositories/MachineRepository';
import { Machine } from '../../domain/entities/Machine';
import { ApiDataSource } from '../datasources/ApiDataSource';
import { LocalStorageDataSource } from '../datasources/LocalStorageDataSource';
import { API_CONFIG } from '@mobile/config/api';

export class MachineRepositoryImpl implements MachineRepository {
  constructor(
    private apiDataSource: ApiDataSource,
    private localStorageDataSource: LocalStorageDataSource
  ) {}

  async getAll(): Promise<Machine[]> {
    // Session-based auth: não precisa de token, usa cookie de sessão
    const response = await this.apiDataSource.get<any>(API_CONFIG.ENDPOINTS.MACHINES);

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
    const response = await this.apiDataSource.get<Machine>(`${API_CONFIG.ENDPOINTS.MACHINES}/${id}`);
    return response.data;
  }

  async getByUnit(unitId: number): Promise<Machine[]> {
    const response = await this.apiDataSource.get<Machine[]>(`${API_CONFIG.ENDPOINTS.MACHINES_BY_UNIT}/${unitId}`);
    return response.data;
  }

  async getAvailable(): Promise<Machine[]> {
    const response = await this.apiDataSource.get<{ success: boolean; machines: Machine[] }>(API_CONFIG.ENDPOINTS.MACHINES_AVAILABLE);

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

  async create(params: CreateMachineParams): Promise<Machine> {
    const response = await this.apiDataSource.post<{ machine: Machine }>(
      API_CONFIG.ENDPOINTS.MACHINES,
      params
    );
    return response.data.machine || response.data as any;
  }

  async update(id: number, params: UpdateMachineParams): Promise<Machine> {
    const response = await this.apiDataSource.put<{ machine: Machine }>(
      `${API_CONFIG.ENDPOINTS.MACHINES}/${id}`,
      params
    );
    return response.data.machine || response.data as any;
  }

  async delete(id: number): Promise<void> {
    await this.apiDataSource.delete(`${API_CONFIG.ENDPOINTS.MACHINES}/${id}`);
  }

  async updateStatus(id: number, params: UpdateStatusParams): Promise<Machine> {
    const response = await this.apiDataSource.put<{ machine: Machine }>(
      `${API_CONFIG.ENDPOINTS.MACHINES}/${id}/status`,
      params
    );
    return response.data.machine;
  }

  async toggleActive(id: number, params: ToggleActiveParams): Promise<Machine> {
    const response = await this.apiDataSource.put<{ machine: Machine }>(
      `${API_CONFIG.ENDPOINTS.MACHINES}/${id}/toggle-active`,
      params
    );
    return response.data.machine;
  }
}