FROM php:8.2-cli

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
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy files
COPY . .

# Install deps without scripts to avoid package:discover issues
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

# Clear cache and set permissions
RUN composer dump-autoload -o --no-dev && \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Nginx config
RUN mkdir -p /var/run/nginx && \
    echo "daemon off;" >> /etc/nginx/nginx.conf && \
    cp /var/www/docker/nginx.conf /etc/nginx/sites-available/default

# Supervisor config
COPY /var/www/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
