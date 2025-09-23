import { AuthRepository } from '../../domain/repositories/AuthRepository';
import { User, LoginCredentials, AuthToken } from '../../domain/entities/User';
import { ApiDataSource } from '../datasources/ApiDataSource';
import { LocalStorageDataSource } from '../datasources/LocalStorageDataSource';

export class AuthRepositoryImpl implements AuthRepository {
  private readonly TOKEN_KEY = 'auth_token';

  constructor(
    private apiDataSource: ApiDataSource,
    private localStorageDataSource: LocalStorageDataSource
  ) {}

  async login(credentials: LoginCredentials): Promise<AuthToken> {
    const response = await this.apiDataSource.post<AuthToken>('/login', credentials);
    return response.data || response as unknown as AuthToken;
  }

  async getCurrentUser(): Promise<User> {
    const token = this.getStoredToken();
    if (!token) {
      throw new Error('Token não encontrado');
    }

    const response = await this.apiDataSource.get<any>('/me', token);
    // API returns { "user": {...} }, extract the user object
    return (response as any).data?.user || (response as any).user || response;
  }

  async logout(): Promise<void> {
    const token = this.getStoredToken();
    if (token) {
      await this.apiDataSource.post('/logout', {}, token);
    }
  }

  getStoredToken(): string | null {
    return this.localStorageDataSource.get(this.TOKEN_KEY);
  }

  storeToken(token: string): void {
    this.localStorageDataSource.set(this.TOKEN_KEY, token);
  }

  removeToken(): void {
    this.localStorageDataSource.remove(this.TOKEN_KEY);
  }

  isAuthenticated(): boolean {
    const token = this.getStoredToken();
    return token !== null && token !== '';
  }
}