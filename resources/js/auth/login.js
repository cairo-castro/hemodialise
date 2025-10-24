import { createApp } from 'vue'
import LoginApp from './components/LoginApp.vue'

console.log('Login.js carregado - iniciando Vue.js para login...')

let appInstance = null

function initializeLoginApp() {
    // Evitar múltiplas montagens
    if (appInstance) {
        console.log('Vue.js Login app já está montado, ignorando inicialização duplicada')
        return
    }
    
    console.log('Procurando elemento #login-app...')
    const loginAppElement = document.getElementById('login-app')
    
    if (loginAppElement) {
        console.log('Elemento #login-app encontrado, criando app Vue.js...')
        
        try {
            appInstance = createApp(LoginApp)
            appInstance.mount('#login-app')
            console.log('Vue.js Login app montado com sucesso!')
        } catch (error) {
            console.error('Erro ao montar Vue.js Login app:', error)
            appInstance = null
        }
    } else {
        console.log('Elemento #login-app não encontrado')
    }
}

// Estratégia de inicialização única
console.log('Configurando inicialização do login...')

if (document.readyState === 'loading') {
    // DOM ainda está carregando, aguardar DOMContentLoaded
    console.log('DOM ainda carregando, aguardando DOMContentLoaded...')
    document.addEventListener('DOMContentLoaded', initializeLoginApp)
} else {
    // DOM já está pronto, inicializar imediatamente
    console.log('DOM já carregado, inicializando login imediatamente...')
    initializeLoginApp()
}

console.log('Login.js configurado completamente')