import { CleaningChecklistRepository } from '../repositories/CleaningChecklistRepository';
import { CleaningChecklist, CleaningChecklistCreate } from '../entities/CleaningChecklist';

export class CreateCleaningChecklistUseCase {
  constructor(private cleaningChecklistRepository: CleaningChecklistRepository) {}

  async execute(data: CleaningChecklistCreate): Promise<CleaningChecklist> {
    return await this.cleaningChecklistRepository.create(data);
  }
}
