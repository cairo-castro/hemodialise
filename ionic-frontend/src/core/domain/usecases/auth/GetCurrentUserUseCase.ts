import { AuthRepository } from '../../repositories/AuthRepository';
import { User } from '../../entities/User';

export class GetCurrentUserUseCase {
  constructor(private authRepository: AuthRepository) {}

  async execute(): Promise<User> {
    if (!this.authRepository.isAuthenticated()) {
      throw new Error('Usuário não está autenticado');
    }

    try {
      return await this.authRepository.getCurrentUser();
    } catch (error) {
      // If API call fails, remove invalid token
      this.authRepository.removeToken();
      throw new Error('Sessão expirada. Faça login novamente.');
    }
  }
}