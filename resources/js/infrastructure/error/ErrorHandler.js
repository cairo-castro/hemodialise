/**
 * Error Handler - Gerencia tratamento de erros
 * Seguindo Clean Architecture: Infrastructure Layer
 * Seguindo SOLID: Single Responsibility Principle
 */
export class ErrorHandler {
    constructor() {
        this.errorContainer = null;
        this.setupErrorContainer();
    }

    /**
     * Configura container para mostrar erros
     */
    setupErrorContainer() {
        // Criar container se não existir
        if (!document.getElementById('error-notifications')) {
            const container = document.createElement('div');
            container.id = 'error-notifications';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
            this.errorContainer = container;
        } else {
            this.errorContainer = document.getElementById('error-notifications');
        }
    }

    /**
     * Mostra erro para o usuário
     */
    showError(message, type = 'error', duration = 5000) {
        const notification = this._createNotification(message, type);
        this.errorContainer.appendChild(notification);

        // Auto-remover após duration
        setTimeout(() => {
            this._removeNotification(notification);
        }, duration);

        return notification;
    }

    /**
     * Mostra aviso para o usuário
     */
    showWarning(message, duration = 5000) {
        return this.showError(message, 'warning', duration);
    }

    /**
     * Mostra informação para o usuário
     */
    showInfo(message, duration = 3000) {
        return this.showError(message, 'info', duration);
    }

    /**
     * Mostra sucesso para o usuário
     */
    showSuccess(message, duration = 3000) {
        return this.showError(message, 'success', duration);
    }

    /**
     * Cria elemento de notificação
     */
    _createNotification(message, type) {
        const notification = document.createElement('div');
        
        const typeClasses = {
            error: 'bg-red-100 border-red-400 text-red-700',
            warning: 'bg-yellow-100 border-yellow-400 text-yellow-700',
            info: 'bg-blue-100 border-blue-400 text-blue-700',
            success: 'bg-green-100 border-green-400 text-green-700'
        };

        const icons = {
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️',
            success: '✅'
        };

        notification.className = `border px-4 py-3 rounded-lg shadow-lg max-w-md ${typeClasses[type]}`;
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <span class="text-lg">${icons[type]}</span>
                <span class="text-sm font-medium">${message}</span>
                <button class="ml-auto text-lg hover:opacity-70" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;

        return notification;
    }

    /**
     * Remove notificação
     */
    _removeNotification(notification) {
        if (notification && notification.parentElement) {
            notification.style.transition = 'opacity 0.3s ease-out';
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.parentElement.removeChild(notification);
                }
            }, 300);
        }
    }

    /**
     * Loga erro no console (para debug)
     */
    logError(error, context = '') {
        console.error(`[ErrorHandler] ${context}:`, error);
    }

    /**
     * Trata erro de rede/fetch
     */
    handleNetworkError(error) {
        if (error.name === 'AbortError') {
            this.showWarning('Operação cancelada por timeout');
        } else if (error.message.includes('fetch')) {
            this.showError('Erro de conexão. Verifique sua internet.');
        } else {
            this.showError('Erro inesperado. Tente novamente.');
        }
        this.logError(error, 'Network');
    }

    /**
     * Trata erro de autenticação
     */
    handleAuthError(error) {
        this.showWarning('Sessão expirada. Por favor, faça login novamente.');
        this.logError(error, 'Authentication');
    }
}