/**
 * HTTP Client - Adaptador para requisições HTTP
 * Seguindo Clean Architecture: Infrastructure Layer
 * Seguindo SOLID: Dependency Inversion Principle
 */
export class HttpClient {
    constructor() {
        this.baseURL = '';
    }

    /**
     * Realiza requisição GET
     */
    async get(url, options = {}) {
        return this._makeRequest(url, 'GET', null, options);
    }

    /**
     * Realiza requisição POST
     */
    async post(url, options = {}) {
        return this._makeRequest(url, 'POST', options.body, options);
    }

    /**
     * Realiza requisição PUT
     */
    async put(url, options = {}) {
        return this._makeRequest(url, 'PUT', options.body, options);
    }

    /**
     * Realiza requisição DELETE
     */
    async delete(url, options = {}) {
        return this._makeRequest(url, 'DELETE', null, options);
    }

    /**
     * Método privado para fazer requisições
     */
    async _makeRequest(url, method, body, options = {}) {
        const config = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...options.headers
            }
        };

        if (body) {
            config.body = typeof body === 'string' ? body : JSON.stringify(body);
        }

        // Implementar timeout se especificado
        if (options.timeout) {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), options.timeout);
            config.signal = controller.signal;
            
            try {
                const response = await fetch(this.baseURL + url, config);
                clearTimeout(timeoutId);
                return response;
            } catch (error) {
                clearTimeout(timeoutId);
                throw error;
            }
        }

        return fetch(this.baseURL + url, config);
    }
}