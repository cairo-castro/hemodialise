export const API_CONFIG = {
  // Base URL - now integrated with Laravel
  // Empty string because routes are at root level (/login, /api/checklists, etc.)
  BASE_URL: '',

  // Get current API URL based on environment
  getApiUrl(): string {
    // Always use relative path since we're integrated with Laravel
    return this.BASE_URL;
  },

  // API endpoints
  ENDPOINTS: {
    // Auth (rotas web do Laravel)
    LOGIN: '/login',
    LOGOUT: '/logout',
    ME: '/api/me',

    // Patients (rotas API)
    PATIENTS: '/api/patients',
    PATIENTS_SEARCH: '/api/patients/search',
    PATIENTS_QUICK_SEARCH: '/api/patients/quick-search',

    // Checklists (rotas API)
    CHECKLISTS: '/api/checklists',
    CHECKLISTS_TODAY: '/api/checklists/today',
    CHECKLISTS_PENDING: '/api/checklists/pending',
    CHECKLISTS_BY_USER: '/api/checklists/user',
    CHECKLISTS_BY_MACHINE: '/api/checklists/machine',

    // Cleaning Checklists (rotas API)
    CLEANING_CHECKLISTS: '/api/cleaning-checklists',
    CLEANING_CHECKLISTS_STATS: '/api/cleaning-checklists/stats',

    // Machines (rotas API)
    MACHINES: '/api/machines',
    MACHINES_AVAILABLE: '/api/machines/available',
    MACHINES_BY_UNIT: '/api/machines/unit'
  }
};

// Development proxy configuration for Vite
export const VITE_PROXY_CONFIG = {
  '/api': {
    target: 'http://localhost:8002',
    changeOrigin: true,
    secure: false,
    rewrite: (path: string) => path
  }
};