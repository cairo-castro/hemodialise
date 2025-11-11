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

  /**
   * Get CSRF token from XSRF-TOKEN cookie (set by Sanctum)
   * The cookie value is URL-encoded, so we need to decode it
   */
  private getCsrfToken(): string | null {
    const cookieMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    if (cookieMatch) {
      return decodeURIComponent(cookieMatch[1]);
    }
    return null;
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

  private retryCount = 0;
  private maxRetries = 1;

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

    // Get CSRF token from XSRF-TOKEN cookie (Sanctum)
    const csrfToken = this.getCsrfToken();
    if (csrfToken) {
      headers['X-XSRF-TOKEN'] = csrfToken;
    }

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    const config: RequestInit = {
      method,
      headers,
      credentials: 'include', // Include cookies for cross-origin requests (Vite proxy)
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

        // CSRF token mismatch (419) - Session expired, force logout for security
        if (response.status === 419) {
          console.log('[ApiDataSource] Session expired (419) - forcing logout');

          // Check if response indicates session expired
          let sessionExpired = false;
          try {
            if (responseData && responseData.session_expired === true) {
              sessionExpired = true;
            }
          } catch (e) {
            // Response is not JSON or doesn't have session_expired flag
          }

          // Force logout and redirect to login
          throw {
            message: 'Sua sessão expirou por motivos de segurança. Por favor, faça login novamente.',
            status: 419,
            session_expired: true,
            shouldLogout: true
          };
        }

        const error: ApiError = {
          message: responseData.message || 'Erro na requisição',
          errors: responseData.errors,
          status: response.status
        };
        throw error;
      }

      // Reset retry count on successful request
      this.retryCount = 0;

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
      // Handle session expired - force logout
      if ((error as any).shouldLogout) {
        console.log('[ApiDataSource] Handling session expiration - logging out');

        // Import session handler dynamically
        import('../../../composables/useSessionHandler').then(({ handleSessionExpiredStatic }) => {
          handleSessionExpiredStatic((error as any).message);
        });

        // Don't throw the error further to prevent additional error handling
        return { data: null, success: false } as ApiResponse<T>;
      }

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