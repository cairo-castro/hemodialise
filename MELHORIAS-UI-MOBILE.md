# Melhorias de UI Mobile - Checklist e Pacientes

## 🎯 Objetivo
Melhorar a UX/UI das telas de Checklist de Segurança e Pacientes no mobile, seguindo o padrão visual da Dashboard.

## 📱 ChecklistPage.vue - Melhorias Principais

### 1. **Adicionar Progress Steps** (Indicador de Progresso)
```vue
<div class="progress-container">
  <div class="progress-steps">
    <div class="step" :class="{ active: currentStep >= 1, completed: currentStep > 1 }">
      <div class="step-number">1</div>
      <div class="step-label">Paciente</div>
    </div>
    <div class="step-line"></div>
    <div class="step" :class="{ active: currentStep >= 2 }">
      <div class="step-number">2</div>
      <div class="step-label">Dados</div>
    </div>
    <div class="step-line"></div>
    <div class="step" :class="{ active: currentStep >= 3 }">
      <div class="step-number">3</div>
      <div class="step-label">Checks</div>
    </div>
  </div>
</div>
```

**Adicionar variável:**
```typescript
const currentStep = ref(1);
```

### 2. **Melhorar Card de Paciente Encontrado**
Trocar o chip simples por um card visual mais bonito:

```vue
<div v-if="selectedPatient" class="patient-found-card">
  <div class="success-icon">
    <ion-icon :icon="checkmarkCircleOutline"></ion-icon>
  </div>
  <div class="patient-details">
    <h3>{{ selectedPatient.full_name }}</h3>
    <div class="patient-meta">
      <span><ion-icon :icon="calendarOutline"></ion-icon> {{ formatDate(selectedPatient.birth_date) }}</span>
      <span><ion-icon :icon="personOutline"></ion-icon> {{ selectedPatient.age }} anos</span>
    </div>
  </div>
  <ion-button fill="clear" @click="goToNextStep">
    <ion-icon :icon="arrowForwardOutline"></ion-icon>
  </ion-button>
</div>
```

**CSS:**
```css
.patient-found-card {
  margin-top: 1.5rem;
  padding: 1.25rem;
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 1rem;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
```

### 3. **Adicionar Seletor Visual de Turno**
Trocar select por chips clicáveis:

```vue
<div class="shift-selector">
  <ion-chip
    v-for="shift in shifts"
    :key="shift.value"
    :color="checklistForm.shift === shift.value ? 'primary' : 'medium'"
    @click="checklistForm.shift = shift.value"
    class="shift-chip"
  >
    <ion-icon :icon="shift.icon"></ion-icon>
    <ion-label>{{ shift.label }}</ion-label>
  </ion-chip>
</div>
```

**Dados:**
```typescript
const shifts = [
  { value: 'matutino', label: 'Manhã', icon: sunnyOutline },
  { value: 'vespertino', label: 'Tarde', icon: partlySunnyOutline },
  { value: 'noturno', label: 'Noite', icon: moonOutline }
];
```

**Imports adicionais:**
```typescript
import { sunnyOutline, partlySunnyOutline, moonOutline } from 'ionicons/icons';
```

### 4. **Melhorar Checkboxes de Segurança**
Trocar `ion-checkbox` por cards clicáveis:

```vue
<div
  v-for="(check, index) in safetyCheckItems"
  :key="index"
  class="check-item"
  :class="{ checked: getCheckValue(check.key) }"
  @click="toggleCheck(check.key)"
>
  <div class="check-box">
    <ion-icon v-if="getCheckValue(check.key)" :icon="checkmarkOutline"></ion-icon>
  </div>
  <div class="check-content">
    <h4>{{ check.label }}</h4>
    <p>{{ check.description }}</p>
  </div>
  <div class="check-icon">
    <ion-icon :icon="check.icon"></ion-icon>
  </div>
</div>
```

**Adicionar ícones aos checks:**
```typescript
const safetyCheckItems = [
  {
    key: 'patient_identification',
    label: 'Identificação do Paciente',
    description: 'Confirmar identidade com dois identificadores',
    icon: personOutline
  },
  {
    key: 'access_patency',
    label: 'Permeabilidade do Acesso',
    description: 'Verificar funcionamento do acesso vascular',
    icon: eyeOutline
  },
  // ... adicionar ícones para todos
];
```

