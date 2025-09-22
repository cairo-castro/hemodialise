import { SafetyChecklist, CreateChecklistData } from '../entities/SafetyChecklist';

export interface ChecklistRepository {
  create(data: CreateChecklistData): Promise<SafetyChecklist>;
  getById(id: number): Promise<SafetyChecklist>;
  getByUser(userId: number, limit?: number): Promise<SafetyChecklist[]>;
  getByMachine(machineId: number, date?: string): Promise<SafetyChecklist[]>;
  getTodaysChecklists(): Promise<SafetyChecklist[]>;
  getPendingChecklists(): Promise<SafetyChecklist[]>;
}