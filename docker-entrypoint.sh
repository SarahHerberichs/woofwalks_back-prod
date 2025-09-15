#!/bin/bash
set -e

echo "Waiting for the database..."
/usr/local/bin/dockerize -wait tcp://mysql:3306 -timeout 20s

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction
echo "Migrations done."

# Clear and warm up cache
php bin/console cache:clear
php bin/console cache:warmup

# Create log file
touch var/log/php_errors.log
chown www-data:www-data var/log/php_errors.log

exec "$@"
