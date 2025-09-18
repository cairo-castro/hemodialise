# 🚀 Guia de Instalação - Sistema de Hemodiálise

## 📋 Pré-requisitos

### Requisitos do Sistema
- **PHP**: 8.3 ou superior
- **Composer**: 2.0 ou superior
- **Node.js**: 18.0 ou superior
- **NPM**: 8.0 ou superior
- **Banco de Dados**: MariaDB 10.3+ ou MySQL 8.0+

### Extensões PHP Necessárias
```bash
# Ubuntu/Debian
sudo apt install php8.3-cli php8.3-fpm php8.3-mysql php8.3-xml php8.3-curl php8.3-zip php8.3-mbstring php8.3-intl php8.3-bcmath

# CentOS/RHEL
sudo yum install php83-cli php83-fpm php83-mysqlnd php83-xml php83-curl php83-zip php83-mbstring php83-intl php83-bcmath
```

## 🔧 Instalação Rápida (Recomendada)

### 1. Clone do Repositório
```bash
git clone https://github.com/cairo-castro/hemodialise.git
cd hemodialise
```

### 2. Script Automatizado
```bash
# Torna o script executável
chmod +x setup.sh

# Executa instalação completa
./setup.sh
```

O script automatizado irá:
- Instalar dependências PHP (Composer)
- Instalar dependências JavaScript (NPM)
- Configurar arquivo .env
- Gerar chave da aplicação
- Configurar banco de dados
- Executar migrações e seeders
- Compilar assets
- Iniciar o servidor

## 🛠️ Instalação Manual

### 1. Preparação do Ambiente

#### Banco de Dados MariaDB
```bash
# Instalar MariaDB
sudo apt install mariadb-server mariadb-client

# Configurar MariaDB
sudo mysql_secure_installation

# Conectar ao MariaDB
sudo mysql -u root -p

# Criar banco de dados
CREATE DATABASE hemodialise CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hemodialise_user'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON hemodialise.* TO 'hemodialise_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Dependências PHP
```bash
# Instalar dependências do Composer
composer install

# Para produção (otimizado)
composer install --optimize-autoloader --no-dev
```

### 3. Configuração da Aplicação
```bash
# Copiar arquivo de configuração
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Gerar chave JWT
php artisan jwt:secret
```

### 4. Configurar .env
Edite o arquivo `.env` com suas configurações:

```env
# Configurações básicas
APP_NAME="Sistema Hemodiálise"
APP_ENV=local
APP_KEY=base64:sua_chave_gerada
APP_DEBUG=true
APP_TIMEZONE=America/Sao_Paulo
APP_URL=http://localhost:8000

# Banco de dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hemodialise
DB_USERNAME=hemodialise_user
DB_PASSWORD=senha_segura

# JWT (gerado automaticamente)
JWT_SECRET=sua_chave_jwt_secreta

# Sessão
SESSION_DRIVER=database
SESSION_LIFETIME=1440

# Cache
CACHE_STORE=database

# Mail (configurar se necessário)
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
```

### 5. Base de Dados
```bash
# Executar migrações
php artisan migrate

# Popular com dados de exemplo
php artisan db:seed

# Ou fazer tudo de uma vez
php artisan migrate:fresh --seed
```

### 6. Assets Frontend
```bash
# Instalar dependências JavaScript
npm install

# Compilar para desenvolvimento
npm run dev

# Ou compilar para produção
npm run build
```

### 7. Permissões de Arquivos
```bash
# Definir permissões corretas
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 🌐 Configuração do Servidor Web

### Apache (.htaccess já incluído)
```apache
<VirtualHost *:80>
    ServerName hemodialise.local
    DocumentRoot /var/www/hemodialise/public

    <Directory /var/www/hemodialise/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/hemodialise_error.log
    CustomLog ${APACHE_LOG_DIR}/hemodialise_access.log combined
</VirtualHost>
```

