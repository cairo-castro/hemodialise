export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'gestor' | 'coordenador' | 'supervisor' | 'tecnico';
  unit: {
    id: number;
    name: string;
  } | null;
  created_at: string;
  updated_at: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface AuthToken {
  token: string;
  user: User;
  access_token?: string; // Legacy compatibility
  token_type?: string;
  expires_in?: number;
}