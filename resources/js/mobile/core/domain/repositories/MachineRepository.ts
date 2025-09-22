import { Machine } from '../entities/Machine';

export interface MachineRepository {
  getAll(): Promise<Machine[]>;
  getById(id: number): Promise<Machine>;
  getByUnit(unitId: number): Promise<Machine[]>;
  getAvailable(): Promise<Machine[]>;
}