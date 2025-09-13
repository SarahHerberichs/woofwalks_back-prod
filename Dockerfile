# Étape 1: Construire l'application (build stage)
FROM composer:2.4 as composer_builder

# Définir le répertoire de travail
WORKDIR /app

# Copier les fichiers Composer
COPY composer.json composer.lock ./

# Exécuter l'installation des dépendances de production sans lancer les scripts
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Étape 2: Construire l'image finale de production
FROM php:8.2-fpm-alpine

# Définir le répertoire de travail
WORKDIR /var/www/html

# Installer les dépendances système et les extensions PHP requises
RUN apk add --no-cache \
    git \
    mysql-client \
    shadow \
    bash \
    # ... autres dépendances nécessaires pour la prod
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /tmp/pear

# Créer l'utilisateur et le groupe www-data
RUN usermod -u 1000 www-data

# Créer les répertoires nécessaires pour Symfony
RUN mkdir -p var/cache var/log var/sessions

# Copier les dépendances installées par Composer depuis le "build stage"
COPY --from=composer_builder /app/vendor /var/www/html/vendor

# Copier le reste du code de l'application
COPY . /var/www/html

# Mettre à jour les permissions des répertoires
RUN chown -R www-data:www-data var/cache var/log var/sessions public
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV DATABASE_URL="mysql://root:IlcNzJQOqGRrtpEqpgzVAtCGfTvlagDM@mysql.railway.internal:3306/railway"
# Étape 1: Construire l'application (build stage)
FROM composer:2.4 as composer_builder

# Définir le répertoire de travail
WORKDIR /app

# Copier les fichiers Composer
COPY composer.json composer.lock ./

# Exécuter l'installation des dépendances de production sans lancer les scripts
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Étape 2: Construire l'image finale de production
FROM php:8.2-fpm-alpine

# Définir le répertoire de travail
WORKDIR /var/www/html

# Installer les dépendances système et les extensions PHP requises
RUN apk add --no-cache \
    git \
    mysql-client \
    shadow \
    bash \
    # ... autres dépendances nécessaires pour la prod
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /tmp/pear

# Créer l'utilisateur et le groupe www-data
RUN usermod -u 1000 www-data

# Créer les répertoires nécessaires pour Symfony
RUN mkdir -p var/cache var/log var/sessions

# Copier les dépendances installées par Composer depuis le "build stage"
COPY --from=composer_builder /app/vendor /var/www/html/vendor

# Copier le reste du code de l'application
COPY . /var/www/html

# Mettre à jour les permissions des répertoires
RUN chown -R www-data:www-data var/cache var/log var/sessions public
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV DATABASE_URL="mysql://root:IlcNzJQOqGRrtpEqpgzVAtCGfTvlagDM@mysql.railway.internal:3306/railway"
# Exposer le port du serveur
EXPOSE 9000

# Démarrer le serveur PHP-FPM
CMD ["php-fpm"]
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh


# Exposer le port du serveur
EXPOSE 9000

ENTRYPOINT ["docker-entrypoint.sh"]

# Démarrer le serveur PHP-FPM
CMD ["php-fpm"]
