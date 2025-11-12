# Deploy Notes - Cleaning Controls & Machine Availability Updates

**Data:** 12 de Novembro de 2025
**Versão:** 1.2.0
**Tipo:** Bug Fixes + Feature Enhancements

## 📋 Resumo das Alterações

Este deploy corrige problemas críticos no sistema de limpeza e melhora a experiência do usuário ao selecionar máquinas disponíveis.

---

## 🐛 Problemas Corrigidos

### 1. **Erro SQL ao Salvar Checklist de Limpeza**
**Problema:** Erro 500 ao finalizar checklist de limpeza
**Causa:** Incompatibilidade entre tipo de dado do banco (TINYINT/boolean) e valores enviados pela aplicação ('C', 'NC', 'NA')
**Solução:** Migration que converte colunas para ENUM('C', 'NC', 'NA')

**Arquivos Afetados:**
- `database/migrations/2025_11_12_130522_production_cleaning_controls_enum_update.php` ✨ NOVO
- `app/Http/Controllers/Api/CleaningControlController.php` (validação atualizada)

### 2. **Máquinas Indisponíveis Aparecendo no Dropdown**
**Problema:** Dropdown mostrava todas as máquinas, incluindo ocupadas, inativas e em manutenção
**Solução:** Filtrar apenas máquinas com status 'available' e active=true

**Arquivos Afetados:**
- `resources/js/desktop/components/CleaningWizardModal.vue` (linha 47-146)

### 3. **Cards de Máquinas Não Atualizavam Após Mudança de Status**
**Problema:** Após alterar status da máquina, card não refletia mudança
**Solução:** Implementar API calls reais no modal de edição de máquina

**Arquivos Afetados:**
- `resources/js/desktop/components/MachineFormModal.vue` (linhas 308-394)
- `app/Http/Controllers/Api/MachineController.php` (validação de checklist ativo)

---

## ✨ Melhorias Implementadas

### 1. **Interface Visual de Seleção de Máquinas (Desktop)**
- Dropdown simples transformado em cards visuais
- Adicionado ícones informativos
- Badges de status (disponível, ocupada, manutenção)
- Exibição de identificador e descrição
- Melhor feedback visual ao selecionar

**Arquivos:**
- `resources/js/desktop/components/CleaningWizardModal.vue`

### 2. **Validação de Checklist Ativo**
- Bloqueia mudança de status se máquina tem checklist ativo (incluindo pausados)
- Mensagem clara informando paciente e fase do checklist
- Previne inconsistências de dados

**Arquivos:**
- `app/Http/Controllers/Api/MachineController.php` (linhas 176-203)

### 3. **Mobile Já Implementado Corretamente**
- Mobile sempre usou endpoint `/api/machines/available`
- Interface de cards visuais já existente
- Validação de disponibilidade implementada
- **Nenhuma alteração necessária no mobile** ✅

---

## 🗄️ Alterações no Banco de Dados

### Migration: `2025_11_12_130522_production_cleaning_controls_enum_update.php`

**Mudanças:**
```
cleaning_controls.hd_machine_cleaning: TINYINT(1) → ENUM('C', 'NC', 'NA')
cleaning_controls.osmosis_cleaning: TINYINT(1) → ENUM('C', 'NC', 'NA')
cleaning_controls.serum_support_cleaning: TINYINT(1) → ENUM('C', 'NC', 'NA')
```

**Conversão de Dados:**
- `1` (true) → `'C'` (Conforme)
- `0` (false) → `'NC'` (Não Conforme)
- `NULL` → `NULL` (não alterado)

**Rollback Disponível:** ✅ Sim (método `down()` implementado)

---

## 🚀 Instruções de Deploy

