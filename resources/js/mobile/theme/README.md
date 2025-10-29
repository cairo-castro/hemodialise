# Sistema de Tema Centralizado - Mobile

Este diretório contém o sistema de tema centralizado para a aplicação mobile de hemodiálise, seguindo as melhores práticas do Vue.js e Ionic Framework.

## 📁 Estrutura de Diretórios

```
theme/
├── index.css                    # Coordenador de imports (ponto de entrada)
│
├── core/                        # Estilos base
│   └── base.css                # Reset CSS, mobile optimizations
│
├── tokens/                      # Design Tokens (variáveis CSS)
│   ├── colors.css              # Paleta de cores
│   ├── spacing.css             # Sistema de espaçamento (4px grid)
│   ├── typography.css          # Tipografia (tamanhos, pesos, etc)
│   ├── shadows.css             # Sombras
│   ├── borders.css             # Bordas e raios
│   └── transitions.css         # Transições e animações
│
├── ionic/                       # Customizações Ionic
│   ├── colors.css              # Mapeamento de cores Ionic
│   └── components.css          # Overrides de componentes Ionic
│
├── palettes/                    # Paletas de cor (light/dark)
│   ├── light.css               # Variáveis modo claro
│   └── dark.css                # Variáveis modo escuro
│
├── components/                  # Estilos de componentes
│   ├── cards.css               # Cards (machine-card, stat-card, etc)
│   ├── buttons.css             # Botões (btn-primary, action-btn, etc)
│   ├── forms.css               # Formulários
│   └── badges.css              # Badges e indicadores
│
├── utilities/                   # Classes utilitárias
│   ├── layout.css              # Flexbox/Grid (flex, grid, gap, etc)
│   ├── spacing.css             # Margin/Padding (m-*, p-*, etc)
│   ├── text.css                # Texto (text-*, font-*, etc)
│   └── animations.css          # Animações (skeleton, spin, etc)
│
└── app/                         # Estilos específicos da aplicação
    └── medical.css             # Sistema médico (checklists, units, etc)
```

## 🎨 Design Tokens

### Cores

```css
/* Status de Máquinas */
--color-available: #10b981;      /* Verde - Disponível */
--color-occupied: #f59e0b;       /* Âmbar - Em uso */
--color-maintenance: #ef4444;    /* Vermelho - Manutenção */
--color-inactive: #6b7280;       /* Cinza - Inativa */

/* Cores de Texto */
--text-primary: var(--ion-text-color);
--text-secondary: var(--ion-color-step-600);
--text-tertiary: var(--ion-color-step-500);
```

### Espaçamento (4px grid)

```css
--spacing-xs: 4px;
--spacing-sm: 8px;
--spacing-md: 16px;
--spacing-lg: 24px;
--spacing-xl: 32px;
```

### Tipografia

```css
--text-xs: 0.65rem;
--text-sm: 0.75rem;
--text-base: 0.85rem;
--text-lg: 1rem;
--text-xl: 1.1rem;
--text-2xl: 1.2rem;
--text-3xl: 1.5rem;
```

### Sombras

```css
--shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
--shadow-md: 0 2px 8px rgba(0, 0, 0, 0.08);
--shadow-lg: 0 4px 12px rgba(0, 0, 0, 0.15);
```

## 🧩 Classes de Componentes

### Cards

```css
/* Card Base */
.app-card                        /* Card padrão */
.app-card--gradient-primary      /* Card com gradiente primary */
.app-card--bordered              /* Card com borda */
.app-card--elevated              /* Card com sombra elevada */

/* Machine Cards */
.machine-card                    /* Card de máquina */
.machine-card--available         /* Máquina disponível (verde) */
.machine-card--occupied          /* Máquina em uso (âmbar) */
.machine-card--maintenance       /* Máquina em manutenção (vermelho) */
.machine-card--inactive          /* Máquina inativa (cinza) */

/* Stat Cards */
.stat-card                       /* Card de estatística */
.stat-card--available            /* Estatística disponível */
.stat-card--occupied             /* Estatística em uso */
.stat-card--total                /* Estatística total */
```

### Botões

```css
.btn-primary                     /* Botão principal com gradiente */
.action-btn                      /* Botão de ação */
.action-btn-icon--primary        /* Ícone primary */
.action-btn-icon--success        /* Ícone success */
.action-btn-icon--warning        /* Ícone warning */
.action-btn-icon--danger         /* Ícone danger */
```

### Badges

```css
.status-badge                    /* Badge de status */
.status-badge--available         /* Status disponível */
.status-badge--occupied          /* Status em uso */
.status-badge--maintenance       /* Status manutenção */
.status-badge--inactive          /* Status inativo */

.role-badge                      /* Badge de role */
.count-badge                     /* Badge de contagem */
```

