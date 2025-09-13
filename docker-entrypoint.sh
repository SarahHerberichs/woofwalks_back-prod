#!/bin/sh
set -e

echo "--- Attente de la base de données ---"
until nc -z -v -w30 mysql.railway.internal 3306
do
  echo "En attente de MySQL..."
  sleep 2
done
echo "Base de données prête."

# Forcer l'environnement prod directement via variables
export APP_ENV=prod
export APP_DEBUG=0
export DATABASE_URL="mysql://root:IlcNzJQOqGRrtpEqpgzVAtCGfTvlagDM@mysql.railway.internal:3306/railway"

echo "--- Statut des migrations ---"
php bin/console doctrine:migrations:status --env=prod || true

echo "--- Exécution des migrations ---"
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

echo "--- Lancement de PHP-FPM ---"
exec php-fpm
