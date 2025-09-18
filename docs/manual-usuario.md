# 📱 Manual do Usuário - Sistema de Hemodiálise

## 🏥 Sobre o Sistema

O Sistema de Hemodiálise é uma plataforma digital desenvolvida para modernizar e digitalizar os processos de controle em clínicas de hemodiálise, substituindo as planilhas manuais utilizadas pela Secretaria de Estado da Saúde do Maranhão.

## 👥 Tipos de Usuário

### 🔧 Técnico de Campo (field_user)
- **Interface**: Aplicativo móvel (PWA)
- **Acesso**: `http://localhost:8000/mobile`
- **Funções**: Preenchimento de checklists, controles de limpeza e procedimentos

### 👔 Gerente (manager)
- **Interface**: Painel administrativo
- **Acesso**: `http://localhost:8000/admin`
- **Funções**: Supervisão, relatórios, gestão de equipe

### 👑 Administrador (admin)
- **Interface**: Painel administrativo completo
- **Acesso**: `http://localhost:8000/admin`
- **Funções**: Configuração do sistema, gestão de usuários, todas as funcionalidades

## 🚀 Primeiro Acesso

### 1. Acessando o Sistema
1. Abra o navegador
2. Digite: `http://localhost:8000`
3. Você será redirecionado para a tela de login

### 2. Login no Sistema
1. Digite seu email e senha
2. Clique em "Entrar"
3. O sistema redirecionará automaticamente baseado no seu perfil:
   - **Técnicos**: Interface móvel
   - **Gerentes/Admins**: Painel administrativo

### 3. Usuários Padrão do Sistema
```
Administrador:
Email: admin@hemodialise.com
Senha: admin123

Técnico:
Email: tecnico.joao@hemodialise.com
Senha: tecnico123
```

## 📱 Interface Móvel (Técnicos)

### Tela Principal
Após o login, você verá:
- **Dashboard**: Resumo das atividades do dia
- **Menu de Navegação**: Acesso às funcionalidades
- **Status Online/Offline**: Indicador de conectividade

### Funcionalidades Principais

#### 🔍 Checklist de Segurança
**Finalidade**: Verificações obrigatórias antes de cada sessão de hemodiálise

**Como usar**:
1. Toque em "Checklist de Segurança"
2. Selecione a máquina e paciente
3. Escolha o turno (Manhã/Tarde/Noite)
4. Preencha os 8 itens obrigatórios:
   - Identificação correta do paciente
   - Verificação de acesso vascular
   - Conferência de prescrição médica
   - Estado geral do paciente
   - Funcionamento da máquina
   - Material esterilizado
   - Filtros e linhas adequados
   - Medicações necessárias
5. Adicione observações se necessário
6. Toque em "Salvar Checklist"

#### 🧽 Controle de Limpeza
**Finalidade**: Registro dos procedimentos de limpeza e desinfecção

**Tipos de Limpeza**:
- **Diária**: Limpeza após cada uso
- **Semanal**: Limpeza profunda semanal
- **Mensal**: Manutenção mensal completa

**Como usar**:
1. Acesse "Controle de Limpeza"
2. Selecione a máquina
3. Escolha o tipo de limpeza
4. Marque os procedimentos realizados
5. Registre produtos utilizados
6. Adicione observações
7. Confirme com sua assinatura digital

#### 🧪 Desinfecção Química
**Finalidade**: Controle rigoroso do processo de desinfecção química

**Como usar**:
1. Toque em "Desinfecção Química"
2. Selecione a máquina
3. Registre:
   - Produto utilizado
   - Concentração
   - Temperatura
   - Tempo de contato
   - Lote do produto
4. Confirme a eficácia do processo
5. Salve o registro

### 💡 Dicas para Técnicos

#### Uso Offline
- O app funciona mesmo sem internet
- Dados são salvos localmente
- Sincronização automática quando conectar

#### Navegação Rápida
- Use o menu hambúrguer (☰) para navegação
- Deslize para acessar funcionalidades
- Toque duplo para voltar ao início

#### Segurança
- Sempre confirme os dados antes de salvar
- Use sua identificação pessoal nos registros
- Relate problemas imediatamente

## 🖥️ Painel Administrativo (Gerentes/Admins)

### Dashboard Principal
- **Estatísticas**: Visão geral das atividades
- **Gráficos**: Tendências e indicadores
- **Alertas**: Notificações importantes
- **Ações Rápidas**: Acesso direto às funções

### Gestão de Dados

