# Gestão de Status de Máquinas

## Visão Geral

Sistema completo para gerenciamento do status e disponibilidade de máquinas de hemodiálise, permitindo aos usuários colocar máquinas em manutenção, liberar máquinas para uso, e ativar/desativar máquinas conforme necessário.

## Funcionalidades

### 1. Colocar Máquina em Manutenção

**Condições:**
- Máquina deve estar **disponível** (`status = 'available'`)
- Máquina deve estar **ativa** (`is_active = true`)
- Máquina **não pode** estar ocupada ou reservada

**Fluxo:**
1. Usuário clica em "Manutenção" no card da máquina
2. Modal solicita descrição do motivo (opcional)
3. Sistema valida condições
4. Status atualizado para `maintenance`
5. Máquina removida da lista de disponíveis
6. Ação registrada em logs

**Exemplos de uso:**
- Troca de filtros
- Ajuste de pressão
- Calibração de equipamento
- Limpeza profunda
- Verificação preventiva

---

### 2. Liberar Máquina da Manutenção

**Condições:**
- Máquina deve estar **em manutenção** (`status = 'maintenance'`)
- Máquina deve estar **ativa** (`is_active = true`)

**Fluxo:**
1. Usuário clica em "Liberar Máquina" no card
2. Modal de confirmação exibido
3. Status atualizado para `available`
4. Máquina volta para lista de disponíveis
5. Ação registrada em logs

---

### 3. Ativar/Desativar Máquina

**Condições para Desativar:**
- Máquina **não pode** estar ocupada (`status != 'occupied'`)
- Máquina **não pode** estar reservada com checklist ativo

**Condições para Ativar:**
- Máquina pode estar em qualquer status, desde que `is_active = false`

**Fluxo de Desativação:**
1. Usuário clica em "Desativar"
2. Modal solicita motivo (opcional)
3. Sistema valida que máquina não está em uso
4. Campo `is_active` alterado para `false`
5. Máquina marcada visualmente como desativada
6. Ação registrada em logs

**Fluxo de Ativação:**
1. Usuário clica em "Ativar" em máquina desativada
2. Modal de confirmação exibido
3. Campo `is_active` alterado para `true`
4. Status automaticamente alterado para `available`
5. Máquina volta para lista de disponíveis
6. Ação registrada em logs

**Exemplos de desativação:**
- Equipamento danificado esperando reparo
- Máquina aguardando peças
- Equipamento temporariamente fora de operação
- Máquina extra que não está sendo utilizada

---

## Implementação Backend

### Endpoints

#### PUT /api/machines/{machine}/status

Altera o status da máquina entre `available` e `maintenance`.

**Request:**
```json
{
  "status": "maintenance",
  "reason": "Troca de filtros programada"
}
```

**Response (Sucesso):**
```json
{
  "success": true,
  "message": "Máquina colocada em manutenção com sucesso.",
  "machine": {
    "id": 1,
    "name": "Máquina 01",
    "status": "maintenance",
    "is_active": true
  }
}
```

**Response (Erro - Máquina Ocupada):**
```json
{
  "success": false,
  "message": "Não é possível alterar o status de uma máquina ocupada. Finalize a sessão primeiro."
}
```

**Validações:**
- `status` obrigatório, valores: `available`, `maintenance`
- `reason` opcional, máximo 500 caracteres
- Máquina deve estar ativa
- Máquina não pode estar ocupada
- Máquina não pode estar reservada

---

#### PUT /api/machines/{machine}/toggle-active

Ativa ou desativa uma máquina.

**Request:**
```json
{
  "reason": "Equipamento aguardando reparo"
}
```

**Response (Sucesso - Desativação):**
```json
{
  "success": true,
  "message": "Máquina desativada com sucesso.",
  "machine": {
    "id": 1,
    "name": "Máquina 01",
    "status": "available",
    "is_active": false
  }
}
```

**Response (Sucesso - Ativação):**
```json
{
  "success": true,
  "message": "Máquina ativada com sucesso.",
  "machine": {
    "id": 1,
    "name": "Máquina 01",
    "status": "available",
    "is_active": true
  }
}
```

**Validações:**
- `reason` opcional, máximo 500 caracteres
- Para desativar: máquina não pode estar ocupada ou reservada
- Para ativar: sem restrições (status volta para `available`)

