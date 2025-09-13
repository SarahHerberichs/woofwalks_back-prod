# Étape 1: Installer les dépendances avec Composer
FROM composer:2.4 AS builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Étape 2: Image finale
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

# Dépendances système
RUN apk add --no-cache bash git mysql-client shadow \
    && docker-php-ext-install pdo pdo_mysql

# Copier vendor depuis le build
COPY --from=builder /app/vendor /var/www/html/vendor

# Copier tout le code Symfony
COPY . /var/www/html

# Permissions
RUN chown -R www-data:www-data var/cache var/log var/sessions public

# Variables d'environnement
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV DATABASE_URL="mysql://root:IlcNzJQOqGRrtpEqpgzVAtCGfTvlagDM@mysql.railway.internal:3306/railway"

# Entrypoint pour migrations + PHP-FPM
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
