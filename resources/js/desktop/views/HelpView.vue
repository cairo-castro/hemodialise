<template>
  <div class="help-view space-y-6">
    <!-- Help Header -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl p-8 text-white">
      <div class="max-w-3xl">
        <h1 class="text-3xl font-bold mb-2">Central de Ajuda e Suporte</h1>
        <p class="text-primary-100 mb-6">
          Encontre respostas para suas dúvidas e aprenda a usar o Sistema de Hemodiálise
        </p>

        <!-- Search Box -->
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Pesquisar ajuda..."
            class="w-full px-5 py-3 pl-12 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 backdrop-blur-sm"
          >
          <svg class="absolute left-4 top-3.5 w-5 h-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <button
        v-for="link in quickLinks"
        :key="link.id"
        @click="scrollToSection(link.section)"
        class="flex items-start p-4 bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-md transition-all group text-left"
      >
        <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4 flex-shrink-0" :class="link.bgColor">
          <component :is="link.icon" class="w-6 h-6 text-white" />
        </div>
        <div class="flex-1">
          <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
            {{ link.title }}
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ link.description }}</p>
        </div>
      </button>
    </div>

    <!-- FAQs Section -->
    <div ref="faqsSection" class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Perguntas Frequentes</h2>

      <div class="space-y-3">
        <div
          v-for="(faq, index) in filteredFAQs"
          :key="index"
          class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
        >
          <button
            @click="toggleFAQ(index)"
            class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
          >
            <span class="font-medium text-gray-900 dark:text-white pr-4">{{ faq.question }}</span>
            <svg
              class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0 transition-transform"
              :class="{ 'rotate-180': faq.isOpen }"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <Transition name="accordion">
            <div v-if="faq.isOpen" class="px-4 pb-4 text-gray-600 dark:text-gray-400">
              <p v-html="faq.answer"></p>
            </div>
          </Transition>
        </div>
      </div>

      <div v-if="filteredFAQs.length === 0" class="text-center py-12">
        <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-gray-500 dark:text-gray-400">Nenhuma FAQ encontrada para sua busca</p>
      </div>
    </div>

    <!-- Getting Started Guide -->
    <div ref="guideSection" class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Guia de Início Rápido</h2>

      <div class="space-y-6">
        <div v-for="(step, index) in guideSteps" :key="index" class="flex items-start">
          <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/20 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold mr-4">
            {{ index + 1 }}
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ step.title }}</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-3">{{ step.description }}</p>
            <div v-if="step.tips" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
              <p class="text-sm text-blue-800 dark:text-blue-300">
                <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <strong>Dica:</strong> {{ step.tips }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recursos Adicionais -->
    <div ref="resourcesSection" class="bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Recursos Adicionais</h2>

      <div class="space-y-6">
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Atalhos e Dicas Rápidas
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-blue-900 dark:text-blue-100">Checklist Rápido</span>
              </div>
              <p class="text-sm text-blue-800 dark:text-blue-200">Clique em "Novo Checklist" na página inicial para iniciar rapidamente</p>
            </div>

            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="font-medium text-green-900 dark:text-green-100">Busca Rápida</span>
              </div>
              <p class="text-sm text-green-800 dark:text-green-200">Use a busca no topo da página para encontrar pacientes e máquinas</p>
            </div>

            <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="font-medium text-purple-900 dark:text-purple-100">Notificações</span>
              </div>
              <p class="text-sm text-purple-800 dark:text-purple-200">Configure suas preferências de email em Configurações</p>
            </div>

            <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-200 dark:border-orange-800">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span class="font-medium text-orange-900 dark:text-orange-100">Modo Mobile</span>
              </div>
              <p class="text-sm text-orange-800 dark:text-orange-200">Acesse pelo celular e instale como app para usar offline</p>
            </div>
          </div>
        </div>

        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Glossário de Termos
          </h3>
          <div class="space-y-2">
            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="flex items-start justify-between">
                <div>
                  <span class="text-sm font-medium text-gray-900 dark:text-white">Checklist de Segurança</span>
                  <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Verificação de 8 itens obrigatórios antes de cada sessão de diálise</p>
                </div>
              </div>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="flex items-start justify-between">
                <div>
                  <span class="text-sm font-medium text-gray-900 dark:text-white">Limpeza Diária/Semanal/Mensal</span>
                  <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Diferentes níveis de limpeza e manutenção das máquinas</p>
                </div>
              </div>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="flex items-start justify-between">
                <div>
                  <span class="text-sm font-medium text-gray-900 dark:text-white">PWA (Progressive Web App)</span>
                  <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Aplicativo web que funciona como app nativo no celular</p>
                </div>
              </div>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="flex items-start justify-between">
                <div>
                  <span class="text-sm font-medium text-gray-900 dark:text-white">Dashboard</span>
                  <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Página inicial com visão geral de estatísticas e atividades</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 rounded-lg p-6 border border-primary-200 dark:border-primary-800">
          <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Precisa de mais ajuda?</h3>
              <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                Se você não encontrou a resposta para sua dúvida, entre em contato com seu supervisor ou com a equipe de TI da sua unidade para suporte adicional.
              </p>
              <p class="text-xs text-gray-600 dark:text-gray-400">
                <strong>Dica:</strong> Use a busca no topo desta página para encontrar respostas rapidamente.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import {
  BookOpenIcon,
  LightBulbIcon,
  QuestionMarkCircleIcon,
} from '@heroicons/vue/24/outline';

const searchQuery = ref('');
const faqsSection = ref(null);
const guideSection = ref(null);
const resourcesSection = ref(null);

const quickLinks = [
  {
    id: 'getting-started',
    title: 'Guia de Início',
    description: 'Aprenda o básico do sistema',
    icon: BookOpenIcon,
    bgColor: 'bg-blue-500',
    section: 'guide',
  },
  {
    id: 'faqs',
    title: 'Perguntas Frequentes',
    description: 'Respostas para dúvidas comuns',
    icon: QuestionMarkCircleIcon,
    bgColor: 'bg-green-500',
    section: 'faqs',
  },
  {
    id: 'resources',
    title: 'Recursos Adicionais',
    description: 'Dicas e glossário de termos',
    icon: LightBulbIcon,
    bgColor: 'bg-purple-500',
    section: 'resources',
  },
];

const faqs = ref([
  {
    question: 'Como criar um novo checklist de segurança?',
    answer: 'Para criar um novo checklist, acesse o menu <strong>Checklists</strong> na barra lateral e clique no botão <strong>Novo Checklist</strong>. Preencha os dados do paciente, selecione a máquina e o turno, e siga as etapas de verificação.',
    isOpen: false,
    category: 'checklist',
  },
  {
    question: 'Como alterar minha senha?',
    answer: 'Vá para o seu <strong>Perfil</strong> clicando no menu do usuário no canto superior direito. Na seção "Alterar Senha", preencha sua senha atual e a nova senha. Clique em <strong>Salvar</strong> para confirmar.',
    isOpen: false,
    category: 'conta',
  },
  {
    question: 'O que fazer se uma máquina apresentar falha durante o checklist?',
    answer: 'Se detectar alguma falha, marque o item correspondente no checklist e adicione observações detalhadas. O checklist ficará com status de "Atenção" e a máquina deve ser verificada pela equipe de manutenção antes de novo uso.',
    isOpen: false,
    category: 'checklist',
  },
  {
    question: 'Como cadastrar um novo paciente?',
    answer: 'Acesse <strong>Pacientes</strong> no menu lateral e clique em <strong>Novo Paciente</strong>. Preencha todos os dados obrigatórios (nome completo, CPF, data de nascimento) e informações médicas relevantes. Clique em <strong>Salvar</strong>.',
    isOpen: false,
    category: 'paciente',
  },
  {
    question: 'Como visualizar o histórico de checklists de um paciente?',
    answer: 'Acesse a página de <strong>Pacientes</strong>, encontre o paciente desejado e clique em "Ver Detalhes". Na aba "Histórico", você verá todos os checklists realizados com aquele paciente.',
    isOpen: false,
    category: 'paciente',
  },
  {
    question: 'Posso editar um checklist após finalização?',
    answer: 'Não. Por questões de auditoria e segurança, checklists finalizados não podem ser editados. Caso identifique algum erro, entre em contato com seu supervisor ou administrador do sistema.',
    isOpen: false,
    category: 'checklist',
  },
  {
    question: 'Como trocar entre diferentes unidades?',
    answer: 'Se você tem acesso a múltiplas unidades, use o seletor de unidades no topo da barra lateral. Clique na unidade atual e selecione a unidade desejada. Todos os dados exibidos serão filtrados para a unidade selecionada.',
    isOpen: false,
    category: 'sistema',
  },
  {
    question: 'O sistema funciona offline?',
    answer: 'Sim! O sistema possui funcionalidade PWA que permite uso limitado offline. Checklists podem ser preenchidos sem internet e serão sincronizados automaticamente quando a conexão for restabelecida.',
    isOpen: false,
    category: 'sistema',
  },
  {
    question: 'Como ativar o modo escuro?',
    answer: 'Clique no menu do usuário no canto superior direito e use o toggle de tema. Você também pode acessar <strong>Configurações > Aparência</strong> para escolher entre tema claro e escuro.',
    isOpen: false,
    category: 'sistema',
  },
  {
    question: 'Onde encontro relatórios e estatísticas?',
    answer: 'O <strong>Dashboard</strong> exibe as principais métricas e estatísticas em tempo real. Para relatórios mais detalhados, acesse o menu <strong>Relatórios</strong> (disponível para gerentes e administradores).',
    isOpen: false,
    category: 'relatorio',
  },
  {
    question: 'Como configurar minhas preferências de notificações por email?',
    answer: 'Acesse <strong>Configurações > Notificações</strong>. Você pode ativar/desativar notificações para: novos checklists, manutenção de máquinas, relatórios semanais e atualizações do sistema. As alterações são salvas automaticamente.',
    isOpen: false,
    category: 'sistema',
  },
  {
    question: 'Como funciona o sistema de limpeza de máquinas?',
    answer: 'Acesse <strong>Limpezas</strong> no menu lateral. Existem três tipos: <strong>Limpeza Diária</strong> (após cada uso), <strong>Limpeza Semanal</strong> (procedimentos mais profundos) e <strong>Limpeza Mensal</strong> (manutenção completa). Cada tipo tem checklist específico.',
    isOpen: false,
    category: 'limpeza',
  },
  {
    question: 'O que significam os status das máquinas?',
    answer: '<strong>Ativa:</strong> máquina em uso normal. <strong>Manutenção:</strong> máquina temporariamente indisponível. <strong>Inativa:</strong> máquina fora de operação. <strong>Disponível:</strong> pronta para uso mas não em sessão.',
    isOpen: false,
    category: 'maquina',
  },
  {
    question: 'Posso acessar o sistema pelo celular?',
    answer: 'Sim! O sistema é um PWA (Progressive Web App) otimizado para dispositivos móveis. Você pode instalá-lo como aplicativo no seu celular para acesso rápido. O sistema funciona mesmo com conexão limitada e sincroniza automaticamente quando online.',
    isOpen: false,
    category: 'sistema',
  },
  {
    question: 'Como recebo notificações de eventos importantes?',
    answer: 'Acesse <strong>Configurações > Notificações</strong> e ative as notificações que deseja receber por email. Você será notificado sobre novos checklists, manutenções, relatórios semanais e atualizações do sistema.',
    isOpen: false,
    category: 'sistema',
  },
]);

const guideSteps = [
  {
    title: 'Faça login no sistema',
    description: 'Use suas credenciais fornecidas pela administração para acessar o sistema. Caso não tenha credenciais, entre em contato com seu supervisor.',
    tips: 'Mantenha sua senha segura e não a compartilhe com outros usuários.',
  },
  {
    title: 'Selecione sua unidade',
    description: 'Se você trabalha em múltiplas unidades, selecione a unidade correta no topo da barra lateral. Todos os dados exibidos serão filtrados para essa unidade.',
    tips: 'Sempre verifique se está na unidade correta antes de realizar checklists.',
  },
  {
    title: 'Explore o Dashboard',
    description: 'O Dashboard mostra as principais informações: checklists do dia, máquinas ativas, pacientes e taxa de conformidade. Use-o para ter uma visão geral da unidade.',
  },
  {
    title: 'Crie seu primeiro checklist',
    description: 'Acesse Checklists > Novo Checklist. Selecione o paciente, máquina, turno e preencha todas as verificações de segurança obrigatórias.',
    tips: 'Cada checklist tem 8 itens obrigatórios que devem ser verificados antes do início da sessão.',
  },
  {
    title: 'Acompanhe as atividades',
    description: 'Use a seção "Atividade Recente" no Dashboard para acompanhar as últimas ações da equipe em tempo real.',
  },
];

const filteredFAQs = computed(() => {
  if (!searchQuery.value) return faqs.value;

  const query = searchQuery.value.toLowerCase();
  return faqs.value.filter(faq =>
    faq.question.toLowerCase().includes(query) ||
    faq.answer.toLowerCase().includes(query)
  );
});

function toggleFAQ(index) {
  faqs.value[index].isOpen = !faqs.value[index].isOpen;
}

function scrollToSection(section) {
  const sections = {
    faqs: faqsSection.value,
    guide: guideSection.value,
    resources: resourcesSection.value,
  };

  const element = sections[section];
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}
</script>

<style scoped>
.help-view {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.accordion-enter-active,
.accordion-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.accordion-enter-from,
.accordion-leave-to {
  max-height: 0;
  opacity: 0;
}

.accordion-enter-to,
.accordion-leave-from {
  max-height: 500px;
  opacity: 1;
}
</style>
