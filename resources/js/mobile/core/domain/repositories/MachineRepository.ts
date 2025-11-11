import { Machine } from '../entities/Machine';

export interface UpdateStatusParams {
  status: 'available' | 'maintenance';
  reason?: string;
}

export interface ToggleActiveParams {
  reason?: string;
}

export interface CreateMachineParams {
  name: string;
  identifier: string;
  description?: string;
  unit_id: number;
}

export interface UpdateMachineParams {
  name?: string;
  identifier?: string;
  description?: string;
  unit_id?: number;
}

export interface MachineRepository {
  getAll(): Promise<Machine[]>;
  getById(id: number): Promise<Machine>;
  getByUnit(unitId: number): Promise<Machine[]>;
  getAvailable(): Promise<Machine[]>;
  create(params: CreateMachineParams): Promise<Machine>;
  update(id: number, params: UpdateMachineParams): Promise<Machine>;
  delete(id: number): Promise<void>;
  updateStatus(id: number, params: UpdateStatusParams): Promise<Machine>;
  toggleActive(id: number, params: ToggleActiveParams): Promise<Machine>;
}