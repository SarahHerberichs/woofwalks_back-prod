#!/bin/sh
set -e

# Wait for the database service to be available by checking for port 3306 to be open
until nc -z -v -w30 mysql.railway.internal 3306
do
  echo "Waiting for database connection..."
  sleep 1
done

echo "Database is ready. Starting migrations..."

# Run database migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Execute the original command (php-fpm)
exec "$@"
