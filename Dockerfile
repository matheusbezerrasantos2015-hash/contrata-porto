# ─── Estágio 1: Build do Frontend React ──────────────────────────────────────
FROM node:20-alpine AS node-builder

WORKDIR /app/frontend

# Copiar manifests primeiro para aproveitar cache de camadas
COPY frontend/package.json frontend/package-lock.json ./

# Instalar dependências de forma reproduzível
RUN npm ci

# Copiar o restante do frontend e buildar
COPY frontend/ ./

# VITE_API_URL vazio → chamadas /api/* são relativas à mesma origem (sem CORS)
RUN VITE_API_URL='' npm run build

# ─── Estágio 2: PHP-FPM + Nginx (Laravel) ────────────────────────────────────
FROM php:8.2-fpm-alpine

# Instalar dependências de sistema e extensões PHP necessárias
RUN apk add --no-cache \
    nginx \
    libxml2-dev \
    curl-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    bash \
    shadow \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring xml curl zip gd

# Copiar Composer da imagem oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /app

# Copiar arquivos do projeto Laravel para o container
COPY . .

# Copiar o build React compilado para public/app/
COPY --from=node-builder /app/frontend/dist ./public/app

# Copiar configuração customizada do Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Dar permissão de execução no script de entrypoint
RUN chmod +x docker/docker-entrypoint.sh

# Ajustar permissões iniciais dos diretórios para www-data
RUN chown -R www-data:www-data /app

# Executar composer install durante o build para cache de dependências
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-audit --no-scripts --prefer-dist

# Expor a porta padrão (Railway irá sobrescrever isso com $PORT)
EXPOSE 8080

# Definir o script de entrypoint
ENTRYPOINT ["docker/docker-entrypoint.sh"]