import { CleaningChecklistRepository, CleaningChecklistFilters } from '../repositories/CleaningChecklistRepository';
import { CleaningChecklist } from '../entities/CleaningChecklist';

export class GetCleaningChecklistsUseCase {
  constructor(private cleaningChecklistRepository: CleaningChecklistRepository) {}

  async execute(filters?: CleaningChecklistFilters): Promise<CleaningChecklist[]> {
    return await this.cleaningChecklistRepository.getAll(filters);
  }
}
