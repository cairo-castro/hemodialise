import { createApp } from 'vue'
import App from './App.vue'
import router from './router';

import { IonicVue } from '@ionic/vue';

// Import shared auth service
import { AuthService } from '@shared/auth';

/* Core CSS required for Ionic components to work properly */
import '@ionic/vue/css/core.css';

/* Basic CSS for apps built with Ionic */
import '@ionic/vue/css/normalize.css';
import '@ionic/vue/css/structure.css';
import '@ionic/vue/css/typography.css';

/* Optional CSS utils that can be commented out */
import '@ionic/vue/css/padding.css';
import '@ionic/vue/css/float-elements.css';
import '@ionic/vue/css/text-alignment.css';
import '@ionic/vue/css/text-transformation.css';
import '@ionic/vue/css/flex-utils.css';
import '@ionic/vue/css/display.css';

/**
 * Ionic Dark Mode
 * -----------------------------------------------------
 * For more info, please see:
 * https://ionicframework.com/docs/theming/dark-mode
 */

/*
 * Ionic Dark Mode - Class-based theming (manual control)
 * Allows manual dark mode toggle via .ion-palette-dark class
 * Applied to <html> element via useDarkMode() composable
 *
 * IMPORTANT: Use official Ionic CSS palette files
 */
import '@ionic/vue/css/palettes/dark.class.css';

/**
 * Custom Theme
 * -----------------------------------------------------
 * Centralized theme system with:
 * - Design tokens (colors, spacing, typography, etc)
 * - Component styles (cards, buttons, forms, etc)
 * - Utility classes (layout, spacing, text, etc)
 * - Medical system specific styles
 *
 * Loaded LAST to override Ionic defaults
 */
import './theme/index.css';

const app = createApp(App)
  .use(IonicVue, {
    // Configuração de tradução para português
    backButtonText: 'Voltar',
    mode: 'ios', // ou 'md' para Material Design
  })
  .use(router);

// Global auth service
app.config.globalProperties.$auth = AuthService;

// Provide auth service for composition API
app.provide('auth', AuthService);

router.isReady().then(() => {
  app.mount('#app');
});
