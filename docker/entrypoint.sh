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
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
# Controlados por variables de entorno (valores por defecto en Dockerfile.dev / Dockerfile.prod).
#   RUN_MIGRATIONS=true  -> php artisan migrate --force (si falla, el contenedor no arranca)
#   SEED_CATALOG=true    -> carga el catálogo del proveedor (datos + fotos WebP). Es idempotente:
#                           no duplica figuras ni pisa precios, stock o fotos ya capturados.
if [ "$RUN_MIGRATIONS" = "true" ]; then
    # Reintenta por si la base de datos todavía está arrancando.
    tries=0
    until php artisan migrate --force; do
        tries=$((tries + 1))
        if [ "$tries" -ge 10 ]; then
            echo "ERROR: no se pudieron aplicar las migraciones"
            exit 1
        fi
        echo "Migración fallida (intento $tries/10); reintentando en 5 s..."
        sleep 5
    done
fi
if [ "$SEED_CATALOG" = "true" ]; then
    # No tumba el contenedor si el seeder falla: la app sigue arriba y el error queda en el log.
    php artisan db:seed --class=Database\\Seeders\\CatalogSeeder --force || echo "AVISO: el seeder del catálogo falló"
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
