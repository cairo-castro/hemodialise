<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/dashboard"></ion-back-button>
        </ion-buttons>
        <ion-title>Meu Perfil</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="profile-content">
      <div class="profile-container">
        <!-- Pull to refresh -->
        <ion-refresher slot="fixed" @ionRefresh="handleRefresh">
          <ion-refresher-content></ion-refresher-content>
        </ion-refresher>

        <!-- Loading State -->
        <div v-if="isLoading && !user" class="loading-state">
          <ion-spinner name="crescent"></ion-spinner>
          <p>Carregando perfil...</p>
        </div>

        <!-- Content -->
        <div v-else-if="user">
          <!-- Profile Header -->
          <ion-card class="profile-header-card">
            <ion-card-content>
              <div class="profile-header">
                <div class="profile-avatar">
                  <ion-icon :icon="personCircleOutline"></ion-icon>
                </div>
                <div class="profile-header-info">
                  <h2>{{ user.name }}</h2>
                  <p class="user-email">{{ user.email }}</p>
                  <ion-badge :color="getRoleBadgeColor(user.role)">
                    {{ getRoleLabel(user.role) }}
                  </ion-badge>
                </div>
              </div>
              <div class="profile-unit" v-if="user.unit">
                <ion-icon :icon="locationOutline"></ion-icon>
                <span>{{ user.unit.name }}</span>
              </div>
            </ion-card-content>
          </ion-card>

          <!-- Basic Info Form -->
          <ion-card class="form-card">
            <ion-card-header>
              <ion-card-title>Informações Básicas</ion-card-title>
            </ion-card-header>
            <ion-card-content>
              <form @submit.prevent="updateBasicInfo">
                <div class="form-group">
                  <label>Nome Completo</label>
                  <ion-input
                    v-model="formData.name"
                    type="text"
                    placeholder="Digite seu nome"
                    :disabled="isUpdatingInfo"
                    required
                  ></ion-input>
                </div>

                <div class="form-group">
                  <label>E-mail</label>
                  <ion-input
                    v-model="formData.email"
                    type="email"
                    placeholder="Digite seu e-mail"
                    :disabled="isUpdatingInfo"
                    required
                  ></ion-input>
                </div>

                <div class="form-actions">
                  <ion-button
                    expand="block"
                    type="submit"
                    :disabled="isUpdatingInfo || !hasBasicInfoChanges"
                  >
                    <ion-icon slot="start" :icon="saveOutline"></ion-icon>
                    {{ isUpdatingInfo ? 'Salvando...' : 'Salvar Alterações' }}
                  </ion-button>
                </div>
              </form>
            </ion-card-content>
          </ion-card>

          <!-- Change Password Form -->
          <ion-card class="form-card">
            <ion-card-header>
              <ion-card-title>Alterar Senha</ion-card-title>
            </ion-card-header>
            <ion-card-content>
              <form @submit.prevent="updatePassword">
                <div class="form-group">
                  <label>Senha Atual</label>
                  <div class="password-input">
                    <ion-input
                      v-model="passwordData.current_password"
                      :type="showCurrentPassword ? 'text' : 'password'"
                      placeholder="Digite sua senha atual"
                      :disabled="isUpdatingPassword"
                      required
                    ></ion-input>
                    <ion-button
                      fill="clear"
                      @click="showCurrentPassword = !showCurrentPassword"
                      class="password-toggle"
                    >
                      <ion-icon
                        :icon="showCurrentPassword ? eyeOffOutline : eyeOutline"
                      ></ion-icon>
                    </ion-button>
                  </div>
                </div>

                <div class="form-group">
                  <label>Nova Senha</label>
                  <div class="password-input">
                    <ion-input
                      v-model="passwordData.new_password"
                      :type="showNewPassword ? 'text' : 'password'"
                      placeholder="Digite a nova senha"
                      :disabled="isUpdatingPassword"
                      required
                    ></ion-input>
                    <ion-button
                      fill="clear"
                      @click="showNewPassword = !showNewPassword"
                      class="password-toggle"
                    >
                      <ion-icon
                        :icon="showNewPassword ? eyeOffOutline : eyeOutline"
                      ></ion-icon>
                    </ion-button>
                  </div>
                  <div class="password-hint">
                    Mínimo de 8 caracteres
                  </div>
                </div>

                <div class="form-group">
                  <label>Confirmar Nova Senha</label>
                  <div class="password-input">
                    <ion-input
                      v-model="passwordData.new_password_confirmation"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      placeholder="Confirme a nova senha"
                      :disabled="isUpdatingPassword"
                      required
                    ></ion-input>
                    <ion-button
                      fill="clear"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="password-toggle"
                    >
                      <ion-icon
                        :icon="showConfirmPassword ? eyeOffOutline : eyeOutline"
                      ></ion-icon>
                    </ion-button>
                  </div>
                </div>

                <div class="form-actions">
                  <ion-button
                    expand="block"
                    type="submit"
                    color="warning"
                    :disabled="isUpdatingPassword || !hasPasswordData"
                  >
                    <ion-icon slot="start" :icon="lockClosedOutline"></ion-icon>
                    {{ isUpdatingPassword ? 'Alterando...' : 'Alterar Senha' }}
                  </ion-button>
                </div>
              </form>
            </ion-card-content>
          </ion-card>

          <!-- Account Info -->
          <ion-card class="info-card">
            <ion-card-header>
              <ion-card-title>Informações da Conta</ion-card-title>
            </ion-card-header>
            <ion-card-content>
              <div class="info-item">
                <div class="info-label">
                  <ion-icon :icon="shieldCheckmarkOutline"></ion-icon>
                  <span>Tipo de Conta</span>
                </div>
                <div class="info-value">{{ getRoleLabel(user.role) }}</div>
              </div>

              <div class="info-item" v-if="user.unit">
                <div class="info-label">
                  <ion-icon :icon="businessOutline"></ion-icon>
                  <span>Unidade</span>
                </div>
                <div class="info-value">{{ user.unit.name }}</div>
              </div>

              <div class="info-item">
                <div class="info-label">
                  <ion-icon :icon="calendarOutline"></ion-icon>
                  <span>Membro desde</span>
                </div>
                <div class="info-value">{{ formatDate(user.created_at) }}</div>
              </div>
            </ion-card-content>
          </ion-card>
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
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonButtons,
  IonBackButton,
  IonButton,
  IonIcon,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardContent,
  IonRefresher,
  IonRefresherContent,
  IonSpinner,
  IonBadge,
  IonInput,
  toastController,
  loadingController,
  alertController
} from '@ionic/vue';
import {
  personCircleOutline,
  locationOutline,
  saveOutline,
  lockClosedOutline,
  eyeOutline,
  eyeOffOutline,
  shieldCheckmarkOutline,
  businessOutline,
  calendarOutline
} from 'ionicons/icons';

