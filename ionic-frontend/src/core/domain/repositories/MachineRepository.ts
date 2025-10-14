import { Machine } from '../entities/Machine';

export interface UpdateStatusParams {
  status: 'available' | 'maintenance';
  reason?: string;
}

export interface ToggleActiveParams {
  reason?: string;
}

export interface MachineRepository {
  getAll(): Promise<Machine[]>;
  getById(id: number): Promise<Machine>;
  getByUnit(unitId: number): Promise<Machine[]>;
  getAvailable(): Promise<Machine[]>;
  updateStatus(id: number, params: UpdateStatusParams): Promise<Machine>;
  toggleActive(id: number, params: ToggleActiveParams): Promise<Machine>;
}