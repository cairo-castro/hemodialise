import { Patient, PatientSearchCriteria, CreatePatientData } from '../entities/Patient';

export interface PatientRepository {
  search(criteria: PatientSearchCriteria): Promise<Patient | null>;
  create(data: CreatePatientData): Promise<Patient>;
  getById(id: number): Promise<Patient>;
  getAll(searchQuery?: string, perPage?: number): Promise<Patient[]>;
  quickSearch(query: string, limit?: number): Promise<Patient[]>;
  update(id: number, data: Partial<CreatePatientData>): Promise<Patient>;
}