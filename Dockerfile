FROM php:8.4-cli

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy files
COPY . .

# Install deps
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy nginx config
RUN mkdir -p /var/run/nginx && echo "daemon off;" >> /etc/nginx/nginx.conf
COPY docker/nginx.conf /etc/nginx/sites-available/default

EXPOSE 80

# Start nginx and php
CMD service nginx start && php -S 0.0.0.0:80 -t public
