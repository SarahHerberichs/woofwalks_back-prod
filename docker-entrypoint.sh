#!/bin/sh
set -e

echo "--- Attente de la base de données ---"
until nc -z -v -w30 mysql.railway.internal 3306
do
  echo "En attente de la connexion à la base de données..."
  sleep 1
done

echo "Base de données prête."

# Variables d'environnement prod
export APP_ENV=prod
export APP_DEBUG=0

# Vérifier si des migrations sont encore à exécuter
PENDING=$(php bin/console doctrine:migrations:status --env=prod --format=string | grep "New" | awk '{print $2}')

if [ "$PENDING" != "0" ]; then
    echo "Migrations en attente : $PENDING. Lancement..."
    php bin/console doctrine:migrations:migrate --no-interaction --env=prod
else
    echo "Aucune migration à exécuter."
fi

# Démarrer PHP-FPM
php-fpm
