/**
 * Token Storage - Gerencia armazenamento de tokens
 * Seguindo Clean Architecture: Infrastructure Layer
 * Seguindo SOLID: Single Responsibility Principle
 */
export class TokenStorage {
    constructor() {
        this.tokenKey = 'token';
        this.cookieName = 'jwt_token';
    }

    /**
     * Obtém o token do localStorage
     */
    getToken() {
        try {
            return localStorage.getItem(this.tokenKey);
        } catch (error) {
            console.warn('Failed to get token from localStorage:', error);
            return null;
        }
    }

    /**
     * Armazena o token no localStorage e cookie
     */
    setToken(token) {
        try {
            localStorage.setItem(this.tokenKey, token);
            document.cookie = `${this.cookieName}=${token}; path=/; max-age=86400; SameSite=Lax`;
            return true;
        } catch (error) {
            console.error('Failed to store token:', error);
            return false;
        }
    }

    /**
     * Remove o token do localStorage
     */
    removeToken() {
        try {
            localStorage.removeItem(this.tokenKey);
            return true;
        } catch (error) {
            console.warn('Failed to remove token from localStorage:', error);
            return false;
        }
    }

    /**
     * Remove o cookie do token
     */
    removeCookie() {
        try {
            document.cookie = `${this.cookieName}=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;`;
            return true;
        } catch (error) {
            console.warn('Failed to remove token cookie:', error);
            return false;
        }
    }

    /**
     * Verifica se existe token armazenado
     */
    hasToken() {
        return !!this.getToken();
    }
}