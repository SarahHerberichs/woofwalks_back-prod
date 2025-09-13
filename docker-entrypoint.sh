#!/bin/sh
set -e

# Définir l'environnement prod
export APP_ENV=prod
export APP_DEBUG=0

# Vérifier que la base de données est prête
echo "--- Attente de la base de données ---"
until nc -z -v -w30 mysql.railway.internal 3306
do
  echo "En attente de la connexion à la base de données..."
  sleep 1
done
echo "Base de données prête."

# Lancer les migrations Doctrine
echo "--- Exécution des migrations ---"
php bin/console doctrine:migrations:migrate --no-interaction

# Démarrer PHP-FPM
echo "--- Démarrage de PHP-FPM ---"
exec "$@"
