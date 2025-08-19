#!/usr/bin/env bash
set -euo pipefail

# Render provides $PORT; default to 8080 locally
PORT_ENV="${PORT:-8080}"

# Configure Apache to listen on $PORT and update default vhost
sed -i "s/^Listen .*/Listen ${PORT_ENV}/" /etc/apache2/ports.conf
sed -E -i "s#<VirtualHost \*:[0-9]+>#<VirtualHost *:${PORT_ENV}>#" /etc/apache2/sites-available/000-default.conf

# Ensure ServerName is set to avoid FQDN warning
APACHE_SERVER_NAME="${APACHE_SERVER_NAME:-${APP_URL:-${RENDER_EXTERNAL_URL:-localhost}}}"
# Strip protocol if a full URL is provided
APACHE_SERVER_NAME="${APACHE_SERVER_NAME#http://}"
APACHE_SERVER_NAME="${APACHE_SERVER_NAME#https://}"
# Keep only host part (remove path if any)
APACHE_SERVER_NAME="${APACHE_SERVER_NAME%%/*}"
printf "ServerName %s\n" "$APACHE_SERVER_NAME" > /etc/apache2/conf-available/servername.conf
a2enconf servername >/dev/null 2>&1 || true

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


