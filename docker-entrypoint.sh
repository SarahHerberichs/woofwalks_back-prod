#!/bin/bash
set -e

# Prepare writable directories
mkdir -p var/cache/prod/lock var/log var/sessions
chown -R www-data:www-data var

# Optionally run migrations (ignore failures if DB not ready)
if command -v php >/dev/null 2>&1; then
  if php -v >/dev/null 2>&1; then
    php bin/console doctrine:migrations:migrate --no-interaction || true
  fi
fi

# Start PHP-FPM and Nginx
php-fpm -D && nginx -g 'daemon off;'