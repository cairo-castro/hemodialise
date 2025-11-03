import { ref } from 'vue';

// Notification sound settings
const enableSound = ref(localStorage.getItem('notificationSound') !== 'false');
const enableVibration = ref(localStorage.getItem('notificationVibration') !== 'false');
const enableBrowserNotifications = ref(localStorage.getItem('browserNotifications') !== 'false');

// Simple notification sound as data URL (short beep)
const NOTIFICATION_SOUND_DATA = 'data:audio/mpeg;base64,SUQzBAAAAAABEVRYWFgAAAAtAAADY29tbWVudABCaWdTb3VuZEJhbmsuY29tIC8gTGFTb25vdGhlcXVlLm9yZwBURU5DAAAA';

export function useNotificationSound() {
  const audioElement = ref(null);

  // Initialize audio element
  function initializeAudio() {
    if (!audioElement.value) {
      audioElement.value = new Audio(NOTIFICATION_SOUND_DATA);
      audioElement.value.volume = 0.5; // 50% volume
    }
  }

  // Play notification sound
  function playSound() {
    if (!enableSound.value) return;

    try {
      initializeAudio();
      audioElement.value.currentTime = 0; // Reset to start
      audioElement.value.play().catch(err => {
        console.log('Could not play notification sound:', err);
      });
    } catch (error) {
      console.error('Error playing sound:', error);
    }
  }

  // Trigger device vibration
  function triggerVibration() {
    if (!enableVibration.value) return;

    if ('vibrate' in navigator) {
      try {
        // Vibrate pattern: vibrate 200ms, pause 100ms, vibrate 200ms
        navigator.vibrate([200, 100, 200]);
      } catch (error) {
        console.error('Error triggering vibration:', error);
      }
    }
  }

  // Show browser notification
  function showBrowserNotification(title, message, icon = '/hemodialise_logo.png') {
    if (!enableBrowserNotifications.value) return;

    if ('Notification' in window && Notification.permission === 'granted') {
      try {
        const notification = new Notification(title, {
          body: message,
          icon: icon,
          badge: icon,
          silent: true, // We'll play our own sound
        });

        // Auto-close after 5 seconds
        setTimeout(() => notification.close(), 5000);
      } catch (error) {
        console.error('Error showing browser notification:', error);
      }
    }
  }

  // Request notification permission
  async function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
      try {
        const permission = await Notification.requestPermission();
        return permission === 'granted';
      } catch (error) {
        console.error('Error requesting notification permission:', error);
        return false;
      }
    }
    return Notification.permission === 'granted';
  }

  // Handle new notification (play sound, vibrate, show browser notification)
  function notifyUser(notification) {
    playSound();
    triggerVibration();
    showBrowserNotification(notification.title, notification.message);
  }

  // Toggle settings
  function toggleSound() {
    enableSound.value = !enableSound.value;
    localStorage.setItem('notificationSound', enableSound.value);
  }

  function toggleVibration() {
    enableVibration.value = !enableVibration.value;
    localStorage.setItem('notificationVibration', enableVibration.value);
  }

  function toggleBrowserNotifications() {
    enableBrowserNotifications.value = !enableBrowserNotifications.value;
    localStorage.setItem('browserNotifications', enableBrowserNotifications.value);
  }

  return {
    enableSound,
    enableVibration,
    enableBrowserNotifications,
    playSound,
    triggerVibration,
    showBrowserNotification,
    requestNotificationPermission,
    notifyUser,
    toggleSound,
    toggleVibration,
    toggleBrowserNotifications,
  };
}
