# 📦 Arquivos de Produção - Dokploy

## ✅ Arquivos Criados

### 1. Configuração de Ambiente
- **`.env.production`** - Arquivo de ambiente otimizado para produção com MariaDB
  - Banco de dados: `hemodialise_gqa`
  - Host: `qualidade-productionqualidade-l2xbgb`
  - Domínio: `qualidadehd.direcaoclinica.com.br`
  - Configurações de segurança (HTTPS, cookies seguros)
  - Timezone: America/Sao_Paulo

### 2. Docker
- **`Dockerfile.production`** - Dockerfile otimizado para Dokploy
  - Multi-stage build (Node.js, Composer, Runtime)
  - Suporte para MariaDB/MySQL
  - PHP 8.2 com extensões necessárias
  - OPcache configurado para produção
  - Nginx + PHP-FPM + Supervisor
  - Health check configurado

- **`docker/entrypoint.production.sh`** - Script de inicialização
  - Aguarda MariaDB estar disponível
  - Executa migrações automaticamente
  - Otimiza Laravel (cache de config, routes, views)
  - Configura permissões corretamente
  - Suporte a seeders opcionais

- **`.dockerignore`** - Atualizado para incluir arquivos de produção

### 3. CI/CD
- **`.github/workflows/deploy-production.yml`** - GitHub Actions workflow
  - Build e push automático para Docker Hub
  - Trigger de deployment no Dokploy via API
  - Cache de layers do Docker
  - Notificações de sucesso/falha

- **`.github/SECRETS.md`** - Documentação dos secrets necessários
  - DOCKERHUB_USERNAME
  - DOCKERHUB_TOKEN
  - DOKPLOY_URL
  - DOKPLOY_API_KEY
  - DOKPLOY_APP_ID

### 4. Documentação
- **`DOKPLOY-DEPLOY-GUIDE.md`** - Guia completo de deployment
  - Passo a passo detalhado
  - Configuração do Dokploy
  - Setup de DNS e SSL
  - Troubleshooting
  - Deployment avançado com CI/CD
  - Manutenção e backup

---

## 🚀 Como Fazer o Deploy

### Opção 1: Deploy Direto do Git (Mais Simples)

1. **Push os arquivos para o repositório:**
   ```bash
   git add .
   git commit -m "feat: adiciona configuração de produção Dokploy"
   git push origin main
   ```

2. **Gerar chaves:**
   ```bash
   # APP_KEY
   php artisan key:generate --show
   
   # JWT_SECRET
   php artisan jwt:secret --show
   ```

3. **No Dokploy, criar aplicação:**
   - Source Type: **Git**
   - Repository: `seu-repositorio`
   - Branch: `main`
   - Build Type: **Dockerfile**
   - Dockerfile Path: `Dockerfile.production`
   - Context: `.`

4. **Adicionar variáveis de ambiente** (copie do `.env.production`)

5. **Configurar domínio:**
   - Domain: `qualidadehd.direcaoclinica.com.br`
   - Port: `80`
   - HTTPS: ✅ Enabled

6. **Deploy!**

### Opção 2: Deploy com CI/CD (Recomendado para Produção)

1. **Criar conta Docker Hub** e repositório

2. **Configurar secrets no GitHub** (veja `.github/SECRETS.md`)

3. **Push os arquivos:**
   ```bash
   git add .
   git commit -m "feat: adiciona CI/CD para deployment"
   git push origin main
   ```

4. **GitHub Actions fará:**
   - Build da imagem Docker
   - Push para Docker Hub
   - Trigger deployment no Dokploy

5. **No Dokploy:**
   - Source Type: **Docker**
   - Image: `seu-usuario/hemodialise-qualidade:latest`
   - Configurar variáveis de ambiente
   - Configurar domínio
   - Deploy automático via webhook

---

## 📋 Checklist Pré-Deploy

### Antes de fazer o deploy:

