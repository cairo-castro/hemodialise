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

  constructor(baseUrl?: string) {
    this.baseUrl = baseUrl || API_CONFIG.getApiUrl();
  }

  setAuthRepository(authRepository: any) {
    this.authRepository = authRepository;
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
        // Session-based auth: 401 means session expired, redirect to login
        if (response.status === 401 && endpoint !== '/login') {
          if (this.authRepository) {
            this.authRepository.removeToken();
          }
          throw {
            message: 'Sessão expirada. Faça login novamente.',
            status: 401
          };
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

}