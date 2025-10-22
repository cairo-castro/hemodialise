# 🧹 Limpeza de Disco do Servidor de Produção

## 📊 Quando usar este guia?

Execute a limpeza quando o disco estiver com **mais de 70% de uso**.

### Verificar uso atual do disco:

```bash
ssh root@212.85.1.175 "df -h /"
```

**Saída esperada:**
```
Filesystem      Size  Used Avail Use% Mounted on
/dev/sda1        96G   XXG   XXG  XX% /
```

---

## 🔍 Diagnóstico Rápido

### 1. Ver uso geral do Docker:

```bash
ssh root@212.85.1.175 "docker system df"
```

**O que observar:**
- **Images RECLAIMABLE > 10GB** → Tem imagens antigas para limpar
- **Build Cache > 5GB** → Tem cache de build para limpar
- **Containers RECLAIMABLE > 1GB** → Tem containers parados

### 2. Ver quantas imagens antigas existem:

```bash
ssh root@212.85.1.175 "docker images | grep '<none>' | wc -l"
```

Se retornar **mais de 10**, precisa limpar.

---

## 🧹 Comandos de Limpeza

### ⚠️ **IMPORTANTE:** Faça backup antes de limpar!

```bash
# Verificar quais serviços estão rodando
ssh root@212.85.1.175 "docker service ls"

# Verificar saúde dos containers
ssh root@212.85.1.175 "docker ps --format 'table {{.Names}}\t{{.Status}}'"
```

---

## 🚀 Limpeza Completa (Recomendado)

### Opção 1: Limpeza Total Automática

```bash
ssh root@212.85.1.175 "docker system prune -af --volumes"
```

**O que remove:**
- ✅ Todas as imagens não utilizadas
- ✅ Todos os containers parados
- ✅ Todo o cache de build
- ✅ Volumes não utilizados
- ⚠️ **Atenção:** Remove TUDO que não está em uso!

**Espaço liberado:** ~50-70GB (dependendo do acúmulo)

---

### Opção 2: Limpeza Seletiva (Mais Seguro)

#### Passo 1: Remover apenas imagens não utilizadas

```bash
ssh root@212.85.1.175 "docker image prune -af"
```

**O que remove:**
- Imagens sem tag (`<none>`)
- Imagens antigas não usadas por nenhum container

**Espaço liberado:** ~30-40GB

#### Passo 2: Remover cache de build

```bash
ssh root@212.85.1.175 "docker builder prune -af"
```

**O que remove:**
- Todo cache de compilação do Docker buildkit

**Espaço liberado:** ~10-50GB

#### Passo 3: Remover containers parados

```bash
ssh root@212.85.1.175 "docker container prune -f"
```

**O que remove:**
- Containers que não estão rodando

**Espaço liberado:** ~100MB-1GB

#### Passo 4 (OPCIONAL): Remover volumes não utilizados

⚠️ **CUIDADO:** Só execute se tiver certeza que não precisa dos dados!

```bash
ssh root@212.85.1.175 "docker volume prune -f"
```

---

## 📋 Script de Limpeza Automatizado

Salve este script no servidor para facilitar:

```bash
# Criar script de limpeza
cat > /root/limpar-docker.sh << 'EOF'
#!/bin/bash
echo "🧹 Iniciando limpeza do Docker..."
echo ""

echo "📊 Uso ANTES da limpeza:"
df -h / | tail -1
docker system df
echo ""

echo "🗑️ Removendo imagens não utilizadas..."
docker image prune -af

echo ""
echo "🗑️ Removendo cache de build..."
docker builder prune -af

echo ""
echo "🗑️ Removendo containers parados..."
docker container prune -f

echo ""
echo "✅ Limpeza concluída!"
echo ""
echo "📊 Uso DEPOIS da limpeza:"
df -h / | tail -1
docker system df
EOF

# Dar permissão de execução
chmod +x /root/limpar-docker.sh
```

**Para executar o script:**

```bash
ssh root@212.85.1.175 "/root/limpar-docker.sh"
```

---

## 🔄 Limpeza Agendada (Cron)

Para executar limpeza automática toda semana:

```bash
# Agendar para todo domingo às 3h da manhã
ssh root@212.85.1.175 "crontab -l 2>/dev/null; echo '0 3 * * 0 /usr/bin/docker image prune -af && /usr/bin/docker builder prune -af' | crontab -"
```

**Verificar agendamento:**

```bash
ssh root@212.85.1.175 "crontab -l"
```

---

## 🚨 Solução de Problemas

### Problema: "operation already running"

Se aparecer erro dizendo que já há uma operação rodando:

```bash
# Aguardar 2 minutos e tentar novamente
sleep 120
ssh root@212.85.1.175 "docker system prune -af"
```

### Problema: Serviços pararam após limpeza

Se algum serviço parar acidentalmente:

```bash
# Verificar status
ssh root@212.85.1.175 "docker service ls"

# Reiniciar serviço específico
ssh root@212.85.1.175 "docker service update --force qualidade-qualidadehd-bue1bg"
```

---

## 📈 Monitoramento

### Ver uso do disco em tempo real:

```bash
ssh root@212.85.1.175 "watch -n 5 'df -h / && echo && docker system df'"
```

Pressione `Ctrl+C` para sair.

### Ver logs de uso de disco:

```bash
ssh root@212.85.1.175 "du -sh /var/lib/docker/* 2>/dev/null | sort -hr | head -10"
```

### Listar as 20 maiores imagens:

```bash
ssh root@212.85.1.175 "docker images --format 'table {{.Repository}}\t{{.Tag}}\t{{.Size}}' | sort -k3 -hr | head -20"
```

---

## 📝 Checklist de Limpeza

Antes de executar:
- [ ] Verificar uso atual do disco (`df -h /`)
- [ ] Verificar serviços rodando (`docker service ls`)
- [ ] Anotar imagens importantes que devem ficar

Durante:
- [ ] Executar limpeza (`docker system prune -af`)
- [ ] Aguardar conclusão (pode demorar 5-10 minutos)

Depois:
- [ ] Verificar uso final do disco (`df -h /`)
- [ ] Verificar serviços ainda rodando (`docker service ls`)
- [ ] Testar aplicação (https://qualidadehd.direcaoclinica.com.br)

---

## 💡 Boas Práticas

1. **Limpeza Regular:** Execute limpeza a cada 2-4 semanas
2. **Monitoramento:** Configure alerta quando disco atingir 70%
3. **Logs:** Mantenha logs de limpeza para referência futura
4. **Backup:** Sempre tenha backup antes de limpar volumes

---

## 🔗 Links Úteis

- **Painel Dokploy:** https://212.85.1.175:3000
- **Aplicação:** https://qualidadehd.direcaoclinica.com.br
- **Servidor:** 212.85.1.175
- **Usuário:** root
- **Senha:** ClinQua-Hosp@2025

---

## 📞 Comandos Rápidos de Emergência

```bash
# Ver uso crítico
ssh root@212.85.1.175 "df -h / && docker system df"

# Limpeza rápida (30 segundos)
ssh root@212.85.1.175 "docker image prune -af && docker builder prune -af"

# Limpeza profunda (5 minutos)
ssh root@212.85.1.175 "docker system prune -af --volumes"

# Verificar se tudo está ok
ssh root@212.85.1.175 "docker service ls && df -h /"
```

---

**Última atualização:** Outubro 2025  
**Resultado da última limpeza:** 70GB liberados (84% → 11% de uso)
