set -e

# Prepare writable directories
mkdir -p var/cache var/cache/prod var/cache/prod/lock var/log var/sessions /tmp/lock
chown -R www-data:www-data var /tmp/lock

# Optionally run migrations (ignore failures if DB not ready)
if command -v php >/dev/null 2>&1; then
  if php -v >/dev/null 2>&1; then
    php bin/console doctrine:migrations:migrate --no-interaction || true
  fi
fi
mkdir -p /var/www/html/public/media
chown -R www-data:www-data /var/www/html/public

# Start PHP-FPM and Nginx
php-fpm -D && nginx -g 'daemon off;'