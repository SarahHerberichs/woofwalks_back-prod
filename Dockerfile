# =========================
# Étape 1 : Installer les dépendances avec Composer
# =========================
FROM composer:2.4 AS builder

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# =========================
# Étape 2 : Image finale pour prod
# =========================
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Installer les dépendances système et extensions PHP
RUN apk add --no-cache bash git shadow mysql-client icu-dev \
    && docker-php-ext-install pdo pdo_mysql intl

# Copier vendor depuis le build stage
COPY --from=builder /app/vendor /var/www/html/vendor

# Copier tout le code Symfony
COPY . /var/www/html
# Copier le dossier de configuration
COPY config /var/www/html/config
# Après la ligne qui copie votre code
COPY php.ini /usr/local/etc/php/
# Copier explicitement le dossier des migrations
COPY migrations /var/www/html/migrations
COPY .env.railway /var/www/html/.env

# Créer les répertoires nécessaires et mettre les permissions
RUN mkdir -p var/cache var/log var/sessions public \
    && chown -R www-data:www-data var

# Copier l’entrypoint et le rendre exécutable
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Définir le point d'entrée pour l'image
ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["php-fpm", "-F", "-d", "listen=0.0.0.0:$PORT"]
