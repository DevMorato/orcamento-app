#!/bin/bash
set -e

echo "🚀 Iniciando aplicação..."

# Aguardar o banco de dados estar pronto (se necessário)
if [ ! -z "$DB_HOST" ] && [ "$DB_CONNECTION" != "sqlite" ]; then
    echo "⏳ Aguardando banco de dados em $DB_HOST:$DB_PORT..."
    timeout=60
    while ! nc -z $DB_HOST ${DB_PORT:-5432} 2>/dev/null; do
        timeout=$((timeout - 1))
        if [ $timeout -le 0 ]; then
            echo "❌ Timeout aguardando banco de dados"
            exit 1
        fi
        sleep 1
    done
    echo "✅ Banco de dados disponível"
fi

# Criar banco SQLite se necessário
if [ "$DB_CONNECTION" = "sqlite" ]; then
    if [ ! -f "$DB_DATABASE" ] && [ ! -f "/var/www/html/database/database.sqlite" ]; then
        echo "📝 Criando arquivo SQLite..."
        touch /var/www/html/database/database.sqlite
    fi
fi

# Executar migrations
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "🔄 Executando migrations..."
    php artisan migrate --force --no-interaction --isolated || echo "⚠️  Migrations falharam, continuando..."
fi

# Executar seeders (apenas se configurado)
if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo "🌱 Executando seeders..."
    php artisan db:seed --force --no-interaction
fi

# Limpar e cachear configurações para produção
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "⚡ Otimizando para produção..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

# Criar storage link
if [ ! -L "/var/www/html/public/storage" ]; then
    echo "🔗 Criando storage link..."
    php artisan storage:link
fi

echo "✅ Aplicação pronta!"

# Executar comando passado como argumento
exec "$@"