**Método para toggle:**
```typescript
const toggleCheck = (key: string) => {
  const currentValue = getCheckValue(key);
  setCheckValue(key, !currentValue);
};
```

**CSS dos check items:**
```css
.check-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  border: 2px solid transparent;
}

.check-item.checked {
  background: #d1fae5;
  border-color: var(--ion-color-success);
}

.check-box {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  border: 2px solid var(--ion-color-medium);
  display: flex;
  align-items: center;
  justify-content: center;
}

.check-item.checked .check-box {
  background: var(--ion-color-success);
  border-color: var(--ion-color-success);
}
```

### 5. **Adicionar Progress Bar dos Checks**
Mostrar progresso de preenchimento:

```vue
<div class="checks-progress">
  <div class="progress-text">
    <span>{{ completedChecksCount }}/{{ safetyCheckItems.length }}</span>
    <span>{{ checksProgressPercent }}%</span>
  </div>
  <div class="progress-bar">
    <div class="progress-fill" :style="{ width: checksProgressPercent + '%' }"></div>
  </div>
</div>
```

**Computed properties:**
```typescript
const completedChecksCount = computed(() => {
  return safetyCheckItems.filter(item => {
    const key = item.key as keyof CreateChecklistData;
    return checklistForm.value[key] === true;
  }).length;
});

const checksProgressPercent = computed(() => {
  return Math.round((completedChecksCount.value / safetyCheckItems.length) * 100);
});
```

### 6. **Adicionar Botão de Reset**
No header, adicionar botão para reiniciar:

```vue
<ion-buttons slot="end">
  <ion-button @click="resetForm" v-if="selectedPatient">
    <ion-icon :icon="refreshOutline" slot="icon-only"></ion-icon>
  </ion-button>
</ion-buttons>
```

**Método:**
```typescript
const resetForm = async () => {
  const alert = await alertController.create({
    header: 'Reiniciar Formulário',
    message: 'Tem certeza? Todos os dados serão perdidos.',
    buttons: [
      { text: 'Cancelar', role: 'cancel' },
      {
        text: 'Reiniciar',
        role: 'destructive',
        handler: () => {
          selectedPatient.value = null;
          currentStep.value = 1;
          // Reset forms...
        }
      }
    ]
  });
  await alert.present();
};
```

---

## 👥 PatientsPage.vue - Melhorias Principais

### 1. **Adicionar Filtros Rápidos**
```vue
<div class="filter-chips">
  <ion-chip
    v-for="filter in filters"
    :key="filter.value"
    :color="activeFilter === filter.value ? 'primary' : 'medium'"
    @click="activeFilter = filter.value"
  >
    <ion-icon :icon="filter.icon"></ion-icon>
    <ion-label>{{ filter.label }}</ion-label>
  </ion-chip>
</div>
```

**Dados:**
```typescript
const activeFilter = ref('all');
const filters = [
  { value: 'all', label: 'Todos', icon: peopleOutline },
  { value: 'active', label: 'Ativos', icon: checkmarkCircleOutline },
  { value: 'recent', label: 'Recentes', icon: timeOutline }
];
```

### 2. **Melhorar Cards de Pacientes**
Adicionar mais informações visuais:

```vue
<ion-card class="patient-card-enhanced">
  <ion-card-content>
    <div class="patient-header">
      <div class="patient-avatar">
        {{ patient.full_name.charAt(0) }}
      </div>
      <div class="patient-info">
        <h3>{{ patient.full_name }}</h3>
        <div class="patient-meta">
          <span><ion-icon :icon="calendarOutline"></ion-icon> {{ patient.age }} anos</span>
          <span v-if="patient.blood_type"><ion-icon :icon="waterOutline"></ion-icon> {{ patient.blood_type }}</span>
        </div>
      </div>
      <ion-badge color="success">Ativo</ion-badge>
    </div>

    <div class="patient-stats" v-if="patient.total_sessions">
      <div class="stat">
        <ion-icon :icon="clipboardOutline"></ion-icon>
        <span>{{ patient.total_sessions }} sessões</span>
      </div>
      <div class="stat">
        <ion-icon :icon="calendarOutline"></ion-icon>
        <span>Última: {{ formatDate(patient.last_session) }}</span>
      </div>
    </div>
  </ion-card-content>
</ion-card>
```

