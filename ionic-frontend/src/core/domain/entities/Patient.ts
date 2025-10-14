export interface Patient {
  id: number;
  full_name: string;
  birth_date: string;
  blood_group?: string;
  rh_factor?: string;
  blood_type?: string;
  allergies?: string;
  observations?: string;
  age: number;
  created_at: string;
  updated_at: string;
}

export interface PatientSearchCriteria {
  full_name: string;
  birth_date: string;
}

export interface CreatePatientData {
  full_name: string;
  birth_date: string;
  blood_group?: string;
  rh_factor?: string;
  allergies?: string;
  observations?: string;
}