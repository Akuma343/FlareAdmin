#!/usr/bin/env bash
set -euo pipefail

# Render provides $PORT; default to 8080 locally
PORT_ENV="${PORT:-8080}"

# Configure Apache to listen on $PORT and update default vhost
sed -i "s/^Listen .*/Listen ${PORT_ENV}/" /etc/apache2/ports.conf
sed -E -i "s#<VirtualHost \*:[0-9]+>#<VirtualHost *:${PORT_ENV}>#" /etc/apache2/sites-available/000-default.conf

# Ensure runtime permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Optimize Laravel caches if artisan exists
if [ -f /var/www/html/artisan ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Start Apache in the foreground
exec apache2-foreground


