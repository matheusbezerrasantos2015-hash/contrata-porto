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

# Executar migrações (se falhar, apenas avisa e continua para não quebrar a inicialização do container)
echo "Running database migrations..."
php artisan migrate --force || echo "WARNING: Database migrations failed! Please check DB environment variables."

# Criar link simbólico do storage
echo "Linking storage..."
php artisan storage:link --force || echo "WARNING: storage:link failed."

# Cache de configurações e rotas
echo "Caching config and routes..."
php artisan config:cache || echo "WARNING: config:cache failed."
php artisan route:cache || echo "WARNING: route:cache failed."

# Iniciar o PHP-FPM em background
echo "Starting PHP-FPM..."
php-fpm -D

# Iniciar o Nginx em foreground
echo "Starting Nginx..."
exec nginx -g "daemon off;"