## 🛠️ Classes Utilitárias

### Layout

```css
/* Flexbox */
.flex                            /* display: flex */
.flex-col                        /* flex-direction: column */
.flex-between                    /* justify-content: space-between */
.flex-center                     /* align-items e justify: center */
.gap-sm, .gap-md, .gap-lg        /* Espaçamento entre elementos */

/* Grid */
.grid                            /* display: grid */
.grid-cols-2, .grid-cols-3       /* 2 ou 3 colunas */

/* Containers */
.container                       /* Container com max-width */
.section                         /* Seção com padding */
```

### Espaçamento

```css
/* Margin */
.m-sm, .m-md, .m-lg              /* Margin em todos os lados */
.mt-md, .mb-lg                   /* Margin top/bottom */
.mx-md, .my-lg                   /* Margin horizontal/vertical */

/* Padding */
.p-sm, .p-md, .p-lg              /* Padding em todos os lados */
.pt-md, .pb-lg                   /* Padding top/bottom */
.px-md, .py-lg                   /* Padding horizontal/vertical */
```

### Texto

```css
/* Tamanhos */
.text-sm, .text-md, .text-lg     /* Tamanhos de texto */

/* Peso */
.font-normal, .font-medium       /* Pesos de fonte */
.font-semibold, .font-bold

/* Cores */
.text-primary                    /* Cor primária de texto */
.text-secondary                  /* Cor secundária */
.text-success, .text-warning     /* Cores semânticas */

/* Utilitários */
.truncate                        /* Text overflow ellipsis */
.uppercase, .lowercase           /* Text transform */
```

## 📱 Uso em Componentes

### Antes (com CSS duplicado)

```vue
<template>
  <div class="welcome-section">
    <h1>Olá!</h1>
  </div>
  <div class="stat-card">
    <div class="stat-number">10</div>
    <div class="stat-label">Disponíveis</div>
  </div>
</template>

<style scoped>
.welcome-section {
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 16px;
  /* ... mais 40 linhas de CSS duplicado */
}
</style>
```

### Depois (usando classes utilitárias)

```vue
<template>
  <div class="app-card app-card--gradient-primary p-lg">
    <h1>Olá!</h1>
  </div>
  <div class="stat-card stat-card--available">
    <div class="stat-number">10</div>
    <div class="stat-label">Disponíveis</div>
  </div>
</template>

<style scoped>
/* Apenas estilos específicos desta página */
</style>
```

## 🌓 Dark Mode

O dark mode é suportado automaticamente através das variáveis CSS do Ionic. Todas as classes usam variáveis CSS que se adaptam ao tema:

```css
/* Sempre use variáveis CSS em vez de cores hard-coded */

/* ❌ Errado */
color: #1f2937;
background: white;

/* ✅ Correto */
color: var(--ion-text-color);
background: var(--ion-card-background);
```

O dark mode é ativado via composable `useDarkMode()` que adiciona a classe `.ion-palette-dark` ao elemento `<html>`.

## 📖 Convenções

1. **Use variáveis CSS** para todas as propriedades tematizáveis (cores, espaçamento, etc)
2. **Use classes utilitárias** para layout e espaçamento comum
3. **Use classes de componentes** para padrões reutilizáveis (cards, buttons, etc)
4. **Mantenha CSS scoped mínimo** - apenas para layout específico da página
5. **Evite valores hard-coded** - sempre use tokens
6. **Prefira composição** - combine classes pequenas em vez de criar classes grandes

## 🎯 Benefícios

- **Manutenção**: Alterar estilos em um só lugar
- **Consistência**: Padrões visuais uniformes
- **Performance**: Menor tamanho de CSS (~60-70% de redução)
- **Dark Mode**: Suporte automático e consistente
- **DX**: Desenvolvimento mais rápido com classes reutilizáveis
- **Onboarding**: Mais fácil para novos desenvolvedores

## 🔄 Migração

Para migrar um componente para o novo sistema:

1. **Identifique padrões** comuns (cards, buttons, etc)
2. **Substitua por classes** utilitárias do tema
3. **Remova CSS duplicado** do `<style scoped>`
4. **Mantenha apenas** layout específico da página
5. **Teste em light/dark mode**

## 📚 Referências

- [Ionic Theming Documentation](https://ionicframework.com/docs/theming)
- [Ionic Dark Mode Guide](https://ionicframework.com/docs/theming/dark-mode)
- [CSS Custom Properties](https://developer.mozilla.org/en-US/docs/Web/CSS/--*)
