import { ref, computed, readonly } from 'vue';

/**
 * Composable para gerenciamento de estado de autenticação
 * Segue SRP - responsável apenas pelo estado de auth
 */
export function useAuthStore() {
    // Estado reativo
    const user = ref(null);
    const loading = ref(false);
    const error = ref(null);

    // Getters computados
    const isAuthenticated = computed(() => user.value !== null);
    const isGuest = computed(() => user.value?.role === 'guest');
    const canToggleInterfaces = computed(() => user.value?.canToggleInterfaces() || false);
    const userInitials = computed(() => user.value?.getInitials() || 'U');
    const userRoleDisplay = computed(() => user.value?.getRoleDisplay() || 'Usuário');

    // Actions
    function setUser(newUser) {
        user.value = newUser;
        error.value = null;
    }

    function setLoading(isLoading) {
        loading.value = isLoading;
    }

    function setError(newError) {
        error.value = newError;
    }

    function clearUser() {
        user.value = null;
        error.value = null;
    }

    function setGuestUser() {
        user.value = {
            name: 'Usuário',
            role: 'guest',
            canToggleInterfaces: () => false,
            getInitials: () => 'U',
            getRoleDisplay: () => 'Convidado',
            canAccessDesktop: () => true
        };
    }

    return {
        // Estado
        user: readonly(user),
        loading: readonly(loading),
        error: readonly(error),
        
        // Getters
        isAuthenticated,
        isGuest,
        canToggleInterfaces,
        userInitials,
        userRoleDisplay,
        
        // Actions
        setUser,
        setLoading,
        setError,
        clearUser,
        setGuestUser
    };
}