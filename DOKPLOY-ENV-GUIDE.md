# 🔐 Guia de Boas Práticas - Variáveis de Ambiente no Dokploy

## 📋 Índice
1. [Visão Geral](#visão-geral)
2. [Por Que Usar Environment Variables no Dokploy](#por-que-usar)
3. [Níveis de Configuração](#níveis-de-configuração)
4. [Configuração Passo a Passo](#configuração-passo-a-passo)
5. [Variáveis Necessárias para o Projeto](#variáveis-necessárias)
6. [Atualização do Dockerfile](#atualização-do-dockerfile)
7. [Segurança e Boas Práticas](#segurança-e-boas-práticas)

---

## 🎯 Visão Geral

O Dokploy permite gerenciar variáveis de ambiente em **3 níveis hierárquicos**, oferecendo flexibilidade e segurança para suas aplicações. Este guia demonstra como configurar corretamente o Sistema Hemodiálise usando as melhores práticas.

## ⚡ Por Que Usar Environment Variables no Dokploy

### ❌ **Problemas com Hardcoded Credentials:**
- Credenciais expostas no repositório Git
- Impossível ter diferentes configs por ambiente (dev/staging/prod)
- Risco de segurança ao compartilhar código
- Difícil rotacionar senhas sem rebuild

### ✅ **Vantagens do Dokploy Environment:**
- **Segurança:** Credenciais armazenadas de forma criptografada
- **Flexibilidade:** Alterar configs sem rebuild da aplicação
- **Reutilização:** Compartilhar variáveis entre múltiplos serviços
- **Auditoria:** Rastreamento de mudanças nas configurações
- **Isolamento:** Cada ambiente (staging/production) tem suas próprias variáveis

---

## 📊 Níveis de Configuração

### 1️⃣ **Project-Level (Compartilhado)**
Variáveis acessíveis por **todos os serviços** dentro do projeto.

**Exemplo:**
```env
DATABASE_URL=postgresql://postgres:postgres@database:5432/postgres
```

**Uso nos serviços:**
```env
DATABASE_URL=${{project.DATABASE_URL}}
```

**Quando usar:**
- Credenciais de banco de dados compartilhado
- URLs de APIs externas
- Chaves de serviços comuns (S3, Redis, etc.)

---

### 2️⃣ **Environment-Level**
Variáveis específicas para cada **ambiente** (staging, production).

**Exemplo:**
```env
APP_ENV=${{environment.APP_ENV}}
```

**Quando usar:**
- Configurações que mudam entre staging e production
- URLs de domínio diferentes
- Níveis de log diferentes

---

### 3️⃣ **Service-Level**
Variáveis específicas para um **serviço individual**.

**Quando usar:**
- Configurações únicas daquele serviço
- Overrides de variáveis compartilhadas

---

## 🛠️ Configuração Passo a Passo

### **Passo 1: Acessar o Dokploy**

1. Acesse: `http://212.85.1.175:3000`
2. Faça login com suas credenciais
3. Navegue até o projeto **"qualidade"**

---

### **Passo 2: Configurar Variáveis no Nível do Projeto**

#### **2.1 Acessar Project Settings**
1. No menu lateral, clique em **"Projects"**
2. Selecione o projeto **"qualidade"**
3. Clique na aba **"Environment Variables"** ou **"Variables"**

#### **2.2 Adicionar Variáveis Compartilhadas**

Adicione as seguintes variáveis no nível do projeto (serão compartilhadas entre `qualidadeHD` e outros serviços):

```env
# Database Credentials (Compartilhado)
DB_HOST=qualidade-productionqualidade-l2xbgb
DB_PORT=3306
DB_USERNAME=Usr_QltGest@2025
DB_PASSWORD=Qlt!H0sp#2025
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

**💡 Dica:** Use nomes descritivos como `SHARED_DB_HOST` para deixar claro que são compartilhadas.

---

### **Passo 3: Configurar Variáveis do Serviço**

#### **3.1 Acessar Service Settings**
1. No projeto, clique no serviço **"qualidadehd"** (qualidade-qualidadehd-bue1bg)
2. Clique na aba **"Environment"** ou **"Environment Variables"**

#### **3.2 Adicionar Variáveis Específicas**

```env
# ===================================
# APPLICATION
# ===================================
APP_NAME=Sistema Hemodiálise - Qualidade
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qualidadehd.direcaoclinica.com.br
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

# ===================================
# SECURITY KEYS (Gerar novos!)
# ===================================
APP_KEY=base64:H2UOVPoDW9emzGrn1Rx2EV15YOsosTwPtvPUPmsVph0=
JWT_SECRET=NHJVkr3HSHQM1D7BxhToHm5rB4EffTsCvHpyhjljiygtoIqFZAySrokYTpnHlNxX

# ===================================
# DATABASE (Referenciando Project Variables)
# ===================================
DB_CONNECTION=mariadb
DB_HOST=${{project.DB_HOST}}
DB_PORT=${{project.DB_PORT}}
DB_DATABASE=hemodialise_gqa
DB_USERNAME=${{project.DB_USERNAME}}
DB_PASSWORD=${{project.DB_PASSWORD}}
DB_CHARSET=${{project.DB_CHARSET}}
DB_COLLATION=${{project.DB_COLLATION}}

# ===================================
# CACHE & SESSION
# ===================================
CACHE_STORE=database
CACHE_PREFIX=hemodialise_cache
SESSION_DRIVER=database
SESSION_LIFETIME=480
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

# ===================================
# QUEUE & BROADCAST
# ===================================
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

# ===================================
# LOGGING
# ===================================
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning

# ===================================
# MAIL (Configurar depois)
# ===================================
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@direcaoclinica.com.br
MAIL_FROM_NAME=${APP_NAME}

# ===================================
# INITIALIZATION FLAGS
# ===================================
RUN_MIGRATIONS=true
RUN_SEEDERS=false

# ===================================
# SECURITY
# ===================================
TRUSTED_PROXIES=*
SANCTUM_STATEFUL_DOMAINS=qualidadehd.direcaoclinica.com.br
```

---

### **Passo 4: Variáveis Multiline (Se Necessário)**

Para chaves privadas, certificados, ou conteúdo multiline:

```env
PRIVATE_KEY='"-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...
-----END PRIVATE KEY-----"'
```

**Sintaxe:** `'"conteúdo_aqui"'`

---

## 📝 Variáveis Necessárias para o Projeto

### **✅ Checklist Completo**

#### **Essenciais (OBRIGATÓRIAS):**
- ✅ `APP_ENV=production`
- ✅ `APP_KEY` (gerado via `php artisan key:generate`)
- ✅ `DB_CONNECTION=mariadb`
- ✅ `DB_HOST=${{project.DB_HOST}}`
- ✅ `DB_PORT=3306`
- ✅ `DB_DATABASE=hemodialise_gqa`
- ✅ `DB_USERNAME=${{project.DB_USERNAME}}`
- ✅ `DB_PASSWORD=${{project.DB_PASSWORD}}`

#### **Recomendadas:**
- ✅ `JWT_SECRET` (para autenticação)
- ✅ `SESSION_DRIVER=database`
- ✅ `CACHE_STORE=database`
- ✅ `RUN_MIGRATIONS=true`

#### **Opcionais:**
- ⚪ Configuração de email (MAIL_*)
- ⚪ AWS S3 (se usar storage em nuvem)
- ⚪ Redis (se implementar cache distribuído)

---

## 🐳 Atualização do Dockerfile

### **Remover Hardcoded Credentials**

#### **❌ ANTES (Inseguro):**
```dockerfile
# Copiar arquivo com credenciais hardcoded
COPY --chown=laravel:laravel .env.docker .env
```

#### **✅ DEPOIS (Seguro):**
```dockerfile
# Dockerfile.production

# Não copiar .env - será injetado pelo Dokploy
# As variáveis de ambiente serão automaticamente disponíveis no container

# Criar um .env mínimo apenas para build time se necessário
RUN echo "APP_ENV=production" > .env.build
```

### **Atualização do Entrypoint**

Modifique o `docker/entrypoint.production.sh` para verificar variáveis:

```bash
#!/bin/sh
set -e

echo "=============================================="
echo "Starting Hemodialise Production Application"
echo "=============================================="

# Verificar variáveis críticas
if [ -z "$DB_HOST" ]; then
    echo "❌ ERROR: DB_HOST not set!"
    echo "Configure environment variables in Dokploy"
    exit 1
fi

if [ -z "$APP_KEY" ]; then
    echo "❌ ERROR: APP_KEY not set!"
    echo "Generate one using: php artisan key:generate --show"
    exit 1
fi

if [ "$DB_CONNECTION" != "mariadb" ] && [ "$DB_CONNECTION" != "mysql" ]; then
    echo "⚠️  WARNING: DB_CONNECTION=$DB_CONNECTION"
    echo "Expected: mariadb or mysql"
fi

echo "✅ Environment variables validated"
echo "✅ DB_HOST: $DB_HOST"
echo "✅ DB_CONNECTION: $DB_CONNECTION"

# Continue with normal startup...
```

---

## 🔒 Segurança e Boas Práticas

### **1. Rotação de Credenciais**

```bash
# Gerar novas keys
php artisan key:generate --show
openssl rand -base64 32  # Para JWT_SECRET
```

**Processo:**
1. Gerar novas keys
2. Atualizar no Dokploy (Environment Variables)
3. Restart do serviço (automático no Dokploy)
4. ❌ **NÃO fazer rebuild** - apenas restart

---

### **2. Separação por Ambiente**

#### **Project Variables (Shared):**
```env
# Compartilhado entre staging e production
DB_HOST=qualidade-productionqualidade-l2xbgb
DB_PORT=3306
DB_USERNAME=Usr_QltGest@2025
DB_PASSWORD=Qlt!H0sp#2025
```

#### **Environment Variables (Staging):**
```env
APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging-qualidadehd.direcaoclinica.com.br
LOG_LEVEL=debug
```

#### **Environment Variables (Production):**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qualidadehd.direcaoclinica.com.br
LOG_LEVEL=warning
```

---

### **3. Documentação**

Crie um arquivo `ENVIRONMENT_VARS.md` no repositório (SEM valores):

```markdown
# Environment Variables Documentation

## Required Variables

| Variable | Type | Example | Description |
|----------|------|---------|-------------|
| APP_KEY | string | base64:xxx... | Laravel encryption key |
| DB_HOST | string | db.example.com | Database host |
| DB_PASSWORD | secret | - | Database password (set in Dokploy) |
```

---

### **4. Auditoria**

✅ **Faça regularmente:**
- Revisar quem tem acesso ao Dokploy
- Verificar logs de mudanças em variáveis
- Testar rotação de credenciais
- Documentar variáveis adicionadas

---

## 🚀 Processo de Deploy Seguro

### **Workflow Completo:**

```
1. Desenvolvimento Local
   └─> Usar .env local com valores de teste

2. Commit & Push
   └─> .env NÃO vai para o git (.gitignore)
   └─> .env.docker removido (legacy)

3. Dokploy Build
   └─> Faz pull do código
   └─> Build da imagem Docker
   └─> Injeta environment variables automaticamente

4. Container Runtime
   └─> Variáveis disponíveis via process.env / $_ENV
   └─> Laravel lê via env()

5. Atualizar Credenciais
   └─> Alterar no Dokploy UI
   └─> Restart automático do container
   └─> SEM rebuild necessário
```

---

## 🔄 Migração do Sistema Atual

### **Passo 1: Backup**
```bash
# Backup do .env.docker atual
cp .env.docker .env.docker.backup
```

### **Passo 2: Adicionar ao .dockerignore**
```
# .dockerignore
.env
.env.*
!.env.example
# Remover: !.env.docker (não precisamos mais)
```

### **Passo 3: Atualizar Dockerfile**
```dockerfile
# Remover linha:
# COPY --chown=laravel:laravel .env.docker .env

# Adicionar comentário:
# Environment variables are injected by Dokploy at runtime
```

### **Passo 4: Configurar no Dokploy**
- Seguir passos da seção "Configuração Passo a Passo"

### **Passo 5: Testar**
```bash
# Após deploy, verificar logs
docker logs <container_id> | grep "DB_HOST"

# Deve mostrar: ✅ DB_HOST: qualidade-productionqualidade-l2xbgb
```

### **Passo 6: Remover arquivos antigos**
```bash
# Remover .env.docker do repositório
git rm .env.docker
git commit -m "Remove hardcoded credentials - use Dokploy environment variables"
git push
```

---

## 📚 Referências

- [Dokploy Environment Variables](https://docs.dokploy.com/docs/core/variables)
- [Laravel Environment Configuration](https://laravel.com/docs/configuration#environment-configuration)
- [12 Factor App - Config](https://12factor.net/config)

---

## ✅ Checklist Final

Antes de fazer o deploy:

- [ ] Todas as variáveis configuradas no Dokploy
- [ ] `.env.docker` removido do projeto
- [ ] `.dockerignore` atualizado
- [ ] `Dockerfile.production` não copia .env
- [ ] `entrypoint.sh` valida variáveis críticas
- [ ] Documentação atualizada
- [ ] Backup das credenciais atuais
- [ ] Teste em staging primeiro
- [ ] Plano de rollback pronto

---

## 🆘 Troubleshooting

### **Problema: "DB_HOST not set"**
**Solução:** Verificar se variáveis foram configuradas no service (não só no project)

### **Problema: "Still using SQLite"**
**Solução:** Verificar se `DB_CONNECTION=mariadb` está configurado no Dokploy

### **Problema: "APP_KEY not found"**
**Solução:**
```bash
php artisan key:generate --show
# Copiar output para Dokploy: APP_KEY=base64:xxx...
```

### **Problema: Variáveis não aparecem no container**
**Solução:**
1. Verificar se foram salvas no Dokploy
2. Fazer restart do serviço
3. Verificar com: `docker exec <container> env | grep DB_`

---

## 📞 Suporte

Se precisar de ajuda:
1. Verificar logs do container
2. Consultar documentação do Dokploy
3. Verificar se o serviço está usando a imagem mais recente

---

**Atualizado em:** Janeiro 2025
**Versão:** 1.0
**Projeto:** Sistema Hemodiálise - Qualidade HD
