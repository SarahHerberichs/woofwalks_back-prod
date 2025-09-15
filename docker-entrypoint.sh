#!/bin/bash
set -e

# Remplacer le port 9000 par la variable d'environnement PORT de Railway
sed -i "s/9000/$PORT/" /usr/local/etc/php-fpm.d/www.conf

# Exécuter la commande principale
exec "$@"