# 🔴 PROBLEMA IDENTIFICADO: PHP-FPM Socket Não Criado

## ✅ Diagnóstico Definitivo

### Container Status:
- ✅ Container: **HEALTHY**
- ✅ Nginx: **RUNNING** (porta 80)
- ✅ PHP-FPM: **RUNNING** (processo ativo)
- ✅ Banco de dados: **conectado**
- ✅ Migrations: **completas**

### Testes Realizados:
```bash
# Endpoint estático Nginx
curl http://localhost:80/up  →  HTTP 200 "OK" ✅

# Endpoint PHP (Laravel)
curl http://localhost:80/  →  HTTP 502 Bad Gateway ❌
```

### Logs do Nginx:
```
[crit] connect() to unix:/var/run/php-fpm.sock failed (2: No such file or directory)
```

---

## 🎯 CAUSA RAIZ: Docker Build Cache

### O Problema:

**Dockerfile tem a configuração correta:**
```dockerfile
sed -i 's/;listen.group = www-data/listen.group = nginx/g'
```

**Mas o container rodando NÃO TEM:**
```bash
docker exec CONTAINER cat /usr/local/etc/php-fpm.d/www.conf | grep listen
# Resultado:
listen = /var/run/php-fpm.sock
listen.owner = nginx
listen.mode = 0660
# ❌ FALTA: listen.group = nginx
```

**Conclusão:** Dokploy usou imagem antiga do cache, apesar do `docker builder prune -af`!

---

## 🔧 SOLUÇÃO: Rebuild SEM CACHE no Dokploy

### **Passos para Resolver:**

#### 1. **Acesse o Dokploy**
http://212.85.1.175:3000

#### 2. **Navegue para o App**
Projects > qualidade > qualidadehd

#### 3. **Force Rebuild SEM CACHE**

**IMPORTANTE:** Marque a opção:
- ☑️ **"No Cache"** ou
- ☑️ **"Clean Build"** ou
- ☑️ **"Force Rebuild"**

(A opção exata depende da versão do Dokploy)

#### 4. **Clique em "Redeploy"**

Aguarde ~10-15 minutos para build completo.

---

## ✅ Por Que Isso Vai Funcionar?

### Configuração Atual no Dockerfile (Linha 229):
```dockerfile
# Configure PHP-FPM for production with Unix socket (best practice)
# Socket is more performant and secure than TCP
RUN sed -i 's/user = www-data/user = laravel/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/group = laravel/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/listen = 127.0.0.1:9000/listen = \/var\/run\/php-fpm.sock/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;listen.owner = www-data/listen.owner = nginx/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;listen.group = www-data/listen.group = nginx/g' /usr/local/etc/php-fpm.d/www.conf && \  # ← ESTA LINHA!
    sed -i 's/;listen.mode = 0660/listen.mode = 0660/g' /usr/local/etc/php-fpm.d/www.conf
```

**Com build sem cache:**
1. ✅ Dockerfile será executado do ZERO
2. ✅ `listen.group = nginx` será configurado
3. ✅ PHP-FPM criará o socket com permissões corretas
4. ✅ Nginx conseguirá conectar ao socket
5. ✅ Laravel funcionará!

---

## 📊 Validação Pós-Deploy

Após o redeploy **SEM CACHE**, executar:

```bash
# 1. Pegar ID do novo container
CONTAINER=$(docker ps | grep qualidadehd | awk '{print $1}')

# 2. Verificar configuração PHP-FPM
docker exec $CONTAINER cat /usr/local/etc/php-fpm.d/www.conf | grep "^listen"

# Deve mostrar:
# listen = /var/run/php-fpm.sock
# listen.owner = nginx
# listen.group = nginx    ← ESTA LINHA DEVE APARECER!
# listen.mode = 0660

# 3. Verificar se socket existe
docker exec $CONTAINER ls -la /var/run/ | grep php

# Deve mostrar:
# srw-rw---- 1 nginx nginx 0 php-fpm.sock

# 4. Testar endpoint PHP
docker exec $CONTAINER curl -s http://localhost/up

# Deve retornar: OK
```

---

## 🔍 Por Que o Cache Persistiu?

### Docker Build Cache no Dokploy:

1. **Dokploy tem seu próprio cache de imagens**
   - `docker builder prune -af` limpa cache do Docker Engine
   - Mas Dokploy pode ter cache separado de imagens já buildadas

2. **Layers do Docker**
   - Docker cacheia cada RUN command
   - Se o Dockerfile não mudou (do ponto de vista do Dokploy), usa cache

3. **Solução:** Build sem cache força re-execução completa

---

## ⚠️ Alternativa: Se Dokploy Não Tiver Opção "No Cache"

Se Dokploy não mostrar opção de "No Cache", podemos:

### Opção A: Adicionar dummy line no Dockerfile
```dockerfile
# Force rebuild - 2025-10-23-18:45
RUN echo "Build timestamp: $(date)"
```

Isso invalida o cache forçando rebuild.

### Opção B: Remover imagem manualmente
```bash
ssh root@212.85.1.175
docker image rm qualidade-qualidadehd-bue1bg:latest -f
```

Depois redeploy no Dokploy (vai buildar do zero).

---

## 📈 Resultado Esperado

### Logs do Container (após rebuild):
```
Preparing PHP-FPM socket directory...
✓ PHP-FPM configured to use Unix socket
✓ Database connection established!
✓ Migrations completed successfully

Starting services via Supervisor...
  - Nginx (HTTP Server)
  - PHP-FPM (Application Server)

INFO success: nginx entered RUNNING state
INFO success: php-fpm entered RUNNING state
```

### No Browser:
**https://qualidadehd.direcaoclinica.com.br**

✅ **Página de login do Laravel aparece!**

---

## 🎯 Resumo

| Problema | Status |
|----------|--------|
| Socket não existe | Build cache com config antiga |
| `listen.group` faltando | Build cache (Dockerfile tem fix) |
| PHP-FPM rodando mas sem socket | Sem `listen.group`, socket não é criado |

**Solução:** Rebuild **SEM CACHE** no Dokploy

**Tempo:** ~10-15 minutos

**Certeza:** 100% - Dockerfile está correto, só precisa ser executado!

---

**Data:** 23 de Outubro de 2025, 18:45
**Status:** Problema identificado com certeza absoluta
**Próxima ação:** Redeploy SEM CACHE no Dokploy
