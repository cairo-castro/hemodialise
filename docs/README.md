# 📚 Documentação - Sistema de Hemodiálise

Bem-vindo à documentação completa do Sistema de Hemodiálise! Esta pasta contém todos os manuais e guias necessários para instalação, uso e manutenção do sistema.

## 📋 Documentos Disponíveis

### 📖 [Manual do Usuário](manual-usuario.md)
Guia completo para todos os tipos de usuários do sistema:
- **Técnicos de Campo**: Como usar a interface móvel (PWA)
- **Gerentes**: Navegação no painel administrativo
- **Administradores**: Configuração e gestão do sistema

**Conteúdo**:
- Primeiro acesso e login
- Funcionalidades principais
- Checklists de segurança
- Controle de limpeza
- Desinfecção química
- Relatórios e monitoramento
- Solução de problemas
- Contatos de suporte

### 🔧 [Manual Técnico](manual-tecnico.md)
Documentação técnica detalhada para desenvolvedores e administradores de sistema:
- Arquitetura do sistema
- Sistema de autenticação dual (JWT + Session)
- Modelos de dados e relacionamentos
- Endpoints da API
- Configurações avançadas

**Conteúdo**:
- Stack tecnológico
- Fluxo de autenticação
- Estrutura de diretórios
- Middlewares customizados
- Interface móvel (PWA)
- Base de dados
- Deploy e produção
- Testes e debugging
- Segurança

### 🚀 [Guia de Instalação](instalacao.md)
Instruções passo a passo para instalação e configuração:
- Pré-requisitos do sistema
- Instalação automatizada
- Instalação manual
- Configuração de servidor web
- Solução de problemas

**Conteúdo**:
- Requisitos de sistema
- Script de instalação automatizada
- Configuração manual detalhada
- Configuração Apache/Nginx
- Usuários padrão
- Verificação da instalação
- Problemas comuns e soluções
- Scripts utilitários

## 🏥 Sobre o Sistema

O Sistema de Hemodiálise é uma plataforma digital desenvolvida para modernizar os processos de controle em clínicas de hemodiálise, substituindo as planilhas manuais utilizadas pela Secretaria de Estado da Saúde do Maranhão.

### Características Principais
- **Mobile-First**: Interface otimizada para smartphones e tablets
- **PWA**: Funciona offline como aplicativo nativo
- **Dual Interface**: Painel admin para gestão + app móvel para campo
- **Segurança**: Autenticação JWT + auditoria completa
- **Conformidade**: Baseado nas normas da Secretaria de Saúde do MA

## 🚀 Instalação Rápida

```bash
# Clone o repositório
git clone https://github.com/cairo-castro/hemodialise.git
cd hemodialise

# Execute o script de instalação automatizada
chmod +x setup.sh
./setup.sh

# Acesse o sistema
http://localhost:8000
```

**Usuários padrão**:
- **Admin**: admin@hemodialise.com / admin123
- **Técnico**: tecnico.joao@hemodialise.com / tecnico123

## 🎯 Público-Alvo da Documentação

### 👥 Usuários Finais
- **Técnicos de Enfermagem**: Interface móvel para procedimentos
- **Gerentes de Clínica**: Painel administrativo para supervisão
- **Administradores**: Configuração e gestão completa

### 🔧 Técnicos
- **Desenvolvedores**: Arquitetura e APIs
- **Administradores de Sistema**: Instalação e manutenção
- **DevOps**: Deploy e monitoramento

## 🗂️ Estrutura de Módulos

### 🏢 Gestão de Unidades
- Cadastro de clínicas/centros
- Gestão de equipes
- Configurações por unidade

### 🏭 Gestão de Máquinas
- Cadastro de equipamentos
- Controle de manutenção
- Status operacional

### 👤 Gestão de Pacientes
- Prontuário digital
- Histórico de sessões
- Informações médicas

### 🔍 Checklist de Segurança
- 8 verificações obrigatórias
- Controle por turnos
- Cálculo de completude

### 🧽 Controle de Limpeza
- Procedimentos diários/semanais/mensais
- Registro de produtos
- Assinatura digital

### 🧪 Desinfecção Química
- Controle de processo
- Registro de concentrações
- Rastreabilidade de lotes

## 📊 Tecnologias Utilizadas

### Backend
- **Laravel 12**: Framework PHP
- **Filament 3**: Painel administrativo
- **JWT Auth**: Autenticação API
- **SQLite/MariaDB**: Base de dados

### Frontend
- **Alpine.js**: Reatividade
- **TailwindCSS 4**: Estilização
- **Vite**: Build e assets
- **PWA**: Service Worker

### DevOps
- **Composer**: Dependências PHP
- **NPM**: Dependências JavaScript
- **Git**: Controle de versão

## 🆘 Suporte

### Canais de Suporte
- **Email**: suporte@hemodialise.com
- **WhatsApp**: (85) 9xxxx-xxxx
- **GitHub Issues**: [Link do repositório]

### Horário de Atendimento
- **Segunda a Sexta**: 8h às 18h
- **Resposta**: Até 24h em dias úteis

### Documentação Online
- **Portal**: https://docs.hemodialise.com.br
- **API Docs**: https://api.hemodialise.com.br/docs
- **Changelog**: https://github.com/cairo-castro/hemodialise/releases

## 🔄 Atualizações da Documentação

### Versão Atual: 1.0
- **Data**: Janeiro 2025
- **Autor**: Equipe de Desenvolvimento
- **Status**: Estável

### Histórico de Versões
- **v1.0**: Documentação inicial completa
- **v0.9**: Versão beta para testes
- **v0.8**: Documentação preliminar

### Como Contribuir
1. Fork do repositório
2. Crie branch para documentação: `git checkout -b docs/nova-secao`
3. Faça suas alterações nos arquivos markdown
4. Envie pull request com descrição detalhada

## 📝 Convenções

### Estrutura dos Documentos
- **Título**: H1 com emoji identificador
- **Seções**: H2 com emojis descritivos
- **Subseções**: H3 sem emojis
- **Código**: Blocos com sintaxe highlight
- **Avisos**: Caixas de destaque quando necessário

### Formato de Links
- **Links internos**: `[Texto](arquivo.md)`
- **Links externos**: `[Texto](https://url.com)`
- **Âncoras**: `[Texto](#secao)`

### Exemplos de Código
```bash
# Comandos de terminal
comando --parametro valor
```

```php
// Código PHP
<?php
echo "Hello World";
```

```javascript
// Código JavaScript
console.log('Hello World');
```

---

**Sistema de Hemodiálise - Documentação v1.0**
*Desenvolvido para a Secretaria de Estado da Saúde do Maranhão*
*Atualizado em: Janeiro 2025*