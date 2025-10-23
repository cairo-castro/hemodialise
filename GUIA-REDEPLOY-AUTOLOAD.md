# 🚀 Guia Rápido: Redeploy com Autoload Corrigido

## ✅ O que foi corrigido

1. **Composer autoload otimizado** com estrutura completa da aplicação
2. **Composer disponível em produção** para manutenção
3. **Verificação automática** de classes críticas no entrypoint
4. **Auto-recuperação** se detectar problemas de autoload

## 📋 Passo a Passo para Redeploy

### Opção 1: Via Interface Dokploy (RECOMENDADO)

1. **Acesse o Dokploy**
   ```
   https://qualidadehd.direcaoclinica.com.br/dokploy
   ```

2. **Navegue até o projeto**
   - Projects → qualidade → qualidadehd

3. **Force Rebuild**
   - Clique em **"Redeploy"**
   - ✅ **MARQUE a opção "Force Rebuild"** (IMPORTANTE!)
   - Confirme o redeploy

4. **Monitore os logs**
   - Aguarde o build completar
   - Verifique logs por:
     - ✅ "Autoload optimized successfully!"
     - ✅ "Composer installed for production maintenance"
     - ✅ "Verifying Composer autoload..."
     - ✅ "AuthController is autoloadable"

### Opção 2: Via SSH (Se preferir)

```bash
# 1. Conecte ao servidor
ssh root@212.85.1.175

# 2. Force rebuild do projeto
cd /opt/dokploy/projects/qualidade
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# 3. Verifique os logs
docker-compose logs -f --tail 100
```

## 🧪 Teste Local (OPCIONAL)

Antes de fazer deploy, você pode testar localmente:

```bash
# Na sua máquina local
cd /home/Hemodialise/sistema-hemodialise

# Execute o script de teste
./test-dockerfile-autoload.sh
```

O script irá:
- ✅ Fazer build da imagem
- ✅ Verificar se Composer está instalado
- ✅ Testar se autoload foi gerado
- ✅ Validar classes críticas (AuthController)
- ✅ Iniciar container de teste
- ✅ Executar health checks

## 🔍 Verificação Pós-Deploy

### 1. Verifique que o container iniciou

```bash
ssh root@212.85.1.175
docker ps | grep qualidade
```

**Esperado:** Container com status `Up` e `healthy`

### 2. Verifique os logs do entrypoint

```bash
docker logs <container-id> | grep -A 5 "Verifying Composer autoload"
```

**Esperado:**
```
Step 3: Verifying Composer autoload...
  → Checking AuthController class...
✓ Api\AuthController is autoloadable
✓ Frontend\AuthController is autoloadable
```

### 3. Teste o login na aplicação

```
https://qualidadehd.direcaoclinica.com.br
```

**Credenciais de teste:**
- Email: `admin@hemodialise.ma.gov.br`
- Senha: `admin123`

**Esperado:** Login bem-sucedido, sem erro 500

### 4. Verifique que Composer está disponível

```bash
docker exec <container-id> composer --version
```

**Esperado:** 
```
Composer version 2.x.x
```

## 🐛 Troubleshooting

### Erro persiste após redeploy?

```bash
# 1. Entre no container
docker exec -it <container-id> sh

# 2. Regenere o autoload manualmente
composer dump-autoload --optimize --classmap-authoritative --no-dev

# 3. Limpe os caches do Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache

# 4. Teste a classe
php -r "require 'vendor/autoload.php'; var_dump(class_exists('App\Http\Controllers\Api\AuthController'));"
```

### Container não inicia?

```bash
# Veja os logs completos
docker logs <container-id> --tail 200

# Procure por erros no entrypoint
docker logs <container-id> | grep -i error
```

### Rebuild não está funcionando?

```bash
# Force remove da imagem antiga
docker image prune -a

# Ou especificamente
docker rmi $(docker images | grep qualidade | awk '{print $3}')

# Depois faça rebuild
```

## 📊 Diferenças Antes vs Depois

| Verificação | Antes ❌ | Depois ✅ |
|-------------|----------|-----------|
| Composer em produção | Não | Sim |
| Autoload com estrutura completa | Não | Sim |
| Verificação no entrypoint | Não | Sim |
| Auto-correção de problemas | Não | Sim |
| Teste de classes críticas | Não | Sim |

## 📚 Documentação Completa

Para detalhes técnicos das melhorias:
- Ver: `DOCKERFILE-AUTOLOAD-FIX.md`

## ⏱️ Tempo Estimado

- **Build**: ~3-5 minutos
- **Deploy**: ~1-2 minutos
- **Inicialização**: ~30-60 segundos
- **Total**: ~5-8 minutos

## ✅ Checklist Final

- [ ] Código commitado e pushed para GitHub
- [ ] (Opcional) Teste local executado com sucesso
- [ ] Redeploy no Dokploy com **Force Rebuild** marcado
- [ ] Logs verificados (autoload OK)
- [ ] Container rodando e healthy
- [ ] Login testado com sucesso
- [ ] Erro 500 resolvido

---

**Última atualização:** 2025-10-23  
**Commit:** e9f0182  
**Status:** ✅ Pronto para Deploy
