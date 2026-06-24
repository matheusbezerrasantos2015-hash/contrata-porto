# ─── Estágio 1: Build do Frontend React ──────────────────────────────────────
FROM node:20-alpine AS node-builder

WORKDIR /app/frontend

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci

COPY frontend/ ./
RUN VITE_API_URL='' npm run build

# ─── Estágio 2: PHP-FPM + Nginx (Laravel) ────────────────────────────────────
FROM php:8.2-fpm-alpine

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

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

COPY --from=node-builder /app/frontend/dist ./public/app

COPY docker/nginx.conf /etc/nginx/http.d/default.conf

RUN chmod +x docker/docker-entrypoint.sh

RUN chown -R www-data:www-data /app

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts --prefer-dist

EXPOSE 8080

ENTRYPOINT ["docker/docker-entrypoint.sh"]