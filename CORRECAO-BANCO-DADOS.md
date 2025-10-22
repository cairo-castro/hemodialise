# 🔧 Correção do Problema de Conexão com Banco de Dados

## 📊 Status Atual

**Data:** 22/10/2025  
**Problema:** Serviço `qualidade-qualidadehd-bue1bg` não está subindo  
**Causa Raiz Identificada:** Permissões do banco de dados + possível problema no entrypoint

---

## ✅ Correções Aplicadas

### 1. Variáveis de Ambiente Configuradas

As seguintes variáveis foram aplicadas ao serviço Docker:

```bash
APP_NAME="Sistema Hemodiálise - Qualidade"
APP_ENV=production
APP_KEY=base64:+vZ3zPQKzXvnwJGVYTz5hN8yR6mL4kW2xB7fC9dE0aA=
APP_DEBUG=false
APP_URL=https://qualidadehd.direcaoclinica.com.br
DB_CONNECTION=mysql
DB_HOST=qualidade-productionqualidade-l2xbgb
DB_PORT=3306
DB_DATABASE=hemodialise_gqa
DB_USERNAME=Usr_QltGest@2025
DB_PASSWORD=Qlt!H0sp#2025
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
RUN_MIGRATIONS=false
```

**Comando usado:**
```bash
/root/fix-env-vars.sh
```

### 2. Permissões do Banco de Dados Corrigidas

**Problema encontrado:**
- Usuário `Usr_QltGest@2025` tinha permissão apenas no banco `GQA_AuditDB_2025`
- Não tinha permissão no banco `hemodialise_gqa`

**Solução aplicada:**
```sql
GRANT ALL PRIVILEGES ON hemodialise_gqa.* TO 'Usr_QltGest@2025'@'%';
FLUSH PRIVILEGES;
```

**Verificação:**
```bash
docker exec dec9081bf509 mariadb -u "Usr_QltGest@2025" -p"Qlt!H0sp#2025" -e "USE hemodialise_gqa; SHOW TABLES;"
```

✅ **Resultado:** 22 tabelas encontradas, usuário com acesso completo

---

## ⚠️ Problema Remanescente

### Sintoma
O serviço continua em loop de restart:
- Status: `0/1 replicas`
- Containers iniciam mas não completam o healthcheck
- Reiniciam antes de atingir estado "healthy"

### Diagnóstico

1. **Variáveis de ambiente:** ✅ OK (verificado no container)
2. **Conexão com banco:** ✅ OK (mensagem "Database connection established!")
3. **Permissões de banco:** ✅ OK (cache clearing funciona)
4. **Healthcheck:** ❌ **Pode estar falhando muito cedo**

**Healthcheck configurado:**
```json
{
    "Test": ["CMD-SHELL", "curl -f http://localhost/up || exit 1"],
    "Interval": 30s,
    "Timeout": 10s,
    "StartPeriod": 60s,
    "Retries": 3
}
```

### Logs Observados

Os logs param em:
```
✓ Database connection established!
Setting up application permissions...
Optimizing Laravel for production...
  → Clearing existing caches...
   INFO  Configuration cache cleared successfully.
```

**Hipótese:** O entrypoint pode estar travando durante o processo de otimização ou o Supervisor não está iniciando corretamente.

---

## 🚀 Solução Recomendada

### Opção 1: Rebuild da Imagem (RECOMENDADO)

A imagem atual foi buildada com a versão antiga do `entrypoint.production.sh` que tinha o comando do Composer.

**Passos:**

1. **Fazer rebuild via Dokploy:**
   - Acessar: https://212.85.1.175:3000
   - Navegar até o projeto `qualidade-qualidadehd-bue1bg`
   - Clicar em **"Redeploy"**
   - Aguardar build completar (~10 minutos)

2. **Verificar após o build:**
```bash
ssh root@212.85.1.175 "docker service ls | grep qualidade-qualidadehd"
```

Deve mostrar: `1/1 replicas`

### Opção 2: Desabilitar Healthcheck Temporariamente

Se o rebuild demorar, pode desabilitar o healthcheck:

```bash
ssh root@212.85.1.175 "docker service update --health-cmd='' --health-interval=0s qualidade-qualidadehd-bue1bg"
```

⚠️ **Atenção:** Isso fará o container subir mesmo sem estar saudável.

### Opção 3: Ajustar Healthcheck

Aumentar o StartPeriod para dar mais tempo:

```bash
ssh root@212.85.1.175 "docker service update --health-start-period=180s qualidade-qualidadehd-bue1bg"
```

---

## 📋 Verificações Pós-Deploy

Após o serviço subir, executar:

### 1. Verificar status
```bash
ssh root@212.85.1.175 "docker service ls"
```
✅ Deve mostrar `1/1`

### 2. Verificar logs
```bash
ssh root@212.85.1.175 "docker service logs qualidade-qualidadehd-bue1bg --tail 50"
```
✅ Deve mostrar "Application setup completed successfully!"

### 3. Testar aplicação
```bash
curl -I https://qualidadehd.direcaoclinica.com.br
```
✅ Deve retornar `HTTP/1.1 200 OK`

### 4. Acessar no navegador
https://qualidadehd.direcaoclinica.com.br
✅ Deve mostrar tela de login

### 5. Rodar seeders (primeira vez)
```bash
ssh root@212.85.1.175 'CID=$(docker ps --filter name=qualidade-qualidadehd --format "{{.ID}}" | head -1); docker exec $CID su-exec laravel php artisan db:seed --class=UserSeeder --force'
```
✅ Deve criar usuários globais

---

## 🔐 Credenciais

### Banco de Dados MariaDB

**Container:** `qualidade-productionqualidade-l2xbgb`

**Root:**
- User: `root`
- Password: `R00t#GQA!Safe2025`

**Aplicação:**
- User: `Usr_QltGest@2025`
- Password: `Qlt!H0sp#2025`
- Database: `hemodialise_gqa`

### Servidor SSH

- Host: `212.85.1.175`
- User: `root`
- Password: `ClinQua-Hosp@2025`

### Dokploy Panel

- URL: https://212.85.1.175:3000

---

## 📝 Scripts Criados

### `/root/fix-env-vars.sh`
Aplica todas as variáveis de ambiente ao serviço

### `/root/limpar-docker.sh`
Script de limpeza de disco (já criado anteriormente)

---

## 🔄 Próximos Passos

1. ✅ Permissões de banco corrigidas
2. ✅ Variáveis de ambiente aplicadas
3. ⏳ **PENDENTE:** Rebuild da imagem no Dokploy
4. ⏳ Testar aplicação após rebuild
5. ⏳ Executar seeders para popular usuários

---

**Última atualização:** 22/10/2025 20:15  
**Status:** Aguardando rebuild da imagem
