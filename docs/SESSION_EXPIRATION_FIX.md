# Correção do Erro "Page Expired" no Filament

## Problema Identificado

Quando usuários logados no painel Filament deixavam a página aberta por algum tempo e depois tentavam interagir, recebiam o alerta:
```
This page has expired. Would you like to refresh the page?
```

Ao clicar em "OK", eram redirecionados para um erro **500 (Server Error)** em produção.

## Causa Raiz

1. **Expiração de Sessão**: A sessão do Laravel estava configurada com `SESSION_LIFETIME=120` (2 horas)
2. **CSRF Token Inválido**: Quando a sessão expirava, o CSRF token também se tornava inválido
3. **Erro 419 Não Tratado**: Ao tentar submeter uma requisição com token expirado, Laravel retornava erro 419 (Token Mismatch)
4. **Falta de Handler**: O erro 419 não tinha tratamento adequado, causando erro 500 ou comportamento inesperado

## Solução Implementada

### 1. Handler de Exceção para Token Mismatch (419)

Adicionado tratamento específico em `bootstrap/app.php` para capturar `TokenMismatchException`:

```php
$exceptions->render(function (TokenMismatchException $e, $request) {
    // Log the CSRF token mismatch
    \Log::warning('CSRF Token Mismatch detected', [
        'path' => $request->getPathInfo(),
        'method' => $request->getMethod(),
        'user' => auth()->id() ? auth()->user()->email : 'not authenticated',
        'referer' => $request->headers->get('referer'),
    ]);

    // If it's an AJAX request, return JSON response
    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Sua sessão expirou. Por favor, recarregue a página.',
            'redirect' => route('login'),
        ], 419);
    }

    // For Filament admin panel requests, redirect back with error message
    if (str_starts_with($request->getPathInfo(), '/admin')) {
        return redirect()->route('login')
            ->with('error', 'Sua sessão expirou. Por favor, faça login novamente.');
    }

    // For other web requests, redirect to login
    return redirect()->route('login')
        ->with('error', 'Sua sessão expirou. Por favor, faça login novamente.');
});
```

**Benefícios**:
- Captura erro 419 antes de virar erro 500
- Registra no log para monitoramento
- Diferencia entre requisições AJAX e web
- Redireciona usuário para login com mensagem clara
- Evita loops de redirecionamento

### 2. Aumento do Tempo de Vida da Sessão

Alterado `SESSION_LIFETIME` de **120 minutos (2h)** para **480 minutos (8h)**:

```env
SESSION_LIFETIME=480
```

**Justificativa**:
- Usuários do sistema de saúde podem deixar a tela aberta durante turnos
- 8 horas cobre um turno de trabalho completo
- Reduz interrupções e relogins durante uso normal

### 3. Configuração de Cookies para HTTPS

Ajustado `SESSION_SECURE_COOKIE` para **null** (auto-detecção):

```env
SESSION_SECURE_COOKIE=null
```

**Comportamento**:
- Em desenvolvimento (HTTP): cookies funcionam normalmente
- Em produção (HTTPS): cookies automaticamente marcados como `secure`
- Compatível com proxy reverso (Traefik) configurado em `bootstrap/app.php`

### 4. Configuração de Produção

Criado arquivo `.env.production.example` com configurações otimizadas para produção:

```env
SESSION_DRIVER=database
SESSION_LIFETIME=480
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.direcaoclinica.com.br
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=null
SESSION_HTTP_ONLY=true
```

**Configurações importantes**:
- `SESSION_DOMAIN=.direcaoclinica.com.br`: permite cookies em subdomínios
- `SESSION_SAME_SITE=lax`: equilibra segurança e usabilidade
- `SESSION_HTTP_ONLY=true`: previne acesso via JavaScript (segurança XSS)
- `SESSION_SECURE_COOKIE=null`: auto-detecta HTTPS

## Deployment em Produção

### Passos para Aplicar a Correção

1. **Fazer commit e push das alterações**:
```bash
git add bootstrap/app.php .env.production.example docs/SESSION_EXPIRATION_FIX.md
git commit -m "fix: add TokenMismatchException handler and increase session lifetime"
git push origin main
```

2. **Atualizar variáveis de ambiente no Dokploy**:
   - Acessar painel Dokploy
   - Navegar até o projeto `qualidade-qualidadehd`
   - Adicionar/atualizar variáveis de ambiente:
     ```
     SESSION_LIFETIME=480
     SESSION_SECURE_COOKIE=null
     SESSION_DOMAIN=.direcaoclinica.com.br
     ```

3. **Verificar configuração de proxy reverso** (Traefik):
   - Garantir que headers `X-Forwarded-*` estão sendo enviados
   - Já configurado em `bootstrap/app.php`:
     ```php
     $middleware->trustProxies(at: '*');
     ```

4. **Rebuild do container** (automático via Dokploy ao detectar push)

5. **Limpar cache em produção**:
```bash
# Via SSH
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175 \
  "docker exec \$(docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1) \
  sh -c 'cd /var/www/html && php artisan optimize:clear && php artisan config:cache'"
```

### Monitoramento Pós-Deploy

Verificar logs para confirmar que o handler está funcionando:

```bash
# Verificar logs de Token Mismatch
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175 \
  "docker exec \$(docker ps --filter 'name=qualidade-qualidadehd' --format '{{.Names}}' | head -1) \
  tail -100 /var/www/html/storage/logs/laravel-\$(date +%Y-%m-%d).log | grep 'CSRF Token Mismatch'"
```

## Testes Recomendados

1. **Teste de Expiração de Sessão**:
   - Fazer login no Filament
   - Deixar a página aberta por 8+ horas OU manualmente limpar sessão no banco
   - Tentar interagir com qualquer recurso
   - **Esperado**: Redirecionamento suave para login com mensagem clara

2. **Teste AJAX**:
   - Fazer login no Filament
   - Usar Developer Tools para limpar cookies
   - Tentar salvar algum recurso
   - **Esperado**: Resposta JSON com código 419 e mensagem de sessão expirada

3. **Teste de HTTPS/Cookies**:
   - Verificar no Developer Tools que cookies estão marcados como `Secure` em produção
   - Verificar que `SameSite=Lax` está configurado

## Arquitetura de Sessão do Sistema

O sistema usa **autenticação dual**:
- **Session-based** (Filament admin): afetado por essa correção
- **JWT-based** (Mobile API): não afetado, tokens gerenciados separadamente

Esta correção afeta apenas o painel administrativo Filament.

## Prevenção Futura

1. **Monitorar logs** periodicamente para verificar frequência de expiração
2. **Ajustar `SESSION_LIFETIME`** conforme padrões de uso observados
3. **Considerar implementar** "keep-alive" ping para usuários ativos
4. **Documentar** em onboarding que sessão expira após 8h de inatividade

## Referências

- [Laravel Session Configuration](https://laravel.com/docs/11.x/session)
- [Filament Authentication](https://filamentphp.com/docs/3.x/panels/users#authentication)
- [Laravel Exception Handling](https://laravel.com/docs/11.x/errors)
- [CSRF Protection](https://laravel.com/docs/11.x/csrf)
