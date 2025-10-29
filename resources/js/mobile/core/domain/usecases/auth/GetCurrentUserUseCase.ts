import { AuthRepository } from '../../repositories/AuthRepository';
import { User } from '../../entities/User';

export class GetCurrentUserUseCase {
  constructor(private authRepository: AuthRepository) {}

  async execute(): Promise<User> {
    // Session-based auth: não verifica flag antes da primeira chamada
    // A API retornará 401 se a sessão for inválida
    try {
      return await this.authRepository.getCurrentUser();
    } catch (error) {
      // If API call fails, remove invalid session flag
      this.authRepository.removeToken();
      throw new Error('Sessão expirada. Faça login novamente.');
    }
  }
}