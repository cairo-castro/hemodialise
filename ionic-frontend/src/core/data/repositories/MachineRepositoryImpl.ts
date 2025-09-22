import { MachineRepository } from '../../domain/repositories/MachineRepository';
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
    const response = await this.apiDataSource.get<Machine[]>('/machines', token);
    return response.data;
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
    const response = await this.apiDataSource.get<Machine[]>('/machines/available', token);
    return response.data;
  }
}