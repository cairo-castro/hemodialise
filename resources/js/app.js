import './bootstrap';
import { createApp } from 'vue';

// Import Preline components
import 'preline/preline';

// Main desktop app
import DesktopApp from './desktop/presentation/components/DesktopApp.vue';

// Import dependencies for desktop app
import { AuthService } from './desktop/core/data/services/AuthService.js';
import { ErrorHandler } from './desktop/core/data/handlers/ErrorHandler.js';
import { HttpClient } from './desktop/core/data/http/HttpClient.js';

console.log('App.js carregado - iniciando Vue.js...');

// Initialize Vue app for desktop if we're on desktop pages
function initializeDesktopApp() {
    console.log('Procurando elemento #desktop-app...');
    const desktopAppElement = document.getElementById('desktop-app');
    
    if (desktopAppElement) {
        console.log('Elemento encontrado, criando app Vue.js...');

        try {
            // Create dependencies
            const httpClient = new HttpClient();
            const errorHandler = new ErrorHandler();
            const authService = new AuthService(httpClient, errorHandler);

            const app = createApp(DesktopApp, {
                authService,
                errorHandler
            });
            app.mount('#desktop-app');
            console.log('Vue.js app montado com sucesso!');
            
            // Hide loading screen
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen) {
                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                }, 500);
            }
        } catch (error) {
            console.error('Erro ao montar Vue.js app:', error);
        }
    } else {
        console.log('Elemento #desktop-app não encontrado');
    }
}

// Initialize only once when DOM is ready
console.log('Configurando inicialização...');

let initialized = false;

function tryInitialize() {
    if (initialized) {
        console.log('App já inicializado, ignorando chamada duplicada');
        return;
    }
    initialized = true;
    console.log('Inicializando app pela primeira vez...');
    initializeDesktopApp();
}

// Single initialization strategy
if (document.readyState === 'loading') {
    console.log('DOM ainda carregando, aguardando DOMContentLoaded...');
    document.addEventListener('DOMContentLoaded', tryInitialize);
} else {
    console.log('DOM já carregado, inicializando imediatamente...');
    tryInitialize();
}

console.log('App.js configurado completamente');