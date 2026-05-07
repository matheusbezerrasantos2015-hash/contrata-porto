FROM php:8.2-cli-alpine

# Instala dependências do sistema para mbstring e extensões PDO
RUN apk add --no-cache oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring \
    && apk del oniguruma-dev

WORKDIR /app

COPY . .

EXPOSE 8080

# Servindo a partir da raiz /app para permitir acesso ao frontend e backend
CMD php -S 0.0.0.0:$PORT -t /app