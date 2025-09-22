import { ref, readonly } from 'vue';

/**
 * Composable para gerenciamento de estado da aplicação
 * Segue SRP - responsável apenas pelo estado da UI
 */
export function useAppStore() {
    // Estado reativo
    const currentView = ref('dashboard');
    const sidebarOpen = ref(false);
    const notifications = ref([]);

    // Actions
    function setCurrentView(view) {
        currentView.value = view;
    }

    function toggleSidebar() {
        sidebarOpen.value = !sidebarOpen.value;
    }

    function setSidebarOpen(isOpen) {
        sidebarOpen.value = isOpen;
    }

    function addNotification(notification) {
        notifications.value.push({
            id: Date.now(),
            type: notification.type || 'info',
            message: notification.message,
            timestamp: new Date()
        });
    }

    function removeNotification(id) {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index > -1) {
            notifications.value.splice(index, 1);
        }
    }

    function clearNotifications() {
        notifications.value = [];
    }

    // View titles and subtitles
    function getViewTitle() {
        const titles = {
            'dashboard': 'Dashboard Executivo',
            'safety-control': 'Controle de Segurança',
            'machines': 'Gestão de Máquinas',
            'patients': 'Gestão de Pacientes',
            'reports-operational': 'Relatórios Operacionais',
            'reports-quality': 'Qualidade e Segurança',
            'analytics': 'Analytics Avançado'
        };
        return titles[currentView.value] || 'Dashboard';
    }

    function getViewSubtitle() {
        const subtitles = {
            'dashboard': 'Visão geral em tempo real do sistema de hemodiálise',
            'safety-control': 'Monitoramento e controle de protocolos de segurança',
            'machines': 'Status e manutenção de equipamentos',
            'patients': 'Cadastro e acompanhamento de pacientes',
            'reports-operational': 'Relatórios detalhados das operações',
            'reports-quality': 'Indicadores de qualidade e conformidade',
            'analytics': 'Insights e métricas avançadas para tomada de decisão'
        };
        return subtitles[currentView.value] || 'Sistema de gestão executiva';
    }

    return {
        // Estado
        currentView: readonly(currentView),
        sidebarOpen: readonly(sidebarOpen),
        notifications: readonly(notifications),
        
        // Actions
        setCurrentView,
        toggleSidebar,
        setSidebarOpen,
        addNotification,
        removeNotification,
        clearNotifications,
        getViewTitle,
        getViewSubtitle
    };
}