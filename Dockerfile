# syntax=docker/dockerfile:1

############################
# Composer dependencies
############################
FROM composer:2 AS composer
WORKDIR /app

# Copy only composer files to leverage layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies without dev and without running scripts during build
RUN composer install --no-dev --no-interaction --prefer-dist --no-ansi --no-scripts --no-progress

############################
# Frontend assets (Vite)
############################
FROM node:20-bullseye-slim AS node
WORKDIR /app

COPY package*.json ./
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Use npm ci when lockfile exists, otherwise fall back to npm install
RUN ( [ -f package-lock.json ] && npm ci --no-audit --no-fund ) || npm install --no-audit --no-fund \
    && npm run build

############################
# Final image: Apache + PHP 8.3
############################
FROM php:8.3-apache

# Install system dependencies and PHP extensions required by Laravel
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       git \
       unzip \
       libzip-dev \
       libpng-dev \
       libjpeg-dev \
       libfreetype6-dev \
       libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath exif pcntl gd zip \
    && a2enmod rewrite headers env \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Bring in vendor and built assets from previous stages
COPY --from=composer /app/vendor /var/www/html/vendor
COPY --from=node /app/public/build /var/www/html/public/build

# Configure Apache to serve the Laravel public directory
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf "<Directory /var/www/html/public>\n\tAllowOverride All\n</Directory>\n" > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Ensure correct permissions for storage and cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && find /var/www/html/storage -type d -exec chmod 775 {} \; \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Copy entrypoint that configures Apache to listen on Render's $PORT and warms caches when possible
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Default port (Render will set PORT env var)
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]


