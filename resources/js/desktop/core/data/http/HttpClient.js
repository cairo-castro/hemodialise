/**
 * HTTP Client seguindo padrão Adapter
 * Abstrai a implementação do fetch nativo
 */
export class HttpClient {
    constructor(baseUrl = '') {
        this.baseUrl = baseUrl;
    }

    /**
     * Requisição GET
     * @param {string} url
     * @param {object} options
     * @returns {Promise<Response>}
     */
    async get(url, options = {}) {
        return this.request(url, { ...options, method: 'GET' });
    }

    /**
     * Requisição POST
     * @param {string} url
     * @param {object} data
     * @param {object} options
     * @returns {Promise<Response>}
     */
    async post(url, data = {}, options = {}) {
        return this.request(url, {
            ...options,
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
    }

    /**
     * Requisição genérica
     * @param {string} url
     * @param {object} options
     * @returns {Promise<Response>}
     * @private
     */
    async request(url, options = {}) {
        const fullUrl = this.baseUrl + url;

        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch(fullUrl, {
                timeout: 10000, // 10 segundos
                credentials: 'include', // Include cookies for session auth
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
                    ...options.headers
                },
                ...options
            });

            return response;
        } catch (error) {
            throw new Error(`Network request failed: ${error.message}`);
        }
    }
}