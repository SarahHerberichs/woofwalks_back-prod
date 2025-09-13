#!/bin/sh
set -e

echo "--- Attente de la base de données ---"
until nc -z -v -w30 mysql.railway.internal 3306
do
  echo "En attente de la connexion à la base de données..."
  sleep 1
done

echo "Base de données prête. Démarrage des migrations..."

# Debug
echo "--- Contenu du dossier migrations ---"
ls -la /var/www/html/migrations

echo "--- Statut des migrations avant ---"
APP_ENV=prod APP_DEBUG=0 php bin/console doctrine:migrations:status

echo "--- Exécution des migrations ---"
APP_ENV=prod APP_DEBUG=0 php bin/console doctrine:migrations:migrate --no-interaction

echo "--- Statut des migrations après ---"
APP_ENV=prod APP_DEBUG=0 php bin/console doctrine:migrations:status

echo "--- Lancement PHP-FPM ---"
exec "$@"
