import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import DesktopApp from './desktop/DesktopApp.vue';
import './desktop/styles/desktop.css';
import axios from 'axios';

// Routes
import { routes } from './desktop/routes';

// Configure axios globally for CSRF protection
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Add axios interceptor to include CSRF token from cookie
axios.interceptors.request.use(config => {
    // Get CSRF token from XSRF-TOKEN cookie (set by Sanctum)
    const csrfToken = document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1];

    if (csrfToken) {
        config.headers['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken);
    }

    return config;
}, error => {
    return Promise.reject(error);
});

// Add axios response interceptor for session expiration
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 419) {
            console.log('[Desktop] CSRF token mismatch (419) - redirecting to login');
            window.location.href = '/login?session_expired=1';
        }
        return Promise.reject(error);
    }
);

// Create router
const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Create and mount app
const app = createApp(DesktopApp);
app.use(router);

// Mount app and hide loading screen after mount
app.mount('#desktop-app');

// Hide loading screen after Vue is mounted
setTimeout(() => {
    const loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
        loadingScreen.classList.add('fade-out');
        setTimeout(() => {
            loadingScreen.style.display = 'none';
        }, 500);
    }
}, 100);
