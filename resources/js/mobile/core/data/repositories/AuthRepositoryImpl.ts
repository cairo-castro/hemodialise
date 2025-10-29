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
    // Ensure we have a fresh CSRF token before attempting login
    await this.refreshCsrfToken();

    const response = await this.apiDataSource.post<AuthToken>(API_CONFIG.ENDPOINTS.LOGIN, credentials);

    // Session-based auth: NÃO marca como autenticado aqui
    // A flag será definida quando getCurrentUser() for bem-sucedido

    return response.data || response as unknown as AuthToken;
  }

  async getCurrentUser(): Promise<User> {
    // Session-based auth: não precisa de token, usa cookie de sessão
    const response = await this.apiDataSource.get<any>(API_CONFIG.ENDPOINTS.ME);

    // Se chegou aqui, a sessão é válida - marca como autenticado
    this.localStorageDataSource.set(this.AUTH_FLAG_KEY, 'true');

    // API returns { "user": {...} }, extract the user object
    return (response as any).data?.user || (response as any).user || response;
  }

  async logout(): Promise<void> {
    try {
      // Session-based auth: chama logout para destruir sessão
      const response = await this.apiDataSource.post(API_CONFIG.ENDPOINTS.LOGOUT, {});
      this.removeToken();

      // Update CSRF token if returned by logout endpoint
      const csrfToken = (response as any).csrf_token || (response as any).data?.csrf_token;
      if (csrfToken) {
        const currentCsrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (currentCsrfMeta) {
          currentCsrfMeta.setAttribute('content', csrfToken);
          console.log('CSRF token updated from logout response');
        }
      }
    } catch (error) {
      // Even if logout fails, clean up local state
      this.removeToken();
      throw error;
    }

    // Always refresh CSRF token after logout as additional safety measure
    await this.refreshCsrfToken();
  }

  private async refreshCsrfToken(): Promise<void> {
    try {
      // Fetch a fresh CSRF token from dedicated endpoint
      const response = await fetch('/csrf-token', {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (response.ok) {
        const data = await response.json();
        const newToken = data.csrf_token;

        if (newToken) {
          // Update the CSRF token in the current page
          const currentCsrfMeta = document.querySelector('meta[name="csrf-token"]');
          if (currentCsrfMeta) {
            currentCsrfMeta.setAttribute('content', newToken);
            console.log('CSRF token refreshed successfully');
          }
        }
      }
    } catch (error) {
      console.error('Error refreshing CSRF token:', error);
      // Non-critical error, don't throw
    }
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