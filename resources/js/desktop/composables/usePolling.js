import { ref, onMounted, onUnmounted } from 'vue';

/**
 * Composable for efficient data polling
 * @param {Function} fetchFunction - The function to call for fetching data
 * @param {Object} options - Polling configuration
 * @returns {Object} - Reactive data and control functions
 */
export function usePolling(fetchFunction, options = {}) {
  const {
    interval = 30000, // Default: 30 seconds
    immediate = true, // Start polling immediately
    enabled = true, // Enable polling by default
  } = options;

  const data = ref(null);
  const error = ref(null);
  const loading = ref(false);
  const isPolling = ref(false);
  let pollTimer = null;

  /**
   * Fetch data from the provided function
   */
  const fetch = async () => {
    if (loading.value) return; // Prevent concurrent requests

    loading.value = true;
    error.value = null;

    try {
      const result = await fetchFunction();
      data.value = result;
    } catch (err) {
      error.value = err;
      console.error('Polling error:', err);
    } finally {
      loading.value = false;
    }
  };

  /**
   * Start polling
   */
  const startPolling = () => {
    if (isPolling.value || !enabled) return;

    isPolling.value = true;

    // Fetch immediately if requested
    if (immediate) {
      fetch();
    }

    // Set up interval
    pollTimer = setInterval(() => {
      fetch();
    }, interval);
  };

  /**
   * Stop polling
   */
  const stopPolling = () => {
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }
    isPolling.value = false;
  };

  /**
   * Refresh data manually
   */
  const refresh = () => {
    fetch();
  };

  // Lifecycle hooks
  onMounted(() => {
    if (enabled) {
      startPolling();
    }
  });

  onUnmounted(() => {
    stopPolling();
  });

  // Handle visibility change to pause/resume polling when tab is not visible
  const handleVisibilityChange = () => {
    if (document.hidden) {
      stopPolling();
    } else if (enabled) {
      startPolling();
    }
  };

  onMounted(() => {
    document.addEventListener('visibilitychange', handleVisibilityChange);
  });

  onUnmounted(() => {
    document.removeEventListener('visibilitychange', handleVisibilityChange);
  });

  return {
    data,
    error,
    loading,
    isPolling,
    startPolling,
    stopPolling,
    refresh,
  };
}
