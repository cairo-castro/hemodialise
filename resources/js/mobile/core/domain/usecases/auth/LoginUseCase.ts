import { AuthRepository } from '../../repositories/AuthRepository';
import { LoginCredentials, AuthToken } from '../../entities/User';

export class LoginUseCase {
  constructor(private authRepository: AuthRepository) {}

  async execute(credentials: LoginCredentials): Promise<AuthToken> {
    // Validate input
    if (!credentials.email || !credentials.password) {
      throw new Error('Email e senha são obrigatórios');
    }

    if (!this.isValidEmail(credentials.email)) {
      throw new Error('Email deve ter um formato válido');
    }

    // Perform login
    const token = await this.authRepository.login(credentials);

    // Store token
    this.authRepository.storeToken(token.access_token);

    return token;
  }

  private isValidEmail(email: string): boolean {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }
}