# ── Stage 1: Node — build frontend assets ─────────────────────────────────────
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

COPY resources/ resources/
COPY vite.config.js ./
COPY public/ public/

RUN npm run build

# ── Stage 2: Composer — install PHP dependencies ──────────────────────────────
FROM composer:2.8 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

# ── Stage 3: Final image ───────────────────────────────────────────────────────
FROM php:8.3-fpm-alpine

LABEL maintainer="sijad-lara12"

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    mysql-client \
    bash

# Install PHP extensions
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    gd \
    zip \
    mbstring \
    bcmath \
    opcache \
    intl \
    exif \
    pcntl

# Install Redis extension (opsional, jika nanti butuh Redis)
# RUN pecl install redis && docker-php-ext-enable redis

# Copy custom PHP & OPcache configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy Nginx configuration
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copy Supervisor configuration (mengelola nginx + php-fpm dalam 1 container)
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Copy aplikasi
COPY --chown=www-data:www-data . .

# Copy vendor dari stage composer
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor

# Copy built assets dari stage frontend
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

# Buat direktori storage dan set permission
RUN mkdir -p \
    storage/app/private \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
