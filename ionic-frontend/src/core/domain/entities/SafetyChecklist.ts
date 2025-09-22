export interface SafetyChecklist {
  id: number;
  patient_id: number;
  machine_id: number;
  user_id: number;
  shift: 'matutino' | 'vespertino' | 'noturno';
  check_date: string;

  // 8 mandatory safety checks
  patient_identification: boolean;
  access_patency: boolean;
  vital_signs: boolean;
  weight_measurement: boolean;
  machine_parameters: boolean;
  anticoagulation: boolean;
  alarm_tests: boolean;
  emergency_procedures: boolean;

  observations?: string;
  completed_at?: string;
  created_at: string;
  updated_at: string;
}

export interface CreateChecklistData {
  patient_id: number;
  machine_id: number;
  shift: 'matutino' | 'vespertino' | 'noturno';

  patient_identification: boolean;
  access_patency: boolean;
  vital_signs: boolean;
  weight_measurement: boolean;
  machine_parameters: boolean;
  anticoagulation: boolean;
  alarm_tests: boolean;
  emergency_procedures: boolean;

  observations?: string;
}