# 🚀 Guia de Deploy - Dokploy Production
## Sistema Hemodiálise - Qualidade HD

**Domínio**: `qualidadehd.direcaoclinica.com.br`

---

## 📋 Pré-requisitos

1. **Servidor Dokploy** instalado e funcionando
2. **Banco de Dados MariaDB** já existente no Dokploy:
   - Host: `qualidade-productionqualidade-l2xbgb`
   - Database: `hemodialise_gqa`
   - User: `Usr_QltGest@2025`
   - Password: `Qlt!H0sp#2025`
3. **Repositório Git** (GitHub/GitLab/Gitea)
4. **Chaves necessárias**:
   - `APP_KEY` (será gerado)
   - `JWT_SECRET` (será gerado)

---

## 🔑 Passo 1: Gerar Chaves de Segurança

### Gerar APP_KEY

No seu ambiente local, execute:

```bash
php artisan key:generate --show
```

Você receberá algo como:
```
base64:XyZ123abc456DEF789ghi012JKL345mno678PQR901stu234VWX567yza890=
```

### Gerar JWT_SECRET

```bash
php artisan jwt:secret --show
```

Ou gere manualmente:
```bash
openssl rand -base64 64
```

**⚠️ IMPORTANTE**: Guarde essas chaves em local seguro!

---

## 📦 Passo 2: Preparar o Repositório

### 1. Commit os arquivos de produção

Certifique-se que estes arquivos estão no repositório:

- ✅ `Dockerfile.production`
- ✅ `docker/entrypoint.production.sh`
- ✅ `.env.production` (como referência - **NÃO commitar valores reais**)

```bash
git add Dockerfile.production docker/entrypoint.production.sh .env.production
git commit -m "feat: adiciona configuração de produção para Dokploy"
git push origin main
```

### 2. Estrutura esperada no repositório

```
/
├── Dockerfile.production       # Dockerfile otimizado
├── docker/
│   ├── entrypoint.production.sh
│   ├── nginx/
│   │   ├── nginx.conf
│   │   └── default.conf
│   └── supervisor/
│       └── supervisord.conf
├── app/
├── resources/
├── public/
├── composer.json
├── package.json
└── ... (resto da aplicação Laravel)
```

---

## 🎯 Passo 3: Criar Aplicação no Dokploy

### 3.1. Login no Dokploy

Acesse seu painel Dokploy em: `https://seu-dokploy.com`

### 3.2. Criar Nova Aplicação

1. Clique em **"Create Application"**
2. Escolha **"Git Provider"**
3. Configurações:

**General Settings:**
```
Application Name: hemodialise-qualidade
Repository: https://github.com/cairo-castro/hemodialise
Branch: main
Build Path: /
```

**Build Type:**
```
Build Type: Dockerfile
Dockerfile Path: Dockerfile.production
Docker Context Path: .
Docker Build Stage: production
```

### 3.3. Configurar Variáveis de Ambiente

Vá para a aba **"Environment"** e adicione:

```env
# Application
APP_NAME=Sistema Hemodiálise - Qualidade
APP_ENV=production
APP_KEY=base64:COLE_SUA_CHAVE_AQUI
APP_DEBUG=false
APP_URL=https://qualidadehd.direcaoclinica.com.br
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR

# Database - MariaDB Dokploy
DB_CONNECTION=mysql
DB_HOST=qualidade-productionqualidade-l2xbgb
DB_PORT=3306
DB_DATABASE=hemodialise_gqa
DB_USERNAME=Usr_QltGest@2025
DB_PASSWORD=Qlt!H0sp#2025
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=480
SESSION_ENCRYPT=true
SESSION_DOMAIN=.direcaoclinica.com.br
SESSION_SECURE_COOKIE=true
CACHE_STORE=database

# Queue
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

# JWT
JWT_SECRET=COLE_SEU_JWT_SECRET_AQUI
JWT_TTL=60
JWT_REFRESH_TTL=20160

# Logging
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning

# Container Flags
RUN_MIGRATIONS=true
RUN_SEEDERS=false

# Security
TRUSTED_PROXIES=*
SANCTUM_STATEFUL_DOMAINS=qualidadehd.direcaoclinica.com.br
```

---

## 🌐 Passo 4: Configurar Domínio

### 4.1. Adicionar Domínio no Dokploy

1. Vá para a aba **"Domains"**
2. Clique em **"Add Domain"**
3. Configure:

```
Domain: qualidadehd.direcaoclinica.com.br
Port: 80
HTTPS: Enabled (Dokploy configurará SSL automaticamente via Let's Encrypt)
```

### 4.2. Configurar DNS

