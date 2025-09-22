export const API_CONFIG = {
  // Development URLs
  BASE_URL: 'http://localhost:8000/api',

  // Production URL - will be updated during build integration
  PRODUCTION_URL: '/api',

  // Get current API URL based on environment
  getApiUrl(): string {
    // In development, use Laravel dev server
    if (import.meta.env.DEV) {
      return this.BASE_URL;
    }

    // In production, use relative path (same domain as Laravel)
    return this.PRODUCTION_URL;
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