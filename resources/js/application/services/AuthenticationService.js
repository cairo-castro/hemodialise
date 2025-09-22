/**
 * Authentication Service - Gerencia autenticação
 * Seguindo Clean Architecture: Application Layer
 * Seguindo SOLID: Single Responsibility Principle
 */
import { User } from '../entities/User.js';

export class AuthenticationService {
    constructor(httpClient, tokenStorage) {
        this.httpClient = httpClient; // Dependency Injection
        this.tokenStorage = tokenStorage; // Dependency Injection
    }

    /**
     * Verifica se o usuário está autenticado
     */
    async checkAuthentication() {
        try {
            const token = this.tokenStorage.getToken();
            
            if (!token) {
                return { success: false, user: new User({ role: 'guest' }) };
            }

            const response = await this.httpClient.get('/api/me', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                timeout: 5000
            });

            if (response.ok) {
                const data = await response.json();
                return { 
                    success: true, 
                    user: new User(data.user) 
                };
            } else if (response.status === 401) {
                this.tokenStorage.removeToken();
                return { 
                    success: false, 
                    user: new User({ role: 'guest' }) 
                };
            } else {
                throw new Error(`Auth failed with status: ${response.status}`);
            }
        } catch (error) {
            console.warn('Authentication check failed:', error);
            return { 
                success: false, 
                user: new User({ role: 'guest' }),
                error: error.message
            };
        }
    }

    /**
     * Realiza logout do usuário
     */
    async logout() {
        try {
            this.tokenStorage.removeToken();
            this.tokenStorage.removeCookie();

            await this.httpClient.post('/logout', {
                headers: {
                    'X-CSRF-TOKEN': this._getCsrfToken(),
                    'Content-Type': 'application/json'
                }
            });

            return { success: true };
        } catch (error) {
            console.warn('Logout failed:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Obtém o token CSRF
     */
    _getCsrfToken() {
        const metaElement = document.querySelector('meta[name="csrf-token"]');
        return metaElement ? metaElement.getAttribute('content') : '';
    }
}