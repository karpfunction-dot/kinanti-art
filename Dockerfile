FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip curl git libzip-dev zip \
    && docker-php-ext-install zip pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy semua file project
COPY . .

# Install dependency Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permission
RUN chmod -R 775 storage bootstrap/cache

# Generate APP_KEY (jika belum ada)
RUN php artisan key:generate || true

# Expose port (Railway pakai PORT)
CMD php artisan config:clear && php artisan config:cache && php artisan migrate --force || true; php artisan serve --host=0.0.0.0 --port=${PORT}

# Generate APP_KEY (jika belum ada)
RUN php artisan key:generate || true

# Storage
RUN php artisan storage:link || true
