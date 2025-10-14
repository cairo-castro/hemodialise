export interface Machine {
  id: number;
  name: string;
  identifier?: string;
  description?: string;
  model?: string;
  serial_number?: string;
  unit_id: number;
  status: 'available' | 'occupied' | 'reserved' | 'maintenance';
  is_active: boolean;
  last_maintenance?: string;
  current_checklist?: any;
  created_at: string;
  updated_at: string;
}