export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'manager' | 'field_user';
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
  access_token: string;
  token_type: string;
  expires_in: number;
}