No seu provedor de DNS (ex: Cloudflare, GoDaddy), adicione:

```
Tipo: A
Nome: qualidadehd
Valor: [IP-DO-SERVIDOR-DOKPLOY]
TTL: Auto ou 300
```

**Ou com subdomínio completo:**

```
Tipo: CNAME
Nome: qualidadehd.direcaoclinica.com.br
Valor: seu-servidor-dokploy.com
TTL: Auto ou 300
```

---

## 🚀 Passo 5: Deploy Inicial

### 5.1. Primeira Deployment

1. Clique no botão **"Deploy"**
2. Aguarde o build (pode levar 5-10 minutos na primeira vez)
3. Monitore os logs na aba **"Logs"**

### 5.2. Verificar Health Check

Após o deploy, verifique:

```bash
curl https://qualidadehd.direcaoclinica.com.br/up
```

Deve retornar: `OK`

---

## 🗄️ Passo 6: Configurar Banco de Dados (Primeira vez)

### 6.1. Verificar Migrações

O container já roda as migrações automaticamente (`RUN_MIGRATIONS=true`).

### 6.2. Rodar Seeders (Se necessário)

SSH no servidor Dokploy:

```bash
# Encontrar o container
docker ps | grep hemodialise

# Entrar no container
docker exec -it [CONTAINER_ID] sh

# Rodar seeders
su-exec laravel php artisan db:seed --class=RolesAndPermissionsSeeder
su-exec laravel php artisan db:seed --class=UserSeeder
```

**Ou altere temporariamente** a variável de ambiente:
```env
RUN_SEEDERS=true
```

E faça um novo deploy. Depois **desabilite**:
```env
RUN_SEEDERS=false
```

---

## 📊 Passo 7: Configurar Recursos e Healthcheck

### 7.1. Configurar Resources

Vá para **Advanced > Cluster Settings > Swarm Settings**

**Health Check:**
```json
{
  "Test": [
    "CMD",
    "curl",
    "-f",
    "http://localhost/up"
  ],
  "Interval": 30000000000,
  "Timeout": 10000000000,
  "StartPeriod": 60000000000,
  "Retries": 3
}
```

**Update Config (Rollback automático):**
```json
{
  "Parallelism": 1,
  "Delay": 10000000000,
  "FailureAction": "rollback",
  "Order": "start-first"
}
```

### 7.2. Configurar Recursos (Opcional)

**Resources:**
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

---

## 🔄 Passo 8: Configurar Auto Deploy (Opcional)

### Opção 1: Webhook do Git

1. Vá para **Deployments** tab
2. Copie a **Webhook URL**
3. No GitHub: **Settings > Webhooks > Add webhook**
4. Cole a URL
5. Selecione eventos: `push` no branch `main`

### Opção 2: GitHub Actions + Docker Registry

**Recomendado para produção!** Veja seção "Deployment Avançado" abaixo.

---

## 📁 Passo 9: Configurar Volumes (Persistência)

### 9.1. Adicionar Volumes

Vá para **Advanced > Volumes** e adicione:

**Volume 1 - Storage:**
```
Type: Volume
Name: hemodialise-storage
Container Path: /var/www/html/storage
```

**Volume 2 - Bootstrap Cache:**
```
Type: Volume
Name: hemodialise-cache
Container Path: /var/www/html/bootstrap/cache
```

---

## ✅ Passo 10: Verificação Final

### 10.1. Checklist

- [ ] Aplicação responde em `https://qualidadehd.direcaoclinica.com.br`
- [ ] SSL/HTTPS funcionando (certificado válido)
- [ ] Login de usuário funcionando
- [ ] Banco de dados conectado
- [ ] Logs sem erros críticos
- [ ] Health check retornando OK
- [ ] Assets (CSS/JS) carregando corretamente

### 10.2. Testar Funcionalidades

```bash
# Testar API
curl https://qualidadehd.direcaoclinica.com.br/api/health

# Ver logs
# No Dokploy: Logs tab ou:
docker logs -f [CONTAINER_ID]

# Entrar no container
docker exec -it [CONTAINER_ID] sh
```

---

## 🔥 Deployment Avançado (Recomendado para Produção)

### Por que usar CI/CD?

- ✅ Build fora do servidor (não consome recursos do Dokploy)
- ✅ Deployment mais rápido
- ✅ Zero downtime
- ✅ Rollback automático em caso de erro

### GitHub Actions Setup

#### 1. Criar Docker Hub Account

1. Crie conta em https://hub.docker.com
2. Crie repositório: `seu-usuario/hemodialise`
3. Gere Access Token: **Account Settings > Security > New Access Token**

