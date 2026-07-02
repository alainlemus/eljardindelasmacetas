#!/bin/sh
set -e

# Si Dokploy inyecta las variables de entorno, escribirlas al .env
# para que artisan las pueda leer correctamente
if [ -n "$APP_KEY" ]; then
    sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env
fi
if [ -n "$APP_URL" ]; then
    # Forzar https:// siempre
    HTTPS_URL=$(echo "$APP_URL" | sed 's|^http://|https://|')
    sed -i "s|^APP_URL=.*|APP_URL=$HTTPS_URL|" .env
fi
if [ -n "$DB_HOST" ]; then
    sed -i "s|^DB_HOST=.*|DB_HOST=$DB_HOST|" .env
fi
if [ -n "$DB_DATABASE" ]; then
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" .env
fi
if [ -n "$DB_USERNAME" ]; then
    sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" .env
fi
if [ -n "$DB_PASSWORD" ]; then
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env
fi

# Ejecutar comandos de inicialización de Laravel
php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
