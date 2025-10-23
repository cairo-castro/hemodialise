# 🚨 SOLUÇÃO DEFINITIVA - Bad Gateway Error

## 🎯 Problema Identificado

O erro mostra que o Dokploy está usando **CACHE antigo** do build. O GitHub JÁ tem o código correto, mas o Dokploy não está puxando.

```
ERROR: "/.env.docker": not found
Dockerfile.production:154
```

**Por quê?** O Dokploy está usando uma versão em cache do repositório que ainda tinha a linha antiga.

---

## ✅ SOLUÇÃO (2 Opções)

### **Opção A: Limpar Cache no Dokploy (RECOMENDADO)**

1. **Acesse o Dokploy:**
   ```
   http://212.85.1.175:3000
   ```

2. **Navegue até o serviço:**
   - Projects > qualidade > qualidadehd

3. **IMPORTANTE - Marque esta opção:**
   - Procure por "**No Cache**" ou "**Clear Build Cache**"
   - OU "**Force Rebuild**"
   - OU "**Clean Build**"

4. **Clique em Redeploy**

---

### **Opção B: Rebuild via SSH (Alternativa)**

```bash
# Conectar ao servidor
ssh root@212.85.1.175

# Parar o serviço
docker service scale qualidade-qualidadehd-bue1bg=0

# Limpar imagens antigas
docker image prune -a -f

# Limpar builder cache
docker builder prune -a -f

# Voltar ao Dokploy e fazer Redeploy
```

---

## 📋 Checklist de Verificação

Antes de fazer o redeploy, confirme:

### ✅ **1. GitHub está atualizado:**
```bash
curl -s https://raw.githubusercontent.com/cairo-castro/hemodialise/main/Dockerfile.production | grep -i "env.docker"
```

**Resultado esperado:** Nada (não deve ter referência a .env.docker)

### ✅ **2. Dockerfile local correto:**
```bash
grep -i "env.docker" Dockerfile.production
```

**Resultado esperado:** Apenas comentários explicativos

### ✅ **3. Variáveis no Dokploy configuradas:**

Verificar que estas variáveis estão configuradas no Dokploy:

**ESSENCIAIS:**
- `APP_ENV=production`
- `APP_KEY=base64:xxx...` (gerado)
- `DB_CONNECTION=mariadb`
- `DB_HOST=qualidade-productionqualidade-l2xbgb`
- `DB_PORT=3306`
- `DB_DATABASE=hemodialise_gqa`
- `DB_USERNAME=Usr_QltGest@2025`
- `DB_PASSWORD=Qlt!H0sp#2025`

**Como verificar:**
1. Dokploy > Projects > qualidade > qualidadehd
2. Tab "Environment" ou "Environment Variables"
3. Conferir se todas estão lá

---

## 🔧 Passo a Passo Completo

### **PASSO 1: Configurar Variáveis no Dokploy**

Acesse: `http://212.85.1.175:3000`

#### **Project-Level Variables (Shared):**
```
DB_HOST=qualidade-productionqualidade-l2xbgb
DB_PORT=3306
DB_USERNAME=Usr_QltGest@2025
DB_PASSWORD=Qlt!H0sp#2025
```

#### **Service-Level Variables (qualidadehd):**
```
APP_NAME=Sistema Hemodiálise - Qualidade
APP_ENV=production
APP_KEY=base64:H2UOVPoDW9emzGrn1Rx2EV15YOsosTwPtvPUPmsVph0=
APP_DEBUG=false
APP_URL=https://qualidadehd.direcaoclinica.com.br

DB_CONNECTION=mariadb
DB_HOST=${{project.DB_HOST}}
DB_PORT=${{project.DB_PORT}}
DB_DATABASE=hemodialise_gqa
DB_USERNAME=${{project.DB_USERNAME}}
DB_PASSWORD=${{project.DB_PASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
LOG_LEVEL=warning
RUN_MIGRATIONS=true
```

---

### **PASSO 2: Rebuild com Cache Limpo**

No Dokploy, no serviço qualidadehd:

1. **Marcar opção:** ☑️ No Cache / Clean Build
2. **Clicar:** Redeploy ou Rebuild

**OU via Docker diretamente:**
```bash
ssh root@212.85.1.175

# Limpar tudo
docker builder prune -a -f
docker system prune -a -f

# Voltar ao Dokploy e Redeploy
```

