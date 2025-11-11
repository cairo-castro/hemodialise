import { alertController } from '@ionic/vue';
import { useRouter } from 'vue-router';

/**
 * Composable for handling session expiration across the mobile app
 */
export function useSessionHandler() {
  const router = useRouter();

  /**
   * Handle session expiration - show alert and redirect to login
   */
  async function handleSessionExpired(message?: string) {
    console.log('[SessionHandler] Session expired - logging out user');

    // Clear all local storage
    localStorage.clear();
    sessionStorage.clear();

    // Show friendly alert to user
    const alert = await alertController.create({
      header: 'Sessão Expirada',
      message: message || 'Sua sessão expirou por motivos de segurança. Por favor, faça login novamente.',
      buttons: [
        {
          text: 'Fazer Login',
          role: 'confirm',
          handler: () => {
            // Redirect to login page
            router.push('/login').then(() => {
              // Force reload to clear any cached state
              window.location.reload();
            });
          }
        }
      ],
      backdropDismiss: false // Prevent dismissing by tapping backdrop
    });

    await alert.present();
  }

  return {
    handleSessionExpired
  };
}

/**
 * Static method for use outside Vue components (e.g., in ApiDataSource)
 */
export async function handleSessionExpiredStatic(message?: string) {
  console.log('[SessionHandler] Session expired (static) - logging out user');

  // Clear all local storage
  localStorage.clear();
  sessionStorage.clear();

  // Show friendly alert to user
  const alert = await alertController.create({
    header: 'Sessão Expirada',
    message: message || 'Sua sessão expirou por motivos de segurança. Por favor, faça login novamente.',
    buttons: [
      {
        text: 'Fazer Login',
        role: 'confirm',
        handler: () => {
          // Redirect to login page and force reload
          window.location.href = '/mobile/login';
          window.location.reload();
        }
      }
    ],
    backdropDismiss: false
  });

  await alert.present();
}
