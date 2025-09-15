#!/bin/bash
set -e

echo "Waiting for the database..."
/usr/local/bin/dockerize -wait tcp://mysql:3306 -timeout 20s

# echo "Running migrations..."
# php bin/console doctrine:migrations:migrate --no-interaction
# echo "Migrations done."

php-fpm -D && nginx -g 'daemon off;'