**CSS:**
```css
.patient-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 600;
}

.patient-stats {
  display: flex;
  gap: 1rem;
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid var(--ion-color-light);
}

.stat {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: var(--ion-color-medium);
}
```

### 3. **Adicionar Ordenação**
```vue
<ion-item lines="none" class="sort-selector">
  <ion-label>Ordernar por:</ion-label>
  <ion-select v-model="sortBy" interface="popover">
    <ion-select-option value="name">Nome</ion-select-option>
    <ion-select-option value="age">Idade</ion-select-option>
    <ion-select-option value="recent">Mais recente</ion-select-option>
  </ion-select>
</ion-item>
```

**Computed para ordenação:**
```typescript
const sortBy = ref('name');

const sortedPatients = computed(() => {
  const patients = [...filteredPatients.value];
  switch (sortBy.value) {
    case 'name':
      return patients.sort((a, b) => a.full_name.localeCompare(b.full_name));
    case 'age':
      return patients.sort((a, b) => a.age - b.age);
    case 'recent':
      return patients.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
    default:
      return patients;
  }
});
```

### 4. **Adicionar Stats Summary**
No topo da lista:

```vue
<div class="patients-summary">
  <div class="summary-card">
    <h4>{{ patients.length }}</h4>
    <p>Total Pacientes</p>
  </div>
  <div class="summary-card">
    <h4>{{ activePatients }}</h4>
    <p>Ativos</p>
  </div>
  <div class="summary-card">
    <h4>{{ newThisMonth }}</h4>
    <p>Este Mês</p>
  </div>
</div>
```

---

## 🎨 Cores e Temas Consistentes

Usar as mesmas cores da Dashboard:

```css
/* Gradientes */
.header-gradient {
  background: linear-gradient(135deg, var(--ion-color-primary) 0%, var(--ion-color-primary-shade) 100%);
}

.success-gradient {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
}

/* Cards modernos */
.modern-card {
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

/* Botões de ação */
.action-button {
  --border-radius: 12px;
  height: 52px;
  font-weight: 600;
  font-size: 1rem;
}
```

---

## 📝 Checklist de Implementação

### ChecklistPage:
- [ ] Adicionar progress steps (1, 2, 3)
- [ ] Melhorar card de paciente encontrado
- [ ] Adicionar seletor visual de turno (chips)
- [ ] Melhorar checkboxes → cards clicáveis
- [ ] Adicionar progress bar dos checks
- [ ] Adicionar botão de reset
- [ ] Adicionar ícones aos checks
- [ ] Adicionar navegação entre steps
- [ ] Melhorar feedback visual (animações)

### PatientsPage:
- [ ] Adicionar filtros rápidos
- [ ] Melhorar cards de pacientes
- [ ] Adicionar avatar circular
- [ ] Adicionar stats de sessões
- [ ] Adicionar ordenação
- [ ] Adicionar summary no topo
- [ ] Melhorar modal de criação
- [ ] Adicionar swipe actions (editar/deletar)

---

## 🚀 Benefícios das Melhorias

1. **UX Melhorada**: Navegação mais intuitiva com steps visuais
2. **Feedback Visual**: Animações e transições suaves
3. **Consistência**: Mesmo padrão visual da Dashboard
4. **Praticidade**: Menos cliques, mais direto
5. **Modernidade**: Design atual e profissional
6. **Acessibilidade**: Elementos maiores e mais fáceis de tocar
7. **Performance Visual**: Progress bars mostram andamento
8. **Organização**: Informações bem estruturadas

---

## 📦 Imports Adicionais Necessários

```typescript
import {
  sunnyOutline,
  partlySunnyOutline,
  moonOutline,
  arrowForwardOutline,
  arrowBackOutline,
  refreshOutline,
  eyeOutline,
  pulseOutline,
  scaleOutline,
  settingsOutline,
  medkitOutline,
  warningOutline,
  alertController
} from 'ionicons/icons';
```
