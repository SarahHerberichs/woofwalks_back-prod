FROM composer:2.4 AS builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# ======================
# IMAGE FINALE
# ======================
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Install system deps
RUN apk add --no-cache bash git shadow mysql-client icu-dev nginx supervisor \
    && docker-php-ext-install pdo pdo_mysql intl

# Copier vendor
COPY --from=builder /app/vendor /var/www/html/vendor

# Copier code + conf
COPY . /var/www/html
COPY php.ini /usr/local/etc/php/
COPY nginx-prod.conf /etc/nginx/conf.d/default.conf
COPY .env.railway /var/www/html/.env

# Supervisord config
RUN mkdir -p /etc/supervisor.d/
COPY supervisord.conf /etc/supervisor.d/supervisord.ini

# Create dirs
RUN mkdir -p var/cache var/log var/sessions public \
    && chown -R www-data:www-data var /var/www/html

# Env
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV DATABASE_URL="mysql://root:IlcNzJQOqGRrtpEqpgzVAtCGfTvlagDM@mysql.railway.internal:3306/railway"

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor.d/supervisord.ini"]
