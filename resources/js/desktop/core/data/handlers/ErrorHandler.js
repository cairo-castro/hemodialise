import { IErrorHandler } from '../../domain/interfaces/IErrorHandler.js';

/**
 * Implementação concreta do tratamento de erros
 * Segue SRP (Single Responsibility Principle) - responsável apenas por tratar erros
 */
export class ErrorHandler extends IErrorHandler {
    constructor(logger = console) {
        super();
        this.logger = logger;
    }

    /**
     * Trata erros de autenticação
     * @param {Error} error
     */
    handleAuthError(error) {
        this.logger.warn('Auth error:', error.message);
        
        // Estratégias específicas para erros de auth
        if (error.message.includes('401')) {
            localStorage.removeItem('token');
        }
        
        // Não redireciona automaticamente, deixa o componente decidir
    }

    /**
     * Trata erros de rede
     * @param {Error} error
     */
    handleNetworkError(error) {
        this.logger.error('Network error:', error.message);
        
        // Pode implementar retry logic, offline handling, etc.
        if (error.message.includes('fetch')) {
            this.showNetworkErrorNotification();
        }
    }

    /**
     * Trata erros genéricos
     * @param {Error} error
     */
    handleGenericError(error) {
        this.logger.error('Generic error:', error.message);
        
        // Logging para monitoramento
        this.reportErrorToMonitoring(error);
    }

    /**
     * Mostra notificação de erro de rede
     * @private
     */
    showNetworkErrorNotification() {
        // Implementar notificação não intrusiva
        console.warn('Problema de conectividade detectado');
    }

    /**
     * Reporta erro para sistema de monitoramento
     * @private
     * @param {Error} error
     */
    reportErrorToMonitoring(error) {
        // Implementar integração com Sentry, LogRocket, etc.
        // Por enquanto, apenas log
        this.logger.error('Error reported:', {
            message: error.message,
            stack: error.stack,
            timestamp: new Date().toISOString()
        });
    }
}