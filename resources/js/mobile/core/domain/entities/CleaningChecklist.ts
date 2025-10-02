export interface CleaningChecklist {
  id: number;
  machine_id: number;
  user_id: number;
  checklist_date: string;
  shift: '1' | '2' | '3' | '4';
  chemical_disinfection_time?: string;
  chemical_disinfection_completed: boolean;
  hd_machine_cleaning?: 'C' | 'NC' | 'NA';
  osmosis_cleaning?: 'C' | 'NC' | 'NA';
  serum_support_cleaning?: 'C' | 'NC' | 'NA';
  observations?: string;
  created_at: string;
  updated_at: string;
  machine?: {
    id: number;
    name: string;
  };
  user?: {
    id: number;
    name: string;
  };
}

export interface CleaningChecklistCreate {
  machine_id: number;
  checklist_date: string;
  shift: '1' | '2' | '3' | '4';
  chemical_disinfection_time?: string;
  chemical_disinfection_completed?: boolean;
  hd_machine_cleaning?: 'C' | 'NC' | 'NA';
  osmosis_cleaning?: 'C' | 'NC' | 'NA';
  serum_support_cleaning?: 'C' | 'NC' | 'NA';
  observations?: string;
}

export interface CleaningChecklistStats {
  total_today: number;
  total_this_month: number;
  chemical_disinfection_today: number;
  surface_cleaning_today: number;
}
