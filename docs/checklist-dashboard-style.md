# Melhorias da Página de Checklist - Dashboard Style

## Visão Geral

Este documento descreve as melhorias implementadas na página de checklist (`ChecklistPage.vue`), incluindo redesign com estilo dashboard, simplificação do fluxo de busca de pacientes e criação de página dedicada para cadastro de novos pacientes.

**Data**: 13 de outubro de 2025  
**Arquivos Modificados**:
- `resources/js/mobile/views/ChecklistPage.vue`
- `resources/js/mobile/views/PatientFormPage.vue` (novo)
- `resources/js/mobile/router/index.ts`
- `resources/js/mobile/core/di/Container.ts`

---

## 🎨 Redesign com Estilo Dashboard

### Visual Consistente
Todo o visual da página foi atualizado para seguir o mesmo padrão usado no Dashboard:

#### Estrutura de Seções
```vue
<div class="section">
  <h2 class="section-title">
    <ion-icon :icon="icon"></ion-icon>
    Título da Seção
  </h2>
  <!-- Conteúdo -->
</div>
```

#### Cards Modernos
- Background branco com bordas cinzas (#e5e7eb)
- Border-radius: 12px
- Padding consistente: 16px
- Sombras suaves

#### Action Buttons
```css
.action-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
}
```

---

## 🔍 Fluxo Simplificado de Busca

### Antes (Problemático)
1. Usuário preenchia nome e data
2. Sistema criava paciente automaticamente se não encontrado
3. Sem confirmação ou opção de cancelar

### Depois (Melhorado)
1. **Busca em Tempo Real**: Campo de busca com debounce de 500ms
2. **Resultados Instantâneos**: Lista de pacientes encontrados
3. **Sem Resultados**: Alert claro com botão para cadastrar
4. **Cadastro Dedicado**: Página completa para cadastrar novo paciente
5. **Retorno Automático**: Após cadastro, volta ao checklist com paciente selecionado

---

## 📝 Nova Página de Cadastro de Paciente

### Criação do PatientFormPage.vue

#### Estrutura
```
┌─────────────────────────────────────┐
│      [📋 Ícone Grande]              │
│   Cadastrar Paciente                │
│   Preencha as informações...        │
├─────────────────────────────────────┤
│ 👤 Nome Completo *                  │
│ [_____________________________]     │
│                                     │
│ 📅 Data de Nascimento *            │
│ [_____________________________]     │
│                                     │
│ 📄 Prontuário                      │
│ [_____________________________]     │
│                                     │
│ 💧 Tipo Sanguíneo                  │
│ [_____________________________]     │
│                                     │
│ ⚠️  Alergias                        │
│ [_____________________________]     │
│ [_____________________________]     │
│ [_____________________________]     │
│                                     │
│ 📝 Observações                     │
│ [_____________________________]     │
│ [_____________________________]     │
│ [_____________________________]     │
├─────────────────────────────────────┤
│ ℹ️ Campos com * são obrigatórios   │
├─────────────────────────────────────┤
│  [✖ Cancelar]  [✓ Salvar Paciente] │
└─────────────────────────────────────┘
```

#### Campos
- ✅ Nome Completo (obrigatório)
- ✅ Data de Nascimento (obrigatório)
- 📄 Prontuário (opcional)
- 💧 Tipo Sanguíneo (opcional - dropdown)
- ⚠️ Alergias (opcional - textarea)
- 📝 Observações (opcional - textarea)

#### Validações
- Nome completo deve ter pelo menos 1 caractere
- Data de nascimento é obrigatória
- Botão "Salvar" fica desabilitado se campos obrigatórios vazios

#### Fluxo de Cancelamento
```typescript
const handleCancel = async () => {
  if (form has data) {
    // Show confirmation alert
    "Descartar alterações?"
  } else {
    // Go back immediately
  }
};
```

---

## 🔄 Fluxo Completo - Do Início ao Fim

### 1. Busca de Paciente

#### Interface
```vue
<div class="search-card">
  <div class="search-input-wrapper">
    <ion-icon :icon="searchOutline"></ion-icon>
    <input v-model="searchQuery" placeholder="Digite o nome..." />
  </div>
</div>
```

#### Comportamento
- **Debounce**: 500ms após parar de digitar
- **Mínimo**: 3 caracteres para iniciar busca
- **Loading**: Spinner durante busca
- **Resultados**: Lista de pacientes encontrados (máx. 10)

### 2. Paciente Encontrado

```vue
<div class="action-btn selected">
  <div class="action-icon success">
    <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
  </div>
  <div class="action-content">
    <span class="action-title">{{ patient.full_name }}</span>
    <span class="action-subtitle">Data • Idade anos</span>
  </div>
  <ion-button fill="clear" @click="clear">
    <ion-icon :icon="closeOutline"></ion-icon>
  </ion-button>
</div>
```

### 3. Paciente Não Encontrado

```vue
<div class="no-results">
  <ion-icon :icon="alertCircleOutline" class="no-results-icon"></ion-icon>
  <h3>Paciente não encontrado</h3>
  <p>Não encontramos nenhum paciente com esse nome.</p>
  <ion-button @click="navigateToRegisterPatient">
    <ion-icon :icon="personAddOutline"></ion-icon>
    Cadastrar Novo Paciente
  </ion-button>
</div>
```

### 4. Navegação para Cadastro

```typescript
const navigateToRegisterPatient = () => {
  // Store search query to pre-fill form
  localStorage.setItem('patient_search_query', searchQuery.value);
  localStorage.setItem('return_to_checklist', 'true');
  router.push('/patients/new');
};
```

### 5. Cadastro do Paciente

- Formulário pré-preenchido com nome buscado
- Validação em tempo real
- Loading durante salvamento
- Toast de sucesso

### 6. Retorno ao Checklist

```typescript
const handleSubmit = async () => {
  const patient = await createPatientUseCase.execute(form);
  
  // Store patient ID for checklist to load
  localStorage.setItem('new_patient_id', patient.id.toString());
  
  // Return to checklist
  router.replace('/checklist/new');
};
```

### 7. Checklist Carrega Paciente

```typescript
onMounted(() => {
  const returnToChecklist = localStorage.getItem('return_to_checklist');
  const newPatientId = localStorage.getItem('new_patient_id');
  
  if (returnToChecklist === 'true' && newPatientId) {
    loadNewPatient(parseInt(newPatientId));
    // Clean up
    localStorage.removeItem('return_to_checklist');
    localStorage.removeItem('new_patient_id');
    localStorage.removeItem('patient_search_query');
  }
});
```

---

## 🎯 Seleção de Máquina e Turno

### Redesign com Dashboard Style

#### Máquina
```vue
<ion-item fill="solid" class="select-input">
  <ion-select 
    v-model="machine_id"
    interface="action-sheet"
    cancel-text="Cancelar"
  >
    <!-- Options -->
  </ion-select>
</ion-item>
```

#### Turnos com Botões Visuais
```vue
<div class="shift-selector">
  <button class="shift-btn" :class="{ active: shift === 'matutino' }">
    <ion-icon :icon="sunnyOutline"></ion-icon>
    <span>Matutino</span>
  </button>
  <button class="shift-btn" :class="{ active: shift === 'vespertino' }">
    <ion-icon :icon="partlySunnyOutline"></ion-icon>
    <span>Vespertino</span>
  </button>
  <button class="shift-btn" :class="{ active: shift === 'noturno' }">
    <ion-icon :icon="moonOutline"></ion-icon>
    <span>Noturno</span>
  </button>
</div>
```

### Primary Action Button
```vue
<button class="primary-btn" @click="startChecklist" :disabled="!canStart">
  <ion-icon :icon="playOutline"></ion-icon>
  <div class="btn-text">
    <span class="btn-title">Iniciar Checklist</span>
    <span class="btn-subtitle">Começar verificação de segurança</span>
  </div>
  <ion-icon :icon="arrowForwardOutline"></ion-icon>
</button>
```

---

## 🛠️ Alterações Técnicas

### Container.ts - Novo Método
```typescript
getPatientRepository(): PatientRepository {
  return this.get<PatientRepository>('PatientRepository');
}
```

### Router - Nova Rota
```typescript
{
  path: '/patients/new',
  name: 'NewPatient',
  component: () => import('@mobile/views/PatientFormPage.vue'),
  beforeEnter: requiresAuth
}
```

### ChecklistPage - Novo State
```typescript
// Search state
const searchQuery = ref('');
const searchResults = ref<Patient[]>([]);
const isSearching = ref(false);
const selectedPatient = ref<Patient | null>(null);
let searchTimeout: ReturnType<typeof setTimeout>;
```

### Novos Métodos
```typescript
// Debounced search
const handleSearchInput = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  if (searchQuery.value.length < 3) {
    searchResults.value = [];
    return;
  }
  isSearching.value = true;
  searchTimeout = setTimeout(async () => {
    await searchPatients();
  }, 500);
};

// Search patients
const searchPatients = async () => {
  const results = await patientRepository.quickSearch(searchQuery.value, 10);
  searchResults.value = results;
  isSearching.value = false;
};

// Select patient from results
const selectPatient = (patient: Patient) => {
  selectedPatient.value = patient;
  searchQuery.value = patient.full_name;
  searchResults.value = [];
};

// Navigate to register
const navigateToRegisterPatient = () => {
  localStorage.setItem('patient_search_query', searchQuery.value);
  localStorage.setItem('return_to_checklist', 'true');
  router.push('/patients/new');
};

// Load newly created patient
const loadNewPatient = async (patientId: number) => {
  const patient = await patientRepository.getById(patientId);
  if (patient) {
    selectedPatient.value = patient;
    searchQuery.value = patient.full_name;
    // Toast de sucesso
  }
};
```

---

## 🎨 Sistema de Design

### Paleta de Cores

#### Background
- Page Background: `#f9fafb`
- Card Background: `white`
- Input Background: `#f9fafb` (inactive), `white` (focused)

#### Borders
- Default: `#e5e7eb`
- Active: `var(--ion-color-primary)`
- Success: `var(--ion-color-success)`

#### Text
- Primary: `#1f2937`
- Secondary: `#6b7280`
- Disabled: `#9ca3af`

### Tipografia

#### Titles
- Section Title: 1.1rem, weight 700
- Card Title: 1rem, weight 600

#### Body
- Default: 0.95rem, weight 400
- Subtitle: 0.85rem, weight 400

### Espaçamento
- Section Margin: 24px
- Card Padding: 16px
- Form Group Margin: 20px
- Gap between elements: 8px - 12px

### Border Radius
- Cards: 12px
- Primary Button: 16px
- Icons: 12px
- Large Icons: 20px

### Sombras
- Cards: `0 2px 8px rgba(0, 0, 0, 0.08)`
- Primary Button: `0 4px 12px rgba(primary-rgb, 0.3)`

---

## 📱 Responsividade

### Layout Adaptativo

#### Search Input
```css
.search-input-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
}
```

#### Shift Selector
```css
.shift-selector {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}
```

#### Form Actions
```css
.form-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
```

---

## ✨ Melhorias de UX

### Feedback Visual

1. **Loading States**
   - Spinner durante busca
   - Loading overlay ao salvar
   - Toast de sucesso/erro

2. **Estados de Botões**
   - Disabled quando inválido
   - Active transform on click
   - Hover states

3. **Validação em Tempo Real**
   - Botões habilitam/desabilitam automaticamente
   - Campos obrigatórios marcados com *
   - Alert de confirmação ao cancelar com dados

### Microanimações
```css
.action-btn:active {
  transform: scale(0.98);
}

.shift-btn:active {
  transform: scale(0.95);
}

.primary-btn:active:not(:disabled) {
  transform: scale(0.98);
}
```

---

## 🔍 Casos de Uso

### Caso 1: Buscar Paciente Existente
1. Técnico entra em "Novo Checklist"
2. Digite nome do paciente (min. 3 caracteres)
3. Sistema busca em tempo real
4. Lista mostra resultados
5. Técnico clica no paciente correto
6. Paciente é selecionado
7. Técnico prossegue para seleção de máquina

### Caso 2: Cadastrar Novo Paciente
1. Técnico busca paciente que não existe
2. Sistema mostra "Paciente não encontrado"
3. Técnico clica em "Cadastrar Novo Paciente"
4. Sistema redireciona para formulário
5. Nome buscado já está pré-preenchido
6. Técnico completa dados obrigatórios
7. Clica em "Salvar Paciente"
8. Sistema salva e retorna ao checklist
9. Paciente recém-criado já está selecionado
10. Técnico prossegue para seleção de máquina

### Caso 3: Cancelar Cadastro
1. Técnico está preenchendo formulário
2. Clica em "Cancelar"
3. Sistema pergunta: "Descartar alterações?"
4. Técnico confirma
5. Sistema volta ao checklist
6. Busca anterior é mantida

---

## 📊 Benefícios

### Para o Usuário
1. ✅ **Visual consistente** com dashboard
2. ✅ **Busca rápida** com resultados instantâneos
3. ✅ **Processo claro** - sempre sabe o que fazer
4. ✅ **Sem surpresas** - confirmações antes de ações importantes
5. ✅ **Formulário completo** - todos os campos disponíveis
6. ✅ **Retorno automático** - não perde contexto

### Para o Sistema
1. ✅ **Separação de responsabilidades** - busca vs cadastro
2. ✅ **Reutilização de componentes** - PatientFormPage pode ser usado em outros lugares
3. ✅ **Type safety** - TypeScript em todos os lugares
4. ✅ **Manutenibilidade** - código organizado e documentado
5. ✅ **Performance** - busca com debounce, resultados limitados

---

## 🚀 Como Testar

### Teste 1: Busca Bem-Sucedida
```
1. Abrir /checklist/new
2. Digitar nome de paciente existente
3. Verificar loading
4. Verificar resultados aparecem
5. Clicar em um resultado
6. Verificar paciente selecionado
7. Verificar botão "X" limpa seleção
```

### Teste 2: Cadastro de Novo Paciente
```
1. Abrir /checklist/new
2. Digitar nome inexistente (min. 3 chars)
3. Verificar "Paciente não encontrado"
4. Clicar em "Cadastrar Novo Paciente"
5. Verificar redirecionamento para /patients/new
6. Verificar nome pré-preenchido
7. Preencher data de nascimento
8. Clicar em "Salvar Paciente"
9. Verificar loading
10. Verificar toast de sucesso
11. Verificar volta para /checklist/new
12. Verificar paciente já selecionado
```

### Teste 3: Cancelamento de Cadastro
```
1. Seguir passos 1-7 do Teste 2
2. Clicar em "Cancelar"
3. Verificar alert de confirmação
4. Clicar em "Descartar"
5. Verificar volta para checklist
```

### Teste 4: Seleção de Máquina e Turno
```
1. Com paciente selecionado
2. Abrir dropdown de máquinas
3. Selecionar uma máquina
4. Clicar em cada turno (Matutino, Vespertino, Noturno)
5. Verificar visual active
6. Clicar em "Iniciar Checklist"
7. Verificar redirecionamento
```

---

## 🔮 Melhorias Futuras

### Curto Prazo
- [ ] Adicionar foto do paciente
- [ ] Histórico de buscas recentes
- [ ] Busca por prontuário

### Médio Prazo
- [ ] Autocomplete no formulário
- [ ] Validação de CPF
- [ ] Integração com cartão SUS
- [ ] Edição de paciente existente

### Longo Prazo
- [ ] Busca por voz
- [ ] OCR para documentos
- [ ] Sincronização offline
- [ ] Histórico completo do paciente

---

**Desenvolvido para**: Sistema de Hemodiálise - Estado do Maranhão  
**Documentação criada em**: 13/10/2025
