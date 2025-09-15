#!/bin/sh
set -e

echo "--- Attente de la base de données ---"
until nc -z -v -w30 mysql.railway.internal 3306
do
  echo "En attente de la connexion à la base de données..."
  sleep 1
done

echo "Base de données prête."

# Forcer l'environnement prod
export APP_ENV=prod
export APP_DEBUG=0

# Corriger les permissions sur var/cache et var/log
chown -R www-data:www-data var/cache var/log || true
chmod -R 775 var/cache var/log || true

# Vider et régénérer le cache prod
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Ajouter les versions de migrations déjà exécutées (optionnel si ta BDD est déjà à jour)
php bin/console doctrine:migrations:sync-metadata-storage --no-interaction

# Exécuter les migrations en prod
php bin/console doctrine:migrations:migrate --no-interaction --env=prod || true

# Lancer PHP-FPM
exec php-fpm
