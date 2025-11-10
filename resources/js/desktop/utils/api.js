/**
 * API utility for Sanctum SPA authentication
 * Ensures all API calls include proper credentials and CSRF tokens
 */

let csrfInitialized = false;

/**
 * Initialize CSRF cookie from Sanctum
 * Should be called once before making any API requests
 */
export async function initializeCsrf() {
  if (csrfInitialized) return;

  try {
    await fetch('/sanctum/csrf-cookie', {
      credentials: 'include'
    });
    csrfInitialized = true;
  } catch (error) {
    console.error('Failed to initialize CSRF cookie:', error);
    throw error;
  }
}

/**
 * Make an authenticated API request with proper Sanctum configuration
 * @param {string} url - The API endpoint URL
 * @param {object} options - Fetch options (method, body, headers, etc.)
 * @returns {Promise<Response>} The fetch response
 */
export async function apiRequest(url, options = {}) {
  // Ensure CSRF cookie is initialized
  await initializeCsrf();

  // Get CSRF token from cookie or meta tag
  const csrfToken = getCsrfToken();

  // Merge default headers with provided headers
  const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    ...(csrfToken && { 'X-XSRF-TOKEN': csrfToken }),
    ...options.headers
  };

  // Merge default options
  const fetchOptions = {
    ...options,
    headers,
    credentials: 'include' // Always include credentials for Sanctum
  };

  return fetch(url, fetchOptions);
}

/**
 * Get CSRF token from cookie (XSRF-TOKEN) or meta tag
 * @returns {string|null} The CSRF token
 */
function getCsrfToken() {
  // Try to get from XSRF-TOKEN cookie (set by Sanctum)
  const cookieMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  if (cookieMatch) {
    return decodeURIComponent(cookieMatch[1]);
  }

  // Fallback to meta tag
  const metaTag = document.querySelector('meta[name="csrf-token"]');
  return metaTag?.getAttribute('content') || null;
}

/**
 * Convenience methods for common HTTP verbs
 */
export const api = {
  get: (url, options = {}) => apiRequest(url, { ...options, method: 'GET' }),

  post: (url, data, options = {}) => apiRequest(url, {
    ...options,
    method: 'POST',
    body: JSON.stringify(data)
  }),

  put: (url, data, options = {}) => apiRequest(url, {
    ...options,
    method: 'PUT',
    body: JSON.stringify(data)
  }),

  patch: (url, data, options = {}) => apiRequest(url, {
    ...options,
    method: 'PATCH',
    body: JSON.stringify(data)
  }),

  delete: (url, options = {}) => apiRequest(url, {
    ...options,
    method: 'DELETE'
  })
};

export default api;
