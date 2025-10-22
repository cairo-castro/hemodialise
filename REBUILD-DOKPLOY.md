# 🚀 Instruções para Rebuild no Dokploy

## ✅ Mudanças Aplicadas

### Commit: `187cc28`
**Mensagem:** fix: add environment variables directly to Dockerfile and change DB_CONNECTION to mariadb

### Alterações no `Dockerfile.production`:

1. **Variáveis de Ambiente Permanentes**
   - Todas as variáveis foram adicionadas diretamente no Dockerfile via `ENV`
   - Não precisa mais configurar manualmente via `docker service update`

2. **Conexão de Banco Alterada**
   - ❌ Antes: `DB_CONNECTION=mysql`
   - ✅ Agora: `DB_CONNECTION=mariadb`

3. **Healthcheck Ajustado**
   - `StartPeriod`: 60s → **120s** (mais tempo para inicializar)
   - `Timeout`: 10s → **15s**

### Variáveis Configuradas no Dockerfile:

```dockerfile
ENV APP_NAME="Sistema Hemodiálise - Qualidade"
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV APP_URL=https://qualidadehd.direcaoclinica.com.br
ENV DB_CONNECTION=mariadb
ENV DB_HOST=qualidade-productionqualidade-l2xbgb
ENV DB_PORT=3306
ENV DB_DATABASE=hemodialise_gqa
ENV DB_USERNAME=Usr_QltGest@2025
ENV DB_PASSWORD=Qlt!H0sp#2025
ENV SESSION_DRIVER=database
ENV CACHE_STORE=database
ENV QUEUE_CONNECTION=database
ENV RUN_MIGRATIONS=false
```

---

## 🔄 Como Fazer o Rebuild no Dokploy

### Opção 1: Via Interface Web (RECOMENDADO)

1. **Acessar o Painel Dokploy**
   ```
   URL: https://212.85.1.175:3000
   ```

2. **Navegar até o Projeto**
   - Procurar por: **`qualidade-qualidadehd-bue1bg`**
   - Ou: **Sistema Hemodiálise**

3. **Fazer o Redeploy**
   - Clicar no botão **"Redeploy"** ou **"Rebuild"**
   - Aguardar o build completar (~5-10 minutos)
   - Verificar logs do build em tempo real

4. **Verificar Deploy**
   - Status deve mudar para **"Running"**
   - Replicas: **1/1** ✅

### Opção 2: Via Webhook (se configurado)

Se você configurou o webhook do GitHub:
- O código já foi enviado via `git push`
- O Dokploy pode fazer o rebuild automaticamente
- Verifique o painel para ver o status

### Opção 3: Via CLI (Dokploy API)

Se tiver acesso à API do Dokploy:
```bash
# Trigger rebuild via API
curl -X POST https://212.85.1.175:3000/api/deploy/[PROJECT_ID] \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## ✅ Verificações Pós-Rebuild

### 1. Verificar Status do Serviço

```bash
ssh root@212.85.1.175 "docker service ls | grep qualidade-qualidadehd"
```

**Resultado esperado:**
```
tseffwuije3m   qualidade-qualidadehd-bue1bg   replicated   1/1   qualidade-qualidadehd-bue1bg:latest
```
✅ **1/1 replicas** = Serviço rodando!

### 2. Verificar Logs do Container

```bash
ssh root@212.85.1.175 "docker service logs qualidade-qualidadehd-bue1bg --tail 50"
```

**O que procurar:**
```
✓ Database connection established!
✓ Application setup completed successfully!
Starting services via Supervisor...
```

### 3. Verificar Variáveis de Ambiente

```bash
ssh root@212.85.1.175 'CID=$(docker ps --filter name=qualidade-qualidadehd --format "{{.ID}}" | head -1); docker exec $CID env | grep -E "DB_|APP_" | sort'
```

**Deve mostrar:**
```
APP_ENV=production
DB_CONNECTION=mariadb
DB_HOST=qualidade-productionqualidade-l2xbgb
DB_DATABASE=hemodialise_gqa
DB_USERNAME=Usr_QltGest@2025
```

### 4. Testar Aplicação Web

```bash
curl -I https://qualidadehd.direcaoclinica.com.br
```

**Resultado esperado:**
```
HTTP/1.1 200 OK
```

### 5. Testar no Navegador

Abrir: **https://qualidadehd.direcaoclinica.com.br**

✅ Deve mostrar a **tela de login**

---

## 🔧 Solução de Problemas

### Problema: Serviço ainda está 0/1

**Verificar logs detalhados:**
```bash
ssh root@212.85.1.175 "docker service ps qualidade-qualidadehd-bue1bg --no-trunc"
```

**Ver erro específico:**
```bash
ssh root@212.85.1.175 'CID=$(docker ps --filter name=qualidade-qualidadehd --format "{{.ID}}" | head -1); docker logs $CID 2>&1'
```

### Problema: Bad Gateway (502)

**Causa:** Container não está rodando ou está crashando

**Solução:**
1. Verificar logs do serviço (comando acima)
2. Verificar se o MariaDB está rodando:
   ```bash
   ssh root@212.85.1.175 "docker ps | grep mariadb"
   ```
3. Testar conexão com banco:
   ```bash
   ssh root@212.85.1.175 'docker exec qualidade-productionqualidade-l2xbgb.*.* mariadb -u "Usr_QltGest@2025" -p"Qlt!H0sp#2025" -e "SHOW DATABASES;"'
   ```

### Problema: Variáveis não estão aplicadas

**Causa:** Rebuild não foi feito ou imagem antiga ainda está sendo usada

**Solução:**
```bash
# Verificar quando a imagem foi criada
ssh root@212.85.1.175 "docker images qualidade-qualidadehd-bue1bg --format 'table {{.CreatedAt}}\t{{.Size}}'"

