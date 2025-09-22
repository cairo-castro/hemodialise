import './bootstrap';
import { createApp } from 'vue';

// Import Preline components
import 'preline/preline';

// Main desktop app
import DesktopApp from './desktop/presentation/components/DesktopApp.vue';

console.log('App.js carregado - iniciando Vue.js...');

// Initialize Vue app for desktop if we're on desktop pages
function initializeDesktopApp() {
    console.log('Procurando elemento #desktop-app...');
    const desktopAppElement = document.getElementById('desktop-app');
    
    if (desktopAppElement) {
        console.log('Elemento encontrado, criando app Vue.js...');
        
        try {
            const app = createApp(DesktopApp);
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

// Try multiple initialization strategies
console.log('Configurando inicialização...');

// Strategy 1: DOM Content Loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded - tentando inicializar...');
    initializeDesktopApp();
});

// Strategy 2: Window Load (fallback)
window.addEventListener('load', () => {
    console.log('Window Load - tentando inicializar como fallback...');
    setTimeout(initializeDesktopApp, 100);
});

// Strategy 3: Immediate check (if DOM already loaded)
if (document.readyState === 'loading') {
    console.log('DOM ainda carregando, aguardando...');
} else {
    console.log('DOM já carregado, inicializando imediatamente...');
    setTimeout(initializeDesktopApp, 100);
}

console.log('App.js configurado completamente');