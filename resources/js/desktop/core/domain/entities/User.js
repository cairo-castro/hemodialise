/**
 * User Entity
 * Representa a entidade User seguindo DDD (Domain-Driven Design)
 */
export class User {
    constructor(id, name, email, role, unitId = null) {
        this.id = id;
        this.name = name;
        this.email = email;
        this.role = role;
        this.unitId = unitId;
    }

    /**
     * Verifica se o usuário é administrador
     */
    isAdmin() {
        return this.role === 'admin';
    }

    /**
     * Verifica se o usuário é gestor
     */
    isManager() {
        return ['gestor', 'coordenador', 'supervisor'].includes(this.role);
    }

    /**
     * Verifica se o usuário é técnico de campo
     */
    isFieldUser() {
        return ['field_user', 'tecnico'].includes(this.role);
    }

    /**
     * Verifica se pode alternar interfaces
     */
    canToggleInterfaces() {
        return this.isAdmin() || this.isManager();
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
            'field_user': 'Técnico de Campo'
        };
        return roles[this.role] || this.role;
    }

    /**
     * Obtém as iniciais do usuário
     */
    getInitials() {
        return this.name.charAt(0).toUpperCase();
    }

    /**
     * Valida se o usuário pode acessar a interface desktop
     */
    canAccessDesktop() {
        return !this.isFieldUser();
    }

    /**
     * Cria instância a partir de dados da API
     */
    static fromApiResponse(data) {
        return new User(
            data.id,
            data.name,
            data.email,
            data.role,
            data.unit_id
        );
    }
}