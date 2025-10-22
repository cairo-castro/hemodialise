# 👥 Usuários Globais Adicionados ao Sistema

## ✅ Alterações Realizadas

Adicionados **2 novos usuários globais** ao `UserSeeder.php`:

---

## 📊 Usuários Globais do Sistema

### 1. 👨‍💼 Administrador (já existia)
```
Email: admin@hemodialise.ma.gov.br
Senha: admin123
Role: super-admin
Acesso: Total (todas as funcionalidades)
Tipo: Usuário Global (sem unidade específica)
```

### 2. 👔 Gerente Global (NOVO)
```
Nome: Joenvilly Azevedo
Email: joenvilly.azevedo@emserh.ma.gov.br
Senha: A123456*
Role: gestor-unidade
Acesso: Gestão em todas as unidades
Tipo: Usuário Global (sem unidade específica)
Default View: Desktop
```

### 3. 🔧 Coordenador Global (NOVO)
```
Nome: André Campos
Email: andre.campos@emserh.ma.gov.br
Senha: A123456*
Role: coordenador
Acesso: Coordenação em todas as unidades
Tipo: Usuário Global (sem unidade específica)
Default View: Desktop
```

---

## 🔐 Características dos Usuários Globais

✅ **unit_id = null** - Não estão vinculados a nenhuma unidade específica
✅ **Acesso a todas as unidades** - Podem visualizar dados de qualquer unidade
✅ **Email verificado** - Não precisam verificar email para fazer login
✅ **Senhas criptografadas** - Usando bcrypt para segurança
✅ **Default view: desktop** - Interface desktop por padrão

---

## 📝 Como Usar

### Para executar o seeder:

```bash
# Rodar apenas o UserSeeder
php artisan db:seed --class=UserSeeder

# Ou rodar todos os seeders
php artisan db:seed
```

### Para resetar e rodar novamente:

```bash
# Limpar banco e rodar migrations + seeders
php artisan migrate:fresh --seed
```

---

## 🎯 Hierarquia de Permissões

```
┌─────────────────────────────────────────┐
│         SUPER-ADMIN (Admin)             │
│     Acesso total ao sistema             │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│    GESTOR GLOBAL (Joenvilly Azevedo)    │
│  Gestão em todas as unidades            │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│ COORDENADOR GLOBAL (André Campos)       │
│  Coordenação em todas as unidades       │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│     GESTORES DE UNIDADES (26)           │
│  Gestão em unidades específicas         │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│    COORDENADORES/SUPERVISORES           │
│  Coordenação em unidades específicas    │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│         TÉCNICOS                         │
│  Operação em unidades específicas       │
└─────────────────────────────────────────┘
```

---

## 🚀 Deploy em Produção

Quando fizer o deploy no Dokploy, os seeders serão executados automaticamente se:

```env
RUN_SEEDERS=true
```

### Para executar manualmente no servidor:

```bash
# SSH no servidor Dokploy
docker exec -it [CONTAINER_ID] sh

# Rodar seeders
su-exec laravel php artisan db:seed --class=UserSeeder
```

---

## 📋 Checklist

- [x] Gerente global adicionado com email da EMSERH
- [x] Coordenador global adicionado com email da EMSERH
- [x] Senhas fortes configuradas (A123456*)
- [x] Usuários marcados como globais (unit_id = null)
- [x] Roles corretas atribuídas
- [x] Email verificado automaticamente
- [x] Output do seeder atualizado com credenciais
- [x] Total de usuários ajustado (26 + 3 = 29 usuários)

---

## 🔒 Segurança

⚠️ **IMPORTANTE**: As senhas estão hardcoded no seeder para facilitar o setup inicial. 

**Recomendações pós-deploy:**

1. Orientar os usuários a **alterarem as senhas** no primeiro login
2. Implementar **reset de senha obrigatório** no primeiro acesso
3. Considerar **autenticação de dois fatores (2FA)** para usuários globais
4. **Não commitar** senhas reais em produção

---

## 📞 Credenciais para Documentação

```
=================================================================
CREDENCIAIS USUÁRIOS GLOBAIS - SISTEMA HEMODIÁLISE
=================================================================

Admin Global:
  Email: admin@hemodialise.ma.gov.br
  Senha: admin123

Gerente Global:
  Nome: Joenvilly Azevedo
  Email: joenvilly.azevedo@emserh.ma.gov.br
  Senha: A123456*

Coordenador Global:
  Nome: André Campos
  Email: andre.campos@emserh.ma.gov.br
  Senha: A123456*

=================================================================
Usuários das Unidades: [email] / senha123
=================================================================
```

---

**✅ Alterações concluídas com sucesso!**

Os novos usuários globais serão criados quando o seeder for executado.
