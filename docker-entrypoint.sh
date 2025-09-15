#!/bin/bash
set -e

# ... (les migrations et la mise en cache) ...

# Créer le fichier de log pour éviter que tail ne plante
touch /var/www/html/var/log/php_errors.log

# Démarrer le processus PHP-FPM en arrière-plan
php-fpm -F -d "listen=0.0.0.0:$PORT" &

# Afficher les logs en continu pour le débogage
tail -f /var/www/html/var/log/php_errors.log