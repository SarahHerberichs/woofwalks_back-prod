#!/bin/bash
set -e

# Cette ligne est la seule chose à garder pour les permissions
chown -R www-data:www-data /var/www/html/var

# Cette ligne exécute la commande principale de votre Dockerfile
exec "$@"