# CRUD de Máquinas no Mobile - Implementação Completa

## Resumo da Implementação

Foi implementado um CRUD completo de máquinas na interface mobile, permitindo que usuários criem, visualizem, editem e excluam máquinas diretamente de seus dispositivos móveis. Todas as operações respeitam o escopo de unidade do usuário.

## Funcionalidades Implementadas

### 1. **Criar Nova Máquina** ✅
- Botão FAB (Floating Action Button) no canto inferior direito
- Modal com formulário completo
- Campos:
  - Nome da Máquina (obrigatório)
  - Identificador/Código (obrigatório, único)
  - Descrição (opcional)
  - Unidade (obrigatório, auto-selecionada se usuário tem apenas uma unidade)
- Validação em tempo real
- Criação automática com status "disponível" e ativa

### 2. **Editar Máquina** ✅
- Botão "Editar" em cada card de máquina
- Modal com dados pré-preenchidos
- Permite alterar:
  - Nome
  - Identificador
  - Descrição
- **Não permite** alterar a unidade (vinculação permanente)

### 3. **Excluir Máquina** ✅
- Botão "Excluir" dentro do modal de edição
- Confirmação antes de excluir
- **Soft delete**: A máquina não é removida do banco, apenas desativada
- Validações:
  - Não permite excluir máquina ocupada
  - Não permite excluir máquina reservada com checklist ativo

### 4. **Validações de Segurança** ✅

#### Backend (API)
- Verificação de permissão de unidade em todas as operações
- Validação de dados com mensagens em português
- Identificador único (não pode duplicar)
- Logs de auditoria para todas as operações

#### Frontend (Mobile)
- Validação de campos obrigatórios
- Desabilitação de botões durante operações
- Feedback visual (loading, toasts)
- Mensagens de erro amigáveis

## Arquivos Modificados

### 1. **Backend**

#### `app/Http/Controllers/Api/MachineController.php`
Novos métodos adicionados:

```php
store(Request $request)          // Criar máquina
show(Machine $machine)           // Ver detalhes
update(Request $request, ...)    // Atualizar máquina
destroy(Request $request, ...)   // Excluir (soft delete)
```

**Validações implementadas:**
- Nome: obrigatório, string, máx 255 caracteres
- Identificador: obrigatório, string, máx 50 caracteres, único
- Descrição: opcional, string, máx 500 caracteres
- Unit ID: obrigatório, deve existir na tabela units
- Verificação de permissão de acesso à unidade

#### `routes/api.php`
Novas rotas adicionadas:

```php
POST   /api/machines                    // Criar
GET    /api/machines/{machine}          // Ver detalhes
PUT    /api/machines/{machine}          // Atualizar
DELETE /api/machines/{machine}          // Excluir
PUT    /api/machines/{machine}/toggle-active  // Ativar/Desativar
```

### 2. **Frontend Mobile**

#### `resources/js/mobile/views/MachinesPage.vue`

**Novos componentes adicionados:**
- `ion-fab` e `ion-fab-button`: Botão flutuante para criar
- `ion-modal`: Modal de criar/editar
- `ion-input`, `ion-textarea`, `ion-select`: Campos do formulário

**Novas variáveis de estado:**
```typescript
showMachineModal: boolean    // Controle de exibição do modal
isEditMode: boolean          // Modo de edição vs criação
isSaving: boolean            // Loading durante salvamento
availableUnits: array        // Unidades disponíveis para o usuário
machineForm: object          // Dados do formulário
```

**Novos métodos:**
- `loadAvailableUnits()`: Carrega unidades do usuário
- `openCreateMachineModal()`: Abre modal em modo criação
- `openEditMachineModal()`: Abre modal em modo edição
- `closeMachineModal()`: Fecha e limpa o modal
- `saveMachine()`: Salva (cria ou atualiza)
- `confirmDeleteMachine()`: Exibe alerta de confirmação
- `deleteMachine()`: Executa a exclusão

**Novos estilos CSS:**
- `.machine-modal-content`: Estilo do conteúdo do modal
- `.machine-form`: Container do formulário
- `.form-section`, `.form-group`: Organização dos campos
- `.custom-input`, `.custom-textarea`, `.custom-select`: Campos personalizados
- `.form-actions`: Botões de ação
- Estilos para FAB button

## Fluxo de Uso

### Criar Máquina
1. Usuário clica no botão `+` (FAB)
2. Modal abre com formulário vazio
3. Usuário preenche os dados
4. Unidade é selecionada automaticamente (se tiver apenas uma)
5. Clica em "Criar Máquina"
6. Sistema valida e salva
7. Toast de sucesso
8. Lista é recarregada
9. Modal fecha automaticamente

### Editar Máquina
1. Usuário clica em "Editar" no card da máquina
2. Modal abre com dados pré-preenchidos
3. Usuário modifica os dados desejados
4. Clica em "Atualizar Máquina"
5. Sistema valida e atualiza
6. Toast de sucesso
7. Lista é recarregada
8. Modal fecha automaticamente

