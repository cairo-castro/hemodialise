<template>
  <ion-page>
    <ion-content :fullscreen="true" class="login-content">
      <!-- Loading state -->
      <div v-if="isLoading" class="loading-container">
        <ion-spinner name="crescent"></ion-spinner>
        <p>Entrando...</p>
      </div>

      <!-- Login form -->
      <div v-else class="login-container">
        <!-- Header with logo -->
        <div class="login-header">
          <div class="logo-container">
            <ion-icon :icon="medicalOutline" class="logo-icon"></ion-icon>
          </div>
          <h1>Hemodiálise</h1>
          <p>Sistema Mobile de Segurança</p>
        </div>

        <!-- Login form -->
        <form @submit.prevent="handleLogin" class="login-form">
          <div class="form-group">
            <ion-item class="mobile-input" fill="outline">
              <ion-label position="floating">Email</ion-label>
              <ion-input
                v-model="credentials.email"
                type="email"
                required
                :disabled="isLoading"
                placeholder="Digite seu email"
              ></ion-input>
            </ion-item>
          </div>

          <div class="form-group">
            <ion-item class="mobile-input" fill="outline">
              <ion-label position="floating">Senha</ion-label>
              <ion-input
                v-model="credentials.password"
                type="password"
                required
                :disabled="isLoading"
                placeholder="Digite sua senha"
              ></ion-input>
            </ion-item>
          </div>

          <ion-button
            expand="block"
            type="submit"
            class="mobile-button"
            :disabled="isLoading || !isFormValid"
          >
            <ion-icon :icon="logInOutline" slot="start"></ion-icon>
            Entrar
          </ion-button>
        </form>

        <!-- Error message -->
        <ion-alert
          :is-open="showAlert"
          :header="alertData.header"
          :message="alertData.message"
          :buttons="['OK']"
          @didDismiss="showAlert = false"
        ></ion-alert>

        <!-- Footer -->
        <div class="login-footer">
          <p>Sistema de Controle de Qualidade</p>
          <p>Estado do Maranhão</p>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  IonPage,
  IonContent,
  IonItem,
  IonLabel,
  IonInput,
  IonButton,
  IonAlert,
  IonSpinner,
  IonIcon,
  toastController
} from '@ionic/vue';
import { logInOutline, medicalOutline } from 'ionicons/icons';

import { Container } from '@mobile/core/di/Container';
import { LoginCredentials } from '@mobile/core/domain/entities/User';

const router = useRouter();
const container = Container.getInstance();

// Use cases
const loginUseCase = container.getLoginUseCase();
const getCurrentUserUseCase = container.getCurrentUserUseCase();
const authRepository = container.getAuthRepository();

// Reactive state
const credentials = ref<LoginCredentials>({
  email: '',
  password: ''
});

const isLoading = ref(false);
const showAlert = ref(false);
const alertData = ref({
  header: '',
  message: ''
});

// Computed properties
const isFormValid = computed(() => {
  return credentials.value.email.length > 0 && credentials.value.password.length > 0;
});

// Methods
const handleLogin = async () => {
  console.log('Starting login process...');
  isLoading.value = true;

  try {
    console.log('Executing login use case...');
    // Execute login use case
    await loginUseCase.execute(credentials.value);
    console.log('Login use case completed successfully');

    console.log('Getting current user...');
    // Get user info
    const user = await getCurrentUserUseCase.execute();
    console.log('Current user:', user);

    // Check if user is tecnico (mobile only)
    if (user.role !== 'tecnico') {
      throw new Error('Este aplicativo é destinado apenas para usuários de campo. Use o sistema web para acessar a área administrativa.');
    }

    console.log('User role validation passed');

    // Show success message
    const toast = await toastController.create({
      message: `Bem-vindo, ${user.name}!`,
      duration: 1500,
      color: 'success',
      position: 'top'
    });
    await toast.present();
    console.log('Toast presented');

    // Navigate to dashboard after a short delay
    console.log('About to navigate to dashboard...');
    setTimeout(() => {
      console.log('Executing router.replace to /dashboard');
      router.replace('/dashboard');
      console.log('Router.replace called');
    }, 100);

  } catch (error: any) {
    console.error('Login error:', error);

    alertData.value = {
      header: 'Erro no Login',
      message: error.message || 'Erro interno. Tente novamente.'
    };
    showAlert.value = true;
  } finally {
    isLoading.value = false;
  }
};

const checkExistingAuth = async () => {
  try {
    if (authRepository.isAuthenticated()) {
      const user = await getCurrentUserUseCase.execute();

      if (user.role === 'tecnico') {
        router.replace('/dashboard');
      } else {
        // Clear invalid token for non-field users
        authRepository.removeToken();
      }
    }
  } catch (error) {
    // Invalid token, will show login form
    authRepository.removeToken();
  }
};

// Lifecycle
onMounted(() => {
  checkExistingAuth();
});
</script>

<style scoped>
.login-content {
  --background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
  color: white;
  text-align: center;
}

.loading-container ion-spinner {
  --color: white;
  margin-bottom: 1rem;
  transform: scale(1.5);
}

.login-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  padding: 2rem 1.5rem;
  color: white;
}

.login-header {
  text-align: center;
  margin-bottom: 3rem;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.logo-container {
  margin-bottom: 1rem;
}

.logo-icon {
  font-size: 4rem;
  color: white;
}

.login-header h1 {
  font-size: 2rem;
  font-weight: bold;
  margin: 0.5rem 0;
}

.login-header p {
  font-size: 1rem;
  opacity: 0.9;
  margin: 0;
}

.login-form {
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 1rem;
}

.mobile-input {
  --background: rgba(255, 255, 255, 0.95);
  --color: #1f2937;
  border-radius: 12px;
  margin-bottom: 1rem;
}

.mobile-button {
  --background: rgba(255, 255, 255, 0.2);
  --background-activated: rgba(255, 255, 255, 0.3);
  --color: white;
  --border-radius: 12px;
  margin-top: 1rem;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

.login-footer {
  text-align: center;
  opacity: 0.8;
  font-size: 0.875rem;
}

.login-footer p {
  margin: 0.25rem 0;
}
</style>