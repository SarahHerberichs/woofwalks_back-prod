#!/bin/sh
set -e

# Attendre la DB
echo "--- Attente de la base de données ---"
until nc -z -v -w30 mysql.railway.internal 3306
do
  echo "En attente de la connexion à la base de données..."
  sleep 1
done
echo "Base de données prête."

# Lancer les migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Démarrer PHP-FPM
exec "$@"
