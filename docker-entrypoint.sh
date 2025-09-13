#!/bin/sh
set -e

# Attendre que la base de données soit disponible en vérifiant que le port 3306 est ouvert
echo "--- Attente de la base de données ---"
until nc -z -v -w30 mysql.railway.internal 3306
do
  echo "En attente de la connexion à la base de données..."
  sleep 1
done

echo "Base de données prête. Démarrage des migrations..."

# Débogage : Lister le contenu du dossier des migrations
echo "--- Contenu du dossier de migrations ---"
ls -la /var/www/html/src/Migrations

# Débogage : Vérifier le statut des migrations avant l'exécution
echo "--- Statut des migrations avant l'exécution ---"
php bin/console doctrine:migrations:status

# Exécuter les migrations
echo "--- Exécution des migrations ---"
php bin/console doctrine:migrations:migrate --no-interaction

# Débogage : Vérifier le statut des migrations après l'exécution
echo "--- Statut des migrations après l'exécution ---"
php bin/console doctrine:migrations:status

# Démarrer PHP-FPM
echo "--- Démarrage de PHP-FPM ---"
exec "$@"
