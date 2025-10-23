# Variáveis de Ambiente para Dokploy

Configure estas variáveis de ambiente no painel do Dokploy para o projeto **qualidadeHD**:

## Aplicação
```
APP_NAME="Sistema Hemodiálise - Qualidade"
APP_ENV=production
APP_KEY=base64:H2UOVPoDW9emzGrn1Rx2EV15YOsosTwPtvPUPmsVph0=
APP_DEBUG=false
APP_URL=https://qualidadehd.direcaoclinica.com.br
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR
```

## Banco de Dados (MariaDB)
```
DB_CONNECTION=mariadb
DB_HOST=qualidade-productionqualidade-l2xbgb
DB_PORT=3306
DB_DATABASE=hemodialise_gqa
DB_USERNAME=Usr_QltGest@2025
DB_PASSWORD=Qlt!H0sp#2025
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

## Cache & Sessão
```
CACHE_STORE=database
SESSION_DRIVER=database
SESSION_LIFETIME=480
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
```

## JWT
```
JWT_SECRET=NHJVkr3HSHQM1D7BxhToHm5rB4EffTsCvHpyhjljiygtoIqFZAySrokYTpnHlNxX
JWT_TTL=60
JWT_REFRESH_TTL=20160
```

## Inicialização
```
RUN_MIGRATIONS=true
RUN_SEEDERS=false
```

## Logging
```
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

## Como configurar no Dokploy:

1. Acesse: http://212.85.1.175:3000
2. Faça login no Dokploy
3. Encontre o projeto "qualidadeHD" ou "qualidade-qualidadehd-bue1bg"
4. Vá para a seção "Environment Variables"
5. Cole todas as variáveis acima
6. Salve e faça o redeploy

**IMPORTANTE:** Certifique-se de que `DB_CONNECTION=mariadb` está configurado para evitar o erro de SQLite.