### Nginx
```nginx
server {
    listen 80;
    server_name hemodialise.local;
    root /var/www/hemodialise/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 🚀 Iniciando o Sistema

### Desenvolvimento
```bash
# Servidor built-in do PHP
php artisan serve

# Ou usando o script automatizado
./run.sh

# Com porta específica
php artisan serve --port=8080

# Acessível externamente
php artisan serve --host=0.0.0.0 --port=8000
```

### Produção
```bash
# Otimizações de cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpar caches (se necessário)
php artisan optimize:clear
```

## 👤 Usuários Padrão

Após a instalação, os seguintes usuários estarão disponíveis:

### Administrador
- **Email**: admin@hemodialise.com
- **Senha**: admin123
- **Acesso**: http://localhost:8000/admin

### Técnico
- **Email**: tecnico.joao@hemodialise.com
- **Senha**: tecnico123
- **Acesso**: http://localhost:8000/mobile

## 🧪 Verificação da Instalação

### Testes Básicos
```bash
# Executar testes
php artisan test

# Verificar configuração
php artisan about

# Verificar rotas
php artisan route:list
```

### Checklist de Verificação
- [ ] Página inicial redireciona para login
- [ ] Login de admin funciona
- [ ] Painel administrativo carrega
- [ ] Login de técnico funciona
- [ ] Interface móvel carrega
- [ ] APIs respondem corretamente
- [ ] Assets estão sendo servidos

### URLs de Teste
- **Login**: http://localhost:8000/login
- **Admin**: http://localhost:8000/admin
- **Mobile**: http://localhost:8000/mobile
- **API Status**: http://localhost:8000/api/login (POST)

## 🐛 Problemas Comuns

### Erro 500 - Internal Server Error
```bash
# Verificar logs
tail -f storage/logs/laravel.log

# Verificar permissões
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Erro de Conexão com Banco
```bash
# Verificar configuração
php artisan config:clear

# Testar conexão
php artisan migrate:status

# Verificar credenciais no .env
cat .env | grep DB_
```

### Assets não carregam
```bash
# Recompilar assets
npm run build

# Limpar cache
php artisan view:clear

# Verificar manifesto Vite
ls -la public/build/
```

### JWT não funciona
```bash
# Regenerar chave JWT
php artisan jwt:secret --force

# Verificar configuração
php artisan config:clear

# Testar endpoint
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@hemodialise.com","password":"admin123"}'
```

## 🔧 Scripts de Utilitários

### setup.sh
```bash
#!/bin/bash
echo "🚀 Instalando Sistema de Hemodiálise..."

# Instalar dependências
composer install
npm install

# Configurar ambiente
cp .env.example .env
php artisan key:generate
php artisan jwt:secret --force

# Configurar banco
php artisan migrate:fresh --seed

# Compilar assets
npm run build

echo "✅ Instalação concluída!"
echo "🌐 Acesse: http://localhost:8000"
```

### run.sh
```bash
#!/bin/bash
echo "🌐 Iniciando servidor..."

# Matar processos existentes na porta 8000
lsof -ti:8000 | xargs kill -9 2>/dev/null || true

# Iniciar servidor
php artisan serve --host=0.0.0.0 --port=8000
```

## 📊 Monitoramento

### Logs do Sistema
```bash
# Logs em tempo real
tail -f storage/logs/laravel.log

# Logs estruturados (Laravel Pail)
php artisan pail

# Logs por filtro
php artisan pail --filter="level=error"
```

### Performance
```bash
# Otimizações
composer dump-autoload --optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Debug de queries
php artisan db:monitor
```

## 🔄 Atualizações

### Atualizando o Sistema
```bash
# Fazer backup do banco
mysqldump -u user -p hemodialise > backup.sql

# Baixar atualizações
git pull origin main

# Atualizar dependências
composer install
npm install

# Executar migrações
php artisan migrate

# Recompilar assets
npm run build

# Limpar caches
php artisan optimize:clear
```

---

**Sistema de Hemodiálise - Guia de Instalação v1.0**
*Atualizado em: Janeiro 2025*