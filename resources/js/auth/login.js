import { createApp } from 'vue'
import LoginApp from './components/LoginApp.vue'

console.log('Login.js carregado - iniciando Vue.js para login...')

function initializeLoginApp() {
    console.log('Procurando elemento #login-app...')
    const loginAppElement = document.getElementById('login-app')
    
    if (loginAppElement) {
        console.log('Elemento #login-app encontrado, criando app Vue.js...')
        
        try {
            const app = createApp(LoginApp)
            app.mount('#login-app')
            console.log('Vue.js Login app montado com sucesso!')
        } catch (error) {
            console.error('Erro ao montar Vue.js Login app:', error)
        }
    } else {
        console.log('Elemento #login-app não encontrado')
    }
}

// Try multiple initialization strategies
console.log('Configurando inicialização do login...')

// Strategy 1: DOM Content Loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded - tentando inicializar login...')
    initializeLoginApp()
})

// Strategy 2: Window Load (fallback)
window.addEventListener('load', () => {
    console.log('Window Load - tentando inicializar login como fallback...')
    setTimeout(initializeLoginApp, 100)
})

// Strategy 3: Immediate check (if DOM already loaded)
if (document.readyState === 'loading') {
    console.log('DOM ainda carregando, aguardando...')
} else {
    console.log('DOM já carregado, inicializando login imediatamente...')
    setTimeout(initializeLoginApp, 100)
}

console.log('Login.js configurado completamente')