---

### Controller: MachineController.php

**Método: updateStatus()**

```php
public function updateStatus(Request $request, Machine $machine): JsonResponse
{
    // Validação de input
    $request->validate([
        'status' => 'required|in:available,maintenance',
        'reason' => 'nullable|string|max:500'
    ]);

    // Validações de negócio
    if (!$machine->active) {
        return response()->json([
            'success' => false,
            'message' => 'Máquina desativada não pode ter o status alterado.'
        ], 422);
    }

    if ($machine->status === 'occupied') {
        return response()->json([
            'success' => false,
            'message' => 'Não é possível alterar o status de uma máquina ocupada.'
        ], 422);
    }

    // Atualizar status
    $machine->status = $request->input('status');
    $machine->save();

    // Log da operação
    \Log::info("Status da máquina alterado", [
        'machine_id' => $machine->id,
        'old_status' => $oldStatus,
        'new_status' => $newStatus,
        'reason' => $request->input('reason'),
        'user_id' => $request->user()->id
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Status alterado com sucesso.',
        'machine' => [...]
    ]);
}
```

**Método: toggleActive()**

```php
public function toggleActive(Request $request, Machine $machine): JsonResponse
{
    // Se está ativando
    if (!$machine->active) {
        $machine->active = true;
        $machine->status = 'available';
        $machine->save();
        return response()->json([...]);
    }

    // Se está desativando - validar
    if ($machine->status === 'occupied') {
        return response()->json([
            'success' => false,
            'message' => 'Não é possível desativar uma máquina ocupada.'
        ], 422);
    }

    $machine->active = false;
    $machine->save();
    
    return response()->json([...]);
}
```

---

## Implementação Frontend

### Repositório TypeScript

**Interface: MachineRepository.ts**

```typescript
export interface UpdateStatusParams {
  status: 'available' | 'maintenance';
  reason?: string;
}

export interface ToggleActiveParams {
  reason?: string;
}

export interface MachineRepository {
  updateStatus(id: number, params: UpdateStatusParams): Promise<Machine>;
  toggleActive(id: number, params: ToggleActiveParams): Promise<Machine>;
}
```

**Implementação: MachineRepositoryImpl.ts**

```typescript
async updateStatus(id: number, params: UpdateStatusParams): Promise<Machine> {
  const token = this.getToken();
  const response = await this.apiDataSource.put<{ machine: Machine }>(
    `/machines/${id}/status`,
    params,
    token
  );
  return response.data.machine;
}

async toggleActive(id: number, params: ToggleActiveParams): Promise<Machine> {
  const token = this.getToken();
  const response = await this.apiDataSource.put<{ machine: Machine }>(
    `/machines/${id}/toggle-active`,
    params,
    token
  );
  return response.data.machine;
}
```

---

### UI: MachinesPage.vue

**Botões de Ação**

Os botões aparecem condicionalmente no card de cada máquina:

```vue
<div v-if="!machine.current_checklist" class="machine-actions">
  <!-- Máquina disponível: pode ir para manutenção -->
  <ion-button
    v-if="machine.is_active && machine.status === 'available'"
    fill="outline"
    size="small"
    color="warning"
    @click="openMaintenanceModal(machine)"
  >
    <ion-icon slot="start" :icon="constructOutline"></ion-icon>
    Manutenção
  </ion-button>

  <!-- Máquina em manutenção: pode liberar -->
  <ion-button
    v-if="machine.is_active && machine.status === 'maintenance'"
    fill="solid"
    size="small"
    color="success"
    @click="releaseMachine(machine)"
  >
    <ion-icon slot="start" :icon="checkmarkCircleOutline"></ion-icon>
    Liberar Máquina
  </ion-button>

  <!-- Toggle Ativar/Desativar -->
  <ion-button
    v-if="machine.status !== 'occupied'"
    fill="outline"
    size="small"
    :color="machine.is_active ? 'danger' : 'success'"
    @click="openToggleActiveModal(machine)"
  >
    <ion-icon slot="start" :icon="machine.is_active ? powerOutline : checkmarkCircleOutline"></ion-icon>
    {{ machine.is_active ? 'Desativar' : 'Ativar' }}
  </ion-button>
</div>
```

