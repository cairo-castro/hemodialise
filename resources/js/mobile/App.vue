<template>
  <!-- Desktop warning for screens >= 1024px -->
  <div class="desktop-warning" style="display: none;">
    <h1>📱 Aplicativo Mobile</h1>
    <p>Este aplicativo foi projetado exclusivamente para dispositivos móveis.</p>
    <p>Acesse pelo seu smartphone ou tablet para uma melhor experiência.</p>
    <div style="margin-top: 2rem; padding: 1rem; background: rgba(255,255,255,0.1); border-radius: 8px;">
      <p style="font-size: 1rem;">Para testar no navegador, use as ferramentas de desenvolvedor (F12) e ative a visualização mobile.</p>
    </div>
  </div>

  <ion-app>
    <ion-router-outlet />
  </ion-app>
</template>

<script setup lang="ts">
import { IonApp, IonRouterOutlet } from '@ionic/vue';
import { onMounted } from 'vue';
import { useDarkMode } from '@mobile/composables/useDarkMode';
import { useDataSync } from '@mobile/composables/useDataSync';
import { useRouter } from 'vue-router';

// Initialize dark mode globally when app starts
const { initializeDarkMode } = useDarkMode();
const router = useRouter();

// Initialize data sync globally - automatic polling for real-time updates
// Sistema leve que consome apenas 1-2% da performance de WebSockets
const { isPolling, hasUpdates, latestData } = useDataSync({
  interval: 15000, // Verifica a cada 15 segundos (configurável)
  onUpdate: (data) => {
    console.log('[App] Novas atualizações recebidas:', data);

    // Aqui você pode disparar eventos globais, atualizar stores, etc.
    // Por exemplo:
    // - Mostrar notificação toast
    // - Atualizar dados em cache local
    // - Recarregar listas se estiver na página correspondente

    // Exemplo: recarregar página atual se houver atualizações relevantes
    const currentRoute = router.currentRoute.value.name;

    if (currentRoute === 'ChecklistList' && data.safety_checklists?.length > 0) {
      console.log('[App] Atualizações em checklists detectadas');
      // Você pode emitir um evento ou usar um store global aqui
    }

    if (currentRoute === 'CleaningControls' && data.cleaning_controls?.length > 0) {
      console.log('[App] Atualizações em controles de limpeza detectadas');
    }
  }
});

onMounted(() => {
  initializeDarkMode();
  console.log('[App] Mobile App montado');
  console.log('[App] Dark mode inicializado');
  console.log('[App] Data sync ativo:', isPolling.value);
});
</script>
