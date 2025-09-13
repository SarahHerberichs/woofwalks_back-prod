FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    zlib-dev \
    libzip-dev \
    oniguruma-dev \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    opcache

# Enable opcache for production optimization (optional for dev, but good practice)
RUN docker-php-ext-enable opcache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Wait for the database service to be available. This is a crucial step to avoid connection errors on startup.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY woofwalks_back/docker/wait-for-it.sh /usr/local/bin/wait-for-it.sh
RUN chmod +x /usr/local/bin/wait-for-it.sh

# Install PHP dependencies from composer.lock
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader

# Copy the rest of the application code
COPY . .

# Clear the cache and install application dependencies
RUN composer install --no-dev --optimize-autoloader

# Run database migrations
CMD ["sh", "-c", "wait-for-it.sh db:3306 --timeout=60 --strict -- php bin/console doctrine:migrations:migrate --no-interaction"]

# Expose the port for php-fpm
EXPOSE 9000
