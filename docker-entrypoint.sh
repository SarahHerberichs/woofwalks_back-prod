#!/bin/bash
set -e

# Wait for the database to be ready
/usr/local/bin/dockerize -wait tcp://MySQL:3306 -timeout 20s

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction
echo "Migrations done."

# Clear and warm up the cache
php bin/console cache:clear
php bin/console cache:warmup

# Ensure the log file exists
touch /var/www/html/var/log/php_errors.log

# Start the PHP-FPM process and redirect output
exec php-fpm -F -d "listen=0.0.0.0:$PORT"