### 1. **Backup do Banco de Dados**
```bash
# Dentro do container Docker
docker exec qualidade-qualidadehd-[HASH] mysqldump -u root -p hemodialise > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2. **Pull do Código**
```bash
git pull origin main
```

### 3. **Instalar Dependências (se houver)**
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 4. **Executar Migration**
```bash
php artisan migrate
```

**Tempo Estimado:** 2-5 segundos (depende do volume de dados)

**Verificação:**
```bash
php artisan tinker
>>> DB::select("SHOW COLUMNS FROM cleaning_controls WHERE Field IN ('hd_machine_cleaning', 'osmosis_cleaning', 'serum_support_cleaning')");
```

Deve retornar tipo: `enum('C','NC','NA')`

### 5. **Limpar Caches**
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. **Testar Funcionalidades**
- [ ] Criar novo checklist de limpeza (desktop)
- [ ] Criar novo checklist de limpeza (mobile)
- [ ] Verificar filtro de máquinas disponíveis
- [ ] Tentar alterar status de máquina com checklist ativo (deve bloquear)
- [ ] Alterar status de máquina disponível (deve funcionar)

---

## 🔄 Rollback (Se Necessário)

### Opção 1: Rollback da Migration
```bash
php artisan migrate:rollback --step=1
```

### Opção 2: Restaurar Backup
```bash
# Dentro do container
mysql -u root -p hemodialise < backup_YYYYMMDD_HHMMSS.sql
```

### Opção 3: Reverter Código
```bash
git revert [commit-hash]
git push origin main
```

---

## 📊 Impacto e Riscos

### Impacto
- ✅ **Alto:** Corrige erro crítico que impedia salvamento de checklists
- ✅ **Médio:** Melhora experiência do usuário na seleção de máquinas
- ✅ **Baixo:** Validação adicional previne inconsistências

### Riscos
- ⚠️ **Baixo:** Migration altera tipo de coluna (testado em desenvolvimento)
- ⚠️ **Muito Baixo:** Rollback disponível e testado
- ✅ **Dados preservados:** Conversão automática mantém integridade

### Downtime Esperado
- **0 segundos** - Deploy pode ser feito sem interrupção
- Migration é rápida (< 5 segundos)

---

## 🧪 Testes Realizados

### Ambiente de Desenvolvimento
- ✅ Migration executada com sucesso
- ✅ Dados existentes convertidos corretamente (7.000+ registros)
- ✅ Novos checklists salvam sem erros
- ✅ Filtro de máquinas funcionando (desktop e mobile)
- ✅ Validação de checklist ativo funcionando
- ✅ Rollback testado e funcionando

### Casos de Teste
1. ✅ Salvar checklist com valores 'C', 'NC', 'NA'
2. ✅ Salvar checklist com valores NULL
3. ✅ Converter dados existentes (0/1) para ('NC'/'C')
4. ✅ Filtrar apenas máquinas disponíveis
5. ✅ Bloquear mudança de status com checklist ativo
6. ✅ Permitir mudança de status sem checklist ativo

---

## 📝 Notas Adicionais

### Compatibilidade
- **Laravel:** 11.x ✅
- **PHP:** 8.2+ ✅
- **MySQL/MariaDB:** 5.7+ ✅
- **Vue 3:** 3.x ✅

### Monitoramento Pós-Deploy
Verificar logs por 24h após deploy:
```bash
# Laravel logs
docker exec [CONTAINER] tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# Erros específicos
docker exec [CONTAINER] grep -i "cleaning" storage/logs/laravel-$(date +%Y-%m-%d).log
```

### Métricas de Sucesso
- [ ] 0 erros SQL relacionados a cleaning_controls
- [ ] Taxa de sucesso de salvamento de checklists > 99%
- [ ] Nenhuma reclamação sobre máquinas indisponíveis aparecendo
- [ ] Tempo de carregamento de dropdown < 1s

---

## 👥 Contatos

**Desenvolvedor:** Claude AI
**Revisor:** [Seu Nome]
**Aprovador:** [Nome do Responsável]

**Em caso de problemas:**
1. Verificar logs do Laravel
2. Verificar console do navegador (F12)
3. Executar rollback se necessário
4. Contatar equipe de desenvolvimento

---

## 📚 Referências

- Commit principal: [inserir hash após commit]
- Issues relacionadas: #[número]
- Documentação Laravel Migrations: https://laravel.com/docs/11.x/migrations
- Documentação ENUM MySQL: https://dev.mysql.com/doc/refman/8.0/en/enum.html
