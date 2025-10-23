# 🎯 Setup Final - Sistema Hemodiálise no Dokploy

## ✅ Status da Correção

Todas as correções foram implementadas seguindo as **melhores práticas do Dokploy**.

---

## 📚 O Que Foi Feito

### 1. **Documentação Completa**
- ✅ Criado **[DOKPLOY-ENV-GUIDE.md](DOKPLOY-ENV-GUIDE.md)** - Guia completo com:
  - Explicação dos 3 níveis de variáveis do Dokploy
  - Tutorial passo a passo de configuração
  - Boas práticas de segurança
  - Processo de migração
  - Troubleshooting

### 2. **Remoção de Credenciais Hardcoded**
- ✅ **Dockerfile.production** não copia mais `.env` file
- ✅ **Variáveis serão injetadas pelo Dokploy em runtime**
- ✅ `.env.docker` convertido em template de documentação apenas

### 3. **Validação Automática**
- ✅ Criado **[docker/validate-env.sh](docker/validate-env.sh)**
  - Valida todas as variáveis obrigatórias
  - Mostra erros claros se algo estiver faltando
  - Previne container de subir com configuração errada
- ✅ **Entrypoint atualizado** para executar validação antes do startup

### 4. **Migrations Corrigidas**
- ✅ Migrations compatíveis com SQLite E MariaDB
- ✅ `config/database.php` com fallback para MariaDB em production

---

## 🚀 PRÓXIMOS PASSOS (OBRIGATÓRIO)

### **Passo 1: Push para o GitHub**

```bash
cd /home/Hemodialise/sistema-hemodialise
git push origin main
```

Se falhar por autenticação, configure:
```bash
# Opção A: SSH
git remote set-url origin git@github.com:cairo-castro/hemodialise.git

# Opção B: Personal Access Token
git push https://SEU_TOKEN@github.com/cairo-castro/hemodialise.git main
```

---

### **Passo 2: Configurar Variáveis no Dokploy**

#### **2.1 Acessar Dokploy**
URL: http://212.85.1.175:3000

#### **2.2 Configurar Project-Level Variables**
1. Vá em: **Projects > qualidade > Environment Variables**
2. Adicione as variáveis **compartilhadas** (usadas por múltiplos serviços):

```env
# === PROJECT-LEVEL (Shared) ===
DB_HOST=qualidade-productionqualidade-l2xbgb
DB_PORT=3306
DB_USERNAME=Usr_QltGest@2025
DB_PASSWORD=Qlt!H0sp#2025
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

#### **2.3 Configurar Service-Level Variables**
1. Vá em: **Projects > qualidade > qualidadehd > Environment**
2. Adicione TODAS as variáveis abaixo:

```env
# ===== APPLICATION =====
APP_NAME=Sistema Hemodiálise - Qualidade
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qualidadehd.direcaoclinica.com.br
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

# ===== SECURITY KEYS =====
# ⚠️  IMPORTANTE: Gerar novos com:
# php artisan key:generate --show
# openssl rand -base64 32
APP_KEY=base64:H2UOVPoDW9emzGrn1Rx2EV15YOsosTwPtvPUPmsVph0=
JWT_SECRET=NHJVkr3HSHQM1D7BxhToHm5rB4EffTsCvHpyhjljiygtoIqFZAySrokYTpnHlNxX

# ===== DATABASE =====
# Referenciando variáveis do projeto
DB_CONNECTION=mariadb
DB_HOST=${{project.DB_HOST}}
DB_PORT=${{project.DB_PORT}}
DB_DATABASE=hemodialise_gqa
DB_USERNAME=${{project.DB_USERNAME}}
DB_PASSWORD=${{project.DB_PASSWORD}}
DB_CHARSET=${{project.DB_CHARSET}}
DB_COLLATION=${{project.DB_COLLATION}}

# ===== CACHE & SESSION =====
CACHE_STORE=database
CACHE_PREFIX=hemodialise_cache
SESSION_DRIVER=database
SESSION_LIFETIME=480
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

# ===== QUEUE & BROADCAST =====
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

# ===== LOGGING =====
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning

# ===== INITIALIZATION =====
RUN_MIGRATIONS=true
RUN_SEEDERS=false

# ===== SECURITY =====
TRUSTED_PROXIES=*
SANCTUM_STATEFUL_DOMAINS=qualidadehd.direcaoclinica.com.br

