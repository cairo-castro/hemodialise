import { AuthService } from '../data/services/AuthService.js';
import { ErrorHandler } from '../data/handlers/ErrorHandler.js';
import { HttpClient } from '../data/http/HttpClient.js';

/**
 * Container de Injeção de Dependência
 * Segue DIP (Dependency Inversion Principle) e Inversion of Control
 */
export class DependencyContainer {
    constructor() {
        this.dependencies = new Map();
        this.setupDependencies();
    }

    /**
     * Configura todas as dependências
     * @private
     */
    setupDependencies() {
        // HTTP Client
        const httpClient = new HttpClient();
        this.register('httpClient', httpClient);

        // Error Handler
        const errorHandler = new ErrorHandler(console);
        this.register('errorHandler', errorHandler);

        // Auth Service
        const authService = new AuthService(httpClient, errorHandler);
        this.register('authService', authService);
    }

    /**
     * Registra uma dependência
     * @param {string} name
     * @param {*} instance
     */
    register(name, instance) {
        this.dependencies.set(name, instance);
    }

    /**
     * Resolve uma dependência
     * @param {string} name
     * @returns {*}
     */
    resolve(name) {
        const dependency = this.dependencies.get(name);
        if (!dependency) {
            throw new Error(`Dependency '${name}' not found`);
        }
        return dependency;
    }

    /**
     * Verifica se uma dependência existe
     * @param {string} name
     * @returns {boolean}
     */
    has(name) {
        return this.dependencies.has(name);
    }

    /**
     * Lista todas as dependências registradas
     * @returns {string[]}
     */
    list() {
        return Array.from(this.dependencies.keys());
    }
}

// Singleton pattern para o container
let containerInstance = null;

export function getContainer() {
    if (!containerInstance) {
        containerInstance = new DependencyContainer();
    }
    return containerInstance;
}