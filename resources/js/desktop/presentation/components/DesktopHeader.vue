<template>
  <!-- Top Header -->
  <div class="sticky top-0 inset-x-0 z-20 bg-white border-y px-4 sm:px-6 md:px-8 lg:hidden">
    <div class="flex items-center py-4">
      <button type="button" class="text-gray-500 hover:text-gray-600">
        <span class="sr-only">Toggle Navigation</span>
        <svg class="w-5 h-5" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Page Header -->
  <header class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ viewTitle }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ viewSubtitle }}</p>
      </div>

      <div class="flex items-center space-x-4">

        <!-- Real-time Status -->
        <div class="flex items-center space-x-2">
          <span class="status-indicator status-online"></span>
          <span class="text-sm text-gray-600">Sistema Online</span>
        </div>

        <!-- Notifications -->
        <div class="hs-dropdown relative inline-flex">
          <button type="button" class="hs-dropdown-toggle relative p-2 text-gray-500 hover:text-gray-600 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
            </svg>
            <span class="absolute top-1 right-1 status-indicator bg-red-500"></span>
          </button>
        </div>

        <!-- User Menu -->
        <div class="hs-dropdown relative inline-flex">
          <button type="button" class="hs-dropdown-toggle flex items-center space-x-3 text-left">
            <div class="w-8 h-8 medical-gradient rounded-full flex items-center justify-center text-white text-sm font-bold">
              {{ user?.getInitials ? user.getInitials() : user?.name?.charAt(0) || 'U' }}
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
            </svg>
          </button>

          <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg p-2 mt-2">
            <div class="py-2 first:pt-0 last:pb-0">
              <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100" href="#">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zM8 6a2 2 0 114 0v1H8V6z"/>
                </svg>
                Configurações
              </a>
              <button 
                @click="$emit('logout')" 
                class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-red-600 hover:bg-red-50 w-full text-left"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z"/>
                </svg>
                Sair
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
defineProps({
  user: Object,
  viewTitle: String,
  viewSubtitle: String
});

const emit = defineEmits(['logout']);
</script>

<style scoped>
.status-indicator {
  width: 0.5rem;
  height: 0.5rem;
  border-radius: 9999px;
}

.status-online {
  background-color: #10b981;
}

.medical-gradient {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}
</style>