**Lógica dos Modais**

```typescript
// Modal: Colocar em Manutenção
const openMaintenanceModal = async (machine: any) => {
  const alert = await alertController.create({
    header: 'Colocar em Manutenção',
    subHeader: machine.name,
    message: 'Descreva brevemente o motivo da manutenção:',
    inputs: [{
      name: 'reason',
      type: 'textarea',
      placeholder: 'Ex: Troca de filtros, ajuste de pressão, etc.'
    }],
    buttons: [
      { text: 'Cancelar', role: 'cancel' },
      {
        text: 'Confirmar',
        handler: (data) => putMachineInMaintenance(machine, data.reason || '')
      }
    ]
  });
  await alert.present();
};

// Modal: Liberar Máquina
const releaseMachine = async (machine: any) => {
  const alert = await alertController.create({
    header: 'Liberar Máquina',
    message: 'Tem certeza que deseja liberar esta máquina?',
    buttons: [
      { text: 'Cancelar', role: 'cancel' },
      {
        text: 'Liberar',
        handler: async () => {
          const updatedMachine = await machineRepository.updateStatus(machine.id, {
            status: 'available'
          });
          // Atualizar UI local
          machines.value[index] = { ...machines.value[index], ...updatedMachine };
          // Toast de sucesso
        }
      }
    ]
  });
  await alert.present();
};

// Modal: Ativar/Desativar
const openToggleActiveModal = async (machine: any) => {
  const isActivating = !machine.is_active;
  const alert = await alertController.create({
    header: isActivating ? 'Ativar Máquina' : 'Desativar Máquina',
    message: isActivating
      ? 'A máquina será ativada e ficará disponível para uso.'
      : 'Descreva brevemente o motivo da desativação:',
    inputs: !isActivating ? [{
      name: 'reason',
      type: 'textarea',
      placeholder: 'Ex: Equipamento danificado, aguardando reparo, etc.'
    }] : [],
    buttons: [
      { text: 'Cancelar', role: 'cancel' },
      {
        text: 'Confirmar',
        handler: (data) => toggleMachineActive(machine, data?.reason || '')
      }
    ]
  });
  await alert.present();
};
```

---

## Estados Visuais

### Status das Máquinas

