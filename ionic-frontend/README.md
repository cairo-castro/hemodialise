# Sistema de Hemodiálise - Frontend Ionic

Aplicativo móvel web desenvolvido com Ionic Vue seguindo princípios de Clean Architecture e SOLID.

## 🎯 Características

- **Mobile-First**: Projetado exclusivamente para dispositivos móveis
- **PWA**: Progressive Web App com capacidades offline
- **Clean Architecture**: Separação clara entre Domain, Data e Presentation layers
- **SOLID Principles**: Código modular e extensível
- **TypeScript**: Tipagem estática para maior robustez
- **Ionic Vue**: Interface nativa móvel com Vue.js

## 📱 Funcionalidades

### Autenticação
- Login JWT para usuários de campo
- Validação de roles (apenas field_user)
- Gerenciamento automático de tokens

### Dashboard
- Estatísticas em tempo real
- Ações rápidas para checklist e pacientes
- Status de conectividade (online/offline)

### Checklist de Segurança
- 8 verificações obrigatórias de segurança
- Busca/cadastro de pacientes
- Seleção de máquinas e turnos
- Validação completa antes do envio

### Gestão de Pacientes
- Busca de pacientes existentes
- Cadastro de novos pacientes
- Filtros por nome, prontuário e tipo sanguíneo

## 🏗️ Arquitetura

### Domain Layer (Entities, Repositories, Use Cases)
```
src/core/domain/
├── entities/           # Modelos de domínio
├── repositories/       # Interfaces dos repositórios
└── usecases/          # Lógica de negócio
```

### Data Layer (Implementações)
```
src/core/data/
├── datasources/       # API e LocalStorage
├── repositories/      # Implementações concretas
└── models/           # DTOs e mappers
```

### Presentation Layer (Views, Components)
```
src/presentation/
├── pages/            # Páginas da aplicação
├── components/       # Componentes reutilizáveis
└── stores/          # Gerenciamento de estado
```

### Dependency Injection
- Container centralizado para injeção de dependências
- Implementação do padrão Singleton
- Facilitador para testes unitários

## 🚀 Comandos

### Desenvolvimento
```bash
# Instalar dependências
npm install

# Servidor de desenvolvimento
npm run dev

# Lint
npm run lint
```

### Build e Deploy
```bash
# Build para produção
npm run build

# Build integrado para Laravel
npm run build:laravel
```

### Integração com Laravel
```bash
# Executar build automático para Laravel
npm run build:laravel
```

Este comando:
1. Compila o app Ionic
2. Gera arquivos na pasta `../public/ionic-build/`
3. Cria template Blade para Laravel
4. Configura manifest PWA
5. Gera documentação de rotas

## 📋 Integração com Laravel

### 1. Rotas Necessárias

Adicione ao `web.php`:
```php
// Mobile Ionic app route
Route::get('/mobile/ionic', function () {
    return view('mobile.ionic');
})->name('mobile.ionic');

// Redirect mobile users to Ionic app
Route::get('/mobile', function () {
    return redirect()->route('mobile.ionic');
})->name('mobile');
```

### 2. Middleware

O app está configurado para:
- Usar proxy `/api` em desenvolvimento
- Consumir API Laravel em produção
- Validar apenas usuários `field_user`

### 3. CORS (se necessário)

Configure CORS no Laravel para aceitar requests do frontend:
```php
// config/cors.php
'paths' => ['api/*'],
'allowed_origins' => ['http://localhost:5173'],
```

## 🎨 Tema e Estilo

### Cores Principais
- **Primary**: #2563eb (Azul para tema médico)
- **Secondary**: #0d9488 (Teal para contraste)
- **Success**: #059669 (Verde para confirmações)
- **Warning**: #ea580c (Laranja para alertas)
- **Danger**: #dc2626 (Vermelho para erros)

### Design System
- Mobile-first responsivo
- Componentes Ionic customizados
- Tipografia clara e acessível
- Ícones Ionicons

## 📱 Responsividade

### Mobile (< 768px)
- Interface completa do app

### Tablet (768px - 1024px)
- App centralizado com moldura

### Desktop (> 1024px)
- Mensagem informativa sobre uso mobile
- Sugestão para usar ferramentas de desenvolvedor

## 🔧 Configuração

### API Endpoints
Configure em `src/config/api.ts`:
```typescript
export const API_CONFIG = {
  BASE_URL: 'http://localhost:8000/api', // Development
  PRODUCTION_URL: '/api', // Production
  // ...
};
```

### Proxy Development
O Vite está configurado para proxy automático:
```typescript
server: {
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: true
    }
  }
}
```

## 🛡️ Segurança

- Tokens JWT armazenados em localStorage
- Validação de roles no frontend e backend
- Route guards para proteger páginas
- Validação de entrada nos use cases
- Sanitização de dados

## 📊 Estrutura de Dados

### Principais Entidades
- **User**: Usuários do sistema
- **Patient**: Pacientes de hemodiálise
- **SafetyChecklist**: Lista de verificação de segurança
- **Machine**: Máquinas de hemodiálise

### Fluxo de Dados
1. User faz login → JWT token
2. Dashboard carrega estatísticas
3. Checklist busca paciente → seleciona máquina → verifica segurança
4. Dados salvos via API Laravel

## 🧪 Testes

### Estrutura
```bash
npm run test:unit    # Testes unitários (Vitest)
npm run test:e2e     # Testes E2E (Cypress)
```

### Cobertura
- Use cases (lógica de negócio)
- Repositories (integração)
- Components (interface)

## 📦 Deploy

### Build de Produção
1. Execute `npm run build:laravel`
2. Arquivos gerados em `../public/ionic-build/`
3. Configure rotas no Laravel
4. Teste a integração

### PWA
- Manifest configurado automaticamente
- Service Worker para cache
- Instalação no dispositivo disponível

## 🔄 Próximos Passos

- [ ] Implementar modo offline completo
- [ ] Adicionar notificações push
- [ ] Otimizar performance com lazy loading
- [ ] Implementar testes automatizados
- [ ] Adicionar analytics e monitoramento

## 📝 Licença

Este projeto é parte do Sistema de Hemodiálise do Estado do Maranhão.