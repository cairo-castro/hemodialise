/**
 * User Entity - Representa um usuário do sistema
 * Seguindo Clean Architecture: Domain Layer
 */
export class User {
    constructor(data = {}) {
        this.id = data.id || null;
        this.name = data.name || '';
        this.email = data.email || '';
        this.role = data.role || 'guest';
        this.unit = data.unit || null;
    }

    /**
     * Verifica se é um usuário administrador
     */
    isAdmin() {
        return this.role === 'admin';
    }

    /**
     * Verifica se é um gestor (pode alternar interfaces)
     */
    isManager() {
        return ['admin', 'gestor', 'coordenador', 'supervisor'].includes(this.role);
    }

    /**
     * Verifica se é um usuário de campo (apenas mobile)
     */
    isFieldUser() {
        return ['field_user', 'tecnico'].includes(this.role);
    }

    /**
     * Obtém a primeira letra do nome para avatar
     */
    getAvatarLetter() {
        return this.name ? this.name.charAt(0).toUpperCase() : '?';
    }

    /**
     * Obtém o nome de exibição do role
     */
    getRoleDisplay() {
        const roles = {
            'admin': 'Administrador',
            'gestor': 'Gestor Regional',
            'coordenador': 'Coordenador',
            'supervisor': 'Supervisor',
            'tecnico': 'Técnico',
            'field_user': 'Técnico de Campo',
            'guest': 'Convidado'
        };
        return roles[this.role] || this.role;
    }
}