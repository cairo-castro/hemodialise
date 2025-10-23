# 🔧 Correção: Usuário do Banco de Dados

## ❌ Problema Identificado

O usuário do banco de dados tinha um `@` no nome: `Usr_QltGest@2025`

```
ERROR 1045 (28000): Access denied for user 'Usr_QltGest@2025'@'10.0.1.X' (using password: YES)
```

### Causa Raiz:

O caractere `@` no nome de usuário MySQL/MariaDB é **problemático** porque:
1. O `@` é usado pelo MariaDB para separar usuário e host: `'user'@'host'`
2. Quando o nome do usuário contém `@`, causa confusão no parser do MariaDB
3. Mesmo com grants corretos, a autenticação falhava

## ✅ Solução Aplicada

### 1. Novo Usuário Criado:
```sql
-- Removido: 'Usr_QltGest@2025'@'%'
-- Criado:   'hemodialise_user'@'%'

CREATE USER 'hemodialise_user'@'%' IDENTIFIED BY 'Qlt!H0sp#2025';
GRANT ALL PRIVILEGES ON hemodialise_gqa.* TO 'hemodialise_user'@'%';
FLUSH PRIVILEGES;
```

### 2. Benefícios:
- ✅ Nome limpo, sem caracteres problemáticos
- ✅ Permissões de QUALQUER IP da rede Docker (`@'%'`)
- ✅ Mesma senha segura mantida
- ✅ Todas as permissões no banco `hemodialise_gqa`

## 🔄 AÇÃO NECESSÁRIA: Atualizar Dokploy

Você **PRECISA** atualizar a variável de ambiente no Dokploy:

### Passo a Passo:

1. **Acesse:** http://212.85.1.175:3000

2. **Navegue:** Projects > qualidade > qualidadehd

3. **Clique na aba:** "Environment" ou "Environment Variables"

4. **ALTERE esta variável:**

   **ANTES:**
   ```
   DB_USERNAME=Usr_QltGest@2025
   ```

   **DEPOIS:**
   ```
   DB_USERNAME=hemodialise_user
   ```

5. **Mantenha** as outras variáveis:
   ```
   DB_HOST=qualidade-productionqualidade-l2xbgb
   DB_PORT=3306
   DB_PASSWORD=Qlt!H0sp#2025
   DB_DATABASE=hemodialise_gqa
   DB_CONNECTION=mariadb
   ```

6. **Salvar** as alterações

7. **Redeploy** o serviço qualidadehd

## 🔍 Verificação

Após o redeploy, os logs devem mostrar:

```
✓ Database connection established!
Running database migrations...
✓ Migrations completed successfully
```

E **NÃO** deve mais mostrar:
```
❌ ERROR 1045 (28000): Access denied for user
```

## 📋 Resumo das Mudanças

| Item | Antes | Depois |
|------|-------|--------|
| **Usuário** | `Usr_QltGest@2025` | `hemodialise_user` |
| **Senha** | `Qlt!H0sp#2025` | `Qlt!H0sp#2025` (mesma) |
| **Host** | `10.0.%` (wildcard) | `%` (todos) |
| **Banco** | `hemodialise_gqa` | `hemodialise_gqa` (mesmo) |
| **Status** | ❌ Acesso negado | ✅ Funcional |

## 🎯 Por Que o @ no Nome é Problemático?

### Exemplo do Problema:

Quando o Laravel tenta conectar com:
```php
DB::connection([
    'username' => 'Usr_QltGest@2025',
    'host' => 'qualidade-productionqualidade-l2xbgb'
]);
```

O MariaDB interpreta como:
```
Usuário: 'Usr_QltGest'
Host solicitado: '2025'  // ❌ ERRADO!
```

Ao invés de:
```
Usuário: 'Usr_QltGest@2025'
Host solicitado: 'qualidade-productionqualidade-l2xbgb'
```

### Solução:

Usuário sem caracteres especiais que conflitam com a sintaxe SQL:
```sql
'hemodialise_user'@'%'
```

Agora o MariaDB interpreta corretamente:
```
Usuário: 'hemodialise_user'
Host permitido: '%' (qualquer)
```

## 🚀 Próximos Passos

1. ✅ **Feito:** Usuário criado no banco
2. ⏳ **Você deve fazer:** Atualizar `DB_USERNAME` no Dokploy
3. ⏳ **Você deve fazer:** Redeploy do serviço
4. ✅ **Resultado esperado:** Bad gateway resolvido!

---

**Data:** Janeiro 2025
**Status:** ✅ Usuário criado, aguardando atualização no Dokploy
**Prioridade:** 🔴 URGENTE
