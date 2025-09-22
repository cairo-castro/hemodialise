import { IAuthService } from '../../domain/interfaces/IAuthService.js';
import { User } from '../../domain/entities/User.js';

/**
 * Implementação concreta do serviço de autenticação
 * Segue DIP (Dependency Inversion Principle) implementando a interface
 */
export class AuthService extends IAuthService {
    constructor(httpClient, errorHandler) {
        super();
        this.httpClient = httpClient;
        this.errorHandler = errorHandler;
    }

    /**
     * Obtém o usuário atual
     * @returns {Promise<User|null>}
     */
    async getCurrentUser() {
        try {
            const token = localStorage.getItem('token');
            const headers = {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            };

            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            const response = await this.httpClient.get('/api/me', { headers });

            if (response.ok) {
                const data = await response.json();
                return User.fromApiResponse(data.user);
            }

            if (response.status === 401) {
                localStorage.removeItem('token');
                return null;
            }

            throw new Error(`Auth failed with status: ${response.status}`);

        } catch (error) {
            this.errorHandler.handleAuthError(error);
            return null;
        }
    }

    /**
     * Realiza logout
     * @returns {Promise<void>}
     */
    async logout() {
        try {
            localStorage.removeItem('token');
            
            await this.httpClient.post('/logout', {}, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            window.location.href = '/login?logout=true';
        } catch (error) {
            this.errorHandler.handleGenericError(error);
            window.location.href = '/login?logout=true';
        }
    }

    /**
     * Alterna interface
     * @param {string} targetInterface
     * @returns {Promise<string>}
     */
    async switchInterface(targetInterface) {
        try {
            const response = await this.httpClient.post('/api/smart-route/switch-interface', 
                { interface: targetInterface },
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                }
            );

            const data = await response.json();
            
            if (data.success) {
                return data.redirect_url;
            } else {
                throw new Error(data.error || 'Erro ao alternar interface');
            }
        } catch (error) {
            this.errorHandler.handleNetworkError(error);
            throw error;
        }
    }
}