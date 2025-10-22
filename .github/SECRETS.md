# GitHub Secrets Configuration

Este arquivo documenta os secrets necessários para o GitHub Actions funcionar corretamente.

**⚠️ NUNCA commite valores reais de secrets!**

## Como adicionar secrets no GitHub

1. Vá para seu repositório no GitHub
2. Clique em **Settings** > **Secrets and variables** > **Actions**
3. Clique em **New repository secret**
4. Adicione cada secret listado abaixo

---

## Secrets Necessários

### 1. DOCKERHUB_USERNAME
**Descrição**: Seu nome de usuário do Docker Hub  
**Exemplo**: `meuusuario`  
**Como obter**: Seu username no https://hub.docker.com

### 2. DOCKERHUB_TOKEN
**Descrição**: Token de acesso do Docker Hub  
**Como obter**:
1. Login no Docker Hub
2. Vá para **Account Settings** > **Security**
3. Clique em **New Access Token**
4. Nome: `GitHub Actions - Hemodialise`
5. Permissions: `Read, Write, Delete`
6. Copie o token gerado (aparece apenas uma vez!)

### 3. DOKPLOY_URL
**Descrição**: URL base do seu servidor Dokploy  
**Exemplo**: `https://dokploy.seudominio.com`  
**Formato**: Sem trailing slash `/`

### 4. DOKPLOY_API_KEY
**Descrição**: Chave API para autenticação no Dokploy  
**Como obter**:
1. Login no painel Dokploy
2. Vá para **Settings** ou **Profile**
3. Procure por **API Keys** ou **Access Tokens**
4. Clique em **Generate New API Key**
5. Copie a chave gerada

### 5. DOKPLOY_APP_ID
**Descrição**: ID da aplicação no Dokploy  
**Como obter**:
1. Abra sua aplicação no Dokploy
2. O ID aparece na URL: `https://dokploy.com/dashboard/project/xxx/service/APP_ID_AQUI`
3. Ou verifique na aba **General** da aplicação

---

## Resumo dos Secrets

```
DOCKERHUB_USERNAME=seu-usuario-dockerhub
DOCKERHUB_TOKEN=dckr_pat_xxxxxxxxxxxxxxxxxxx
DOKPLOY_URL=https://dokploy.seudominio.com
DOKPLOY_API_KEY=dpk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
DOKPLOY_APP_ID=clxxxxxxxxxxxxxxxx
```

---

## Alternativa: Deployment sem CI/CD

Se você não quiser usar GitHub Actions, pode fazer deployment direto do Git:

### No Dokploy:

1. **Source Type**: Git
2. **Build Type**: Dockerfile
3. **Dockerfile Path**: `Dockerfile.production`
4. **Auto Deploy**: Habilitado (webhook)

### Webhook do GitHub:

1. No Dokploy, copie a **Webhook URL** da aba **Deployments**
2. No GitHub: **Settings** > **Webhooks** > **Add webhook**
3. Cole a URL
4. **Content type**: `application/json`
5. **Events**: Apenas `push` events
6. **Active**: ✅

Pronto! Cada push no branch `main` fará deploy automático.

---

## Troubleshooting

### Erro: "Invalid credentials"
- Verifique se o `DOCKERHUB_TOKEN` está correto
- Token expirado? Gere um novo no Docker Hub

### Erro: "Application not found"
- Verifique o `DOKPLOY_APP_ID`
- Certifique-se que a aplicação existe no Dokploy

### Erro: "Unauthorized"
- Verifique a `DOKPLOY_API_KEY`
- A chave tem permissões suficientes?

### Build timeout
- Considere aumentar recursos do runner
- Ou use deployment direto (Git source no Dokploy)

---

## Segurança

✅ **NUNCA** commite secrets no código  
✅ Use GitHub Secrets para valores sensíveis  
✅ Rotacione tokens periodicamente  
✅ Use tokens com permissões mínimas necessárias  
✅ Revogue tokens não utilizados  

---

**Documentação oficial:**
- GitHub Actions Secrets: https://docs.github.com/en/actions/security-guides/encrypted-secrets
- Docker Hub Access Tokens: https://docs.docker.com/docker-hub/access-tokens/
- Dokploy API: https://docs.dokploy.com/docs/core/auto-deploy
