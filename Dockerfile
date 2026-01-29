# Multi-stage build para otimizar o tamanho da imagem final
FROM php:8.4-fpm-alpine AS base

# Instalar dependências do sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    sqlite-dev \
    icu-dev \
    icu-libs \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    oniguruma-dev \
    bash \
    curl \
    git

# Instalar extensões PHP necessárias para Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_sqlite \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    opcache

# Configurar OPcache para produção
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.fast_shutdown=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Stage de build de assets
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copiar arquivos de dependências
COPY package*.json ./

# Instalar dependências do Node.js
RUN npm ci --production=false

# Copiar código fonte
COPY . .

# Build dos assets
RUN npm run build

# Stage final
FROM base AS final

WORKDIR /var/www/html

# Criar usuário para rodar a aplicação
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Copiar dependências do Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copiar código da aplicação
COPY --chown=www:www . .

# Copiar assets buildados do stage anterior
COPY --from=node-builder --chown=www:www /app/public/build ./public/build

# Criar diretórios necessários e ajustar permissões
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database \
    /var/log/supervisor \
    /var/log/nginx \
    /var/run \
    /var/lib/nginx/tmp/client_body \
    /var/lib/nginx/tmp/proxy \
    /var/lib/nginx/tmp/fastcgi \
    /var/lib/nginx/tmp/uwsgi \
    /var/lib/nginx/tmp/scgi \
    /var/lib/nginx/logs && \
    chown -R www:www storage bootstrap/cache database /var/log/supervisor /var/log/nginx /var/run /var/lib/nginx && \
    chmod -R 775 storage bootstrap/cache database && \
    chmod -R 755 /var/log/supervisor /var/log/nginx /var/lib/nginx

# Configurar Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Configurar PHP-FPM
RUN echo "[www]" > /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "user = www" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "group = www" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "listen = 127.0.0.1:9000" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "pm = dynamic" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "pm.max_children = 50" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "pm.start_servers = 5" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "pm.min_spare_servers = 5" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "pm.max_spare_servers = 35" >> /usr/local/etc/php-fpm.d/zz-docker.conf

# Configurar Supervisor
COPY docker/supervisord.conf /etc/supervisord.conf

# Copiar script de entrada
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Trocar para usuário não-privilegiado
USER www

# Healthcheck para monitoramento de saúde do container
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Expor porta
EXPOSE 80

# Comando de inicialização
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
