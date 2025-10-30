import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import DesktopApp from './desktop/DesktopApp.vue';
import './desktop/styles/desktop.css';

// Routes
import { routes } from './desktop/routes';

// Create router
const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Create and mount app
const app = createApp(DesktopApp);
app.use(router);
app.mount('#desktop-app');

// Hide loading screen when app is ready
app.config.globalProperties.$nextTick(() => {
    const loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
        loadingScreen.classList.add('fade-out');
        setTimeout(() => {
            loadingScreen.style.display = 'none';
        }, 500);
    }
});
