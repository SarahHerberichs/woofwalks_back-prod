# =========================
# Étape 1 : installer les dépendances avec Composer
# =========================
FROM composer:2.4 AS builder

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# =========================
# Étape 2 : image finale avec PHP-FPM + Nginx
# =========================
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Installer dépendances système et extensions PHP + Nginx
RUN apk add --no-cache bash git shadow icu-dev nginx \
    && docker-php-ext-install pdo pdo_mysql intl

# Copier vendor et code
COPY --from=builder /app/vendor /var/www/html/vendor
COPY . /var/www/html
COPY php.ini /usr/local/etc/php/

# Copier migrations et .env
COPY migrations /var/www/html/migrations
COPY .env.railway /var/www/html/.env

# Permissions
RUN mkdir -p var/cache var/cache/lock var/log var/sessions public \
    && chown -R www-data:www-data var

COPY nginx.conf /etc/nginx/nginx.conf

# Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

CMD ["/usr/local/bin/docker-entrypoint.sh"]
