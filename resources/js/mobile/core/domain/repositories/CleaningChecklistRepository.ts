import { CleaningChecklist, CleaningChecklistCreate, CleaningChecklistStats } from '../entities/CleaningChecklist';

export interface CleaningChecklistFilters {
  start_date?: string;
  end_date?: string;
  machine_id?: number;
  shift?: '1' | '2' | '3' | '4';
}

export interface CleaningChecklistRepository {
  getAll(filters?: CleaningChecklistFilters): Promise<CleaningChecklist[]>;
  getById(id: number): Promise<CleaningChecklist>;
  create(data: CleaningChecklistCreate): Promise<CleaningChecklist>;
  update(id: number, data: Partial<CleaningChecklistCreate>): Promise<CleaningChecklist>;
  delete(id: number): Promise<void>;
  getStats(): Promise<CleaningChecklistStats>;
}
