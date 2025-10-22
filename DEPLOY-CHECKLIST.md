# ✅ Checklist de Deploy - Produção Dokploy

**Sistema:** Hemodiálise - Qualidade HD  
**Domínio:** qualidadehd.direcaoclinica.com.br  
**Database:** hemodialise_gqa (MariaDB)  
**Data:** ___/___/______

---

## 🎯 FASE 1: Preparação Local

### 1.1 Gerar Chaves
- [ ] Executar: `./generate-production-keys.sh`
- [ ] Copiar `APP_KEY` gerado
- [ ] Copiar `JWT_SECRET` gerado
- [ ] Guardar chaves em local seguro (ex: 1Password, LastPass)

### 1.2 Preparar Repositório
- [ ] Verificar que `Dockerfile.production` existe
- [ ] Verificar que `docker/entrypoint.production.sh` existe
- [ ] Verificar que `.env.production` existe (como referência)
- [ ] Commit de todos os arquivos necessários
- [ ] Push para repositório remoto

```bash
git add .
git commit -m "feat: adiciona configuração de produção Dokploy"
git push origin main
```

---

## 🗄️ FASE 2: Verificar Banco de Dados

### 2.1 Confirmar Credenciais MariaDB
- [ ] Host: `qualidade-productionqualidade-l2xbgb`
- [ ] Port: `3306`
- [ ] Database: `hemodialise_gqa`
- [ ] Username: `Usr_QltGest@2025`
- [ ] Password: `Qlt!H0sp#2025`

### 2.2 Testar Conexão (Opcional)
```bash
# No servidor Dokploy, testar:
docker exec -it [MARIADB_CONTAINER] mysql -u "Usr_QltGest@2025" -p'Qlt!H0sp#2025' hemodialise_gqa -e "SELECT 1;"
```

---

## 🌐 FASE 3: Configurar DNS

### 3.1 Adicionar Registro DNS
- [ ] Acessar painel do provedor de DNS
- [ ] Adicionar registro A ou CNAME:

**Registro A:**
```
Tipo: A
Nome: qualidadehd
Valor: [IP-DO-SERVIDOR-DOKPLOY]
TTL: 300
```

**OU Registro CNAME:**
```
Tipo: CNAME
Nome: qualidadehd
Valor: dokploy.seudominio.com
TTL: 300
```

### 3.2 Verificar Propagação DNS
```bash
# Testar resolução DNS
nslookup qualidadehd.direcaoclinica.com.br

# Ou
dig qualidadehd.direcaoclinica.com.br
```

- [ ] DNS resolvendo corretamente

---

## 🐳 FASE 4: Configurar no Dokploy

### 4.1 Criar Aplicação
- [ ] Login no painel Dokploy
- [ ] Criar novo projeto (se necessário)
- [ ] Criar nova aplicação
- [ ] Nome: `hemodialise-qualidade`

### 4.2 Configurar Source

**Opção A - Git (Mais Simples):**
- [ ] Source Type: `Git`
- [ ] Provider: GitHub/GitLab/Gitea
- [ ] Repository: `https://github.com/cairo-castro/hemodialise`
- [ ] Branch: `main`
- [ ] Build Path: `/`

**Opção B - Docker (CI/CD):**
- [ ] Source Type: `Docker`
- [ ] Image: `seu-usuario/hemodialise-qualidade:latest`
- [ ] Registry: Docker Hub

### 4.3 Configurar Build
- [ ] Build Type: `Dockerfile`
- [ ] Dockerfile Path: `Dockerfile.production`
- [ ] Docker Context: `.`
- [ ] Build Stage: `production`

### 4.4 Adicionar Variáveis de Ambiente
- [ ] Ir para aba "Environment"
- [ ] Adicionar todas as variáveis:

