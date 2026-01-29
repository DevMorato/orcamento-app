# Docker - Guia de Uso

Este documento explica como construir e executar a aplicação usando Docker.

## 📋 Pré-requisitos

- Docker Desktop instalado e rodando
- Docker Compose (já incluído no Docker Desktop)

## 🚀 Como usar

### 1. Construir a imagem Docker

```bash
docker build -t orcamento-app:latest .
```

Este comando irá:
- Instalar todas as dependências PHP (Composer)
- Instalar e compilar assets (npm run build)
- Configurar Nginx e PHP-FPM
- Otimizar a aplicação para produção

### 2. Executar com Docker Compose (Recomendado)

```bash
# Iniciar a aplicação
docker-compose up -d

# Ver os logs
docker-compose logs -f

# Parar a aplicação
docker-compose down
```

A aplicação estará disponível em: **http://localhost:8000**

### 3. Executar manualmente (sem Docker Compose)

```bash
# Criar banco SQLite
touch database/database.sqlite

# Rodar container
docker run -d \
  --name orcamento-app \
  -p 8000:80 \
  -v $(pwd)/database/database.sqlite:/var/www/html/database/database.sqlite \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  orcamento-app:latest

# Ver logs
docker logs -f orcamento-app

# Parar container
docker stop orcamento-app
docker rm orcamento-app
```

## 🔧 Comandos úteis

### Acessar o shell do container

```bash
docker exec -it orcamento-app sh
```

### Executar comandos Artisan

```bash
# Rodar migrations
docker exec orcamento-app php artisan migrate

# Criar usuário admin
docker exec orcamento-app php artisan make:filament-user

# Limpar cache
docker exec orcamento-app php artisan cache:clear

# Ver rotas
docker exec orcamento-app php artisan route:list
```

### Rebuildar a imagem após mudanças

```bash
docker-compose build --no-cache
docker-compose up -d
```

## 🗄️ Usando PostgreSQL

Se preferir usar PostgreSQL em vez de SQLite:

1. Descomente a seção `db` no `docker-compose.yml`
2. Altere as variáveis de ambiente da aplicação:

```yaml
DB_CONNECTION: pgsql
DB_HOST: db
DB_PORT: 5432
DB_DATABASE: orcamento_familiar
DB_USERNAME: orcamento_user
DB_PASSWORD: senha_segura_aqui
```

3. Restart os containers:

```bash
docker-compose down
docker-compose up -d
```

## 📦 Publicar a imagem

### Docker Hub

```bash
# Login no Docker Hub
docker login

# Taguear imagem
docker tag orcamento-app:latest seu-usuario/orcamento-app:latest

# Publicar
docker push seu-usuario/orcamento-app:latest
```

### Usar a imagem publicada

```bash
docker pull seu-usuario/orcamento-app:latest
docker run -d -p 8000:80 seu-usuario/orcamento-app:latest
```

## 🔒 Variáveis de Ambiente

Principais variáveis que você pode configurar no `docker-compose.yml`:

| Variável | Descrição | Padrão |
|----------|-----------|--------|
| `APP_ENV` | Ambiente (production, local) | production |
| `APP_DEBUG` | Debug mode | false |
| `APP_URL` | URL da aplicação | http://localhost:8000 |
| `DB_CONNECTION` | Tipo de banco (sqlite, pgsql, mysql) | sqlite |
| `RUN_MIGRATIONS` | Executar migrations ao iniciar | true |
| `RUN_SEEDERS` | Executar seeders ao iniciar | false |

## 🐛 Troubleshooting

### Problema de permissões

```bash
docker exec orcamento-app chown -R www:www /var/www/html/storage
docker exec orcamento-app chmod -R 775 /var/www/html/storage
```

### Container não inicia

```bash
# Ver logs detalhados
docker-compose logs

# Verificar status
docker-compose ps
```

### Limpar tudo e recomeçar

```bash
docker-compose down -v
docker system prune -a
docker-compose up -d --build
```

## 📊 Monitoramento

### Ver uso de recursos

```bash
docker stats orcamento-app
```

### Healthcheck

```bash
docker inspect orcamento-app | grep -A 10 Health
```

## 🎯 Próximos passos

- [ ] Configurar CI/CD para build automático
- [ ] Adicionar Redis para cache
- [ ] Configurar backup automático do banco
- [ ] Configurar SSL/HTTPS
- [ ] Adicionar monitoramento (Prometheus/Grafana)
