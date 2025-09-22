import { AuthRepository } from '../../repositories/AuthRepository';

export class LogoutUseCase {
  constructor(private authRepository: AuthRepository) {}

  async execute(): Promise<void> {
    try {
      // Call API to invalidate token
      await this.authRepository.logout();
    } catch (error) {
      // Even if API call fails, remove local token
      console.warn('Erro ao fazer logout na API:', error);
    } finally {
      // Always remove local token
      this.authRepository.removeToken();
    }
  }
}