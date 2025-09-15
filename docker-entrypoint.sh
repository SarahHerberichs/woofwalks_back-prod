#!/bin/sh
set -e

echo "--- Attente de la base de données ---"
until nc -z -v -w30 mysql.railway.internal 3306
do
  echo "En attente de la connexion à la base de données..."
  sleep 1
done

echo "Base de données prête. Démarrage des migrations..."

# Variables d'environnement prod
export APP_ENV=prod
export APP_DEBUG=0

# Exécuter les migrations sans chercher de .env
# php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Lancer le serveur ou le processus principal
php-fpm