```env
APP_NAME=Sistema Hemodiálise - Qualidade
APP_ENV=production
APP_KEY=[COLE_AQUI]
APP_DEBUG=false
APP_URL=https://qualidadehd.direcaoclinica.com.br
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR

DB_CONNECTION=mysql
DB_HOST=qualidade-productionqualidade-l2xbgb
DB_PORT=3306
DB_DATABASE=hemodialise_gqa
DB_USERNAME=Usr_QltGest@2025
DB_PASSWORD=Qlt!H0sp#2025
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

SESSION_DRIVER=database
SESSION_LIFETIME=480
SESSION_ENCRYPT=true
SESSION_DOMAIN=.direcaoclinica.com.br
SESSION_SECURE_COOKIE=true

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

JWT_SECRET=[COLE_AQUI]
JWT_TTL=60
JWT_REFRESH_TTL=20160

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning

RUN_MIGRATIONS=true
RUN_SEEDERS=false

TRUSTED_PROXIES=*
SANCTUM_STATEFUL_DOMAINS=qualidadehd.direcaoclinica.com.br
```

- [ ] Salvar variáveis

---

## 🌍 FASE 5: Configurar Domínio

### 5.1 Adicionar Domínio
- [ ] Ir para aba "Domains"
- [ ] Clicar em "Add Domain"
- [ ] Configurar:
  - Domain: `qualidadehd.direcaoclinica.com.br`
  - Port: `80`
  - HTTPS: ✅ Enabled
  - Certificate: `Let's Encrypt` (automático)

### 5.2 Aguardar Provisionamento SSL
- [ ] Aguardar ~2-5 minutos
- [ ] Verificar certificado SSL ativo

---

## 🚀 FASE 6: Primeiro Deploy

### 6.1 Iniciar Deploy
- [ ] Clicar em botão "Deploy"
- [ ] Monitorar logs na aba "Logs"
- [ ] Aguardar conclusão (5-10 minutos)

### 6.2 Verificar Status
- [ ] Status: ✅ Running
- [ ] Health Check: ✅ Healthy
- [ ] Logs sem erros críticos

### 6.3 Testar Aplicação
- [ ] Acessar: `https://qualidadehd.direcaoclinica.com.br`
- [ ] Página carrega corretamente
- [ ] SSL/HTTPS funcionando (cadeado verde)
- [ ] Assets (CSS/JS/imagens) carregando

```bash
# Testar health check
curl https://qualidadehd.direcaoclinica.com.br/up
# Deve retornar: OK
```

---

## 🗂️ FASE 7: Configurar Database

### 7.1 Verificar Migrações
- [ ] Migrações executadas automaticamente
- [ ] Tabelas criadas no banco

### 7.2 Rodar Seeders (Primeira Vez)

**Opção A - Via SSH:**
```bash
# SSH no servidor
ssh usuario@servidor-dokploy

# Encontrar container
docker ps | grep hemodialise

# Rodar seeders
docker exec -it [CONTAINER_ID] su-exec laravel php artisan db:seed --class=RolesAndPermissionsSeeder
docker exec -it [CONTAINER_ID] su-exec laravel php artisan db:seed --class=UserSeeder
```

- [ ] Seeders executados
- [ ] Usuários e roles criados

**Opção B - Via Environment Variable:**
- [ ] Mudar `RUN_SEEDERS=true`
- [ ] Fazer novo deploy
- [ ] Após deploy, voltar `RUN_SEEDERS=false`

---

## ⚙️ FASE 8: Configurações Avançadas (Opcional)

### 8.1 Health Check
- [ ] Ir para "Advanced" > "Cluster Settings" > "Swarm Settings"
- [ ] Adicionar Health Check:

```json
{
  "Test": ["CMD", "curl", "-f", "http://localhost/up"],
  "Interval": 30000000000,
  "Timeout": 10000000000,
  "StartPeriod": 60000000000,
  "Retries": 3
}
```

### 8.2 Update Config (Auto Rollback)
- [ ] Adicionar Update Config:

```json
{
  "Parallelism": 1,
  "Delay": 10000000000,
  "FailureAction": "rollback",
  "Order": "start-first"
}
```