# Se for antiga (>1 hora), fazer rebuild manual no Dokploy
```

### Problema: Container em loop de restart

**Causa:** Migrations ou outro comando falhando no entrypoint

**Solução temporária:**
```bash
# Desabilitar migrations
ssh root@212.85.1.175 "docker service update --env-add RUN_MIGRATIONS=false qualidade-qualidadehd-bue1bg"
```

---

## 📝 Comandos Úteis

### Ver status em tempo real
```bash
ssh root@212.85.1.175 "watch -n 2 'docker service ls | grep qualidade'"
```

### Ver logs em tempo real
```bash
ssh root@212.85.1.175 "docker service logs qualidade-qualidadehd-bue1bg --follow"
```

### Forçar restart do serviço
```bash
ssh root@212.85.1.175 "docker service update --force qualidade-qualidadehd-bue1bg"
```

### Verificar imagem atual
```bash
ssh root@212.85.1.175 "docker inspect qualidade-qualidadehd-bue1bg:latest --format '{{.Created}}'"
```

---

## 🎯 Próximos Passos Após Rebuild Bem-Sucedido

1. **Testar Login**
   - Usuário: `admin@sistema.com`
   - Senha: `password`

2. **Rodar Seeders (Primeira Vez)**
   ```bash
   ssh root@212.85.1.175 'CID=$(docker ps --filter name=qualidade-qualidadehd --format "{{.ID}}" | head -1); docker exec $CID su-exec laravel php artisan db:seed --class=UserSeeder --force'
   ```

3. **Verificar Usuários Globais**
   - Gerente: `joenvilly.azevedo@emserh.ma.gov.br`
   - Coordenador: `andre.campos@emserh.ma.gov.br`

4. **Configurar Backup Automático**
   ```bash
   # Criar script de backup (fazer depois)
   ```

---

## 📊 Timeline Esperada

- **0-2 min:** Dokploy puxa código do GitHub
- **2-8 min:** Build da imagem (Node + PHP)
- **8-10 min:** Push da imagem e deploy
- **10-12 min:** Container iniciando e rodando migrations
- **12+ min:** ✅ **Aplicação disponível!**

---

## 🔐 Credenciais de Acesso

### Servidor SSH
- **Host:** 212.85.1.175
- **User:** root
- **Password:** ClinQua-Hosp@2025

### Dokploy Panel
- **URL:** https://212.85.1.175:3000

### Banco de Dados MariaDB
- **Container:** qualidade-productionqualidade-l2xbgb
- **Database:** hemodialise_gqa
- **User:** Usr_QltGest@2025
- **Password:** Qlt!H0sp#2025
- **Root Password:** R00t#GQA!Safe2025

---

**Última atualização:** 22/10/2025 21:00  
**Commit:** 187cc28  
**Branch:** main  
**Status:** ⏳ Aguardando rebuild no Dokploy