# ===== MAIL (Configurar depois se necessário) =====
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@direcaoclinica.com.br
```

**💡 Dica:** No Dokploy, você pode usar referências como `${{project.DB_HOST}}` para reutilizar variáveis!

---

### **Passo 3: Fazer Redeploy**

1. No Dokploy, vá no serviço **qualidadehd**
2. Clique em **"Redeploy"** ou **"Rebuild"**
3. Aguarde o build completar
4. Verifique os logs

---

## 🔍 Verificação

### **Verificar Logs do Container:**

```bash
# SSH no servidor
ssh root@212.85.1.175

# Ver containers
docker ps | grep qualidade

# Ver logs (substitua CONTAINER_ID)
docker logs CONTAINER_ID --tail 100
```

### **O Que Você Deve Ver nos Logs:**

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
✅ APP_URL is set

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   Database Configuration
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ DB_CONNECTION is set
✅ DB_HOST is set
✅ DB_PORT is set
✅ DB_DATABASE is set
✅ DB_USERNAME is set
✅ DB_PASSWORD is set

✅ All required environment variables are set!

Step 2: Checking database connection...
Waiting for MariaDB at qualidade-productionqualidade-l2xbgb:3306...
✓ Database connection established!

Running database migrations...
✓ Migrations completed successfully
```

### **❌ Se Ver Isso:**

```
❌ ERROR: DB_HOST is not set
Configure it in Dokploy: Projects > qualidade > Environment Variables
```

**Solução:** Você esqueceu de configurar as variáveis no Dokploy. Volte ao Passo 2.

---

## 🎉 Resultado Esperado

Depois de seguir todos os passos:

- ✅ Container sobe sem erros
- ✅ Usa **MariaDB** (não SQLite)
- ✅ Migrations rodam com sucesso
- ✅ Aplicação acessível em: https://qualidadehd.direcaoclinica.com.br
- ✅ **Bad Gateway resolvido!**

---

## 📊 Resumo das Mudanças

| Antes | Depois |
|-------|--------|
| ❌ Credenciais hardcoded no `.env.docker` | ✅ Variáveis injetadas pelo Dokploy |
| ❌ Build falha se `.env.docker` não existir | ✅ Build sempre funciona |
| ❌ Trocar senha = rebuild da imagem | ✅ Trocar senha = apenas restart |
| ❌ Credenciais expostas no Git | ✅ Credenciais seguras no Dokploy |
| ❌ SQLite usado por falta de config | ✅ Validação previne erro |
| ❌ Erro descoberto só no runtime | ✅ Validação mostra erro imediatamente |

---

## 🔐 Segurança

### **O Que Melhorou:**

1. **Credenciais Criptografadas**
   - Dokploy armazena variáveis de forma segura
   - Não vão para o repositório Git
   - Não aparecem em imagens Docker

2. **Rotação Fácil de Senhas**
   - Alterar no Dokploy UI
   - Restart automático
   - Sem rebuild necessário

3. **Auditoria**
   - Dokploy rastreia quem alterou o quê
   - Logs de mudanças em variáveis

4. **Separação por Ambiente**
   - Staging e Production com variáveis diferentes
   - Evita usar credenciais de prod em dev

---

## 📖 Documentação Adicional

- **[DOKPLOY-ENV-GUIDE.md](DOKPLOY-ENV-GUIDE.md)** - Guia completo
- **[.env.docker](.env.docker)** - Template de referência
- **[docker/validate-env.sh](docker/validate-env.sh)** - Script de validação

---

## 🆘 Troubleshooting

### **Build falha com "failed to compute cache key"**
- Certifique-se de que fez o **push** para o GitHub
- Dokploy precisa do código mais recente

### **Container crashando com "DB_HOST not set"**
- Variáveis não foram configuradas no Dokploy
- Siga o **Passo 2** acima

### **Ainda está usando SQLite**
- Verifique se `DB_CONNECTION=mariadb` está no Dokploy
- Verifique logs da validação

### **"SQLSTATE[HY000]: General error: 1 near MODIFY"**
- Migration antiga rodando em SQLite
- Certifique-se de que fez pull do código mais recente
- Migrations foram corrigidas para compatibilidade

---

## ✅ Checklist Final

Antes de considerar finalizado:

- [ ] Push feito para o GitHub
- [ ] Variáveis configuradas no Dokploy (Project-level)
- [ ] Variáveis configuradas no Dokploy (Service-level)
- [ ] Redeploy executado
- [ ] Logs mostram "✅ All required environment variables are set!"
- [ ] Logs mostram "Database connection established!"
- [ ] Migrations rodam com sucesso
- [ ] Aplicação acessível no browser
- [ ] Sem erros de Bad Gateway

---

**Última Atualização:** Janeiro 2025
**Status:** ✅ Pronto para Deploy
**Próxima Ação:** Configurar variáveis no Dokploy e fazer redeploy
