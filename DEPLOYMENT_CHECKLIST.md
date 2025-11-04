# Checklist de Deployment - Correção de Expiração de Sessão

## Status: ✅ Código Commitado e Pushed

- ✅ Commit `2e2709b` criado com todas as correções
- ✅ Push para `origin/main` concluído
- ⏳ Aguardando rebuild automático do Dokploy

## Próximos Passos - Configuração no Dokploy

### 1. Acessar Painel Dokploy

1. Acesse: `https://dokploy.direcaoclinica.com.br` (ou URL do painel Dokploy)
2. Faça login com suas credenciais
3. Navegue até o projeto: **qualidade-qualidadehd**

### 2. Atualizar Variáveis de Ambiente

Adicione ou atualize as seguintes variáveis de ambiente:

```env
# Aumentar tempo de vida da sessão para 8 horas
SESSION_LIFETIME=480

# Auto-detectar HTTPS para cookies seguros
SESSION_SECURE_COOKIE=null

# Configurar domínio para compartilhamento de cookies em subdomínios
SESSION_DOMAIN=.direcaoclinica.com.br

# Garantir que APP_URL está correto
APP_URL=https://qualidadehd.direcaoclinica.com.br

# Garantir que APP_ENV está em produção
APP_ENV=production

# Desabilitar debug em produção
APP_DEBUG=false

# Configurar nível de log para error
LOG_LEVEL=error
```

### 3. Verificar Container Rebuild

O Dokploy deve automaticamente:
1. Detectar o push no GitHub
2. Fazer rebuild do container Docker
3. Aplicar as novas configurações

**Verificar status via SSH:**
```bash
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175 \
  "docker ps --filter 'name=qualidade-qualidadehd'"
```

### 4. Limpar Cache Após Deployment

Após o container ser atualizado, executar:

```bash
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175 \
  "docker exec \$(docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1) \
  sh -c 'cd /var/www/html && php artisan optimize:clear && php artisan config:cache && php artisan route:cache'"
```

### 5. Testar a Correção

#### Teste 1: Verificar Variáveis de Ambiente
```bash
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175 \
  "docker exec \$(docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1) \
  sh -c 'cd /var/www/html && php artisan tinker --execute=\"echo config(\\\"session.lifetime\\\");\"'"
```
**Esperado:** `480`

#### Teste 2: Verificar Handler de Exceção
1. Fazer login no Filament: `https://qualidadehd.direcaoclinica.com.br/admin`
2. Abrir Developer Tools → Application → Cookies
3. Deletar cookie de sessão manualmente
4. Tentar acessar qualquer recurso no Filament
5. **Esperado:** Redirecionamento suave para `/login` com mensagem "Sua sessão expirou"

#### Teste 3: Monitorar Logs
```bash
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175 \
  "docker exec \$(docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1) \
  tail -50 /var/www/html/storage/logs/laravel-\$(date +%Y-%m-%d).log"
```

Procurar por:
- ✅ `CSRF Token Mismatch detected` (log de warning, não erro)
- ❌ Ausência de stack traces de erro 500

### 6. Validação em Produção

Checklist final:

- [ ] Container foi rebuilded com sucesso
- [ ] Variáveis de ambiente foram atualizadas no Dokploy
- [ ] Cache foi limpo (`optimize:clear`, `config:cache`)
- [ ] `SESSION_LIFETIME` configurado como `480`
- [ ] `SESSION_SECURE_COOKIE` configurado como `null`
- [ ] `SESSION_DOMAIN` configurado como `.direcaoclinica.com.br`
- [ ] Teste de expiração de sessão funciona corretamente
- [ ] Logs mostram tratamento adequado de Token Mismatch
- [ ] Não há mais erros 500 após expiração de sessão

## Monitoramento Pós-Deployment

### Verificar Logs Periodicamente (primeiras 48h)

```bash
# Ver logs em tempo real
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175 \
  "docker logs \$(docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1) -f"
```

### Métricas para Acompanhar

1. **Frequência de Token Mismatch**:
   - Buscar por `CSRF Token Mismatch detected` nos logs
   - Se muito frequente, considerar aumentar ainda mais `SESSION_LIFETIME`

2. **Erros 500 Relacionados a Sessão**:
   - Devem desaparecer completamente
   - Se persistirem, investigar stack trace

3. **Reclamações de Usuários**:
   - Monitorar feedback sobre logout inesperado
   - 8 horas deve ser suficiente para turno de trabalho

## Rollback (Se Necessário)

Se algo der errado, reverter configurações:

1. No Dokploy, alterar variáveis para valores anteriores:
   ```env
   SESSION_LIFETIME=120
   SESSION_SECURE_COOKIE=false
   ```

2. Fazer rollback do código:
   ```bash
   git revert 2e2709b
   git push origin main
   ```

3. Limpar cache novamente:
   ```bash
   php artisan optimize:clear && php artisan config:cache
   ```

## Documentação Adicional

Para detalhes técnicos completos, consultar:
- [docs/SESSION_EXPIRATION_FIX.md](docs/SESSION_EXPIRATION_FIX.md)

---

**Data do Deployment:** 2025-11-04
**Commit:** `2e2709b`
**Responsável:** Cairo Castro (com assistência Claude Code)
