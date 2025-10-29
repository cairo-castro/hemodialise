export interface ApiResponse<T> {
  data: T;
  message?: string;
  success: boolean;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status: number;
}

import { API_CONFIG } from '@mobile/config/api';

export class ApiDataSource {
  private baseUrl: string;
  private authRepository: any = null;
  private isRefreshing = false;
  private refreshSubscribers: Array<(token: string) => void> = [];

  constructor(baseUrl?: string) {
    this.baseUrl = baseUrl || API_CONFIG.getApiUrl();
  }

  setAuthRepository(authRepository: any) {
    this.authRepository = authRepository;
  }

  private subscribeTokenRefresh(cb: (token: string) => void) {
    this.refreshSubscribers.push(cb);
  }

  private onTokenRefreshed(token: string) {
    this.refreshSubscribers.forEach(cb => cb(token));
    this.refreshSubscribers = [];
  }

  async get<T>(endpoint: string, token?: string): Promise<ApiResponse<T>> {
    return this.request<T>('GET', endpoint, undefined, token);
  }

  async post<T>(endpoint: string, data?: any, token?: string): Promise<ApiResponse<T>> {
    return this.request<T>('POST', endpoint, data, token);
  }

  async put<T>(endpoint: string, data?: any, token?: string): Promise<ApiResponse<T>> {
    return this.request<T>('PUT', endpoint, data, token);
  }

  async delete<T>(endpoint: string, token?: string): Promise<ApiResponse<T>> {
    return this.request<T>('DELETE', endpoint, undefined, token);
  }

  private async request<T>(
    method: string,
    endpoint: string,
    data?: any,
    token?: string
  ): Promise<ApiResponse<T>> {
    const url = `${this.baseUrl}${endpoint}`;

    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    };

    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
      headers['X-CSRF-TOKEN'] = csrfToken;
    }

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    const config: RequestInit = {
      method,
      headers,
      credentials: 'same-origin', // Include cookies for session
    };

    if (data && (method === 'POST' || method === 'PUT')) {
      config.body = JSON.stringify(data);
    }

    try {
      const response = await fetch(url, config);
      const responseData = await response.json();

      if (!response.ok) {
        // Handle 401 Unauthorized - try to refresh token
        if (response.status === 401 && this.authRepository && endpoint !== '/login' && endpoint !== '/refresh') {
          try {
            const newToken = await this.refreshToken();
            // Retry request with new token
            headers['Authorization'] = `Bearer ${newToken}`;
            const retryResponse = await fetch(url, { ...config, headers });
            const retryData = await retryResponse.json();

            if (!retryResponse.ok) {
              throw {
                message: retryData.message || 'Sessão expirada. Faça login novamente.',
                errors: retryData.errors,
                status: retryResponse.status
              };
            }

            if (retryData && typeof retryData === 'object' && 'data' in retryData) {
              return retryData;
            }

            return {
              data: retryData,
              success: true
            };
          } catch (refreshError) {
            // Token refresh failed, clear token and throw error
            if (this.authRepository) {
              this.authRepository.removeToken();
            }
            throw {
              message: 'Sessão expirada. Faça login novamente.',
              status: 401
            };
          }
        }

        const error: ApiError = {
          message: responseData.message || 'Erro na requisição',
          errors: responseData.errors,
          status: response.status
        };
        throw error;
      }

      // If responseData already has a 'data' property, return as is
      // Otherwise, wrap the response in the expected format
      if (responseData && typeof responseData === 'object' && 'data' in responseData) {
        return responseData;
      }

      return {
        data: responseData,
        success: true
      };
    } catch (error) {
      if (error instanceof Error && error.name === 'TypeError') {
        throw {
          message: 'Erro de conectividade. Verifique sua conexão.',
          status: 0
        } as ApiError;
      }
      throw error;
    }
  }

  private async refreshToken(): Promise<string> {
    if (this.isRefreshing) {
      // Wait for ongoing refresh to complete
      return new Promise((resolve) => {
        this.subscribeTokenRefresh((token: string) => {
          resolve(token);
        });
      });
    }

    this.isRefreshing = true;

    try {
      const oldToken = this.authRepository.getStoredToken();
      const response = await fetch(`${this.baseUrl}/refresh`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${oldToken}`
        }
      });

      if (!response.ok) {
        throw new Error('Token refresh failed');
      }

      const data = await response.json();
      const newToken = data.token || data.access_token;

      // Store new token
      this.authRepository.storeToken(newToken);

      // Notify subscribers
      this.onTokenRefreshed(newToken);

      return newToken;
    } finally {
      this.isRefreshing = false;
    }
  }
}