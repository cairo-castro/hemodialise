/**
 * Error Handler Interface
 * Define o contrato para tratamento de erros seguindo ISP
 */
export class IErrorHandler {
    /**
     * Trata erro de autenticação
     * @param {Error} error
     */
    handleAuthError(error) {
        throw new Error('Method not implemented');
    }

    /**
     * Trata erro de rede
     * @param {Error} error
     */
    handleNetworkError(error) {
        throw new Error('Method not implemented');
    }

    /**
     * Trata erro genérico
     * @param {Error} error
     */
    handleGenericError(error) {
        throw new Error('Method not implemented');
    }
}