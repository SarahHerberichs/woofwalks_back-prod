#!/bin/bash
set -e

echo "Waiting for the database..."
/usr/local/bin/dockerize -wait tcp://mysql:3306 -timeout 20s

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction
echo "Migrations done."

# This part is crucial for debugging
echo "Starting PHP-FPM in foreground with error logging..."
# Ensure PHP-FPM logs to stdout so Railway can capture it
exec php-fpm -F -O -y /usr/local/etc/php-fpm.conf