#!/bin/sh

# Attendre que la DB soit disponible
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  echo "Waiting for database..."
  sleep 2
done

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Démarrer PHP-FPM
php-fpm
