#!/bin/sh
set -e

cd /var/www/html

# Railway provides PORT; Apache must listen on it
PORT="${PORT:-8080}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Persistent volume mounts arrive owned by root
mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views
chown -R www-data:www-data storage bootstrap/cache

php artisan storage:link --force || true
php artisan migrate --force

# Demo accounts have known passwords; seed once, then remove RUN_SEED
if [ "${RUN_SEED:-false}" = "true" ]; then
    php artisan db:seed --class=RolePermissionSeeder --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
