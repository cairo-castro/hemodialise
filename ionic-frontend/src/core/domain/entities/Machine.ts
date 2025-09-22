export interface Machine {
  id: number;
  name: string;
  model: string;
  serial_number: string;
  unit_id: number;
  status: 'active' | 'maintenance' | 'inactive';
  last_maintenance: string;
  created_at: string;
  updated_at: string;
}