# 🏥 Sistema de Hemodiálise

Sistema de gestão para clínicas de hemodiálise, desenvolvido com Laravel e Filament, otimizado para uso em dispositivos móveis.

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-FFAA00?style=for-the-badge&logo=php&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-07405E?style=for-the-badge&logo=sqlite&logoColor=white)

## 📋 Sobre o Projeto

Este sistema foi desenvolvido para digitalizar e modernizar o controle de procedimentos em clínicas de hemodiálise, baseado nas planilhas de controle manual utilizadas pelo Estado do Maranhão - Secretaria de Saúde.

### 🎯 Objetivo
- Substituir planilhas manuais por sistema digital
- Garantir maior segurança e rastreabilidade
- Facilitar o acesso via dispositivos móveis
- Melhorar a eficiência dos registros
- Reduzir erros humanos

## 🚀 Funcionalidades

### 👥 Gestão de Pacientes
- ✅ Cadastro completo de pacientes
- ✅ Prontuário médico único
- ✅ Tipo sanguíneo e alergias
- ✅ Cálculo automático de idade
- ✅ Histórico de sessões

### 🏭 Gestão de Máquinas
- ✅ Cadastro de equipamentos de hemodiálise
- ✅ Identificação única por máquina
- ✅ Status ativo/inativo
- ✅ Controle de manutenção

### 🔍 Checklist de Segurança do Paciente
- ✅ Verificações pré-diálise obrigatórias
- ✅ Controle por turnos (manhã/tarde/noite)
- ✅ 8 itens de verificação padronizados
- ✅ Cálculo automático de % completude
- ✅ Registro de incidentes

### 🧽 Controle de Limpeza e Desinfecção
- ✅ Limpeza diária, semanal e mensal
- ✅ Registro de produtos utilizados
- ✅ Procedimentos de limpeza detalhados
- ✅ Assinatura digital do responsável

### 🧪 Desinfecção Química
- ✅ Controle rigoroso do processo
- ✅ Registro de produtos e concentrações
- ✅ Monitoramento de temperatura
- ✅ Tempo de contato e eficácia
- ✅ Rastreabilidade por lote

## 📱 Interface Mobile-First

- **Responsiva**: Otimizada para smartphones e tablets
- **SPA**: Navegação rápida sem recarregamento
- **Sidebar Colapsível**: Melhor aproveitamento de tela
- **Formulários Touch-Friendly**: Campos otimizados para toque
- **Filament Admin**: Interface moderna e intuitiva

## 🛠️ Tecnologias Utilizadas

- **Backend**: Laravel 12.x
- **Frontend**: Filament Admin Panel v3
- **Database**: SQLite (desenvolvimento)
- **PHP**: 8.3+
- **CSS Framework**: Tailwind CSS
- **Icons**: Heroicons
- **Package Manager**: Composer & NPM

## 📦 Instalação

### Pré-requisitos
- PHP 8.3 ou superior
- Composer
- Node.js & NPM
- Extensões PHP: `intl`, `pdo_sqlite`, `mbstring`, `xml`

### Passo a passo

1. **Clone o repositório**
```bash
git clone https://github.com/cairo-castro/hemodialise.git
cd hemodialise
```

2. **Instale as dependências PHP**
```bash
composer install
```

3. **Instale as dependências Node.js**
```bash
npm install
```

4. **Configure o ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure o banco de dados**
```bash
# O arquivo .env já está configurado para SQLite
# Certifique-se que o arquivo database/database.sqlite existe
touch database/database.sqlite
```

6. **Execute as migrations e seeders**
```bash
php artisan migrate --seed
```

7. **Compile os assets**
```bash
npm run build
```

8. **Inicie o servidor**
```bash
php artisan serve
```

## 🔑 Acesso ao Sistema

- **URL**: http://localhost:8000/admin
- **Email**: admin@hemodialise.com
- **Senha**: admin123

## 📊 Estrutura do Banco de Dados

### Tabelas Principais

| Tabela | Descrição |
|--------|-----------|
| `users` | Usuários do sistema |
| `machines` | Máquinas de hemodiálise |
| `patients` | Pacientes cadastrados |
| `safety_checklists` | Checklists de segurança |
| `cleaning_controls` | Controles de limpeza |
| `chemical_disinfections` | Desinfecções químicas |

### Relacionamentos
- **Patient** → HasMany → **SafetyChecklist**
- **Machine** → HasMany → **SafetyChecklist, CleaningControl, ChemicalDisinfection**
- **User** → HasMany → **SafetyChecklist, CleaningControl, ChemicalDisinfection**

## 🎨 Customização

### Cores do Sistema
```php
// app/Providers/Filament/AdminPanelProvider.php
->colors([
    'primary' => Color::Blue,
])
```

### Adicionar Novos Recursos
```bash
php artisan make:filament-resource NomeDoRecurso --generate
```

## 📱 Uso Mobile

### Recursos Otimizados
- **Sidebar responsiva**: Colapsa automaticamente em telas pequenas
- **Formulários adaptáveis**: Campos se ajustam ao tamanho da tela
- **Tabelas scrolláveis**: Navegação horizontal em tabelas grandes
- **Botões touch-friendly**: Tamanho adequado para dedos

### PWA (Progressive Web App)
O sistema pode ser instalado como um app no smartphone:
1. Acesse pelo navegador móvel
2. Toque em "Adicionar à tela inicial"
3. Use como um app nativo

## 🔧 Scripts Úteis

```bash
# Resetar banco de dados com dados de exemplo
php artisan migrate:fresh --seed

# Criar novo usuário admin
php artisan make:filament-user

# Limpar cache
php artisan optimize:clear

# Executar testes
php artisan test
```

## 📈 Monitoramento

### Logs do Sistema
```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Logs específicos do Filament
php artisan log:show
```

### Performance
- **Cache**: Configurado para SQLite em desenvolvimento
- **Session**: Armazenamento em banco de dados
- **Queue**: Configuração síncrona para desenvolvimento

## 🚀 Deploy em Produção

### Preparação
```bash
# Otimizar para produção
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

### Banco de Dados de Produção
Para produção, recomenda-se MySQL ou PostgreSQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hemodialise
DB_USERNAME=root
DB_PASSWORD=
```

### Servidor Web
Configure o document root para apontar para a pasta `public/`

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 🆘 Suporte

Para suporte técnico ou dúvidas:
- 📧 Email: suporte@hemodialise.com
- 📱 WhatsApp: (85) 9xxxx-xxxx
- 🐛 Issues: [GitHub Issues](https://github.com/cairo-castro/hemodialise/issues)

## 🏥 Baseado em Normas

Este sistema segue as diretrizes e formulários utilizados pela:
- **Secretaria de Estado da Saúde do Maranhão**
- **Secretaria Adjunta de Assistência à Saúde**
- **Normas de Segurança em Hemodiálise**

## 📸 Screenshots

### Dashboard Principal
![Dashboard](docs/images/dashboard.png)

### Checklist Mobile
![Mobile Checklist](docs/images/mobile-checklist.png)

### Gestão de Pacientes
![Patients](docs/images/patients.png)

---

Desenvolvido com ❤️ para a comunidade de saúde brasileira