### Excluir Máquina
1. Usuário clica em "Editar" no card
2. Dentro do modal, clica em "Excluir Máquina"
3. Alert de confirmação é exibido
4. Usuário confirma
5. Sistema verifica se pode excluir (não ocupada, não reservada)
6. Máquina é desativada (soft delete)
7. Toast de sucesso
8. Lista é recarregada
9. Modal fecha automaticamente

## Validações e Restrições

### Criar
- ✅ Usuário deve ter acesso à unidade selecionada
- ✅ Identificador deve ser único no sistema
- ✅ Nome e identificador são obrigatórios
- ✅ Máquina criada sempre começa como "disponível" e "ativa"

### Editar
- ✅ Usuário deve ter acesso à unidade da máquina
- ✅ Identificador deve ser único (exceto para a própria máquina)
- ✅ Não pode alterar a unidade da máquina
- ✅ Validação em tempo real dos campos

### Excluir
- ❌ Não pode excluir máquina ocupada (com sessão ativa)
- ❌ Não pode excluir máquina reservada (com checklist em andamento)
- ✅ Exclusão é soft delete (apenas desativa)
- ✅ Logs de auditoria são registrados

## Segurança e Auditoria

### Logs Registrados
Todas as operações geram logs com:
- ID e nome da máquina
- ID e nome do usuário
- Timestamp da operação
- Dados alterados (no caso de UPDATE)
- Motivo (quando aplicável)

**Exemplo de log:**
```php
Log::info("Máquina criada", [
    'machine_id' => 5,
    'machine_name' => 'Máquina 05',
    'unit_id' => 2,
    'user_id' => 15,
    'user_name' => 'João Silva'
]);
```

### Escopo de Unidade
Todas as operações respeitam o escopo de unidade:
- Listagem: Mostra apenas máquinas da unidade ativa
- Criação: Só pode criar em unidades autorizadas
- Edição: Só pode editar máquinas de unidades autorizadas
- Exclusão: Só pode excluir máquinas de unidades autorizadas

## Relacionamentos Garantidos

### Máquinas ⟷ Unidade
- Cada máquina pertence a **uma unidade** (`unit_id`)
- Relacionamento não pode ser alterado após criação
- Validação de integridade referencial

### Máquinas ⟷ Checklists
- Máquinas têm relacionamento com:
  - `SafetyChecklist` (Checklists de Segurança)
  - `CleaningControl` (Controles de Limpeza)
  - `ChemicalDisinfection` (Desinfecções Químicas)
- Ao tentar excluir, verifica se há checklists ativos

### Máquinas ⟷ Pacientes
- Checklists vinculam máquinas a pacientes
- Validação indireta: não pode excluir máquina com checklist ativo

## Testes Recomendados

### Cenários de Teste

1. **Criar máquina com sucesso**
   - Preencher todos os campos obrigatórios
   - Verificar se aparece na lista
   - Verificar se está "disponível" e "ativa"

2. **Tentar criar com identificador duplicado**
   - Deve exibir erro de validação
   - Formulário não deve fechar

3. **Editar nome e descrição**
   - Alterações devem persistir
   - Lista deve refletir mudanças

4. **Tentar excluir máquina ocupada**
   - Deve exibir mensagem de erro
   - Máquina não deve ser desativada

5. **Excluir máquina disponível**
   - Deve exibir confirmação
   - Após confirmar, máquina deve sumir da lista (filtrada por ativas)
   - Máquina deve aparecer no filtro "Desativadas"

6. **Usuário de uma unidade tentar criar em outra**
   - Backend deve retornar erro 403
   - Toast de erro deve ser exibido

## Melhorias Futuras (Opcional)

1. **Upload de Foto da Máquina**
   - Campo para adicionar foto
   - Exibir miniatura no card

2. **Histórico de Manutenções**
   - Lista de todas as vezes que foi colocada em manutenção
   - Motivos e datas

3. **Transferência entre Unidades**
   - Permitir transferir máquina para outra unidade
   - Requer aprovação de gestor

4. **QR Code**
   - Gerar QR Code para cada máquina
   - Escanear para abrir detalhes rapidamente

5. **Estatísticas por Máquina**
   - Total de sessões realizadas
   - Tempo médio de uso
   - Histórico de problemas

## Comandos Úteis

```bash
# Recompilar frontend mobile
npm run build:mobile

# Limpar cache Laravel
php artisan config:clear
php artisan cache:clear

# Verificar rotas da API
php artisan route:list --path=api/machines

# Rodar testes (se implementados)
php artisan test --filter=MachineController
```

## Data da Implementação

**21 de Outubro de 2025**

## Desenvolvedor

Sistema de Gestão de Hemodiálise - Maranhão
