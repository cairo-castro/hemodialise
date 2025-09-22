export class LocalStorageDataSource {
  set(key: string, value: string): void {
    try {
      localStorage.setItem(key, value);
    } catch (error) {
      console.error('Erro ao salvar no localStorage:', error);
    }
  }

  get(key: string): string | null {
    try {
      return localStorage.getItem(key);
    } catch (error) {
      console.error('Erro ao ler do localStorage:', error);
      return null;
    }
  }

  remove(key: string): void {
    try {
      localStorage.removeItem(key);
    } catch (error) {
      console.error('Erro ao remover do localStorage:', error);
    }
  }

  clear(): void {
    try {
      localStorage.clear();
    } catch (error) {
      console.error('Erro ao limpar localStorage:', error);
    }
  }

  setObject<T>(key: string, value: T): void {
    try {
      this.set(key, JSON.stringify(value));
    } catch (error) {
      console.error('Erro ao salvar objeto no localStorage:', error);
    }
  }

  getObject<T>(key: string): T | null {
    try {
      const item = this.get(key);
      return item ? JSON.parse(item) : null;
    } catch (error) {
      console.error('Erro ao ler objeto do localStorage:', error);
      return null;
    }
  }
}