#### 2. Adicionar Secrets no GitHub

No seu repositório GitHub: **Settings > Secrets > Actions**

```
DOCKERHUB_USERNAME: seu-usuario
DOCKERHUB_TOKEN: seu-access-token
```

#### 3. Criar Workflow

Crie `.github/workflows/deploy-production.yml`:

```yaml
name: Deploy Production

on:
  push:
    branches: [main]

jobs:
  build-and-deploy:
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout
        uses: actions/checkout@v3
      
      - name: Login to Docker Hub
        uses: docker/login-action@v2
        with:
          username: ${{ secrets.DOCKERHUB_USERNAME }}
          password: ${{ secrets.DOCKERHUB_TOKEN }}
      
      - name: Build and Push
        uses: docker/build-push-action@v4
        with:
          context: .
          file: ./Dockerfile.production
          push: true
          tags: |
            ${{ secrets.DOCKERHUB_USERNAME }}/hemodialise:latest
            ${{ secrets.DOCKERHUB_USERNAME }}/hemodialise:${{ github.sha }}
          platforms: linux/amd64
```

#### 4. Configurar Dokploy para Docker Image

No Dokploy, **altere** as configurações:

```
Source Type: Docker
Docker Image: seu-usuario/hemodialise:latest
```

#### 5. Configurar Auto Deploy no Docker Hub

1. Docker Hub > Seu Repositório > **Webhooks**
2. Nome: `Dokploy Production`
3. Webhook URL: [Cole a URL do Dokploy Deployments tab]

---

## 🐛 Troubleshooting

### Problema: Container não inicia

**Verificar:**
```bash
# Ver logs
docker logs [CONTAINER_ID]

# Verificar variáveis
docker exec [CONTAINER_ID] env | grep APP_KEY
```

### Problema: Erro de conexão com banco

**Verificar:**
```bash
# Testar conexão
docker exec [CONTAINER_ID] nc -zv qualidade-productionqualidade-l2xbgb 3306

# Ver credenciais
docker exec [CONTAINER_ID] env | grep DB_
```

### Problema: Assets não carregam

**Solução:**
```bash
docker exec [CONTAINER_ID] su-exec laravel php artisan storage:link
docker exec [CONTAINER_ID] su-exec laravel php artisan optimize
```

### Problema: Permissões

**Solução:**
```bash
docker exec [CONTAINER_ID] chown -R laravel:laravel storage bootstrap/cache
docker exec [CONTAINER_ID] chmod -R 775 storage bootstrap/cache
```

---

## 🔄 Manutenção

### Atualizar Aplicação

1. Fazer push para o repositório
2. Dokploy fará deploy automaticamente (se webhook configurado)
3. Ou clicar em **"Deploy"** manualmente

### Backup do Banco de Dados

```bash
# SSH no servidor
ssh user@dokploy-server

# Backup
docker exec qualidade-productionqualidade-l2xbgb \
  mysqldump -u Usr_QltGest@2025 -p'Qlt!H0sp#2025' hemodialise_gqa > backup.sql

# Restaurar
docker exec -i qualidade-productionqualidade-l2xbgb \
  mysql -u Usr_QltGest@2025 -p'Qlt!H0sp#2025' hemodialise_gqa < backup.sql
```

### Limpar Caches

```bash
docker exec [CONTAINER_ID] su-exec laravel php artisan optimize:clear
docker exec [CONTAINER_ID] su-exec laravel php artisan optimize
```

### Ver Logs

```bash
# Logs em tempo real
docker logs -f [CONTAINER_ID]

# Logs Laravel
docker exec [CONTAINER_ID] tail -f storage/logs/laravel.log

# Logs do Queue Worker
docker exec [CONTAINER_ID] tail -f storage/logs/worker.log
```

---

## 📞 Suporte

Para problemas ou dúvidas:

- **Documentação Dokploy**: https://docs.dokploy.com
- **Logs**: Sempre verifique os logs primeiro
- **Health Check**: Use `/up` para verificar status

---

## ✨ Recursos Habilitados

✅ **HTTPS/SSL automático** (Let's Encrypt via Traefik)  
✅ **Zero Downtime** deployment  
✅ **Auto Rollback** em caso de falha  
✅ **Health Checks** automáticos  
✅ **Queue Workers** (2 workers)  
✅ **Task Scheduler** (cron jobs)  
✅ **OPcache** otimizado para produção  
✅ **Logs estruturados** (daily rotation)  
✅ **Compressão Gzip** no Nginx  
✅ **Cache de assets** (1 ano)  

---

**🎉 Deploy concluído! Sua aplicação está em produção.**
