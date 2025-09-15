#!/bin/bash
set -e

# Wait for the database to be ready
/usr/local/bin/dockerize -wait tcp://mysql:3306 -timeout 20s

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction
echo "Migrations done."

# Clear and warm up the cache
php bin/console cache:clear
php bin/console cache:warmup
php-fpm -F -d "listen=0.0.0.0:$PORT" &

touch /var/www/html/var/log/php_errors.log

# Start the PHP-FPM process
php-fpm -F -d "listen=0.0.0.0:$PORT" &

# Display the PHP-FPM logs for debugging
tail -f /var/www/html/var/log/php_errors.log
# Start the PHP-FPM process
exec "$@"