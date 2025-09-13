# =========================
# Étape 1 : Installer les dépendances avec Composer
# =========================
FROM composer:2.4 AS builder

WORKDIR /app

# Copier les fichiers Composer
COPY composer.json composer.lock ./

# Installer les dépendances de prod sans dev
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# =========================
# Étape 2 : Image finale pour prod
# =========================
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Installer les dépendances système et extensions PHP
RUN apk add --no-cache bash git shadow mysql-client \
    && docker-php-ext-install pdo pdo_mysql

# Copier vendor depuis le build stage
COPY --from=builder /app/vendor /var/www/html/vendor

# Copier tout le code Symfony
COPY . /var/www/html

# Copier explicitement le dossier des migrations pour s'assurer qu'il est bien inclus
# Correction du chemin en le rendant relatif au contexte de build
COPY migrations /var/www/html/src/Migrations


# Créer les répertoires nécessaires et mettre les permissions
RUN mkdir -p var/cache var/log var/sessions public \
    && chown -R www-data:www-data var/cache var/log var/sessions public

# Variables d'environnement
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV DATABASE_URL="mysql://root:IlcNzJQOqGRrtpEqpgzVAtCGfTvlagDM@mysql.railway.internal:3306/railway"

# Copier l’entrypoint et le rendre exécutable
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Définir le point d'entrée pour l'image
ENTRYPOINT ["docker-entrypoint.sh"]

# Démarrer le serveur PHP-FPM
CMD ["php-fpm"]