#### 👤 Pacientes
**Localização**: Menu lateral → Pacientes

**Funcionalidades**:
- Cadastro de novos pacientes
- Edição de informações
- Histórico de sessões
- Prontuário digital
- Controle de alergias

**Campos obrigatórios**:
- Nome completo
- Data de nascimento
- Tipo sanguíneo
- Número do prontuário

#### 🏭 Máquinas
**Localização**: Menu lateral → Máquinas

**Funcionalidades**:
- Cadastro de equipamentos
- Status (ativo/inativo)
- Histórico de manutenção
- Associação com unidade

#### 🏢 Unidades
**Localização**: Menu lateral → Unidades

**Gestão de**:
- Clínicas/centros de hemodiálise
- Endereços e contatos
- Máquinas por unidade
- Equipe associada

### Relatórios e Monitoramento

#### 📊 Relatórios Disponíveis
1. **Checklists por Período**
   - Filtros: data, máquina, técnico
   - Percentual de completude
   - Incidentes reportados

2. **Controle de Limpeza**
   - Conformidade por tipo
   - Produtos mais utilizados
   - Eficiência por técnico

3. **Desinfecções Químicas**
   - Controle de lotes
   - Temperaturas registradas
   - Taxa de eficácia

#### 🔍 Auditoria
- Todas as ações são registradas
- Rastreabilidade completa
- Logs de acesso
- Histórico de alterações

### Gestão de Usuários (Apenas Admins)

#### Criando Usuários
1. Acesse "Usuários" no menu
2. Clique em "Novo Usuário"
3. Preencha:
   - Nome completo
   - Email (será o login)
   - Senha temporária
   - Tipo de usuário
   - Unidade (se aplicável)
4. Salve e informe as credenciais ao usuário

#### Tipos de Usuário
- **admin**: Acesso total ao sistema
- **manager**: Gestão e relatórios
- **field_user**: Apenas interface móvel

## 🔧 Configurações

### Personalizações Disponíveis

#### Para Administradores
- Configuração de unidades
- Personalização de checklists
- Definição de produtos de limpeza
- Configuração de alertas

#### Para Gerentes
- Configuração de turnos
- Personalização de relatórios
- Configuração de notificações

## 📞 Suporte e Dúvidas

### Problemas Comuns

#### "Não consigo fazer login"
1. Verifique email e senha
2. Confirme se há conexão com internet
3. Limpe o cache do navegador
4. Contate o administrador do sistema

#### "O app não está salvando dados"
1. Verifique conexão com internet
2. Confirme se preencheu campos obrigatórios
3. Tente fazer logout e login novamente
4. Contate o suporte técnico

#### "Não vejo minhas funcionalidades"
1. Confirme seu tipo de usuário
2. Verifique se está na interface correta
3. Contate o administrador para verificar permissões

### Contatos de Suporte
- **Email**: suporte@hemodialise.com
- **WhatsApp**: (85) 9xxxx-xxxx
- **Horário**: Segunda a Sexta, 8h às 18h

### Reportando Problemas
Ao reportar um problema, informe:
1. Seu nome e função
2. Data e hora do problema
3. Descrição detalhada
4. Passos para reproduzir
5. Tela/funcionalidade afetada

## 📚 Glossário

### Termos Técnicos
- **PWA**: Progressive Web App - aplicativo web que funciona como app nativo
- **Checklist**: Lista de verificação obrigatória
- **Dashboard**: Painel principal com resumo das atividades
- **Auditoria**: Registro de todas as ações para controle

### Termos Médicos
- **Hemodiálise**: Procedimento de filtragem do sangue
- **Acesso Vascular**: Local de conexão para hemodiálise
- **Prescrição**: Orientações médicas para o procedimento
- **Prontuário**: Registro médico do paciente

## 🔒 Segurança e Privacidade

### Proteção de Dados
- Todas as informações são criptografadas
- Acesso controlado por usuário e senha
- Logs de auditoria completos
- Backup automático dos dados

### Boas Práticas
1. Nunca compartilhe suas credenciais
2. Faça logout ao sair do sistema
3. Mantenha suas informações atualizadas
4. Reporte acessos suspeitos

### Responsabilidades do Usuário
- Manter sigilo das informações
- Usar o sistema apenas para fins profissionais
- Reportar problemas técnicos
- Seguir os procedimentos estabelecidos

---

**Sistema de Hemodiálise v1.0**
*Desenvolvido para a Secretaria de Estado da Saúde do Maranhão*
*Manual atualizado em: Janeiro 2025*