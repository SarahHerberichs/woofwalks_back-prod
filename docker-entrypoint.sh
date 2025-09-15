#!/bin/sh
set -e

# Préparer cache et logs
mkdir -p var/cache var/log var/sessions
chown -R www-data:www-data var

# Exécuter la commande passée au container (php-fpm)
exec "$@"
