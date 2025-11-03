# Configuração de Email - Gmail SMTP

Este documento descreve como configurar o envio de emails no Sistema de Hemodiálise usando Gmail SMTP.

## ✅ Configuração Local (Desenvolvimento)

O arquivo `.env` local já foi configurado com as seguintes variáveis:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=emserhq@gmail.com
MAIL_PASSWORD="dhay wwwg klly wrti"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=emserhq@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

## 🚀 Configuração no Dokploy (Produção)

Adicione as seguintes variáveis de ambiente no Dokploy:

```env
# ===================================
# MAIL CONFIGURATION - Gmail SMTP
# ===================================
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=emserhq@gmail.com
MAIL_PASSWORD=dhay wwwg klly wrti
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=emserhq@gmail.com
MAIL_FROM_NAME=Sistema Hemodiálise - Qualidade
```

### Passos no Dokploy:

1. Acesse o painel do Dokploy
2. Vá até o projeto "qualidade"
3. Clique em "Environment Variables"
4. Adicione cada uma das variáveis acima
5. Salve e faça o redeploy do container

## 🧪 Testando a Configuração

### Localmente:

```bash
# Limpar cache de configuração
php artisan config:clear

# Enviar email de teste
php artisan mail:test emserhq@gmail.com

# Ou para outro email
php artisan mail:test seuemail@exemplo.com
```

### Em Produção (via SSH):

```bash
# Conectar ao servidor
sshpass -p 'ClinQua-Hosp@2025' ssh -o StrictHostKeyChecking=no root@212.85.1.175

# Encontrar o container
CONTAINER_ID=$(docker ps --filter "name=qualidade-qualidadehd" --format "{{.Names}}" | head -1)

# Executar o comando de teste
docker exec $CONTAINER_ID php artisan mail:test emserhq@gmail.com
```

## 📧 Como o Gmail App Password foi Obtido

1. ✅ **2-Factor Authentication habilitado** na conta emserhq@gmail.com
2. ✅ **App Password gerado** nas configurações de segurança do Google
3. ✅ **Senha de 16 caracteres:** `dhay wwwg klly wrti`

## ⚠️ Importante

- **NÃO use a senha normal do Gmail** - use apenas o App Password
- O App Password tem formato: `xxxx xxxx xxxx xxxx` (16 caracteres com espaços)
- Não é necessário remover os espaços - o Laravel trata automaticamente
- Se o envio falhar, verifique:
  - ✅ 2FA está habilitado na conta Gmail
  - ✅ App Password está correto
  - ✅ Porta 587 está liberada no firewall
  - ✅ TLS está configurado corretamente

## 📊 Limites do Gmail SMTP

- **Emails por dia:** 500 (conta Gmail gratuita)
- **Emails por dia:** 2000 (Google Workspace)
- **Destinatários por mensagem:** 100

Se exceder esses limites, o Gmail pode temporariamente bloquear o envio.

## 🎯 Funcionalidades que Usarão Email

Uma vez configurado, o sistema enviará emails para:

1. **Recuperação de senha** - Link para reset de senha
2. **Notificações de checklist** - Alertas de checklists pendentes
3. **Alertas de manutenção** - Notificações de manutenções programadas
4. **Relatórios semanais** - Resumo semanal de atividades
5. **Atualizações do sistema** - Notificações importantes

## 🔧 Troubleshooting

### Erro: "Could not authenticate"
- Verifique se o App Password está correto
- Confirme que 2FA está habilitado

### Erro: "Connection refused"
- Verifique se a porta 587 está liberada
- Tente porta 465 com `MAIL_ENCRYPTION=ssl`

### Erro: "Too many login attempts"
- Gmail detectou muitas tentativas - aguarde 15-30 minutos
- Verifique se há múltiplas instâncias tentando enviar

### Email não chega na caixa de entrada
- Verifique pasta de SPAM
- Adicione emserhq@gmail.com aos contatos confiáveis
- Aguarde alguns minutos (pode haver delay)

## 📝 Arquivo de Configuração

O arquivo `config/mail.php` foi atualizado para incluir suporte a encryption:

```php
'smtp' => [
    'transport' => 'smtp',
    'scheme' => env('MAIL_SCHEME'),
    'url' => env('MAIL_URL'),
    'host' => env('MAIL_HOST', '127.0.0.1'),
    'port' => env('MAIL_PORT', 2525),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'), // ← Adicionado
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'timeout' => null,
    'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
],
```

## 🔐 Segurança

- ❌ **NUNCA** commite arquivos .env com senhas reais
- ✅ Use variáveis de ambiente no Dokploy
- ✅ App Password é mais seguro que senha normal
- ✅ Pode revogar App Password sem alterar senha do Gmail
- ✅ Cada aplicação deve ter seu próprio App Password

## 📚 Referências

- [Laravel Mail Documentation](https://laravel.com/docs/11.x/mail)
- [Gmail SMTP Settings](https://support.google.com/mail/answer/7126229)
- [Google App Passwords](https://support.google.com/accounts/answer/185833)