### 8.3 Resources
- [ ] Configurar limites de recursos:

```json
{
  "Limits": {
    "NanoCPUs": 2000000000,
    "MemoryBytes": 2147483648
  },
  "Reservations": {
    "NanoCPUs": 500000000,
    "MemoryBytes": 536870912
  }
}
```

### 8.4 Volumes (Persistência)
- [ ] Volume 1: `hemodialise-storage` → `/var/www/html/storage`
- [ ] Volume 2: `hemodialise-cache` → `/var/www/html/bootstrap/cache`

---

## 🔄 FASE 9: Auto Deploy (Opcional)

### Opção A - Webhook Git
- [ ] Copiar Webhook URL da aba "Deployments"
- [ ] Adicionar webhook no GitHub/GitLab
- [ ] Testar fazendo push no repositório

### Opção B - CI/CD (Docker Hub)
- [ ] Configurar secrets no GitHub (veja `.github/SECRETS.md`)
- [ ] Verificar workflow: `.github/workflows/deploy-production.yml`
- [ ] Testar fazendo push no repositório
- [ ] Monitorar GitHub Actions

---

## ✅ FASE 10: Verificação Final

### 10.1 Testes Funcionais
- [ ] Login de usuário funcionando
- [ ] Dashboard carregando
- [ ] Cadastro de pacientes funcionando
- [ ] Listagem de máquinas funcionando
- [ ] Upload de arquivos funcionando
- [ ] Permissões de usuário funcionando

### 10.2 Testes de Performance
- [ ] Tempo de resposta < 2s
- [ ] Assets carregando rapidamente
- [ ] Sem erros 500 nos logs

### 10.3 Testes de Segurança
- [ ] HTTPS funcionando
- [ ] Redirect HTTP → HTTPS
- [ ] Headers de segurança presentes
- [ ] Cookies seguros

```bash
# Verificar headers
curl -I https://qualidadehd.direcaoclinica.com.br

# Deve conter:
# X-Frame-Options: SAMEORIGIN
# X-Content-Type-Options: nosniff
# X-XSS-Protection: 1; mode=block
```

### 10.4 Monitoramento
- [ ] Verificar logs: `docker logs -f [CONTAINER_ID]`
- [ ] Verificar métricas no Dokploy (CPU, RAM, Disk)
- [ ] Configurar alertas (opcional)

---

## 📋 FASE 11: Pós-Deploy

### 11.1 Documentação
- [ ] Documentar senhas em local seguro
- [ ] Documentar IPs e domínios
- [ ] Documentar processo de deploy
- [ ] Treinar equipe

### 11.2 Backup
- [ ] Configurar backup automático do banco
- [ ] Testar restore do backup
- [ ] Documentar procedimento de backup

```bash
# Backup manual
docker exec [MARIADB_CONTAINER] mysqldump -u "Usr_QltGest@2025" -p'Qlt!H0sp#2025' hemodialise_gqa > backup-$(date +%Y%m%d).sql
```

### 11.3 Monitoramento
- [ ] Configurar uptime monitoring (ex: UptimeRobot)
- [ ] Configurar notificações de erro
- [ ] Configurar alertas de recursos

---

## 🎉 Deploy Concluído!

**Data de Deploy:** ___/___/______  
**Responsável:** _______________________  
**Status:** ✅ Produção

**URLs:**
- 🌐 Aplicação: https://qualidadehd.direcaoclinica.com.br
- 🔧 Dokploy: https://_______________
- 📊 Monitoring: https://_______________

---

## 📞 Contatos de Emergência

**Servidor:** _______________________  
**Suporte Dokploy:** _______________________  
**Suporte DNS:** _______________________  
**Suporte Banco:** _______________________

---

## 📝 Notas Adicionais

_Espaço para anotações importantes durante o deploy:_

```
_______________________________________________________________

_______________________________________________________________

_______________________________________________________________

_______________________________________________________________
```

---

**✨ Sistema em Produção com Sucesso! ✨**
