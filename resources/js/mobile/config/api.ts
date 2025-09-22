export const API_CONFIG = {
  // Base URL - now integrated with Laravel
  BASE_URL: '/api',

  // Get current API URL based on environment
  getApiUrl(): string {
    // Always use relative path since we're integrated with Laravel
    return this.BASE_URL;
  },

  // API endpoints
  ENDPOINTS: {
    // Auth
    LOGIN: '/login',
    LOGOUT: '/logout',
    ME: '/me',

    // Patients
    PATIENTS: '/patients',
    PATIENTS_SEARCH: '/patients/search',

    // Checklists
    CHECKLISTS: '/checklists',
    CHECKLISTS_TODAY: '/checklists/today',
    CHECKLISTS_PENDING: '/checklists/pending',
    CHECKLISTS_BY_USER: '/checklists/user',
    CHECKLISTS_BY_MACHINE: '/checklists/machine',

    // Machines
    MACHINES: '/machines',
    MACHINES_AVAILABLE: '/machines/available',
    MACHINES_BY_UNIT: '/machines/unit'
  }
};

// Development proxy configuration for Vite
export const VITE_PROXY_CONFIG = {
  '/api': {
    target: 'http://localhost:8000',
    changeOrigin: true,
    secure: false,
    rewrite: (path: string) => path
  }
};