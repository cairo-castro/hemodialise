import { AuthRepository } from '../../domain/repositories/AuthRepository';
import { User, LoginCredentials, AuthToken } from '../../domain/entities/User';
import { ApiDataSource } from '../datasources/ApiDataSource';
import { LocalStorageDataSource } from '../datasources/LocalStorageDataSource';
import { API_CONFIG } from '@mobile/config/api';

export class AuthRepositoryImpl implements AuthRepository {
  private readonly TOKEN_KEY = 'auth_token';
  private readonly AUTH_FLAG_KEY = 'is_authenticated';

  constructor(
    private apiDataSource: ApiDataSource,
    private localStorageDataSource: LocalStorageDataSource
  ) {}

  async login(credentials: LoginCredentials): Promise<AuthToken> {
    const response = await this.apiDataSource.post<AuthToken>(API_CONFIG.ENDPOINTS.LOGIN, credentials);

    // Session-based auth: marca como autenticado após login bem-sucedido
    if (response.success || (response as any).success) {
      this.localStorageDataSource.set(this.AUTH_FLAG_KEY, 'true');
    }

    return response.data || response as unknown as AuthToken;
  }

  async getCurrentUser(): Promise<User> {
    // Session-based auth: não precisa de token, usa cookie de sessão
    const response = await this.apiDataSource.get<any>(API_CONFIG.ENDPOINTS.ME);
    // API returns { "user": {...} }, extract the user object
    return (response as any).data?.user || (response as any).user || response;
  }

  async logout(): Promise<void> {
    // Session-based auth: chama logout para destruir sessão
    await this.apiDataSource.post(API_CONFIG.ENDPOINTS.LOGOUT, {});
    this.removeToken();
  }

  getStoredToken(): string | null {
    // Para compatibilidade com data sync que verifica token
    return this.localStorageDataSource.get(this.AUTH_FLAG_KEY);
  }

  storeToken(token: string): void {
    this.localStorageDataSource.set(this.AUTH_FLAG_KEY, token);
  }

  removeToken(): void {
    this.localStorageDataSource.remove(this.AUTH_FLAG_KEY);
    this.localStorageDataSource.remove(this.TOKEN_KEY);
  }

  isAuthenticated(): boolean {
    return this.localStorageDataSource.get(this.AUTH_FLAG_KEY) === 'true';
  }
}