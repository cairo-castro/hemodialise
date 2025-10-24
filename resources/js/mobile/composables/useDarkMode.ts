import { ref, watch } from 'vue';

// Global state for dark mode (shared across all components)
const isDarkMode = ref(false);
const isInitialized = ref(false);

/**
 * Composable for managing dark mode across the entire application
 * Usage: const { isDarkMode, toggleDarkMode, initializeDarkMode } = useDarkMode();
 */
export function useDarkMode() {
  /**
   * Apply dark mode to the document
   * Following Ionic 8 official documentation:
   * - Class name: 'ion-palette-dark' (Ionic 8+)
   * - Applied to: document.documentElement (html element)
   */
  const applyDarkMode = () => {
    console.log('[useDarkMode] Applying dark mode:', isDarkMode.value);
    if (isDarkMode.value) {
      document.documentElement.classList.add('ion-palette-dark');
      console.log('[useDarkMode] Added ion-palette-dark class');
    } else {
      document.documentElement.classList.remove('ion-palette-dark');
      console.log('[useDarkMode] Removed ion-palette-dark class');
    }
    console.log('[useDarkMode] HTML classes:', document.documentElement.className);
  };

  /**
   * Initialize dark mode from localStorage or system preference
   * Only runs once when the app starts
   */
  const initializeDarkMode = () => {
    if (isInitialized.value) {
      return; // Already initialized
    }

    // Check if user has a preference saved
    const savedPreference = localStorage.getItem('dark-mode');

    if (savedPreference !== null) {
      isDarkMode.value = savedPreference === 'true';
    } else {
      // Use system preference if no saved preference
      isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    // Apply the theme
    applyDarkMode();

    // Watch for changes and persist them
    watch(isDarkMode, (newValue) => {
      localStorage.setItem('dark-mode', newValue.toString());
      applyDarkMode();
    });

    isInitialized.value = true;
    console.log('Dark mode initialized:', isDarkMode.value);
  };

  /**
   * Toggle dark mode on/off
   */
  const toggleDarkMode = () => {
    console.log('[useDarkMode] Toggle called. Current:', isDarkMode.value);
    isDarkMode.value = !isDarkMode.value;
    console.log('[useDarkMode] New value:', isDarkMode.value);
  };

  /**
   * Set dark mode explicitly
   */
  const setDarkMode = (value: boolean) => {
    isDarkMode.value = value;
  };

  return {
    isDarkMode,
    toggleDarkMode,
    initializeDarkMode,
    setDarkMode,
  };
}
