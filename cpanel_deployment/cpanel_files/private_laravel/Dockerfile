# Multi-stage Dockerfile for Laravel Hotel Management System
# Stage 1: Build frontend assets
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Copy package files
COPY package*.json ./
COPY vite.config.js ./
COPY tailwind.config.js ./

# Install dependencies
RUN npm ci --only=production

# Copy source files for asset building
COPY resources/ ./resources/
COPY public/ ./public/

# Build assets
RUN npm run build

# Stage 2: PHP Application
FROM php:8.2-fpm-alpine AS php-base

# Install system dependencies
RUN apk add --no-cache \
    curl \
    zip \
    unzip \
    git \
    nginx \
    supervisor \
    sqlite \
    mysql-client \
    postgresql-client \
    redis \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    pcntl \
    opcache \
    bcmath

# Install Redis PHP extension
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create application user
RUN addgroup -g 1000 -S app && \
    adduser -u 1000 -S app -G app

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copy application code
COPY --chown=app:app . .

# Copy built assets from frontend stage
COPY --from=frontend-builder --chown=app:app /app/public/build ./public/build

# Create necessary directories and set permissions
RUN mkdir -p \
    storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    database \
    && chown -R app:app storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache

# Stage 3: Production image
FROM php-base AS production

# Copy configuration files
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create SQLite database if it doesn't exist
RUN touch /var/www/html/database/production.sqlite \
    && chown app:app /var/www/html/database/production.sqlite

# Health check script
COPY docker/health-check.sh /usr/local/bin/health-check
RUN chmod +x /usr/local/bin/health-check

# Switch to app user for security
USER app

# Optimize Laravel for production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Switch back to root for supervisor
USER root

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD /usr/local/bin/health-check

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Stage 4: Development image (optional)
FROM php-base AS development

# Install development dependencies
RUN composer install --optimize-autoloader --no-interaction --no-progress

# Install Xdebug for development
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

# Copy Xdebug configuration
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Don't cache configs in development
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Switch to app user
USER app

# Start PHP-FPM
CMD ["php-fpm"]