const router = useRouter();

// State
const user = ref<any>(null);
const isLoading = ref(false);
const isUpdatingInfo = ref(false);
const isUpdatingPassword = ref(false);

// Form Data
const formData = ref({
  name: '',
  email: ''
});

const passwordData = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

// Password visibility toggles
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Computed
const hasBasicInfoChanges = computed(() => {
  if (!user.value) return false;
  return (
    formData.value.name !== user.value.name ||
    formData.value.email !== user.value.email
  );
});

const hasPasswordData = computed(() => {
  return (
    passwordData.value.current_password.length > 0 &&
    passwordData.value.new_password.length > 0 &&
    passwordData.value.new_password_confirmation.length > 0
  );
});

// Methods
const loadUserProfile = async () => {
  try {
    isLoading.value = true;

    const response = await fetch('/api/me', {
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const data = await response.json();

    if (data.success && data.user) {
      user.value = data.user;

      // Populate form data
      formData.value = {
        name: user.value.name || '',
        email: user.value.email || ''
      };
    } else {
      throw new Error(data.message || 'Erro ao carregar perfil');
    }
  } catch (error: any) {
    console.error('Error loading profile:', error);
    const toast = await toastController.create({
      message: error.message || 'Erro ao carregar perfil',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    isLoading.value = false;
  }
};

const updateBasicInfo = async () => {
  try {
    isUpdatingInfo.value = true;

    const response = await fetch('/api/profile/update', {
      method: 'PUT',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(formData.value)
    });

    const data = await response.json();

    if (data.success) {
      user.value = data.user;

      // Update form data to reflect saved values
      formData.value = {
        name: user.value.name || '',
        email: user.value.email || ''
      };

      const toast = await toastController.create({
        message: data.message || 'Perfil atualizado com sucesso',
        duration: 2000,
        color: 'success',
        position: 'top'
      });
      await toast.present();
    } else {
      throw new Error(data.message || 'Erro ao atualizar perfil');
    }
  } catch (error: any) {
    console.error('Error updating profile:', error);
    const toast = await toastController.create({
      message: error.message || 'Erro ao atualizar perfil',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    isUpdatingInfo.value = false;
  }
};

const updatePassword = async () => {
  // Validate password match
  if (passwordData.value.new_password !== passwordData.value.new_password_confirmation) {
    const toast = await toastController.create({
      message: 'As senhas não coincidem',
      duration: 3000,
      color: 'warning',
      position: 'top'
    });
    await toast.present();
    return;
  }

  // Validate password length
  if (passwordData.value.new_password.length < 8) {
    const toast = await toastController.create({
      message: 'A nova senha deve ter no mínimo 8 caracteres',
      duration: 3000,
      color: 'warning',
      position: 'top'
    });
    await toast.present();
    return;
  }

  try {
    isUpdatingPassword.value = true;

    const response = await fetch('/api/profile/password', {
      method: 'PUT',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(passwordData.value)
    });

    const data = await response.json();

    if (data.success) {
      // Clear password fields
      passwordData.value = {
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
      };

      // Hide password fields
      showCurrentPassword.value = false;
      showNewPassword.value = false;
      showConfirmPassword.value = false;

      const alert = await alertController.create({
        header: 'Senha Alterada',
        message: 'Sua senha foi alterada com sucesso!',
        buttons: ['OK']
      });
      await alert.present();
    } else {
      throw new Error(data.message || 'Erro ao alterar senha');
    }
  } catch (error: any) {
    console.error('Error updating password:', error);
    const toast = await toastController.create({
      message: error.message || 'Erro ao alterar senha',
      duration: 3000,
      color: 'danger',
      position: 'top'
    });
    await toast.present();
  } finally {
    isUpdatingPassword.value = false;
  }
};

const handleRefresh = async (event: any) => {
  await loadUserProfile();
  event.target.complete();
};

const getRoleLabel = (role: string) => {
  const roleMap: Record<string, string> = {
    'admin': 'Administrador',
    'manager': 'Gestor',
    'field_user': 'Usuário de Campo',
    'tecnico': 'Técnico',
    'coordenador': 'Coordenador',
    'supervisor': 'Supervisor'
  };
  return roleMap[role] || role;
};

const getRoleBadgeColor = (role: string) => {
  const colorMap: Record<string, string> = {
    'admin': 'danger',
    'manager': 'warning',
    'field_user': 'primary',
    'tecnico': 'primary',
    'coordenador': 'secondary',
    'supervisor': 'tertiary'
  };
  return colorMap[role] || 'medium';
};

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  });
};

// Lifecycle
onMounted(() => {
  loadUserProfile();
});
</script>

<style scoped>
.profile-content {
  --background: #f5f7fa;
}

.profile-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 16px;
  padding-bottom: 32px;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  gap: 16px;
}

.loading-state p {
  color: var(--ion-color-step-600);
  font-size: 0.9rem;
}

/* Profile Header Card */
.profile-header-card {
  margin-bottom: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 16px;
}

.profile-avatar {
  width: 80px;
  height: 80px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.profile-avatar ion-icon {
  font-size: 4rem;
  color: white;
}

.profile-header-info {
  flex: 1;
  min-width: 0;
}

.profile-header-info h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 4px 0;
  color: white;
}

.user-email {
  font-size: 0.9rem;
  margin: 0 0 8px 0;
  opacity: 0.9;
  word-break: break-word;
}

.profile-unit {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 8px;
}

.profile-unit ion-icon {
  font-size: 1.2rem;
  flex-shrink: 0;
}

.profile-unit span {
  font-size: 0.9rem;
  font-weight: 500;
}

/* Form Card */
.form-card {
  margin-bottom: 20px;
}

.form-card ion-card-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--ion-text-color);
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--ion-text-color);
  margin-bottom: 8px;
}