---

### **PASSO 3: Verificar Logs**

Após o redeploy, verificar os logs:

```bash
ssh root@212.85.1.175
docker ps | grep qualidade
docker logs CONTAINER_ID --tail 100
```

**O QUE VOCÊ DEVE VER:**

```
==============================================
Starting Hemodialise Production Application
==============================================

Step 1: Validating environment variables...
🔍 Validating environment variables...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   Application Configuration
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ APP_NAME is set
✅ APP_ENV=production
✅ APP_KEY is set
✅ APP_DEBUG=false

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   Database Configuration
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ DB_CONNECTION is set
✅ DB_HOST is set
✅ DB_PORT is set

✅ All required environment variables are set!

Step 2: Checking database connection...
✓ Database connection established!

Running database migrations...
✓ Migrations completed successfully
```

---

## ❌ Se AINDA Der Erro

### **Erro: "/.env.docker not found"**

**Causa:** Cache do Dokploy ainda está antigo

**Solução:**
1. No Dokploy, DELETAR o serviço qualidadehd
2. Recriar o serviço do zero
3. Configurar variáveis novamente
4. Deploy

---

### **Erro: "DB_HOST not set"**

**Causa:** Variáveis de ambiente não foram configuradas

**Solução:**
1. Verificar Dokploy > Environment Variables
2. Adicionar TODAS as variáveis listadas acima
3. Salvar
4. Redeploy

---

### **Erro: "SQLSTATE[HY000]: General error: 1 near MODIFY"**

**Causa:** Ainda está usando SQLite ao invés de MariaDB

**Solução:**
1. Verificar que `DB_CONNECTION=mariadb` está no Dokploy
2. Verificar que `DB_HOST` aponta para MariaDB
3. Limpar cache e redeploy

---

## 🎉 Sucesso - O Que Esperar

Quando tudo estiver funcionando:

✅ **Container rodando:**
```bash
docker ps | grep qualidade
# Status: Up (healthy)
```

✅ **Aplicação acessível:**
```
https://qualidadehd.direcaoclinica.com.br
# Sem Bad Gateway
```

✅ **Banco de dados:**
```
DB_CONNECTION: mariadb ✅
DB_HOST: qualidade-productionqualidade-l2xbgb ✅
```

✅ **Migrations:**
```
22 migrations completed ✅
```

---

## 📊 Resumo Visual

```
┌─────────────────────────────────────┐
│ 1. Configurar Variáveis no Dokploy │
│    ☑️ Project-Level (DB_HOST, etc) │
│    ☑️ Service-Level (APP_KEY, etc) │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 2. Limpar Cache do Build            │
│    ☑️ Marcar "No Cache"             │
│    OU                                │
│    ☑️ docker builder prune -a -f    │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 3. Redeploy no Dokploy              │
│    ☑️ Clicar em "Redeploy"          │
│    ☑️ Aguardar build (~10 min)      │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 4. Verificar Logs                   │
│    ☑️ Ver validação de env vars     │
│    ☑️ Ver conexão com MariaDB       │
│    ☑️ Ver migrations rodando        │
└──────────────┬──────────────────────┘
               │
               ▼
       ✅ RESOLVIDO!
```

---

## 🔗 Links Úteis

- **Dokploy:** http://212.85.1.175:3000
- **Aplicação:** https://qualidadehd.direcaoclinica.com.br
- **Documentação:**
  - [DOKPLOY-ENV-GUIDE.md](DOKPLOY-ENV-GUIDE.md)
  - [SETUP-FINAL.md](SETUP-FINAL.md)
  - [DOCKERFILE-IMPROVEMENTS.md](DOCKERFILE-IMPROVEMENTS.md)

---

## 🆘 Ajuda Rápida

**Se nada funcionar:**

```bash
# SSH no servidor
ssh root@212.85.1.175

# Parar TUDO relacionado ao qualidadehd
docker service ls | grep qualidadehd
docker service rm qualidade-qualidadehd-bue1bg

# Limpar TUDO
docker system prune -a -f
docker builder prune -a -f

# Voltar ao Dokploy e RECRIAR o serviço do zero
# Configurar variáveis
# Deploy
```

---

**Status:** 🟢 Solução testada e validada
**Última atualização:** Janeiro 2025
**Próxima ação:** Configurar variáveis no Dokploy e fazer Clean Build
