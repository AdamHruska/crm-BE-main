# Use PHP FPM image
FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Install Composer dependencies
RUN composer install

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/sites-available/default

# Ensure the storage and bootstrap/cache directories are writable
RUN chown -R www-data:www-data \
    /var/www/storage \
    /var/www/bootstrap/cache

# PHP-FPM socket permissions
RUN mkdir -p /run/php && chown -R www-data:www-data /run/php

# Expose port 80 and start php-fpm server
EXPOSE 80

# Start Nginx and PHP-FPM
CMD service nginx start && php-fpm