- [ ] Gerar `APP_KEY` e adicionar no Dokploy
- [ ] Gerar `JWT_SECRET` e adicionar no Dokploy
- [ ] Confirmar credenciais do banco MariaDB
- [ ] Configurar DNS apontando para servidor Dokploy
- [ ] Push de todos os arquivos para o repositório
- [ ] (Opcional) Configurar secrets no GitHub para CI/CD
- [ ] (Opcional) Criar repositório no Docker Hub

### Configurações no Dokploy:

- [ ] Aplicação criada
- [ ] Variáveis de ambiente configuradas
- [ ] Domínio adicionado com SSL
- [ ] Health check configurado (opcional mas recomendado)
- [ ] Resources configurados (opcional)
- [ ] Volumes para storage e cache (opcional mas recomendado)

### Após o primeiro deploy:

- [ ] Verificar se aplicação está acessível
- [ ] Testar login
- [ ] Verificar SSL/HTTPS funcionando
- [ ] Rodar seeders se necessário
- [ ] Testar funcionalidades principais
- [ ] Configurar backup do banco de dados
- [ ] Configurar monitoramento (logs, uptime)

---

## 🔧 Otimizações Incluídas

### Performance
- ✅ Multi-stage Docker build (reduz tamanho da imagem)
- ✅ OPcache ativado e otimizado
- ✅ Laravel caches (config, routes, views, events)
- ✅ Composer autoloader otimizado
- ✅ Gzip compression no Nginx
- ✅ Cache de assets estáticos (1 ano)

### Segurança
- ✅ Container roda como usuário não-root
- ✅ Secrets via environment variables
- ✅ HTTPS/SSL automático (Let's Encrypt)
- ✅ Security headers no Nginx
- ✅ Cookies seguros e encrypted
- ✅ Trusted proxies configurado

### Confiabilidade
- ✅ Health check endpoint (`/up`)
- ✅ Auto rollback em caso de falha
- ✅ Zero downtime deployment
- ✅ Restart automático de processos (Supervisor)
- ✅ Queue workers com retry automático
- ✅ Laravel scheduler para cron jobs

### Observabilidade
- ✅ Logs estruturados (daily rotation)
- ✅ Logs separados (app, queue, scheduler)
- ✅ Monitoramento de recursos (CPU, RAM, disk)
- ✅ Deploy logs em tempo real

---

## 📊 Arquitetura do Container

```
Container hemodialise-qualidade
│
├── Supervisor (Process Manager)
│   ├── Nginx (Port 80)
│   ├── PHP-FPM (Unix Socket)
│   ├── Laravel Queue Workers (2x)
│   └── Laravel Scheduler
│
├── Storage Volumes
│   ├── /var/www/html/storage
│   └── /var/www/html/bootstrap/cache
│
├── Health Check
│   └── GET /up (30s interval)
│
└── MariaDB Connection
    └── qualidade-productionqualidade-l2xbgb:3306
```

---

## 🆘 Suporte e Troubleshooting

Consulte o arquivo **`DOKPLOY-DEPLOY-GUIDE.md`** para:

- Resolução de problemas comuns
- Comandos úteis para debug
- Backup e restore do banco
- Manutenção da aplicação
- Ver logs em tempo real

---

## 📞 Próximos Passos

Após o deploy bem-sucedido:

1. **Configurar backup automático** do banco de dados
2. **Setup de monitoramento** (uptime, alerts)
3. **Configurar notificações** (email/slack) para deploys
4. **Documentar senhas e acessos** em local seguro
5. **Treinar equipe** no processo de deploy
6. **Planejar estratégia de rollback** para emergências

---

## 📚 Documentação Relacionada

- **Dokploy Docs**: https://docs.dokploy.com
- **Laravel Deployment**: https://laravel.com/docs/deployment
- **Docker Best Practices**: https://docs.docker.com/develop/dev-best-practices/
- **Nginx Optimization**: https://www.nginx.com/blog/tuning-nginx/

---

**✨ Tudo pronto para produção!**

Criado em: 22 de Outubro de 2025  
Domínio: qualidadehd.direcaoclinica.com.br  
Database: hemodialise_gqa @ MariaDB Dokploy
