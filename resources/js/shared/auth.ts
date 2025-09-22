// Shared authentication utilities
export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  unit_id?: number;
}

export interface AuthResponse {
  access_token: string;
  token_type: string;
  expires_in: number;
  user: User;
}

export class AuthService {
  private static TOKEN_KEY = 'auth_token';
  private static USER_KEY = 'auth_user';

  static getToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY);
  }

  static setToken(token: string): void {
    localStorage.setItem(this.TOKEN_KEY, token);
  }

  static removeToken(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    localStorage.removeItem(this.USER_KEY);
  }

  static getUser(): User | null {
    const userData = localStorage.getItem(this.USER_KEY);
    return userData ? JSON.parse(userData) : null;
  }

  static setUser(user: User): void {
    localStorage.setItem(this.USER_KEY, JSON.stringify(user));
  }

  static isAuthenticated(): boolean {
    const token = this.getToken();
    const user = this.getUser();
    return !!(token && user);
  }

  static getAuthHeaders(): Record<string, string> {
    const token = this.getToken();
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    // Add CSRF token for Laravel
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
      headers['X-CSRF-TOKEN'] = csrfToken;
    }

    return headers;
  }

  static async logout(): Promise<void> {
    try {
      // Call logout API
      await fetch('/api/logout', {
        method: 'POST',
        headers: this.getAuthHeaders(),
      });
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      // Always clear local storage
      this.removeToken();
    }
  }

  static async checkAuth(): Promise<boolean> {
    if (!this.isAuthenticated()) {
      return false;
    }

    try {
      const response = await fetch('/api/me', {
        headers: this.getAuthHeaders(),
      });

      if (response.ok) {
        const user = await response.json();
        this.setUser(user);
        return true;
      } else {
        // Token is invalid
        this.removeToken();
        return false;
      }
    } catch (error) {
      console.error('Auth check error:', error);
      this.removeToken();
      return false;
    }
  }

  static async login(email: string, password: string): Promise<AuthResponse> {
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email, password }),
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Login failed');
    }

    const authData: AuthResponse = await response.json();

    // Store token and user data
    this.setToken(authData.access_token);
    this.setUser(authData.user);

    return authData;
  }
}