| Status | Cor | Ícone | Descrição |
|--------|-----|-------|-----------|
| `available` | Verde (#10b981) | ✓ | Disponível para uso |
| `occupied` | Laranja (#f59e0b) | ⏱ | Em uso (sessão ativa) |
| `maintenance` | Vermelho (#ef4444) | 🔧 | Em manutenção |
| `reserved` | Azul (#3b82f6) | 📋 | Reservada (checklist iniciado) |
| Desativada | Cinza (#6b7280) | ⚡ | Máquina desativada |

### Indicadores Visuais

**Dot de Status:**
- Pulsante (animação) para máquinas disponíveis
- Estático para outros status
- Box-shadow colorido para destaque

**Badge de Desativada:**
- Aparece no canto superior direito
- Fundo cinza escuro semi-transparente
- Ícone de energia (power)
- Texto "DESATIVADA"

**Botões de Ação:**
- Aparecem apenas quando relevantes
- Cores semânticas (warning/success/danger)
- Icons intuitivos
- Separados por borda superior

---

## Regras de Negócio

### Matriz de Permissões

| Status Atual | Pode ir para Manutenção? | Pode Liberar? | Pode Desativar? | Pode Ativar? |
|--------------|--------------------------|---------------|-----------------|--------------|
| available + active | ✅ Sim | ❌ Não | ✅ Sim | ❌ Não |
| available + inactive | ❌ Não | ❌ Não | ❌ Não | ✅ Sim |
| occupied | ❌ Não | ❌ Não | ❌ Não | ❌ Não |
| reserved | ❌ Não | ❌ Não | ❌ Não | ❌ Não |
| maintenance + active | ❌ Não | ✅ Sim | ✅ Sim | ❌ Não |
| maintenance + inactive | ❌ Não | ❌ Não | ❌ Não | ✅ Sim |

### Transições de Estado

```
available (active) 
  → Colocar em Manutenção → maintenance (active)
  → Desativar → available (inactive)

maintenance (active)
  → Liberar → available (active)
  → Desativar → maintenance (inactive)

occupied
  → (Nenhuma ação permitida até finalizar sessão)

* (inactive)
  → Ativar → available (active)
```

---

## Logs e Auditoria

Todas as ações de mudança de status são registradas em logs:

```php
\Log::info("Status da máquina alterado", [
    'machine_id' => $machine->id,
    'machine_name' => $machine->name,
    'old_status' => $oldStatus,
    'new_status' => $newStatus,
    'reason' => $reason,
    'user_id' => $request->user()->id,
    'user_name' => $request->user()->name
]);
```

**Informações registradas:**
- ID e nome da máquina
- Status anterior e novo
- Motivo informado pelo usuário
- ID e nome do usuário que realizou a ação
- Timestamp automático

---

## Tratamento de Erros

### Erros de Validação (422)

**Frontend mostra toast vermelho com mensagem:**
- "Máquina desativada não pode ter o status alterado. Ative a máquina primeiro."
- "Não é possível alterar o status de uma máquina ocupada. Finalize a sessão primeiro."
- "Máquina reservada para um checklist. Cancele o checklist primeiro."
- "Não é possível desativar uma máquina ocupada. Finalize a sessão primeiro."

### Erros de Servidor (500)

**Frontend mostra toast genérico:**
- "Erro ao alterar status da máquina."
- "Erro ao alterar estado da máquina."

### Tratamento de Rede

```typescript
try {
  const updatedMachine = await machineRepository.updateStatus(...);
  // Sucesso
} catch (error: any) {
  console.error('Error:', error);
  const toast = await toastController.create({
    message: error.response?.data?.message || 'Erro ao realizar operação',
    duration: 4000,
    color: 'danger',
    position: 'top'
  });
  await toast.present();
}
```

---

## Testes Recomendados

### Testes Funcionais

1. **Colocar máquina disponível em manutenção**
   - Verificar modal exibido
   - Inserir motivo
   - Verificar status alterado
   - Verificar toast de sucesso

2. **Tentar colocar máquina ocupada em manutenção**
   - Verificar erro 422
   - Verificar mensagem apropriada

3. **Liberar máquina da manutenção**
   - Verificar modal de confirmação
   - Verificar status volta para available
   - Verificar toast de sucesso

4. **Desativar máquina disponível**
   - Verificar modal com campo de motivo
   - Inserir motivo
   - Verificar is_active alterado
   - Verificar badge visual aparece

5. **Tentar desativar máquina ocupada**
   - Verificar erro 422
   - Verificar mensagem apropriada

6. **Ativar máquina desativada**
   - Verificar modal de confirmação (sem campo motivo)
   - Verificar status volta para available
   - Verificar badge visual removido

### Testes de Integração

1. **Verificar atualização em tempo real**
   - Alterar status de máquina
   - Verificar lista atualizada imediatamente
   - Verificar filtros ainda funcionam

2. **Verificar logs registrados**
   - Alterar status
   - Verificar log em storage/logs/laravel.log
   - Verificar informações completas

3. **Verificar disponibilidade para checklists**
   - Colocar máquina em manutenção
   - Tentar criar checklist
   - Verificar máquina não aparece em disponíveis

---

## Melhorias Futuras

1. **Histórico de Manutenções:**
   - Tabela `machine_maintenances` com histórico completo
   - Exibir últimas manutenções no card
   - Relatório de manutenções por período

2. **Notificações:**
   - Notificar gestores quando máquina vai para manutenção
   - Alerta para máquinas há muito tempo em manutenção
   - Lembrete para manutenção preventiva

3. **Agendamento:**
   - Agendar manutenções futuras
   - Bloquear horários para manutenção preventiva
   - Calendário de manutenções

4. **Dashboard de Manutenções:**
   - Tempo médio em manutenção por máquina
   - Motivos mais comuns
   - Máquinas com mais manutenções
   - Custo estimado de downtime

---

## Conclusão

O sistema de gestão de status de máquinas fornece controle completo sobre a disponibilidade dos equipamentos, com validações robustas, interface intuitiva, e logs completos para auditoria. A implementação garante que máquinas em uso nunca sejam alteradas acidentalmente, mantendo a integridade dos dados e a segurança das operações.
