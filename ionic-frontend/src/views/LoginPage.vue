<template>
  <ion-page>
    <ion-content :fullscreen="true" class="login-content">
      <!-- Loading state -->
      <div v-if="isLoading" class="loading-container">
        <div class="loading-card">
          <ion-spinner name="crescent" class="loading-spinner"></ion-spinner>
          <p class="loading-text">Autenticando...</p>
        </div>
      </div>

      <!-- Login form -->
      <div v-else class="login-container">
        <!-- Decorative shapes -->
        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>
        <div class="bg-shape shape-3"></div>

        <!-- Login Card -->
        <div class="login-card">
          <!-- Header with logo -->
          <div class="login-header">
            <div class="logo-wrapper">
              <div class="logo-circle">
                <ion-icon :icon="medicalOutline" class="logo-icon"></ion-icon>
              </div>
            </div>
            <h1 class="app-title">Hemodiálise</h1>
            <p class="app-subtitle">Sistema de Segurança e Qualidade</p>
          </div>

          <!-- Login form -->
          <form @submit.prevent="handleLogin" class="login-form">
            <!-- Email Input -->
            <div class="input-group">
              <label class="input-label">
                <ion-icon :icon="mailOutline" class="label-icon"></ion-icon>
                <span>Email</span>
              </label>
              <div class="input-wrapper">
                <input
                  v-model="credentials.email"
                  type="email"
                  required
                  :disabled="isLoading"
                  placeholder="seu.email@exemplo.com"
                  class="input-field"
                  autocomplete="email"
                />
              </div>
            </div>

            <!-- Password Input -->
            <div class="input-group">
              <label class="input-label">
                <ion-icon :icon="lockClosedOutline" class="label-icon"></ion-icon>
                <span>Senha</span>
              </label>
              <div class="input-wrapper">
                <input
                  v-model="credentials.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  :disabled="isLoading"
                  placeholder="••••••••"
                  class="input-field"
                  autocomplete="current-password"
                />
                <button
                  type="button"
                  class="password-toggle"
                  @click="showPassword = !showPassword"
                  tabindex="-1"
                >
                  <ion-icon :icon="showPassword ? eyeOffOutline : eyeOutline"></ion-icon>
                </button>
              </div>
            </div>

            <!-- Login Button -->
            <button
              type="submit"
              class="login-button"
              :disabled="isLoading || !isFormValid"
            >
              <ion-icon :icon="logInOutline" class="button-icon"></ion-icon>
              <span>Entrar no Sistema</span>
            </button>
          </form>

          <!-- Footer Info -->
          <div class="card-footer">
            <div class="footer-badge">
              <ion-icon :icon="shieldCheckmarkOutline" class="badge-icon"></ion-icon>
              <span>Acesso Seguro</span>
            </div>
          </div>
        </div>

        <!-- Bottom Info -->
        <div class="bottom-info">
          <p class="info-text">Sistema de Controle de Qualidade</p>
          <p class="info-subtext">Estado do Maranhão · 2025</p>
        </div>

        <!-- Error Alert -->
        <ion-alert
          :is-open="showAlert"
          :header="alertData.header"
          :message="alertData.message"
          :buttons="['OK']"
          @didDismiss="showAlert = false"
        ></ion-alert>
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
import { 
  logInOutline, 
  medicalOutline, 
  mailOutline, 
  lockClosedOutline, 
  eyeOutline, 
  eyeOffOutline,
  shieldCheckmarkOutline 
} from 'ionicons/icons';

