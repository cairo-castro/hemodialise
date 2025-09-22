/**
 * Authentication Service Interface
 * Define o contrato para serviços de autenticação seguindo ISP (Interface Segregation Principle)
 */
export class IAuthService {
    /**
     * Verifica se o usuário está autenticado
     * @returns {Promise<User|null>}
     */
    async getCurrentUser() {
        throw new Error('Method not implemented');
    }

    /**
     * Realiza logout do usuário
     * @returns {Promise<void>}
     */
    async logout() {
        throw new Error('Method not implemented');
    }

    /**
     * Alterna interface do usuário
     * @param {string} targetInterface
     * @returns {Promise<string>}
     */
    async switchInterface(targetInterface) {
        throw new Error('Method not implemented');
    }
}