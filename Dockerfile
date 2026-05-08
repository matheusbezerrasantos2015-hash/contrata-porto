FROM php:8.2-cli-alpine

# Instala dependências do sistema para mbstring, PDO e cURL
RUN apk add --no-cache oniguruma-dev curl-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring curl \
    && apk del oniguruma-dev curl-dev

WORKDIR /app

COPY . .

EXPOSE 8080

# Servindo a partir da raiz /app para permitir acesso ao frontend e backend
CMD php -S 0.0.0.0:$PORT router.php