import { Container } from '@/core/di/Container';
import { LoginCredentials } from '@/core/domain/entities/User';

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
const showPassword = ref(false);
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
  isLoading.value = true;

  try {
    // Execute login use case
    await loginUseCase.execute(credentials.value);

    // Get user info
    const user = await getCurrentUserUseCase.execute();

    // Check if user is field_user (mobile only)
    if (user.role !== 'field_user') {
      throw new Error('Este aplicativo é destinado apenas para usuários de campo. Use o sistema web para acessar a área administrativa.');
    }

    // Show success message
    const toast = await toastController.create({
      message: `Bem-vindo, ${user.name}!`,
      duration: 2000,
      color: 'success',
      position: 'top'
    });
    await toast.present();

    // Navigate to dashboard
    router.replace('/dashboard');

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

      if (user.role === 'field_user') {
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
/* ===== BACKGROUND ===== */
.login-content {
  --background: #f8f9fa;
}

/* ===== DECORATIVE SHAPES ===== */
.bg-shape {
  position: absolute;
  border-radius: 50%;
  opacity: 0.05;
  pointer-events: none;
}

.shape-1 {
  width: 400px;
  height: 400px;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  top: -200px;
  right: -100px;
}

.shape-2 {
  width: 300px;
  height: 300px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  bottom: -150px;
  left: -100px;
}

.shape-3 {
  width: 200px;
  height: 200px;
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

/* ===== LOADING STATE ===== */
.loading-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 2rem;
}

.loading-card {
  background: white;
  padding: 3rem 2rem;
  border-radius: 24px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.loading-spinner {
  --color: #3b82f6;
  transform: scale(1.5);
  margin-bottom: 1.5rem;
}

.loading-text {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

/* ===== CONTAINER ===== */
.login-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 2rem 1.5rem;
  position: relative;
  overflow: hidden;
}

/* ===== LOGIN CARD ===== */
.login-card {
  width: 100%;
  max-width: 420px;
  background: white;
  border-radius: 24px;
  padding: 2.5rem 2rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
  position: relative;
  z-index: 10;
}

/* ===== HEADER ===== */
.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo-wrapper {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.logo-circle {
  width: 80px;
  height: 80px;
  border-radius: 20px;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
}

.logo-icon {
  font-size: 3rem;
  color: white;
}

.app-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 0.5rem 0;
  letter-spacing: -0.5px;
}

.app-subtitle {
  font-size: 0.938rem;
  color: #6b7280;
  margin: 0;
  font-weight: 500;
}

/* ===== FORM ===== */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.input-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.label-icon {
  font-size: 1rem;
  color: #3b82f6;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-field {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 1rem;
  color: #1f2937;
  background: #f9fafb;
  transition: all 0.2s ease;
  outline: none;
}

.input-field:focus {
  border-color: #3b82f6;
  background: white;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.input-field:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.input-field::placeholder {
  color: #9ca3af;
}

.password-toggle {
  position: absolute;
  right: 1rem;
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
}

.password-toggle:hover {
  color: #3b82f6;
}

.password-toggle ion-icon {
  font-size: 1.25rem;
}

/* ===== LOGIN BUTTON ===== */
.login-button {
  width: 100%;
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
  margin-top: 0.5rem;
}

.login-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
}

.login-button:active:not(:disabled) {
  transform: translateY(0);
}

.login-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.button-icon {
  font-size: 1.25rem;
}

/* ===== CARD FOOTER ===== */
.card-footer {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #f3f4f6;
  display: flex;
  justify-content: center;
}

.footer-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #f0fdf4;
  border-radius: 8px;
  font-size: 0.813rem;
  font-weight: 600;
  color: #059669;
}

.badge-icon {
  font-size: 1rem;
}

/* ===== BOTTOM INFO ===== */
.bottom-info {
  margin-top: 2rem;
  text-align: center;
  z-index: 10;
}

.info-text {
  font-size: 0.875rem;
  font-weight: 600;
  color: #4b5563;
  margin: 0 0 0.25rem 0;
}

.info-subtext {
  font-size: 0.813rem;
  color: #9ca3af;
  margin: 0;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 480px) {
  .login-card {
    padding: 2rem 1.5rem;
    border-radius: 20px;
  }

  .logo-circle {
    width: 70px;
    height: 70px;
    border-radius: 18px;
  }

  .logo-icon {
    font-size: 2.5rem;
  }

  .app-title {
    font-size: 1.5rem;
  }

  .app-subtitle {
    font-size: 0.875rem;
  }

  .login-form {
    gap: 1.25rem;
  }

  .input-field {
    padding: 0.75rem 0.875rem;
    font-size: 0.938rem;
  }

  .login-button {
    padding: 0.875rem 1.25rem;
    font-size: 0.938rem;
  }

  .shape-1,
  .shape-2,
  .shape-3 {
    opacity: 0.03;
  }
}

@media (max-height: 700px) {
  .login-container {
    padding: 1.5rem 1.5rem;
  }

  .login-card {
    padding: 2rem 1.5rem;
  }

  .login-header {
    margin-bottom: 1.5rem;
  }

  .logo-wrapper {
    margin-bottom: 1rem;
  }

  .logo-circle {
    width: 60px;
    height: 60px;
  }

  .logo-icon {
    font-size: 2rem;
  }

  .login-form {
    gap: 1rem;
  }

  .card-footer {
    margin-top: 1.5rem;
    padding-top: 1rem;
  }
}
</style>