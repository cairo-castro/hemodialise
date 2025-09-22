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
</script>
