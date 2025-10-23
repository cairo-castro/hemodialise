# 🔍 Auditoria Completa do Banco de Dados

**Data:** Janeiro 2025
**Servidor:** 212.85.1.175
**Container MariaDB:** qualidade-productionqualidade-l2xbgb (dec9081bf509)

---

## 📊 Bancos de Dados Existentes

| # | Nome do Banco | Propósito |
|---|---------------|-----------|
| 1 | `GQA_AuditDB_2025` | Sistema atual (outro projeto) |
| 2 | `hemodialise_gqa` | Sistema Hemodiálise (qualidadehd) |
| 3 | `information_schema` | Sistema MariaDB |
| 4 | `mysql` | Sistema MariaDB |
| 5 | `performance_schema` | Sistema MariaDB |
| 6 | `sys` | Sistema MariaDB |

---

## 👥 Usuários do Banco de Dados

| Usuário | Host | Propósito |
|---------|------|-----------|
| `Usr_QltGest@2025` | `%` | Usuário principal para hemodialise_gqa |
| `Usr_QltGest@2025` | `10.0.%` | Acesso de rede Docker 10.0.x |
| `Usr_QltGest@2025` | `172.%` | Acesso de rede Docker 172.x |
| `hemodialise_user` | `%` | Usuário alternativo (criado por engano) |
| `root` | `%` | Root para acesso remoto |
| `root` | `localhost` | Root local |
| `healthcheck` | `127.0.0.1` | Health checks |
| `healthcheck` | `::1` | Health checks IPv6 |
| `healthcheck` | `localhost` | Health checks local |
| `mariadb.sys` | `localhost` | Sistema MariaDB |

---

## 🔐 Privilégios por Banco

### hemodialise_gqa (Sistema Hemodiálise):

| Usuário | Host | SELECT | INSERT | UPDATE | DELETE |
|---------|------|--------|--------|--------|--------|
| `Usr_QltGest@2025` | `%` | ✅ | ✅ | ✅ | ✅ |
| `Usr_QltGest@2025` | `10.0.%` | ✅ | ✅ | ✅ | ✅ |
| `Usr_QltGest@2025` | `172.%` | ✅ | ✅ | ✅ | ✅ |
| `hemodialise_user` | `%` | ✅ | ✅ | ✅ | ✅ |

### GQA_AuditDB_2025 (Outro Sistema):

**Nenhum usuário específico configurado na tabela mysql.db**

Possíveis cenários:
1. Sistema conecta como `root`
2. Sistema tem usuário criado mas sem grants explícitos
3. Sistema usa outro método de autenticação

---

## ⚠️ PROBLEMA IDENTIFICADO

O usuário `Usr_QltGest@2025` tem:
- ✅ Usuário criado corretamente
- ✅ Senha configurada corretamente
- ✅ Privilégios ALL no banco `hemodialise_gqa`
- ✅ Hosts permitidos: `%`, `10.0.%`, `172.%`

**MAS AINDA ASSIM:**
```
ERROR 1045 (28000): Access denied for user 'Usr_QltGest@2025'@'10.0.1.X' (using password: YES)
```

### Possíveis Causas:

1. **Caractere @ no username** - MariaDB pode estar interpretando mal
2. **Ordem de matching** - MariaDB pode estar pegando outro usuário primeiro
3. **Plugin de autenticação** - Usando mysql_native_password corretamente
4. **Senha com caracteres especiais** - `!` e `#` podem estar causando problemas
5. **FLUSH PRIVILEGES não aplicou** - Cache interno do MariaDB

---

## 🔧 SOLUÇÃO RECOMENDADA

### Opção 1: Testar com Escape Correto no Laravel

O Laravel pode estar precisando de escape especial para o `@` no username.

**No Dokploy Environment Variables, tente:**
```
DB_USERNAME=Usr_QltGest@2025
```

**Se não funcionar, tente escapado:**
```
DB_USERNAME=Usr_QltGest\@2025
```

**Ou com aspas:**
```
DB_USERNAME="Usr_QltGest@2025"
```

### Opção 2: Criar Usuário Sem Caracteres Especiais (MELHOR PRÁTICA)

Criar um novo usuário seguindo convenções SQL padrão:

```sql
-- Usuário limpo para o novo sistema
CREATE USER 'hemodialise_app'@'%' IDENTIFIED BY 'Qlt!H0sp#2025';
GRANT ALL PRIVILEGES ON hemodialise_gqa.* TO 'hemodialise_app'@'%';
FLUSH PRIVILEGES;
```

**Vantagens:**
- ✅ Sem conflito de caracteres
- ✅ Segue best practices SQL
- ✅ Não afeta sistema existente
- ✅ Fácil de gerenciar

### Opção 3: Usar Usuário Root (NÃO RECOMENDADO)

```
DB_USERNAME=root
DB_PASSWORD=R00t#GQA!Safe2025
```

**Desvantagens:**
- ❌ Má prática de segurança
- ❌ Root tem acesso a TUDO
- ❌ Dificulta auditoria
- ❌ Risco de segurança

---

## 📝 RECOMENDAÇÃO FINAL

### Para Produção (Best Practices):

1. **Criar novo usuário limpo** para o sistema de Hemodiálise:
```sql
CREATE USER 'hemodialise_app'@'%' IDENTIFIED BY 'SenhaSegura2025!';
GRANT ALL PRIVILEGES ON hemodialise_gqa.* TO 'hemodialise_app'@'%';
FLUSH PRIVILEGES;
```

2. **Manter** `Usr_QltGest@2025` para o outro sistema (se necessário)

3. **Documentar** cada usuário e seu propósito

4. **Seguir convenções**:
   - Usernames: `[projeto]_[role]` (ex: `hemodialise_app`, `audit_reader`)
   - Evitar: `@`, `#`, `!` nos usernames
   - Usar: lowercase, underscore

### Para Verificar o Outro Sistema:

Execute no servidor:
```bash
# Ver qual usuário o sistema qualidade usa
docker inspect fddd9e3fe3a7 --format='{{range .Config.Env}}{{println .}}{{end}}' | grep DB_
```

---

## 🎯 PRÓXIMO PASSO

**Qual opção você prefere?**

1. ✅ **RECOMENDADO:** Criar `hemodialise_app` (limpo, best practices)
2. ⚠️ Tentar escapar `Usr_QltGest@2025` no Dokploy
3. ❌ **NÃO RECOMENDADO:** Usar root

Me diga qual opção você quer seguir e eu implemento!

---

**Status Atual:**
- ✅ Usuário `Usr_QltGest@2025` restaurado
- ✅ Permissões em `hemodialise_gqa` confirmadas
- ⏳ Aguardando decisão sobre qual username usar
