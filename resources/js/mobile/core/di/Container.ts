// Data Sources
import { ApiDataSource } from '../data/datasources/ApiDataSource';
import { LocalStorageDataSource } from '../data/datasources/LocalStorageDataSource';

// Repository Implementations
import { AuthRepositoryImpl } from '../data/repositories/AuthRepositoryImpl';
import { PatientRepositoryImpl } from '../data/repositories/PatientRepositoryImpl';
import { ChecklistRepositoryImpl } from '../data/repositories/ChecklistRepositoryImpl';
import { MachineRepositoryImpl } from '../data/repositories/MachineRepositoryImpl';

// Repository Interfaces
import { AuthRepository } from '../domain/repositories/AuthRepository';
import { PatientRepository } from '../domain/repositories/PatientRepository';
import { ChecklistRepository } from '../domain/repositories/ChecklistRepository';
import { MachineRepository } from '../domain/repositories/MachineRepository';

// Use Cases
import { LoginUseCase } from '../domain/usecases/auth/LoginUseCase';
import { GetCurrentUserUseCase } from '../domain/usecases/auth/GetCurrentUserUseCase';
import { LogoutUseCase } from '../domain/usecases/auth/LogoutUseCase';
import { SearchPatientUseCase } from '../domain/usecases/patient/SearchPatientUseCase';
import { CreatePatientUseCase } from '../domain/usecases/patient/CreatePatientUseCase';
import { CreateChecklistUseCase } from '../domain/usecases/checklist/CreateChecklistUseCase';

export class Container {
  private static instance: Container;
  private services: Map<string, any> = new Map();

  private constructor() {
    this.registerServices();
  }

  static getInstance(): Container {
    if (!Container.instance) {
      Container.instance = new Container();
    }
    return Container.instance;
  }

  private registerServices(): void {
    // Data Sources
    const apiDataSource = new ApiDataSource();
    const localStorageDataSource = new LocalStorageDataSource();

    this.services.set('ApiDataSource', apiDataSource);
    this.services.set('LocalStorageDataSource', localStorageDataSource);

    // Repositories
    const authRepository: AuthRepository = new AuthRepositoryImpl(apiDataSource, localStorageDataSource);
    const patientRepository: PatientRepository = new PatientRepositoryImpl(apiDataSource, localStorageDataSource);
    const checklistRepository: ChecklistRepository = new ChecklistRepositoryImpl(apiDataSource, localStorageDataSource);
    const machineRepository: MachineRepository = new MachineRepositoryImpl(apiDataSource, localStorageDataSource);

    this.services.set('AuthRepository', authRepository);
    this.services.set('PatientRepository', patientRepository);
    this.services.set('ChecklistRepository', checklistRepository);
    this.services.set('MachineRepository', machineRepository);

    // Use Cases
    this.services.set('LoginUseCase', new LoginUseCase(authRepository));
    this.services.set('GetCurrentUserUseCase', new GetCurrentUserUseCase(authRepository));
    this.services.set('LogoutUseCase', new LogoutUseCase(authRepository));
    this.services.set('SearchPatientUseCase', new SearchPatientUseCase(patientRepository));
    this.services.set('CreatePatientUseCase', new CreatePatientUseCase(patientRepository));
    this.services.set('CreateChecklistUseCase', new CreateChecklistUseCase(checklistRepository));
  }

  get<T>(serviceName: string): T {
    const service = this.services.get(serviceName);
    if (!service) {
      throw new Error(`Service ${serviceName} not found`);
    }
    return service;
  }

  // Convenience methods for common services
  getLoginUseCase(): LoginUseCase {
    return this.get<LoginUseCase>('LoginUseCase');
  }

  getCurrentUserUseCase(): GetCurrentUserUseCase {
    return this.get<GetCurrentUserUseCase>('GetCurrentUserUseCase');
  }

  getLogoutUseCase(): LogoutUseCase {
    return this.get<LogoutUseCase>('LogoutUseCase');
  }

  getSearchPatientUseCase(): SearchPatientUseCase {
    return this.get<SearchPatientUseCase>('SearchPatientUseCase');
  }

  getCreatePatientUseCase(): CreatePatientUseCase {
    return this.get<CreatePatientUseCase>('CreatePatientUseCase');
  }

  getCreateChecklistUseCase(): CreateChecklistUseCase {
    return this.get<CreateChecklistUseCase>('CreateChecklistUseCase');
  }

  getAuthRepository(): AuthRepository {
    return this.get<AuthRepository>('AuthRepository');
  }

  getMachineRepository(): MachineRepository {
    return this.get<MachineRepository>('MachineRepository');
  }
}