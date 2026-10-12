# Fase 1: compilar assets con Node/Vite
FROM node:22 AS assets
WORKDIR /app
COPY package*.json vite.config.js ./
RUN npm ci
COPY resources ./resources
RUN npm run build

# Fase 2: aplicación PHP
FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql mbstring zip bcmath pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p storage/framework/{sessions,views,cache} \
    && chmod -R 775 storage bootstrap/cache

ENV PHP_CLI_SERVER_WORKERS=4
EXPOSE 8080
CMD php artisan migrate --force && php artisan db:seed --force && php artisan config:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
