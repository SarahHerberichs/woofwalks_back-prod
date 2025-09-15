# Étape 1 : Build vendor
FROM composer:2.4 AS builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Étape 2 : PHP-FPM
FROM php:8.2-fpm-alpine AS php-fpm
WORKDIR /var/www/html
RUN apk add --no-cache bash git shadow mysql-client icu-dev \
    && docker-php-ext-install pdo pdo_mysql intl
COPY --from=builder /app/vendor /var/www/html/vendor
COPY . /var/www/html
COPY php.ini /usr/local/etc/php/
COPY .env.railway /var/www/html/.env
COPY nginx-prod.conf /etc/nginx/conf.d/default.conf
RUN mkdir -p var/cache var/log var/sessions \
    && chown -R www-data:www-data var

# Étape 3 : Nginx + PHP-FPM ensemble
FROM nginx:alpine
WORKDIR /var/www/html
COPY --from=php-fpm /var/www/html /var/www/html

# PHP-FPM
COPY --from=php-fpm /usr/local/etc/php /usr/local/etc/php
COPY --from=php-fpm /usr/local/bin/docker-php-ext-* /usr/local/bin/
COPY --from=php-fpm /usr/local/sbin/php-fpm /usr/local/sbin/php-fpm

# Superviser Nginx + PHP-FPM
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"
