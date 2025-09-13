# -----------------------------
# Étape 1 : Build Composer
# -----------------------------
FROM composer:2.4 AS composer_builder

WORKDIR /app

# Copier uniquement les fichiers nécessaires pour composer install
COPY composer.json composer.lock ./

# Installer les dépendances prod
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# -----------------------------
# Étape 2 : Image finale PHP-FPM
# -----------------------------
FROM php:8.2-fpm-alpine

# Répertoire de travail
WORKDIR /var/www/html

# Installer dépendances système et extensions PHP
RUN apk add --no-cache \
    git \
    mysql-client \
    bash \
    shadow \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /tmp/pear

# Créer l'utilisateur www-data avec UID 1000
RUN usermod -u 1000 www-data

# Créer les répertoires nécessaires à Symfony
RUN mkdir -p var/cache var/log var/sessions

# Copier les dépendances Composer depuis le build stage
COPY --from=composer_builder /app/vendor /var/www/html/vendor

# Copier explicitement les dossiers Symfony essentiels
COPY bin /var/www/html/bin
COPY config /var/www/html/config
COPY src /var/www/html/src
COPY public /var/www/html/public
COPY migrations /var/www/html/migrations

# Mettre à jour les permissions
RUN chown -R www-data:www-data var/cache var/log var/sessions public

# Variables d'environnement Symfony
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV DATABASE_URL="mysql://root:IlcNzJQOqGRrtpEqpgzVAtCGfTvlagDM@mysql.railway.internal:3306/railway"

# Copier le script entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Exposer le port PHP-FPM
EXPOSE 9000

# Définir l'entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Démarrer PHP-FPM
CMD ["php-fpm"]
