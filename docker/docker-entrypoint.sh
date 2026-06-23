#!/bin/sh
set -e

# Substituir a porta do Nginx pelo valor fornecido pela Railway (env PORT)
if [ -n "$PORT" ]; then
    echo "Configuring Nginx to listen on port $PORT..."
    sed -i "s/listen 8080/listen $PORT/g" /etc/nginx/http.d/default.conf
fi

# Garantir permissões de escrita para pastas do Laravel
echo "Setting permissions for storage and bootstrap/cache..."
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

# Executar migrações
echo "Running database migrations..."
php artisan migrate --force

# Criar link simbólico do storage
echo "Linking storage..."
php artisan storage:link --force

# Cache de configurações e rotas (melhora performance em produção)
echo "Caching config and routes..."
php artisan config:cache
php artisan route:cache

# Iniciar o PHP-FPM em background
echo "Starting PHP-FPM..."
php-fpm -D

# Iniciar o Nginx em foreground
echo "Starting Nginx..."
exec nginx -g "daemon off;"
