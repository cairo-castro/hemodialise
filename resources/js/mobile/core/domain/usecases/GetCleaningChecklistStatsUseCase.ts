import { CleaningChecklistRepository } from '../repositories/CleaningChecklistRepository';
import { CleaningChecklistStats } from '../entities/CleaningChecklist';

export class GetCleaningChecklistStatsUseCase {
  constructor(private cleaningChecklistRepository: CleaningChecklistRepository) {}

  async execute(): Promise<CleaningChecklistStats> {
    return await this.cleaningChecklistRepository.getStats();
  }
}
