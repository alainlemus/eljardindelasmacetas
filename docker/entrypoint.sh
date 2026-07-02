#!/bin/sh
set -e

# Ejecutar comandos de inicialización de Laravel
php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
