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

        // CSRF token mismatch - refresh token and retry once
        if (response.status === 419) {
          console.log('CSRF token mismatch detected (419)');

          // Only retry once to avoid infinite loops
          if (this.retryCount < this.maxRetries) {
            this.retryCount++;
            console.log(`Attempting to refresh CSRF token and retry (attempt ${this.retryCount}/${this.maxRetries})`);

            try {
              // Fetch fresh CSRF token
              const csrfResponse = await fetch('/csrf-token', {
                method: 'GET',
                credentials: 'include',
                headers: {
                  'Accept': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest'
                }
              });

              if (csrfResponse.ok) {
                const csrfData = await csrfResponse.json();
                const newToken = csrfData.csrf_token;

                if (newToken) {
                  // Update the CSRF token in the page
                  const currentCsrfMeta = document.querySelector('meta[name="csrf-token"]');
                  if (currentCsrfMeta) {
                    currentCsrfMeta.setAttribute('content', newToken);
                    console.log('CSRF token refreshed, retrying request...');

                    // Reset retry count for successful refresh
                    this.retryCount = 0;

                    // Retry the original request with new token
                    return this.request<T>(method, endpoint, data, token);
                  }
                }
              }
            } catch (refreshError) {
              console.error('Failed to refresh CSRF token:', refreshError);
            }
          }

          // If we couldn't refresh or exceeded retry limit, reload the page
          console.log('Could not refresh CSRF token, reloading page...');
          throw {
            message: 'Token de segurança expirado. Recarregando a página...',
            status: 419,
            shouldReload: true
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
      // Handle reload for CSRF errors
      if ((error as any).shouldReload) {
        setTimeout(() => {
          window.location.reload();
        }, 1500);
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