.form-group ion-input {
  --background: var(--ion-background-color);
  --padding-start: 12px;
  --padding-end: 12px;
  border: 2px solid var(--ion-color-step-150);
  border-radius: 8px;
  font-size: 0.95rem;
}

.form-group ion-input:focus-within {
  --background: var(--ion-card-background);
  border-color: var(--ion-color-primary);
}

.password-input {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input ion-input {
  flex: 1;
  padding-right: 48px;
}

.password-toggle {
  position: absolute;
  right: 0;
  --padding-start: 8px;
  --padding-end: 8px;
  height: 100%;
  margin: 0;
}

.password-toggle ion-icon {
  font-size: 1.3rem;
  color: var(--ion-color-step-600);
}

.password-hint {
  font-size: 0.8rem;
  color: var(--ion-color-step-600);
  margin-top: 4px;
  font-style: italic;
}

.form-actions {
  margin-top: 24px;
}

.form-actions ion-button {
  --border-radius: 10px;
  height: 48px;
  font-weight: 600;
}

/* Info Card */
.info-card {
  margin-bottom: 20px;
}

.info-card ion-card-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--ion-text-color);
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid var(--ion-color-step-150);
}

.info-item:last-child {
  border-bottom: none;
}

.info-label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.9rem;
  color: var(--ion-color-step-600);
  font-weight: 500;
}

.info-label ion-icon {
  font-size: 1.2rem;
  color: var(--ion-color-step-500);
}

.info-value {
  font-size: 0.9rem;
  color: var(--ion-text-color);
  font-weight: 600;
  text-align: right;
}

/* Responsive */
@media (max-width: 480px) {
  .profile-container {
    padding: 12px;
  }

  .profile-avatar {
    width: 60px;
    height: 60px;
  }

  .profile-avatar ion-icon {
    font-size: 3rem;
  }

  .profile-header-info h2 {
    font-size: 1.3rem;
  }

  .info-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }

  .info-value {
    text-align: left;
  }
}
</style>
