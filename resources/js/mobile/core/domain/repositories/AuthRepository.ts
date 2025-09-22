import { User, LoginCredentials, AuthToken } from '../entities/User';

export interface AuthRepository {
  login(credentials: LoginCredentials): Promise<AuthToken>;
  getCurrentUser(): Promise<User>;
  logout(): Promise<void>;
  getStoredToken(): string | null;
  storeToken(token: string): void;
  removeToken(): void;
  isAuthenticated(): boolean;
}